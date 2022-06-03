<?php

namespace r3pt1s\BanSystem\command\ban;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\form\ban\BanLogsForm;
use r3pt1s\BanSystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class BanLogCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("banlog.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($sender->hasPermission($this->getPermission())) {
                if (isset($args[0])) {
                    if (BanSystem::getInstance()->isPlayerCreated($args[0])) {
                        $sender->sendForm(new BanLogsForm($args[0]));
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7doesn't exists!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c/banlog <player>");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
            }
        }
        return true;
    }
}