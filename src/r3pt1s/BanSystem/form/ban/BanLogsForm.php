<?php

namespace r3pt1s\BanSystem\form\ban;

use r3pt1s\BanSystem\manager\ban\BanManager;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class BanLogsForm extends MenuForm {

    private array $options = [];
    private array $banLogs = [];

    public function __construct(string $target) {
        $banLogs = BanManager::getInstance()->getBanLogs($target);
        foreach ($banLogs as $banLog) {
            $this->options[] = new MenuOption("§c" . $banLog["BannedAt"] . "\n§e" . $banLog["Reason"]);
            $this->banLogs[] = $banLog;
        }

        parent::__construct("§cBanLogs §8» §e" . $target, "§e" . $target . " §7has §c" . count($banLogs) . " banlogs§7!", $this->options, function (Player $player, int $data) use($target): void {
            if (isset($this->banLogs[$data])) {
                $player->sendForm(new ViewBanLogForm($this->banLogs[$data], $target));
            }
        });
    }
}