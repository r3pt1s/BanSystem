<?php

namespace r3pt1s\bansystem\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use r3pt1s\bansystem\util\Configuration;

class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        if (($screen = BanManager::getInstance()->getBanHandler()->handle($player->getName())) !== null && !$player->hasPermission("bansystem.bypass.ban")) {
            $event->setKickMessage($screen);
            $event->cancel();
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        BanSystem::getInstance()->getProvider()->checkPlayer($player->getName())->onCompletion(
            function(bool $exists) use($player): void {
                if ($exists) {
                    BanSystem::getInstance()->getProvider()->hasNotifications($player->getName())->onCompletion(
                        fn(bool $has) => NotifyManager::getInstance()->setState($player, $has),
                        fn() => BanSystem::getInstance()->getLogger()->warning("§cFailed to fetch notification state of §e" . $player->getName())
                    );
                } else BanSystem::getInstance()->getProvider()->createPlayer($player);
            },
            fn() => BanSystem::getInstance()->getLogger()->warning("§cFailed to check if §e" . $player->getName() . " §calready exists")
        );

        BanSystem::getInstance()->getProvider()->getBanPoints($player->getName());
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        if (($screen = MuteManager::getInstance()->getMuteHandler()->handle($player->getName())) !== null && !$player->hasPermission("bansystem.bypass.mute")) {
            $player->sendMessage($screen);
            $event->cancel();
        }
    }

    public function onCommand(CommandEvent $event): void {
        $sender = $event->getSender();
        if ($sender instanceof Player) {
            $commandLine = explode(" ", $event->getCommand());

            if (!MuteManager::getInstance()->isMuted($sender)) return;
            if ($sender->hasPermission("bansystem.bypass.mute")) return;
            if (!isset($commandLine[0])) return;
            if ($commandLine[0] == "/") $command = Server::getInstance()->getCommandMap()->getCommand(substr($commandLine[0], 1));
            else $command = Server::getInstance()->getCommandMap()->getCommand($commandLine[0]);

            if ($command !== null) {
                $anyAliasBlocked = in_array($command->getName(), Configuration::getInstance()->getBlockedCommands());
                if (!$anyAliasBlocked) {
                    if (count(array_filter($command->getAliases(), fn(string $alias) => in_array($alias, Configuration::getInstance()->getBlockedCommands()))) > 0) {
                        $anyAliasBlocked = true;
                    }
                }

                if ($anyAliasBlocked) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You can't execute the command §e/" . $command->getName() . "§7.");
                    $event->cancel();
                }
            }
        }
    }
}