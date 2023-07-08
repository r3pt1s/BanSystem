<?php

namespace r3pt1s\bansystem\form\warn;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\manager\warn\Warn;

class ViewWarnForm extends MenuForm {

    public function __construct(
        private readonly Warn $warn,
        private readonly string $target
    ) {
        $text = "§7Player: §e" . $this->target . "\n";
        $text .= "§7Moderator: §e" . $this->warn->getModerator() . "\n";
        $text .= "§7Warned At: §e" . $this->warn->getTime()->format("Y-m-d H:i:s") . "\n";
        $text .= "§7Reason: §e" . $this->warn->getReason();

        parent::__construct("§c" . $this->warn->getTime()->format("Y-m-d H:i:s"), $text, [new MenuOption("§cBack")], function (Player $player, int $data) use($target): void {
            $player->sendForm(new WarnsForm($target));
        });
    }
}