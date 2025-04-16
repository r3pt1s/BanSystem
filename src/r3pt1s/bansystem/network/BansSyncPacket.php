<?php

namespace r3pt1s\bansystem\network;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;

final class BansSyncPacket extends StarGatePacket {

    private array $bans = [];

    public function encodePayload(): void {
        PacketHelper::writeString($this, json_encode(array_map(fn(Ban $ban) => $ban->toArray(), BanManager::getInstance()->getBans())));
    }

    public function decodePayload(): void {
        $this->bans = array_map(fn(array $banInfo) => Ban::fromArray($banInfo), json_decode(PacketHelper::readString($this), true));
    }

    public function setBans(array $bans): void {
        $this->bans = $bans;
    }

    public function getPacketId(): int {
        return BanSystemPackets::BANS_SYNC_PACKET;
    }

    public function getBans(): array {
        return $this->bans;
    }

    public function handle(StarGatePacketHandler $handler): bool {
        BanManager::getInstance()->sync($this->bans);
        return true;
    }

    public static function create(): self {
        return new self;
    }
}