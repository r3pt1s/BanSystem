<?php

namespace r3pt1s\bansystem\provider;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\utils\Config;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\util\Configuration;

class JSONProvider implements Provider {

    private Config $bansFile;
    private Config $mutesFile;
    private Config $playersFile;

    public function load(): void {
        if (!file_exists(Configuration::getInstance()->getInfoPath())) mkdir(Configuration::getInstance()->getInfoPath());
        if (!file_exists(Configuration::getInstance()->getInfoPath() . "banlogs/")) mkdir(Configuration::getInstance()->getInfoPath() . "banlogs/");
        if (!file_exists(Configuration::getInstance()->getInfoPath() . "mutelogs/")) mkdir(Configuration::getInstance()->getInfoPath() . "mutelogs/");
        $this->bansFile = new Config(Configuration::getInstance()->getBanPath() . "bans.json", Config::JSON);
        $this->mutesFile = new Config(Configuration::getInstance()->getMutePath() . "mutes.json", Config::JSON);
        $this->playersFile = new Config(Configuration::getInstance()->getInfoPath() . "players.json", Config::JSON);
    }

    public function addBan(Ban $ban): void {
        $this->bansFile->set($ban->getPlayer(), $ban->toArray());
        try {
            $this->bansFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function addBanLog(Ban $ban): void {
        $file = $this->getBanLogsFile($ban);
        $file->set($ban->getTime()->format("Y-m-d H:i:s"), $ban->toArray());
        try {
            $file->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function removeBan(Ban $ban): void {
        $this->bansFile->remove($ban->getPlayer());
        try {
            $this->bansFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function editBan(Ban $ban, ?string $newTime): void {
        $data = $ban->toArray();
        $data["expire"] = $newTime;
        $this->bansFile->set($ban->getPlayer(), $data);
        try {
            $this->bansFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function getBan(string $username): Promise {
        $resolver = new PromiseResolver();

        if ($this->bansFile->exists($username) && ($ban = Ban::fromArray($this->bansFile->get($username))) !== null) {
            $resolver->resolve($ban);
        } else $resolver->reject();

        return $resolver->getPromise();
    }

    public function getBans(): Promise {
        $bans = [];
        $resolver = new PromiseResolver();

        foreach ($this->bansFile->getAll() as $data) {
            if (($ban = Ban::fromArray($data)) !== null) {
                $bans[$ban->getPlayer()] = $ban;
            }
        }

        $resolver->resolve($bans);
        return $resolver->getPromise();
    }

    public function addMute(Mute $mute): void {
        $this->mutesFile->set($mute->getPlayer(), $mute->toArray());
        try {
            $this->mutesFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function addMuteLog(Mute $mute): void {
        $file = $this->getMuteLogsFile($mute);
        $file->set($mute->getTime()->format("Y-m-d H:i:s"), $mute->toArray());
        try {
            $file->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function removeMute(Mute $mute): void {
        $this->mutesFile->remove($mute->getPlayer());
        try {
            $this->mutesFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function editMute(Mute $mute, ?string $newTime): void {
        $data = $mute->toArray();
        $data["expire"] = $newTime;
        $this->mutesFile->set($mute->getPlayer(), $data);
        try {
            $this->mutesFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function getMute(string $username): Promise {
        $resolver = new PromiseResolver();

        if ($this->mutesFile->exists($username) && ($mute = Mute::fromArray($this->mutesFile->get($username))) !== null) {
            $resolver->resolve($mute);
        } else $resolver->reject();

        return $resolver->getPromise();
    }

    public function getMutes(): Promise {
        $mutes = [];
        $resolver = new PromiseResolver();

        foreach ($this->mutesFile->getAll() as $data) {
            if (($mute = Mute::fromArray($data)) !== null) {
                $mutes[$mute->getPlayer()] = $mute;
            }
        }

        $resolver->resolve($mutes);
        return $resolver->getPromise();
    }

    public function createPlayer(Player $player): void {
        $this->playersFile->set($player->getName(), [
            "ban_points" => 0,
            "mute_points" => 0,
            "notifications" => false
        ]);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function setNotifications(string $username, bool $value): void {
        $this->playersFile->setNested($username . ".notifications", $value);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function addBanPoint(string $username): void {
        $points = $this->playersFile->getNested($username . ".ban_points", 0);
        $this->playersFile->setNested($username . ".ban_points", ++$points);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function removeBanPoint(string $username): void {
        $points = $this->playersFile->getNested($username . ".ban_points", 0);
        if ($points <= 0) return;
        $this->playersFile->setNested($username . ".ban_points", --$points);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function addMutePoint(string $username): void {
        $points = $this->playersFile->getNested($username . ".mute_points", 0);
        $this->playersFile->setNested($username . ".mute_points", ++$points);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function getBanPoints(string $username): Promise {
        $resolver = new PromiseResolver();

        if ($this->playersFile->exists($username)) {
            $resolver->resolve($this->playersFile->getNested($username . ".ban_points"));
        } else $resolver->reject();

        return $resolver->getPromise();
    }

    public function getMutePoints(string $username): Promise {
        $resolver = new PromiseResolver();

        if ($this->playersFile->exists($username)) {
            $resolver->resolve($this->playersFile->getNested($username . ".mute_points"));
        } else $resolver->reject();

        return $resolver->getPromise();
    }

    public function removeMutePoint(string $username): void {
        $points = $this->playersFile->getNested($username . ".mute_points", 0);
        if ($points <= 0) return;
        $this->playersFile->setNested($username . ".mute_points", --$points);
        try {
            $this->playersFile->save();
        } catch (\JsonException $e) {
            BanSystem::getInstance()->getLogger()->logException($e);
        }
    }

    public function getBanLogs(string $username): Promise {
        $banLogs = [];
        $resolver = new PromiseResolver();

        foreach ($this->getBanLogsFile($username)->getAll() as $data) {
            if (($ban = Ban::fromArray($data)) !== null) {
                $banLogs[] = $ban;
            }
        }

        $resolver->resolve($banLogs);
        return $resolver->getPromise();
    }

    public function getMuteLogs(string $username): Promise {
        $muteLogs = [];
        $resolver = new PromiseResolver();

        foreach ($this->getMuteLogsFile($username)->getAll() as $data) {
            if (($mute = Mute::fromArray($data)) !== null) {
                $muteLogs[] = $mute;
            }
        }

        $resolver->resolve($muteLogs);
        return $resolver->getPromise();
    }

    public function isBanned(string $username): Promise {
        $resolver = new PromiseResolver();
        $resolver->resolve($this->bansFile->exists($username));
        return $resolver->getPromise();
    }

    public function isMuted(string $username): Promise {
        $resolver = new PromiseResolver();
        $resolver->resolve($this->mutesFile->exists($username));
        return $resolver->getPromise();
    }

    public function checkPlayer(string $username): Promise {
        $resolver = new PromiseResolver();
        $resolver->resolve($this->playersFile->exists($username));
        return $resolver->getPromise();
    }

    public function hasNotifications(string $username): Promise {
        $resolver = new PromiseResolver();

        if ($this->playersFile->exists($username)) {
            $resolver->resolve($this->playersFile->getNested($username . ".notifications", false));
        } else $resolver->reject();

        return $resolver->getPromise();
    }

    private function getBanLogsFile(Ban|Mute|string $player): Config {
        $player = ($player instanceof Ban || $player instanceof Mute) ? $player->getPlayer() : $player;
        return new Config(Configuration::getInstance()->getInfoPath() . "banlogs/" . $player . ".json", 1);
    }

    private function getMuteLogsFile(Ban|Mute|string $player): Config {
        $player = ($player instanceof Ban || $player instanceof Mute) ? $player->getPlayer() : $player;
        return new Config(Configuration::getInstance()->getInfoPath() . "mutelogs/" . $player . ".json", 1);
    }
}