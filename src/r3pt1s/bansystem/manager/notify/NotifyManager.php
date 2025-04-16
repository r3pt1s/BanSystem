<?php

namespace r3pt1s\bansystem\manager\notify;

use alemiz\sga\StarGateAtlantis;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\event\notify\NotifyEvent;
use r3pt1s\bansystem\network\NotifyPacket;

final class NotifyManager {
    use SingletonTrait;

    private array $states = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function sendNotification(string $message): void {
        ($ev = new NotifyEvent($message))->call();
        if ($ev->isCancelled()) return;
        foreach (array_filter(Server::getInstance()->getOnlinePlayers(), fn(Player $player) => $this->hasNotifications($player) && $player->hasPermission("bansystem.receive.notify")) as $player) {
            $player->sendMessage($ev->getMessage());
        }

        BanSystem::getInstance()->getLogger()->notice("§8[§4NOTIFICATION§8] §e" . str_replace(BanSystem::getPrefix(), "", $message));
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(NotifyPacket::create($message));
    }

    public function setState(Player $player, bool $v): void {
        $this->states[$player->getName()] = $v;
        BanSystem::getInstance()->getProvider()->setNotifications($player->getName(), $v);
    }

    public function hasNotifications(Player $player): bool {
        return $this->states[$player->getName()] ?? false;
    }

    public static function getInstance(): ?self {
        return self::$instance ?? null;
    }
}