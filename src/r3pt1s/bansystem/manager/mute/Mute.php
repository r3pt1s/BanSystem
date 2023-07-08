<?php

namespace r3pt1s\bansystem\manager\mute;

class Mute {

    public function __construct(
        private readonly string $player,
        private readonly string $moderator,
        private readonly string $reason,
        private readonly \DateTime $time,
        private ?\DateTime $expire = null
    ) {}

    public function setExpire(?\DateTime $expire): void {
        $this->expire = $expire;
    }

    public function isExpired(): bool {
        return $this->expire !== null && $this->expire <= new \DateTime("now");
    }

    public function getPlayer(): string {
        return $this->player;
    }

    public function getModerator(): string {
        return $this->moderator;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function getTime(): \DateTime {
        return $this->time;
    }

    public function getExpire(): ?\DateTime {
        return $this->expire;
    }

    public function toArray(): array {
        return [
            "player" => $this->player,
            "moderator" => $this->moderator,
            "reason" => $this->reason,
            "time" => $this->time->format("Y-m-d H:i:s"),
            "expire" => $this->expire?->format("Y-m-d H:i:s") ?? null
        ];
    }

    public static function fromArray(array $data): ?self {
        if (isset($data["player"]) && isset($data["moderator"]) && isset($data["reason"]) && isset($data["time"])) {
            return new self(
                $data["player"],
                $data["moderator"],
                $data["reason"],
                new \DateTime($data["time"]),
                (isset($data["expire"]) ? new \DateTime($data["expire"]) : null)
            );
        }
        return null;
    }
}