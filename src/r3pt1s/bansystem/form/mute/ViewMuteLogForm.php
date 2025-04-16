<?php

namespace r3pt1s\bansystem\form\mute;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class ViewMuteLogForm extends MenuForm {

    public function __construct(
        private readonly Mute $muteLog,
        private readonly string $target
    ) {
        parent::__construct(
            Language::get()->translate(LanguageKeys::UI_MUTE_LOGS_VIEW_TITLE, $this->muteLog->getTime()->format("Y-m-d H:i:s")),
            Language::get()->translate(
                LanguageKeys::UI_MUTE_LOGS_VIEW_TEXT,
                $this->target,
                $this->muteLog->getModerator(),
                $this->muteLog->getTime()->format("Y-m-d H:i:s"),
                $this->muteLog->getReason(),
                ($this->muteLog->getExpire()?->format("Y-m-d H:i:s") ?? "§c§l" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))
            ),
            [new MenuOption("§cBack")],
            function (Player $player, int $data): void {
                BanSystem::getInstance()->getProvider()->getmuteLogs($this->target)->onCompletion(
                    fn(array $logs) => $player->sendForm(new MuteLogsForm($this->target, $logs)),
                    fn() => $player->sendMessage(Language::get()->translate(LanguageKeys::CHECK_MUTE_LOGS_FAILED, $this->target))
                );
            }
        );
    }
}