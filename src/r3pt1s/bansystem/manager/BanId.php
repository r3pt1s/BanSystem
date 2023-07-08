<?php

namespace r3pt1s\bansystem\manager;

class BanId {

    public function __construct(
        private readonly int $id,
        private readonly string $reason,
        private readonly ?string $duration
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function getDuration(): ?string {
        return $this->duration;
    }
}