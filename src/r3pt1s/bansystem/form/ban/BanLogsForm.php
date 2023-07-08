<?php

namespace r3pt1s\bansystem\form\ban;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use r3pt1s\bansystem\manager\ban\Ban;

class BanLogsForm extends MenuForm {

    public function __construct(
        private readonly string $target,
        private readonly array $banLogs
    ) {
        $options = [];
        /** @var Ban $banLog */
        foreach ($this->banLogs as $banLog) {
            $options[] = new MenuOption("§c" . $banLog->getTime()->format("Y-m-d H:i:s") . "\n§e" . $banLog->getReason());
        }

        parent::__construct("§cBanLogs §8» §e" . $this->target, "§e" . $this->target . " §7has §c" . count($this->banLogs) . " banlogs§7.", $options, function (Player $player, int $data): void {
            if (isset($this->banLogs[$data])) {
                $player->sendForm(new ViewBanLogForm($this->banLogs[$data], $this->target));
            }
        });
    }
}