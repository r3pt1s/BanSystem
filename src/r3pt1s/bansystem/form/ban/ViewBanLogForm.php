<?php

namespace r3pt1s\bansystem\form\ban;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;

class ViewBanLogForm extends MenuForm {

    public function __construct(
        private readonly Ban $banLog,
        private readonly string $target
    ) {
        $text = "§7Player: §e" . $this->target . "\n";
        $text .= "§7Moderator: §e" . $this->banLog->getModerator() . "\n";
        $text .= "§7Banned At: §e" . $this->banLog->getTime()->format("Y-m-d H:i:s") . "\n";
        $text .= "§7Reason: §e" . $this->banLog->getReason() . "\n";
        $text .= "§7Until: §e" . ($this->banLog->getExpire()?->format("Y-m-d H:i:s") ?? "§c§lPERMANENTLY");

        parent::__construct("§c" . $this->banLog->getTime()->format("Y-m-d H:i:s"), $text, [new MenuOption("§cBack")], function (Player $player, int $data) use($target): void {
            BanSystem::getInstance()->getProvider()->getBanLogs($this->target)->onCompletion(
                fn(array $logs) => $player->sendForm(new BanLogsForm($this->target, $logs)),
                fn() => $player->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch banlogs of §e" . $this->target . "§4.")
            );
        });
    }
}