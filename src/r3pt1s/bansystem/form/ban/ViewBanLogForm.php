<?php

namespace r3pt1s\bansystem\form\ban;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class ViewBanLogForm extends MenuForm {

    public function __construct(
        private readonly Ban $banLog,
        private readonly string $target
    ) {
        parent::__construct(
            Language::get()->translate(LanguageKeys::UI_BAN_LOGS_VIEW_TITLE, $this->banLog->getTime()->format("Y-m-d H:i:s")),
            Language::get()->translate(
                LanguageKeys::UI_BAN_LOGS_VIEW_TEXT,
                $this->target,
                $this->banLog->getModerator(),
                $this->banLog->getTime()->format("Y-m-d H:i:s"),
                $this->banLog->getReason(),
                ($this->banLog->getExpire()?->format("Y-m-d H:i:s") ?? "§c§l" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))
            ),
            [new MenuOption("§cBack")],
            function (Player $player, int $data): void {
                BanSystem::getInstance()->getProvider()->getBanLogs($this->target)->onCompletion(
                    fn(array $logs) => $player->sendForm(new BanLogsForm($this->target, $logs)),
                    fn() => $player->sendMessage(Language::get()->translate(LanguageKeys::CHECK_BAN_LOGS_FAILED, $this->target))
                );
            }
        );
    }
}