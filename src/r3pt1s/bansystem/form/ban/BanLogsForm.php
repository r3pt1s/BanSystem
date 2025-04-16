<?php

namespace r3pt1s\bansystem\form\ban;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanLogsForm extends MenuForm {

    public function __construct(
        private readonly string $target,
        private readonly array $banLogs
    ) {
        $options = [];
        /** @var Ban $banLog */
        foreach ($this->banLogs as $banLog) {
            $options[] = new MenuOption("§c" . $banLog->getTime()->format("Y-m-d H:i:s") . "\n§e" . $banLog->getReason());
        }

        parent::__construct(
            Language::get()->translate(LanguageKeys::UI_BAN_LOGS_TITLE),
            Language::get()->translate(LanguageKeys::UI_BAN_LOGS_TEXT, $this->target, count($this->banLogs)),
            $options,
            function (Player $player, int $data): void {
                if (isset($this->banLogs[$data])) {
                    $player->sendForm(new ViewBanLogForm($this->banLogs[$data], $this->target));
                }
            }
        );
    }
}