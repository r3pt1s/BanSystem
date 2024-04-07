<?php

namespace r3pt1s\bansystem\network;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;

class MutesSyncPacket extends StarGatePacket {

    private array $mutes = [];

    public function encodePayload(): void {
        PacketHelper::writeString($this, json_encode(array_map(fn(Mute $mute) => $mute->toArray(), MuteManager::getInstance()->getMutes())));
    }

    public function decodePayload(): void {
        $this->mutes = array_map(fn(array $muteInfo) => Mute::fromArray($muteInfo), json_decode(PacketHelper::readString($this), true));
    }

    public function setMutes(array $mutes): void {
        $this->mutes = $mutes;
    }

    public function getPacketId(): int {
        return BanSystemPackets::MUTES_SYNC_PACKET;
    }

    public function getMutes(): array {
        return $this->mutes;
    }

    public function handle(StarGatePacketHandler $handler): bool {
        MuteManager::getInstance()->sync($this->mutes);
        return true;
    }

    public static function create(): self {
        return new self;
    }
}