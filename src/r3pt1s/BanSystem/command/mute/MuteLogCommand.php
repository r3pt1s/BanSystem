<?php

namespace r3pt1s\BanSystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\form\mute\MuteLogsForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class MuteLogCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.mutelog.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($sender->hasPermission($this->getPermission())) {
                if (isset($args[0])) {
                    if ($this->getOwningPlugin()->isPlayerCreated($args[0])) {
                        $sender->sendForm(new MuteLogsForm($args[0]));
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7doesn't exists!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c/mutelog <player>");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}