<?php

namespace r3pt1s\BanSystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use r3pt1s\BanSystem\provider\CurrentProvider;

class BanCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.ban.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0]) && isset($args[1])) {
                if (CurrentProvider::get()->isPlayerCreated($args[0])) {
                    if (!BanManager::getInstance()->isBanned($args[0])) {
                        if (is_numeric($args[1])) {
                            if (BanManager::getInstance()->isBanId(intval($args[1]))) {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7was banned!");
                                BanManager::getInstance()->banPlayer($args[0], $sender, intval($args[1]));
                            } else {
                                $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid banid!");
                            }
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§c/ban <player> <banId>");
                        }
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7is already banned!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player doesn't exists!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/ban <player> <banId>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}