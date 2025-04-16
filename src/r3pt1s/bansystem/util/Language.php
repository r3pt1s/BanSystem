<?php

namespace r3pt1s\bansystem\util;

use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\exception\LanguageFileNotFoundException;

final class Language {
    use SingletonTrait {
        getInstance as private;
        getInstance as public get;
    }

    private array $languageData = [];

    public function __construct() {
        self::setInstance($this);
    }

    /**
     * @throws LanguageFileNotFoundException
     */
    public function load(): void {
        $path = BanSystem::getInstance()->getResourcePath("lang/" . $lang = Configuration::getInstance()->getLanguage() . ".json");
        if (!@file_exists($path)) throw new LanguageFileNotFoundException("Language file for " . $lang . " not found");
        $this->languageData = json_decode(file_get_contents($path), true);
    }

    public function translate(string $key, string ...$args): string {
        if (!isset($this->languageData[$key])) return $key;
        $translation = $this->languageData[$key];
        
        if (is_string($translation)) return $this->applyPlaceholders($translation, $args);

        if (is_array($translation)) {
            $translatedArray = [];
            foreach ($translation as $string) $translatedArray[] = $this->applyPlaceholders($string, $args);
            return implode("\n", $translatedArray);
        }

        return $translation;
    }

    private function applyPlaceholders(string $string, array $args): string {
        $string = str_replace(["{PREFIX}"], [BanSystem::getPrefix()], $string);
        foreach ($args as $index => $arg) $string = str_replace("%" . $index . "%", $arg, $string);
        return $string;
    }
}