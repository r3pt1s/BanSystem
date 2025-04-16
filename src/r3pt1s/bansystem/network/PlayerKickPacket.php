<?php

namespace r3pt1s\bansystem\network;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\protocol\types\PacketHelper;

final class PlayerKickPacket extends StarGatePacket {

    private string $player = "";
    private string $reason = "";

    public function encodePayload(): void {
        PacketHelper::writeString($this, $this->player);
        PacketHelper::writeString($this, $this->reason);
    }

    public function decodePayload(): void {
        $this->player = PacketHelper::readString($this);
        $this->reason = PacketHelper::readString($this);
    }

    public function setPlayer(string $player): void {
        $this->player = $player;
    }

    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    public function getPacketId(): int {
        return BanSystemPackets::PLAYER_KICK_PACKET;
    }

    public function getPlayer(): string {
        return $this->player;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public static function create(string $player, string $reason): self {
        $packet = new self;
        $packet->player = $player;
        $packet->reason = $reason;
        return $packet;
    }
}
