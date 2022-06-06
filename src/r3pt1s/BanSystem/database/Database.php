<?php

namespace r3pt1s\BanSystem\database;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\provider\CurrentProvider;
use r3pt1s\BanSystem\utils\Medoo;

class Database {

    private static self $instance;
    private Medoo $database;

    public function __construct() {
        self::$instance = $this;
        $this->database = new Medoo([
            "type" => "mysql",
            "database" => BanSystem::getInstance()->getConfiguration()->getMysqlSettings()["database"],
            "host" => BanSystem::getInstance()->getConfiguration()->getMysqlSettings()["host"],
            "username" => BanSystem::getInstance()->getConfiguration()->getMysqlSettings()["user"],
            "password" => BanSystem::getInstance()->getConfiguration()->getMysqlSettings()["password"],
            "port" => BanSystem::getInstance()->getConfiguration()->getMysqlSettings()["port"]
        ]);
        $this->createTables();
    }

    private function createTables() {
        $this->database->create("bans",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "Type" => ["INT", "NOT NULL"],
                "Moderator" => ["VARCHAR(16)", "NOT NULL"],
                "Id" => ["INT"],
                "Reason" => ["VARCHAR(100)"],
                "Time" => ["VARCHAR(100)", "NOT NULL"],
                "BannedAt" => ["VARCHAR(100)", "NOT NULL"]
            ]
        );

        $this->database->create("mutes",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "Type" => ["INT", "NOT NULL"],
                "Moderator" => ["VARCHAR(16)", "NOT NULL"],
                "Id" => ["INT"],
                "Reason" => ["VARCHAR(100)"],
                "Time" => ["VARCHAR(100)", "NOT NULL"],
                "MutedAt" => ["VARCHAR(100)", "NOT NULL"]
            ]
        );

        $this->database->create("warns",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "Moderator" => ["VARCHAR(16)", "NOT NULL"],
                "Reason" => ["VARCHAR(100)", "NOT NULL"],
                "WarnedAt" => ["VARCHAR(100)", "NOT NULL"]
            ]
        );

        $this->database->create("players",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "BanPoints" => ["INT", "NOT NULL"],
                "MutePoints" => ["INT", "NOT NULL"],
                "WarnCount" => ["INT", "NOT NULL"],
                "Notify" => ["BOOLEAN", "NOT NULL"]
            ]
        );

        $this->database->create("banlogs",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "Moderator" => ["VARCHAR(16)", "NOT NULL"],
                "Reason" => ["VARCHAR(100)", "NOT NULL"],
                "Time" => ["VARCHAR(100)", "NOT NULL"],
                "BannedAT" => ["VARCHAR(100)", "NOT NULL"]
            ]
        );

        $this->database->create("mutelogs",
            [
                "Player" => ["VARCHAR(16)", "NOT NULL"],
                "Moderator" => ["VARCHAR(16)", "NOT NULL"],
                "Reason" => ["VARCHAR(100)", "NOT NULL"],
                "Time" => ["VARCHAR(100)", "NOT NULL"],
                "MutedAt" => ["VARCHAR(100)", "NOT NULL"]
            ]
        );
    }

    public static function getInstance(): Database {
        return self::$instance;
    }

    public function getDatabase(): Medoo {
        return $this->database;
    }
}