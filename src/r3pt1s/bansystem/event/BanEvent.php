<?php

namespace r3pt1s\bansystem\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use r3pt1s\bansystem\manager\ban\Ban;

abstract class BanEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private readonly Ban $ban) {}

    public function getBan(): Ban {
        return $this->ban;
    }
}