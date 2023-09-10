<?php

namespace r3pt1s\bansystem\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Database;

class AsyncExecutorTask extends AsyncTask {

    private NonThreadSafeValue $mysql;

    public function __construct(
        private readonly \Closure $closure,
        private readonly ?\Closure $completion = null
    ) {
        $this->mysql = new NonThreadSafeValue(Configuration::getInstance()->getMysqlSettings());
    }

    public function onRun(): void {
        $result = ($this->closure)($this, new Database($this->mysql->deserialize()));
        if (!$result instanceof \PDOStatement) $this->setResult($result);
    }

    public function onCompletion(): void {
        if ($this->completion !== null) {
            ($this->completion)($this->getResult());
        }
    }

    public static function new(\Closure $closure, ?\Closure $completion = null): self {
        return new self($closure, $completion);
    }
}