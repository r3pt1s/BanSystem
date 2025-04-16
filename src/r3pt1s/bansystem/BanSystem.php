<?php

namespace r3pt1s\bansystem;

use alemiz\sga\StarGateAtlantis;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
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
use r3pt1s\bansystem\command\util\ChatMuteCommand;
use r3pt1s\bansystem\command\util\FreezeCommand;
use r3pt1s\bansystem\command\util\SpectateCommand;
use r3pt1s\bansystem\command\util\VanishCommand;
use r3pt1s\bansystem\command\warn\ClearWarnsCommand;
use r3pt1s\bansystem\command\warn\WarnCommand;
use r3pt1s\bansystem\command\warn\WarnsCommand;
use r3pt1s\bansystem\event\kick\PlayerKickEvent;
use r3pt1s\bansystem\exception\LanguageFileNotFoundException;
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
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanSystem extends PluginBase {
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
    private bool $chatMuted = false;
    private array $frozenPlayers = [];
    private array $spectatingPlayers = [];
    private array $vanishedPlayers = [];

    /**
     * @throws LanguageFileNotFoundException
     */
    protected function onEnable(): void {
        self::setInstance($this);

        if (file_exists($this->getDataFolder() . "config.yml")) $this->checkConfig();

        $this->saveDefaultConfig();
        $this->saveResource("lang/de_DE.json");
        $this->saveResource("lang/en_US.json");
        $this->saveResource("ids.json");

        $this->configuration = new Configuration(new Config($this->getDataFolder() . "config.yml", Config::YAML), new Config($this->getDataFolder() . "ids.json", Config::YAML));
        $this->provider = match (strtolower($this->configuration->getProvider())) {
            "mysql" => new MySQLProvider(),
            default => new JSONProvider()
        };
        $this->provider->load();

        Language::get()->load();

        $this->banManager = new BanManager();
        $this->muteManager = new MuteManager();
        $this->warnManager = new WarnManager();
        $this->notifyManager = new NotifyManager();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        foreach ([
            "bansystem.receive.notify",
            "bansystem.vanish.see",
            "bansystem.bypass.ban", "bansystem.bypass.mute", "bansystem.bypass.kick", "bansystem.bypass.freeze", "bansystem.bypass.chat_mute",
            "bansystem.command.kick",
            "bansystem.command.warn", "bansystem.command.warns", "bansystem.command.clearwarns",
            "bansystem.command.notify",
            "bansystem.command.ban", "bansystem.command.banids", "bansystem.command.baninfo", "bansystem.command.banlist", "bansystem.command.banlog", "bansystem.command.editban", "bansystem.command.tempban", "bansystem.command.unban",
            "bansystem.command.mute", "bansystem.command.muteids", "bansystem.command.muteinfo", "bansystem.command.mutelist", "bansystem.command.mutelog", "bansystem.command.editmute", "bansystem.command.tempmute", "bansystem.command.unmute",
            "bansystem.command.chat_mute", "bansystem.command.freeze", "bansystem.command.vanish", "bansystem.command.spectate"
        ] as $permission) {
            DefaultPermissions::registerPermission(new Permission($permission), [PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR)]);
        }

        foreach (array_merge(["kick"], (Configuration::getInstance()->isBanSystemEnabled() ? ["ban", "pardon", "banlist"] : [])) as $cmd) {
            if (($command = $this->getServer()->getCommandMap()->getCommand($cmd)) !== null) $this->getServer()->getCommandMap()->unregister($command);
        }

        $banCommands = [];
        $muteCommands = [];
        $warnCommands = [];
        $staffCommands = [];

        if (Configuration::getInstance()->isBanSystemEnabled()) $banCommands = [new BanCommand(), new BanIdsCommand(), new BanInfoCommand(), new BanListCommand(), new BanLogCommand(), new EditBanCommand(), new TempBanCommand(), new UnbanCommand()];
        if (Configuration::getInstance()->isMuteSystemEnabled()) $muteCommands = [new MuteCommand(), new MuteIdsCommand(), new MuteInfoCommand(), new MuteListCommand(), new MuteLogCommand(), new EditMuteCommand(), new TempMuteCommand(), new UnmuteCommand()];
        if (Configuration::getInstance()->isWarnSystemEnabled()) $warnCommands = [new WarnCommand(), new WarnsCommand(), new ClearWarnsCommand()];
        if (Configuration::getInstance()->isStaffToolsEnabled()) $staffCommands = [new ChatMuteCommand(), new FreezeCommand(), new SpectateCommand(), new VanishCommand()];
        $generalCommands = [new KickCommand(), new NotifyCommand()];

        $this->getServer()->getCommandMap()->registerAll("BanSystem", array_merge($banCommands, $muteCommands, $warnCommands, $staffCommands, $generalCommands));

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            BanManager::getInstance()->check();
            MuteManager::getInstance()->check();
            $this->checkVanishVisibility();
        }), 20);

        if ($this->getServer()->getPluginManager()->getPlugin("StarGate-Atlantis") === null) {
            $this->getLogger()->notice(Language::get()->translate(LanguageKeys::WATERDOGPE_INFO));
        } else $this->initStarGate();
    }

    private function checkConfig(): void {
        $c = yaml_parse(file_get_contents($this->getDataFolder() . "config.yml"));
        if (!isset($c["features"])) {
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "old_config.yml");
            $this->getLogger()->notice("Your config is not on the latest version, renaming it to 'old_config.yml' and creating a new one...");
        }
    }

    private function initStarGate(): void {
        $this->usingStarGate = true;
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::BANS_SYNC_PACKET, new BansSyncPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::MUTES_SYNC_PACKET, new MutesSyncPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::PLAYER_KICK_PACKET, new PlayerKickPacket());
        StarGateAtlantis::getInstance()->getDefaultClient()->getProtocolCodec()->registerPacket(BanSystemPackets::NOTIFY_PACKET, new NotifyPacket());
    }

    public function muteChat(string $player): void {
        if ($this->chatMuted) return;
        $this->chatMuted = true;
        Server::getInstance()->broadcastMessage(Language::get()->translate(LanguageKeys::CHAT_MUTE_MUTED_GLOBAL, $player));
    }

    public function unmuteChat(string $player): void {
        if (!$this->chatMuted) return;
        $this->chatMuted = false;
        Server::getInstance()->broadcastMessage(Language::get()->translate(LanguageKeys::CHAT_MUTE_UNMUTED_GLOBAL, $player));
    }

    public function freezePlayer(Player $player, Player $moderator): void {
        if ($this->isFrozen($player)) return;
        $this->frozenPlayers[] = $player->getName();
        $player->setNoClientPredictions();

        $player->sendMessage(Language::get()->translate(LanguageKeys::FREEZE_FROZEN));
        $moderator->sendMessage(Language::get()->translate(LanguageKeys::FREEZE_FREEZE_SUCCESS, $player->getName()));
    }

    public function releasePlayer(Player $player, Player $moderator): void {
        if (!$this->isFrozen($player)) return;
        if (in_array($player->getName(), $this->frozenPlayers)) unset($this->frozenPlayers[array_search($player->getName(), $this->frozenPlayers)]);
        $player->setNoClientPredictions(false);

        $player->sendMessage(Language::get()->translate(LanguageKeys::FREEZE_RELEASED));
        $moderator->sendMessage(Language::get()->translate(LanguageKeys::FREEZE_RELEASE_SUCCESS, $player->getName()));
    }

    public function spectatePlayer(Player $player, Player $moderator): void {
        if ($this->isSpectating($moderator)) return;
        $this->spectatingPlayers[$moderator->getName()] = [$moderator->getLocation(), $moderator->getGamemode()];
        $moderator->setGamemode(GameMode::SPECTATOR);
        $moderator->teleport($player->getLocation());

        $moderator->sendMessage(Language::get()->translate(LanguageKeys::SPECTATE_START, $player->getName()));
    }

    public function stopSpectating(Player $moderator, bool $forced = false): void {
        if (!$this->isSpectating($moderator)) return;
        [$oldPos, $oldGamemode] = $this->spectatingPlayers[$moderator->getName()];
        unset($this->spectatingPlayers[$moderator->getName()]);
        if (!$forced) {
            $moderator->setGamemode($oldGamemode);
            $moderator->teleport($oldPos);

            $moderator->sendMessage(Language::get()->translate(LanguageKeys::SPECTATE_STOP));
        }
    }

    public function vanishPlayer(Player $player): void {
        if ($this->isVanished($player)) return;
        $this->vanishedPlayers[] = $player->getName();

        $player->sendMessage(Language::get()->translate(LanguageKeys::VANISH_VANISHED));
    }

    public function showPlayer(Player $player): void {
        if (!$this->isVanished($player)) return;
        unset($this->vanishedPlayers[array_search($player->getName(), $this->vanishedPlayers)]);

        $player->sendMessage(Language::get()->translate(LanguageKeys::VANISH_SHOWN));
    }

    public function checkVanishVisibility(): void {
        if (count($this->vanishedPlayers) == 0) return;
        foreach ($this->vanishedPlayers as $i => $player) {
            $player = Server::getInstance()->getPlayerExact($player);
            if ($player === null) {
                unset($this->vanishedPlayers[$i]);
                continue;
            }

            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                if ($player === $onlinePlayer) continue;
                if ($onlinePlayer->hasPermission("bansystem.vanish.see")) {
                    if (!$onlinePlayer->canSee($player)) {
                        try {
                            $onlinePlayer->getNetworkSession()->sendDataPacket(PlayerListPacket::add(
                                [PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), (new LegacySkinAdapter())->toSkinData($player->getSkin()), $player->getXuid())]
                            ));
                            $onlinePlayer->showPlayer($player);
                        } catch (JsonException $e) {
                            $this->getLogger()->logException($e);
                        }
                    }
                } else {
                    if ($onlinePlayer->canSee($player)) {
                        $onlinePlayer->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($player->getUniqueId())]));
                        $onlinePlayer->hidePlayer($player);
                    }
                }
            }
        }
    }

    public function kickPlayer(Player $player, CommandSender $moderator, string $reason = ""): int {
        ($ev = new PlayerKickEvent($player, $moderator, $reason))->call();
        if ($ev->isCancelled()) return self::FAILED_CANCELLED;

        if ($player->hasPermission("bansystem.bypass.kick")) return self::FAILED_CANT;

        $reason = Language::get()->translate(LanguageKeys::SCREEN_KICK, ($ev->getReason() == "" ? "§c/" : $ev->getReason()));
        if ($this->usingStarGate) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(PlayerKickPacket::create($player->getName(), $reason));
        else $player->kick($reason);

        NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_KICK, $player->getName(), $moderator->getName(), ($ev->getReason() == "" ? "§c/" : $ev->getReason())));

        return self::SUCCESS;
    }

    public function isChatMuted(): bool {
        return $this->chatMuted;
    }

    public function isFrozen(Player $player): bool {
        return in_array($player->getName(), $this->frozenPlayers) || $player->hasNoClientPredictions();
    }

    public function isSpectating(Player $player): bool {
        return isset($this->spectatingPlayers[$player->getName()]);
    }

    public function isVanished(Player $player): bool {
        return in_array($player->getName(), $this->vanishedPlayers);
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