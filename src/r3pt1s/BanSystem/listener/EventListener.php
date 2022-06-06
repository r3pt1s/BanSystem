<?php

namespace r3pt1s\BanSystem\listener;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\handler\BanHandler;
use r3pt1s\BanSystem\handler\MuteHandler;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Server;
use r3pt1s\BanSystem\provider\CurrentProvider;

class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event) {
        CurrentProvider::get()->createPlayer($event->getPlayer()->getName());
        if (BanHandler::getInstance()->handle($event->getPlayer(), $screen)) {
            $event->setKickMessage($screen);
            $event->cancel();
        }
    }

    public function onChat(PlayerChatEvent $event) {
        if (MuteHandler::getInstance()->handle($event->getPlayer(), $screen)) {
            $event->getPlayer()->sendMessage($screen);
            $event->cancel();
        }
    }

    public function onCommand(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $msg = explode(" ", $event->getMessage());

        if (!isset($event->getMessage()[0])) return;
        if ($event->getMessage()[0] === "/") {
            $command = Server::getInstance()->getCommandMap()->getCommand(substr($msg[0], 1));
            if ($command !== null) {
                if (MuteManager::getInstance()->isMuted($player)) {
                    if (in_array($command->getName(), BanSystem::getInstance()->getConfiguration()->getBlockedCommands())) {
                        if (!$player->hasPermission("bansystem.mute.bypass")) {
                            $player->sendMessage(BanSystem::getPrefix() . "§7You can't execute the command §e" . substr($msg[0], 1) . "§7!");
                            $event->cancel();
                        }
                    }
                }
            }
        } else {
            if (isset($event->getMessage()[0])) {
                if (isset($event->getMessage()[1])) {
                    if ($event->getMessage()[0] === "." && $event->getMessage()[1] === "/") {
                        $command = Server::getInstance()->getCommandMap()->getCommand(substr($msg[0], 2));
                        if ($command !== null) {
                            if (MuteManager::getInstance()->isMuted($player)) {
                                if (in_array($command->getName(), BanSystem::getInstance()->getConfiguration()->getBlockedCommands())) {
                                    if (!$player->hasPermission("bansystem.mute.bypass")) {
                                        $player->sendMessage(BanSystem::getPrefix() . "§7You can't execute the command §e" . substr($msg[0], 1) . "§7!");
                                        $event->cancel();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}