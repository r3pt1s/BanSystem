<?php

namespace r3pt1s\BanSystem\provider;

use r3pt1s\BanSystem\BanSystem;

class CurrentProvider {

    private static ?Provider $provider = null;

    public static function get(): Provider {
        if (self::$provider === null) {
            switch (BanSystem::getInstance()->getConfiguration()->getProvider()) {
                case "json":
                    self::set(new JSONProvider());
                    break;
                case "mysql":
                    self::set(new MySQLProvider());
                    break;
                default:
                    self::set(new JSONProvider());
            }
        }
        return self::$provider;
    }

    public static function set(Provider $provider) {
        self::$provider = $provider;
    }
}