<?php

namespace r3pt1s\BanSystem\form\mute;

use r3pt1s\BanSystem\manager\mute\MuteManager;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class MuteLogsForm extends MenuForm {

    private array $options = [];
    private array $muteLogs = [];

    public function __construct(string $target) {
        $muteLogs = MuteManager::getInstance()->getMuteLogs($target);
        foreach ($muteLogs as $muteLog) {
            $this->options[] = new MenuOption("§c" . $muteLog["MutedAt"] . "\n§e" . $muteLog["Reason"]);
            $this->muteLogs[] = $muteLog;
        }

        parent::__construct("§cMuteLogs §8» §e" . $target, "§e" . $target . " §7has §c" . count($muteLogs) . " mutelogs§7!", $this->options, function (Player $player, int $data) use($target): void {
            if (isset($this->muteLogs[$data])) {
                $player->sendForm(new ViewMuteLogForm($this->muteLogs[$data], $target));
            }
        });
    }
}