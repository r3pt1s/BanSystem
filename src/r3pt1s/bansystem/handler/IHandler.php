<?php

namespace r3pt1s\bansystem\handler;

interface IHandler {

    public function handle(string $player): ?string;
}