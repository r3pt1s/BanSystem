<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use r3pt1s\bansystem\form\mute\MuteLogsForm;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class MuteLogCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mutelog", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_MUTE_LOG), "/mutelog <player>");
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
                            $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_LOG_NONE, $target));
                            return;
                        }

                        $sender->sendForm(new MuteLogsForm($target, $logs));
                    },
                    fn() => $sender->sendMessage(Language::get()->translate(LanguageKeys::CHECK_MUTE_LOGS_FAILED, $target))
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