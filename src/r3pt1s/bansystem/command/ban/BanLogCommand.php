<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\form\ban\BanLogsForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class BanLogCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("banlog", "See a list of every ban from a player", "/banlog <player>");
        $this->setPermission("bansystem.command.banlog");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (count($args) == 0) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                    return true;
                }

                $target = implode(" ", $args);

                BanSystem::getInstance()->getProvider()->getBanLogs($target)->onCompletion(
                    function(array $logs) use($sender, $target): void {
                        if (count($logs) == 0) {
                            $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target . " §7has no banlogs.");
                            return;
                        }

                        $sender->sendForm(new BanLogsForm($target, $logs));
                    },
                    fn() => $sender->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch banlogs from §e" . $target)
                );
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