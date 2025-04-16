<?php

namespace r3pt1s\bansystem\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use r3pt1s\bansystem\manager\warn\Warn;

abstract class WarnEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private readonly Warn $warn) {}

    public function getWarn(): Warn {
        return $this->warn;
    }
}