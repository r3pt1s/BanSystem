<?php

namespace r3pt1s\bansystem\network;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;
use pocketmine\player\Player;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\event\notify\NotifyEvent;
use r3pt1s\bansystem\manager\notify\NotifyManager;

class NotifyPacket extends StarGatePacket {

    private string $message = "";

    public function encodePayload(): void {
        PacketHelper::writeString($this,  $this->message);
    }

    public function decodePayload(): void {
        $this->message = PacketHelper::readString($this);
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getPacketId(): int {
        return BanSystemPackets::NOTIFY_PACKET;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function handle(StarGatePacketHandler $handler): bool {
        ($ev = new NotifyEvent($this->message))->call();
        if ($ev->isCancelled()) return true;
        foreach (array_filter(Server::getInstance()->getOnlinePlayers(), fn(Player $player) => NotifyManager::getInstance()->hasNotifications($player) && $player->hasPermission("bansystem.receive.notify")) as $player) {
            $player->sendMessage($ev->getMessage());
        }

        BanSystem::getInstance()->getLogger()->notice("§8[§4NOTIFICATION§8] §e" . str_replace(BanSystem::getPrefix(), "", $this->message));
        return true;
    }

    public static function create(string $message): self {
        $packet = new self;
        $packet->message = $message;
        return $packet;
    }
}