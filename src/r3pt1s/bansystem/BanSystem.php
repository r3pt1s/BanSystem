<?php

namespace r3pt1s\bansystem;

use alemiz\sga\StarGateAtlantis;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\command\ban\BanCommand;
use r3pt1s\bansystem\command\ban\BanIdsCommand;
use r3pt1s\bansystem\command\ban\BanInfoCommand;
use r3pt1s\bansystem\command\ban\BanListCommand;
use r3pt1s\bansystem\command\ban\BanLogCommand;
use r3pt1s\bansystem\command\ban\EditBanCommand;
use r3pt1s\bansystem\command\ban\TempBanCommand;
use r3pt1s\bansystem\command\ban\UnbanCommand;
use r3pt1s\bansystem\command\KickCommand;
use r3pt1s\bansystem\command\mute\EditMuteCommand;
use r3pt1s\bansystem\command\mute\MuteCommand;
use r3pt1s\bansystem\command\mute\MuteIdsCommand;
use r3pt1s\bansystem\command\mute\MuteInfoCommand;
use r3pt1s\bansystem\command\mute\MuteListCommand;
use r3pt1s\bansystem\command\mute\MuteLogCommand;
use r3pt1s\bansystem\command\mute\TempMuteCommand;
use r3pt1s\bansystem\command\mute\UnmuteCommand;
use r3pt1s\bansystem\command\notify\NotifyCommand;
use r3pt1s\bansystem\command\warn\ClearWarnsCommand;
use r3pt1s\bansystem\command\warn\WarnCommand;
use r3pt1s\bansystem\command\warn\WarnsCommand;
use r3pt1s\bansystem\event\kick\PlayerKickEvent;
use r3pt1s\bansystem\listener\EventListener;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use r3pt1s\bansystem\manager\warn\WarnManager;
use r3pt1s\bansystem\network\BansSyncPacket;
use r3pt1s\bansystem\network\BanSystemPackets;
use r3pt1s\bansystem\network\MutesSyncPacket;
use r3pt1s\bansystem\network\NotifyPacket;
use r3pt1s\bansystem\network\PlayerKickPacket;
use r3pt1s\bansystem\provider\JSONProvider;
use r3pt1s\bansystem\provider\MySQLProvider;
use r3pt1s\bansystem\provider\Provider;
use r3pt1s\bansystem\util\Configuration;

class BanSystem extends PluginBase {
    use SingletonTrait;

    public static function getPrefix(): string {
        return self::getInstance()->getConfiguration()->getPrefix();
    }

    public const NO_PERMS = "§cYou don't have the permission to use this command.";

    public const SUCCESS = 0;
    public const FAILED_ALREADY = 1;
    public const FAILED_CANCELLED = 2;
    public const FAILED_NOT = 3;
    public const FAILED_CANT = 4;

    private Configuration $configuration;
    private Provider $provider;
    private BanManager $banManager;
    private MuteManager $muteManager;
    private WarnManager $warnManager;
    private NotifyManager $notifyManager;
    private bool $usingStarGate = false;

    protected function onEnable(): void {
        self::setInstance($this);

        $this->saveDefaultConfig();
        $this->saveResource("ids.json");

        $this->configuration = new Configuration(new Config($this->getDataFolder() . "config.yml", Config::YAML), new Config($this->getDataFolder() . "ids.json", Config::YAML));
        $this->provider = match (strtolower($this->configuration->getProvider())) {
            "mysql" => new MySQLProvider(),
            default => new JSONProvider()
        };
        $this->provider->load();

        $this->banManager = new BanManager();
        $this->muteManager = new MuteManager();
        $this->warnManager = new WarnManager();
        $this->notifyManager = new NotifyManager();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        foreach ([
            "bansystem.receive.notify",
            "bansystem.bypass.ban",
            "bansystem.bypass.mute",
            "bansystem.bypass.kick",
            "bansystem.command.kick",
            "bansystem.command.warn", "bansystem.command.warns", "bansystem.command.clearwarns",
            "bansystem.command.notify",
            "bansystem.command.ban", "bansystem.command.banids", "bansystem.command.baninfo", "bansystem.command.banlist", "bansystem.command.banlog", "bansystem.command.editban", "bansystem.command.tempban", "bansystem.command.unban",
            "bansystem.command.mute", "bansystem.command.muteids", "bansystem.command.muteinfo", "bansystem.command.mutelist", "bansystem.command.mutelog", "bansystem.command.editmute", "bansystem.command.tempmute", "bansystem.command.unmute"
        ] as $permission) {
            DefaultPermissions::registerPermission(new Permission($permission), [PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR)]);
        }

        foreach (["ban", "pardon", "kick", "banlist"] as $cmd) {
            if (($command = $this->getServer()->getCommandMap()->getCommand($cmd)) !== null) $this->getServer()->getCommandMap()->unregister($command);
        }

        $this->getServer()->getCommandMap()->registerAll("BanSystem", [
            new KickCommand(),
            new WarnCommand(), new WarnsCommand(), new ClearWarnsCommand(),
            new NotifyCommand(),
            new BanCommand(), new BanIdsCommand(), new BanInfoCommand(), new BanListCommand(), new BanLogCommand(), new EditBanCommand(), new TempBanCommand(), new UnbanCommand(),
            new MuteCommand(), new MuteIdsCommand(), new MuteInfoCommand(), new MuteListCommand(), new MuteLogCommand(), new EditMuteCommand(), new TempMuteCommand(), new UnmuteCommand()
        ]);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            BanManager::getInstance()->check();
            MuteManager::getInstance()->check();
        }), 20);

        if ($this->getServer()->getPluginManager()->getPlugin("StarGate-Atlantis") === null) {
            $this->getLogger()->notice("§c1If you are using WaterdogPE, consider using §bStarGate §cto sync bans and mutes on the entire network.");
        } else $this->initStarGate();
    }

    private function initStarGate(): void {
        $this->usingStarGate = true;
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::BANS_SYNC_PACKET, new BansSyncPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::MUTES_SYNC_PACKET, new MutesSyncPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::PLAYER_KICK_PACKET, new PlayerKickPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::NOTIFY_PACKET, new NotifyPacket());
    }

    public function kickPlayer(Player $player, CommandSender $moderator, string $reason = "No reason provided"): int {
        ($ev = new PlayerKickEvent($player, $moderator, $reason))->call();
        if ($ev->isCancelled()) return self::FAILED_CANCELLED;

        if ($player->hasPermission("bansystem.bypass.mute")) return self::FAILED_CANT;

        $reason = "§8» §cYou have been §lkicked §r§8«\n§8» §7Reason: §e" . ($ev->getReason() == "" ? "No reason provided" : $ev->getReason());
        if ($this->usingStarGate) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(PlayerKickPacket::create($player->getName(), $reason));
        else $player->kick($reason);

        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $player->getName() . " §7has been §ckicked §7by §e" . $moderator->getName() . "§7.");
        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Reason: §e" . ($ev->getReason() == "" ? "No reason provided" : $ev->getReason()));

        return self::SUCCESS;
    }

    public function isUsingStarGate(): bool {
        return $this->usingStarGate;
    }

    public function getNotifyManager(): NotifyManager {
        return $this->notifyManager;
    }

    public function getWarnManager(): WarnManager {
        return $this->warnManager;
    }

    public function getMuteManager(): MuteManager {
        return $this->muteManager;
    }

    public function getBanManager(): BanManager {
        return $this->banManager;
    }

    public function getProvider(): Provider {
        return $this->provider;
    }

    public function getConfiguration(): Configuration {
        return $this->configuration;
    }

    public static function getInstance(): ?self {
        return self::$instance ?? null;
    }
}