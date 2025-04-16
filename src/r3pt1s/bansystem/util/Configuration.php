<?php

namespace r3pt1s\bansystem\util;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\BanId;
use r3pt1s\bansystem\manager\MuteId;

final class Configuration {
    use SingletonTrait;

    private const VALID_ACTIONS = [
        "ban", "mute", "kick", "warn"
    ];

    private const VALID_PROVIDERS = [
        "mysql", "json"
    ];

    private Config $config;
    private Config $idsFile;
    private string $provider;
    private string $language;
    private array $mysqlSettings;
    private string $prefix;
    private string $mutePath;
    private string $banPath;
    private string $infoPath;
    private int $maxWarns;
    private string $maxWarnsAction;
    private string $maxWarnsActionReason;
    private ?string $maxWarnsActionDuration;
    private bool $makeBanMuteLogs;
    private array $features;
    private array $blockedCommands;
    /** @var array<BanId> */
    private array $banIds;
    /** @var array<MuteId> */
    private array $muteIds;

    public function __construct(Config $config, Config $idsFile) {
        self::setInstance($this);
        $this->config = $config;
        $this->idsFile = $idsFile;

        $this->load();
    }

    private function load(): void {
        if ($this->config->exists("provider")) {
            if (in_array(strtolower($this->config->get("provider")), self::VALID_PROVIDERS)) {
                $this->provider = strtolower($this->config->get("provider"));
            } else $this->provider = "json";
        } else $this->provider = "json";

        $this->language = $this->config->get("language", "en_US");

        if ($this->provider == "mysql") {
            if ($this->config->exists("database")) {
                $this->mysqlSettings = (array) $this->config->get("database", []);
            } else $this->mysqlSettings = ["host" => "127.0.0.1", "port" => 3306, "user" => "root", "password" => "", "database" => "bansystem"];
        } else $this->mysqlSettings = [];

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

        if ($this->config->exists("max-warns-action-duration")) {
            $this->maxWarnsActionDuration = $this->config->get("max-warns-action-duration");
        } else $this->maxWarnsActionDuration = "1d";

        if ($this->config->exists("make-ban-mute-logs")) {
            $this->makeBanMuteLogs = boolval($this->config->get("make-ban-mute-logs"));
        } else $this->makeBanMuteLogs = true;

        if ($this->config->exists("features")) {
            $features = [];
            foreach ($this->config->get("features", []) as $feature => $enabled) {
                $features[$feature] = $enabled;
            }

            $this->features = $features;
        } else $this->blockedCommands = [];

        if ($this->config->exists("blocked-commands")) {
            $blockedCommands = [];
            foreach ($this->config->get("blocked-commands", []) as $command) {
                $blockedCommands[] = $command;
            }

            $this->blockedCommands = $blockedCommands;
        } else $this->blockedCommands = [];

        $this->loadIds();
    }

    private function loadIds(): void {
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
                            $this->banIds[intval($data["id"])] = new BanId(intval($data["id"]), $data["reason"], ($data["duration"] == "-1" ? null : $data["duration"]));
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
                            $this->muteIds[intval($data["id"])] = new MuteId(intval($data["id"]), $data["reason"], ($data["duration"] == "-1" ? null : $data["duration"]));
                        }
                    }
                }
            }
        }
    }

    public function reload(): void {
        $this->config->reload();
        $this->load();
    }

    public function getBanId(int|string $v): ?BanId {
        if (is_int($v) || is_numeric($v)) return $this->banIds[intval($v)] ?? null;
        foreach ($this->banIds as $banId) {
            if ($banId->getReason() == $v) return $banId;
        }
        return null;
    }

    public function getMuteId(int|string $v): ?MuteId {
        if (is_int($v) || is_numeric($v)) return $this->muteIds[intval($v)] ?? null;
        foreach ($this->muteIds as $muteId) {
            if ($muteId->getReason() == $v) return $muteId;
        }
        return null;
    }

    public function getProvider(): string {
        return $this->provider;
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public function getMysqlSettings(): array {
        return $this->mysqlSettings;
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

    public function getMaxWarnsActionDuration(): ?string {
        return $this->maxWarnsActionDuration;
    }

    public function isMakeBanMuteLogs(): bool {
        return $this->makeBanMuteLogs;
    }

    public function isBanSystemEnabled(): bool {
        return $this->features["ban_system"] ?? true;
    }

    public function isMuteSystemEnabled(): bool {
        return $this->features["mute_system"] ?? true;
    }

    public function isWarnSystemEnabled(): bool {
        return $this->features["warn_system"] ?? true;
    }

    public function isStaffToolsEnabled(): bool {
        return $this->features["staff_tools"] ?? true;
    }

    public function getFeatures(): array {
        return $this->features;
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

    public static function getInstance(): ?self {
        return self::$instance ?? null;
    }
}