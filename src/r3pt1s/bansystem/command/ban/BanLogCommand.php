<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\form\ban\BanLogsForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanLogCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("banlog", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_BAN_LOG), "/banlog <player>");
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
                            $sender->sendMessage(Language::get()->translate(LanguageKeys::BAN_LOG_NONE, $target));
                            return;
                        }

                        $sender->sendForm(new BanLogsForm($target, $logs));
                    },
                    fn() => $sender->sendMessage(Language::get()->translate(LanguageKeys::CHECK_BAN_LOGS_FAILED, $target))
                );
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}