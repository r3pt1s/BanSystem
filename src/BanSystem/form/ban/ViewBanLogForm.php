<?php

namespace BanSystem\form\ban;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class ViewBanLogForm extends MenuForm {

    private array $options = [];

    public function __construct(array $banLog, string $target) {
        $text = "§7Player: §e" . $target . "\n";
        $text .= "§7Moderator: §e" . $banLog["Moderator"] . "\n";
        $text .= "§7Banned At: §e" . $banLog["BannedAt"] . "\n";
        $text .= "§7Reason: §e" . $banLog["Reason"];

        $this->options[] = new MenuOption("§cBack");

        parent::__construct("§c" . $banLog["BannedAt"], $text, $this->options, function (Player $player, int $data) use($target): void {
            $player->sendForm(new BanLogsForm($target));
        });
    }
}