<?php

namespace r3pt1s\BanSystem;

use r3pt1s\BanSystem\command\ban\BanCommand;
use r3pt1s\BanSystem\command\ban\BanIdsCommand;
use r3pt1s\BanSystem\command\ban\BanInfoCommand;
use r3pt1s\BanSystem\command\ban\BanListCommand;
use r3pt1s\BanSystem\command\ban\BanLogCommand;
use r3pt1s\BanSystem\command\ban\EditBanCommand;
use r3pt1s\BanSystem\command\ban\TempBanCommand;
use r3pt1s\BanSystem\command\ban\UnBanCommand;
use r3pt1s\BanSystem\command\BanSystemCommand;
use r3pt1s\BanSystem\command\kick\KickCommand;
use r3pt1s\BanSystem\command\mute\EditMuteCommand;
use r3pt1s\BanSystem\command\mute\MuteCommand;
use r3pt1s\BanSystem\command\mute\MuteIdsCommand;
use r3pt1s\BanSystem\command\mute\MuteInfoCommand;
use r3pt1s\BanSystem\command\mute\MuteListCommand;
use r3pt1s\BanSystem\command\mute\MuteLogCommand;
use r3pt1s\BanSystem\command\mute\TempMuteCommand;
use r3pt1s\BanSystem\command\mute\UnMuteCommand;
use r3pt1s\BanSystem\command\notify\NotifyCommand;
use r3pt1s\BanSystem\command\warn\ResetWarnsCommand;
use r3pt1s\BanSystem\command\warn\WarnCommand;
use r3pt1s\BanSystem\command\warn\WarnsCommand;
use r3pt1s\BanSystem\database\Database;
use r3pt1s\BanSystem\listener\EventListener;
use r3pt1s\BanSystem\manager\ban\BanManager;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use r3pt1s\BanSystem\manager\warn\WarnManager;
use r3pt1s\BanSystem\task\CheckTask;
use r3pt1s\BanSystem\task\CheckUpdateTask;
use r3pt1s\BanSystem\task\PlayerKickTask;
use r3pt1s\BanSystem\utils\Configuration;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class BanSystem extends PluginBase {

    const NO_PERMS = "§cYou do not have the permission to use the command!";
    public static float $VERSION = 2.0;

    private static self $instance;
    private Configuration $configuration;
    private BanManager $banManager;
    private MuteManager $muteManager;
    private WarnManager $warnManager;
    private NotifyManager $notifyManager;
    private ?Database $database = null;

    protected function onEnable(): void {
        self::$instance = $this;

        $this->saveResource("config.yml");
        $this->saveResource("ids.json");

        $this->configuration = new Configuration(new Config($this->getDataFolder() . "config.yml", 2), new Config($this->getDataFolder() . "ids.json", 1));
        $this->banManager = new BanManager();
        $this->muteManager = new MuteManager();
        $this->warnManager = new WarnManager();
        $this->notifyManager = new NotifyManager();

        if ($this->configuration->getProvider() == "mysql") {
            $this->database = new Database();
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new PlayerKickTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new CheckTask(), 20);

        $this->registerPermission("bansystem.kick.command", "bansystem.notify.command", "bansystem.warn.command", "bansystem.warns.command", "bansystem.resetwarns.command");
        $this->registerPermission("bansystem.ban.command", "bansystem.tempban.command", "bansystem.unban.command", "bansystem.banids.command", "bansystem.banlist.command", "bansystem.baninfo.command", "bansystem.editban.command", "bansystem.banlog.command");
        $this->registerPermission("bansystem.mute.command", "bansystem.tempmute.command", "bansystem.unmute.command", "bansystem.muteids.command", "bansystem.mutelist.command", "bansystem.muteinfo.command", "bansystem.editmute.command", "bansystem.mutelog.command");
        $this->registerPermission("bansystem.notify.receive", "bansystem.ban.bypass", "bansystem.mute.bypass");

        $this->unregisterCommand("kick", "ban", "pardon", "banlist");
        $this->getServer()->getCommandMap()->registerAll("banSystem", [
            new BanSystemCommand("bansystem", "Showw informations about the plugin", "/bansystem", ["bansysteminfo"]),
            new KickCommand("kick", "Kick a player", "/kick <player> <reason>"),
            new NotifyCommand("notify", "Activate notifications", "/notify"),
            new WarnCommand("warn", "Warn a player", "/warn <player> <reason>"),
            new WarnsCommand("warns", "See the warns of an player", "/warns <player>"),
            new ResetWarnsCommand("resetwarns", "Reset the warns of a player", "/resetwarns <player>"),
            new BanCommand("ban", "Ban a player", "/ban <player> <banId>"),
            new TempBanCommand("tempban", "TempBan a player", "/tempban <player> <reason> <duration>"),
            new UnBanCommand("unban", "Unban a player", "/unban <player>"),
            new BanIdsCommand("banids", "See all valid banids", "/banids"),
            new BanListCommand("banlist", "See all banned players", "/banlist"),
            new BanInfoCommand("baninfo", "See the ban information about a player", "/baninfo <player>"),
            new EditBanCommand("editban", "Edit a ban of a player", "/editban <player> <add | sub> <time>"),
            new BanLogCommand("banlog", "See the banlogs of a player", "/banlog <player>"),
            new MuteCommand("mute", "Mute a player", "/mute <player> <muteId>"),
            new TempMuteCommand("tempmute", "TempMute a player", "/tempmute <player> <reason> <duration>"),
            new UnMuteCommand("unmute", "Unmute  a player", "/unmute <player>"),
            new MuteIdsCommand("muteids", "See all valid muteids", "/muteids"),
            new MuteListCommand("mutelist", "See all muted players", "/mutelist"),
            new MuteInfoCommand("muteinfo", "See the mute information about a player", "/muteinfo <player>"),
            new EditMuteCommand("editmute", "Edit a mute of a player", "/editmute <player> <add | sub> <time>"),
            new MuteLogCommand("mutelog", "See the mutelogs of a player", "/mutelog <player>")
        ]);

        $this->getLogger()->info(self::getPrefix() . "§aLoaded!");
        $this->getLogger()->info(self::getPrefix() . "§7Version: §e" . $this->getDescription()->getVersion());
        $this->getLogger()->info(self::getPrefix() . "§7Developer(s): §e" . implode("§8, §e", $this->getDescription()->getAuthors()));
        $this->getLogger()->info(self::getPrefix() . "§7Provider: §b" . strtoupper($this->getConfiguration()->getProvider()));
        $this->getLogger()->info(self::getPrefix() . "§7Checking for updates...");
        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask());
    }

    private function registerPermission(string... $permissions) {
        $operator = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
        if ($operator !== null) {
            foreach ($permissions as $permission) DefaultPermissions::registerPermission(new Permission($permission), [$operator]);
        }
    }

    private function unregisterCommand(string... $commandNames) {
        foreach ($commandNames as $commandName) {
            if (($command = $this->getServer()->getCommandMap()->getCommand($commandName)) !== null) $this->getServer()->getCommandMap()->unregister($command);
        }
    }

    public function getConfiguration(): Configuration {
        return $this->configuration;
    }

    public function getBanManager(): BanManager {
        return $this->banManager;
    }

    public function getMuteManager(): MuteManager {
        return $this->muteManager;
    }

    public function getWarnManager(): WarnManager {
        return $this->warnManager;
    }

    public function getNotifyManager(): NotifyManager {
        return $this->notifyManager;
    }

    public function getDatabase(): ?Database {
        return $this->database;
    }

    public static function getPrefix(): string {
        return self::getInstance()->getConfiguration()->getPrefix();
    }

    public static function getInstance(): BanSystem {
        return self::$instance;
    }
}