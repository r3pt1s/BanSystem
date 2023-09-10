<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use r3pt1s\bansystem\form\mute\MuteLogsForm;

class MuteLogCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mutelog", "See a list of every mute from a player", "/mutelog <player>");
        $this->setPermission("bansystem.command.mutelog");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (count($args) == 0) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                    return true;
                }

                $target = implode(" ", $args);

                BanSystem::getInstance()->getProvider()->getMuteLogs($target)->onCompletion(
                    function(array $logs) use($sender, $target): void {
                        if (count($logs) == 0) {
                            $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target . " §7has no mutelogs.");
                            return;
                        }

                        $sender->sendForm(new MuteLogsForm($target, $logs));
                    },
                    fn() => $sender->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch mutelogs from §e" . $target)
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