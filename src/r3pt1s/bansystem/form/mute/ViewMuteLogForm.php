<?php

namespace r3pt1s\bansystem\form\mute;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\Mute;

class ViewMuteLogForm extends MenuForm {

    public function __construct(
        private readonly Mute $muteLog,
        private readonly string $target
    ) {
        $text = "§7Player: §e" . $this->target . "\n";
        $text .= "§7Moderator: §e" . $this->muteLog->getModerator() . "\n";
        $text .= "§7Banned At: §e" . $this->muteLog->getTime()->format("Y-m-d H:i:s") . "\n";
        $text .= "§7Reason: §e" . $this->muteLog->getReason() . "\n";
        $text .= "§7Until: §e" . ($this->muteLog->getExpire()?->format("Y-m-d H:i:s") ?? "§c§lPERMANENTLY");

        parent::__construct("§c" . $this->muteLog->getTime()->format("Y-m-d H:i:s"), $text, [new MenuOption("§cBack")], function (Player $player, int $data) use($target): void {
            BanSystem::getInstance()->getProvider()->getMuteLogs($this->target)->onCompletion(
                fn(array $logs) => $player->sendForm(new MuteLogsForm($this->target, $logs)),
                fn() => $player->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch mutelogs of §e" . $this->target . "§4.")
            );
        });
    }
}