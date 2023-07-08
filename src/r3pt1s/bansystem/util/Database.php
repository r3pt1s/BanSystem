<?php

namespace r3pt1s\bansystem\util;

class Database extends Medoo {

    public function __construct(private array $data) {
        if (isset($this->data["user"])) $this->data["username"] = $this->data["user"];
        parent::__construct(array_merge(["type" => "mysql"], $this->data));
    }

    public function exec(string $statement, array $map = [], callable $callback = null): ?\PDOStatement {
        try {
            return parent::exec($statement, $map, $callback);
        } catch (\Exception $exception) {
            if (str_contains("gone away", $exception->getMessage())) {
                parent::__construct(array_merge(["type" => "mysql"], $this->data));
                return parent::exec($statement, $map, $callback);
            } else \GlobalLogger::get()->logException($exception);
        }
        return null;
    }

    public function initializeTable(): void {
        $this->create("bans", [
            "player" => "VARCHAR(16) PRIMARY KEY",
            "moderator" => "VARCHAR(16)",
            "reason" => "VARCHAR(100)",
            "time" => "TIMESTAMP",
            "expire" => "TIMESTAMP NULL DEFAULT NULL"
        ]);

        $this->create("mutes", [
            "player" => "VARCHAR(16) PRIMARY KEY",
            "moderator" => "VARCHAR(16)",
            "reason" => "VARCHAR(100)",
            "time" => "TIMESTAMP",
            "expire" => "TIMESTAMP NULL DEFAULT NULL"
        ]);

        $this->create("players", [
            "player" => "VARCHAR(16)",
            "ban_points" => "INT",
            "mute_points" => "INT",
            "notifications" => "TINYINT"
        ]);

        $this->create("banlogs", [
            "player" => "VARCHAR(16)",
            "moderator" => "VARCHAR(16)",
            "reason" => "VARCHAR(100)",
            "time" => "TIMESTAMP",
            "expire" => "TIMESTAMP NULL DEFAULT NULL"
        ]);

        $this->create("mutelogs", [
            "player" => "VARCHAR(16)",
            "moderator" => "VARCHAR(16)",
            "reason" => "VARCHAR(100)",
            "time" => "TIMESTAMP",
            "expire" => "TIMESTAMP NULL DEFAULT NULL"
        ]);
    }
}