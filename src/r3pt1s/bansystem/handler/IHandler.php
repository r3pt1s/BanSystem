<?php

namespace r3pt1s\bansystem\handler;

use pocketmine\player\Player;

interface IHandler {

    public function handle(Player $player): ?string;
}