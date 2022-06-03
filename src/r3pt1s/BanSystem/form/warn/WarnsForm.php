<?php

namespace r3pt1s\BanSystem\form\warn;

use r3pt1s\BanSystem\manager\warn\WarnManager;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;

class WarnsForm extends MenuForm {

    private array $options = [];
    private array $warns = [];

    public function __construct(string $target, int $pageNumber = 1) {
        $warns = WarnManager::getInstance()->getWarns($target);
        $pages = [];
        $currentPageNumber = 1;
        $i = 0;
        foreach ($warns as $warnIndex => $warnData) {
            if ($i == 10) {
                $currentPageNumber++;
                $i = 0;
            }
            $pages[$currentPageNumber][$warnIndex] = $warnData;
            $i++;
        }
        $pageNumber = (count($pages) > 0 ? ($pageNumber <= 0 || $pageNumber > count(($pages ?? [])) ? 1 : $pageNumber) : 0);;

        foreach ($pages[$pageNumber] ?? [] as $warnData) {
            $this->options[] = new MenuOption("§c" . $warnData["WarnedAt"] . "\n§e" . $warnData["Reason"]);
            $this->warns[] = $warnData;
        }

        if (isset($pages[$pageNumber])) if (count($pages[$pageNumber]) >= 10) if (count($warns) > 10) $this->options[] = new MenuOption("§6Next Site");
        parent::__construct("§cWarns §8» §e" . $target, "§e" . $target . " §7has §c" . count($warns) . " warns§7!", $this->options, function (Player $player, int $data) use($pageNumber, $target): void {
            $button = $this->options[$data] ?? null;
            if ($button instanceof MenuOption) {
                if ($button->getText() == "§6Next Site") {
                    $player->sendForm(new self($target, $pageNumber + 1));
                } else {
                    if (isset($this->warns[$data])) {
                        $player->sendForm(new ViewWarnForm($this->warns[$data], $target));
                    }
                }
            }
        });
    }
}