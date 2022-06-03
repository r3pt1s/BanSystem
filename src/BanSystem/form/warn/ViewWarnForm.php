<?php

namespace BanSystem\form\warn;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class ViewWarnForm extends MenuForm {

    private array $options = [];

    public function __construct(array $warnData, string $target) {
        $text = "§7Player: §e" . $target . "\n";
        $text .= "§7Moderator: §e" . $warnData["Moderator"] . "\n";
        $text .= "§7Warned At: §e" . $warnData["WarnedAt"] . "\n";
        $text .= "§7Reason: §e" . $warnData["Reason"];

        $this->options[] = new MenuOption("§cBack");

        parent::__construct("§c" . $warnData["WarnedAt"], $text, $this->options, function (Player $player, int $data) use($target): void {
            $player->sendForm(new WarnsForm($target));
        });
    }
}