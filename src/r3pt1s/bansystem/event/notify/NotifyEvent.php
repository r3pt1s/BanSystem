<?php

namespace r3pt1s\bansystem\event\notify;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class NotifyEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private string $message) {}

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getMessage(): string {
        return $this->message;
    }
}