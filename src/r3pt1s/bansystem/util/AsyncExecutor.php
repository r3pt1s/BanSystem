<?php

namespace r3pt1s\bansystem\util;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use r3pt1s\bansystem\task\AsyncExecutorTask;

class AsyncExecutor {

    public static function execute(\Closure $asyncClosure, ?\Closure $syncClosure = null): void {
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncExecutorTask(fn(AsyncTask $task, Database $database) => ($asyncClosure)($database), function(mixed $result) use($syncClosure): void {
            if ($syncClosure !== null) $syncClosure($result);
        }));
    }
}