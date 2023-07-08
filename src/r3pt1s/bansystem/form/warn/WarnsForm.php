<?php

namespace r3pt1s\bansystem\form\warn;

use r3pt1s\bansystem\manager\warn\Warn;
use r3pt1s\bansystem\manager\warn\WarnManager;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class WarnsForm extends MenuForm {

    public function __construct(private readonly string $target) {
        $warns = WarnManager::getInstance()->getWarns($this->target);
        $options = [];
        /** @var Warn $warn */
        foreach ($warns as $warn) {
            $options[] = new MenuOption("§c" . $warn->getTime()->format("Y-m-d H:i:s") . "\n§e" . $warn->getReason());
        }

        parent::__construct("§cWarns §8» §e" . $this->target, "§e" . $this->target . " §7has §c" . count($warns) . " warns§7.", $options, function (Player $player, int $data) use($warns): void {
            if (isset($warns[$data])) {
                $player->sendForm(new ViewWarnForm($warns[$data], $this->target));
            }
        });
    }
}