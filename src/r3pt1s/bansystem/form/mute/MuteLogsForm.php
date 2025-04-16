<?php

namespace r3pt1s\bansystem\form\mute;

use r3pt1s\bansystem\manager\mute\Mute;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class MuteLogsForm extends MenuForm {

    public function __construct(
        private readonly string $target,
        private readonly array $muteLogs
    ) {
        $options = [];
        /** @var Mute $muteLog */
        foreach ($this->muteLogs as $muteLog) {
            $options[] = new MenuOption("§c" . $muteLog->getTime()->format("Y-m-d H:i:s") . "\n§e" . $muteLog->getReason());
        }

        parent::__construct(
            Language::get()->translate(LanguageKeys::UI_MUTE_LOGS_TITLE),
            Language::get()->translate(LanguageKeys::UI_MUTE_LOGS_TEXT, $this->target, count($this->muteLogs)),
            $options,
            function (Player $player, int $data): void {
                if (isset($this->muteLogs[$data])) {
                    $player->sendForm(new ViewMuteLogForm($this->muteLogs[$data], $this->target));
                }
            }
        );
    }
}