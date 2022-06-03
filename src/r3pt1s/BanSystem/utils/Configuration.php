<?php

namespace r3pt1s\BanSystem\utils;

use r3pt1s\BanSystem\BanSystem;
use pocketmine\Server;
use pocketmine\utils\Config;

class Configuration {

    private const VALID_ACTIONS = [
        "ban", "mute", "kick", "warn"
    ];

    private Config $config;
    private Config $idsFile;
    private string $prefix;
    private string $mutePath;
    private string $banPath;
    private string $warnPath;
    private string $infoPath;
    private int $maxWarns;
    private string $maxWarnsAction;
    private string $maxWarnsActionReason;
    private bool $makeBanMuteLogs;
    private array $blockedCommands;
    private array $banIds;
    private array $muteIds;

    public function __construct(Config $config, Config $idsFile) {
        $this->config = $config;
        $this->idsFile = $idsFile;

        $this->load();
    }
    
    private function load() {
        if ($this->config->exists("prefix")) {
            $this->prefix = $this->config->get("prefix");
        } else $this->prefix = "§c§lBanSystem §r§8» §7";

        if ($this->config->exists("mute-path")) {
            if (@file_exists($this->config->get("mute-path"))) $this->mutePath = $this->config->get("mute-path");
            else $this->mutePath = BanSystem::getInstance()->getDataFolder();
        } else $this->mutePath = BanSystem::getInstance()->getDataFolder();

        if ($this->config->exists("ban-path")) {
            if (@file_exists($this->config->get("ban-path"))) $this->banPath = $this->config->get("ban-path");
            else $this->banPath = BanSystem::getInstance()->getDataFolder();
        } else $this->banPath = BanSystem::getInstance()->getDataFolder();

        if ($this->config->exists("warn-path")) {
            if (@file_exists($this->config->get("warn-path"))) $this->warnPath = $this->config->get("warn-path");
            else $this->warnPath = BanSystem::getInstance()->getDataFolder();
        } else $this->warnPath = BanSystem::getInstance()->getDataFolder();

        if ($this->config->exists("info-path")) {
            if (@file_exists($this->config->get("info-path"))) $this->infoPath = $this->config->get("info-path");
            else $this->infoPath = BanSystem::getInstance()->getDataFolder();
        } else $this->infoPath = BanSystem::getInstance()->getDataFolder();

        if ($this->config->exists("max-warns")) {
            if (is_numeric($this->config->get("max-warns"))) {
                if (intval($this->config->get("max-warns")) > 0) $this->maxWarns = intval($this->config->get("max-warns"));
                else $this->maxWarns = -1;
            } else $this->maxWarns = 3;
        } else $this->maxWarns = 3;

        if ($this->config->exists("max-warns-action")) {
            if (in_array($this->config->get("max-warns-action"), self::VALID_ACTIONS)) {
                if (strtolower($this->config->get("max-warns-action")) !== "warn") {
                    $this->maxWarnsAction = strtolower($this->config->get("max-warns-action"));
                } else $this->maxWarnsAction = "ban";
            } else $this->maxWarnsAction = "ban";
        } else $this->maxWarnsAction = "ban";

        if ($this->config->exists("max-warns-action-reason")) {
            $this->maxWarnsActionReason = $this->config->get("max-warns-action-reason");
        } else $this->maxWarnsActionReason = "Warns Limit reached.";

        if ($this->config->exists("make-ban-mute-logs")) {
            $this->makeBanMuteLogs = boolval($this->config->get("make-ban-mute-logs"));
        } else $this->makeBanMuteLogs = true;

        if ($this->config->exists("blocked-commands")) {
            $blockedCommands = [];
            foreach ($this->config->get("blocked-commands") ?? [] as $command) {
                $blockedCommands[] = $command;
            }
            $this->blockedCommands = $blockedCommands;
        } else $this->blockedCommands = [];

        $this->loadIds();

        if (!@file_exists($this->infoPath . "/banlogs/")) @mkdir($this->infoPath . "/banlogs/");
        if (!@file_exists($this->infoPath . "/mutelogs/")) @mkdir($this->infoPath . "/mutelogs/");
    }

    private function loadIds() {
        foreach ($this->idsFile->get("banIds") ?? [] as $data) {
            if (isset($data["id"]) && isset($data["reason"]) && isset($data["duration"])) {
                if (is_numeric($data["id"])) {
                    if (isset($this->banIds[intval($data["id"])])) {
                        Server::getInstance()->getLogger()->error("§cThe banid §e" . intval($data["id"]) . " §cis already registered.");
                        continue;
                    }

                    if (is_string($data["duration"])) {
                        $isDurationValid = false;
                        if ($data["duration"] == "-1") $isDurationValid = true;
                        else if (Utils::convertStringToDateFormat($data["duration"]) !== null) $isDurationValid = true;

                        if ($isDurationValid) {
                            $this->banIds[intval($data["id"])] = ["reason" => $data["reason"], "duration" => $data["duration"]];
                        }
                    }
                }
            }
        }

        foreach ($this->idsFile->get("muteIds") ?? [] as $data) {
            if (isset($data["id"]) && isset($data["reason"]) && isset($data["duration"])) {
                if (is_numeric($data["id"])) {
                    if (isset($this->muteIds[intval($data["id"])])) {
                        Server::getInstance()->getLogger()->error("§cThe muteid §e" . intval($data["id"]) . " §cis already registered.");
                        continue;
                    }

                    if (is_string($data["duration"])) {
                        $isDurationValid = false;
                        if ($data["duration"] == "-1") $isDurationValid = true;
                        else if (Utils::convertStringToDateFormat($data["duration"]) !== null) $isDurationValid = true;

                        if ($isDurationValid) {
                            $this->muteIds[intval($data["id"])] = ["reason" => $data["reason"], "duration" => $data["duration"]];
                        }
                    }
                }
            }
        }
    }

    public function reload() {
        $this->config->reload();
        $this->load();
    }

    public function getPrefix(): string {
        return $this->prefix;
    }

    public function getMutePath(): string {
        return $this->mutePath;
    }

    public function getBanPath(): string {
        return $this->banPath;
    }

    public function getWarnPath(): string {
        return $this->warnPath;
    }

    public function getInfoPath(): string {
        return $this->infoPath;
    }

    public function getMaxWarns(): int {
        return $this->maxWarns;
    }

    public function getMaxWarnsAction(): string {
        return $this->maxWarnsAction;
    }

    public function getMaxWarnsActionReason(): string {
        return $this->maxWarnsActionReason;
    }

    public function isMakeBanMuteLogs(): bool {
        return $this->makeBanMuteLogs;
    }

    public function getBlockedCommands(): array {
        return $this->blockedCommands;
    }

    public function getBanIds(): array {
        return $this->banIds;
    }

    public function getMuteIds(): array {
        return $this->muteIds;
    }

    public function getIdsFile(): Config {
        return $this->idsFile;
    }
    
    public function getConfig(): Config {
        return $this->config;
    }
}