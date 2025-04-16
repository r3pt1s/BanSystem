<?php

namespace r3pt1s\bansystem\form\warn;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\manager\warn\Warn;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class ViewWarnForm extends MenuForm {

    public function __construct(
        private readonly Warn $warn,
        private readonly string $target
    ) {
        parent::__construct(
            Language::get()->translate(LanguageKeys::UI_WARN_LOGS_VIEW_TITLE, $this->warn->getTime()->format("Y-m-d H:i:s")),
            Language::get()->translate(
                LanguageKeys::UI_WARN_LOGS_VIEW_TEXT,
                $this->target,
                $this->warn->getModerator(),
                $this->warn->getTime()->format("Y-m-d H:i:s"),
                $this->warn->getReason()
            ),
            [new MenuOption("§cBack")],
            function (Player $player, int $data): void {
                $player->sendForm(new WarnsForm($this->target));
            }
        );
    }
}