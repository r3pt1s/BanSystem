<?php

namespace r3pt1s\bansystem\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use r3pt1s\bansystem\manager\mute\Mute;

abstract class MuteEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private readonly Mute $mute) {}

    public function getMute(): Mute {
        return $this->mute;
    }
}