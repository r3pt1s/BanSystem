<?php

namespace r3pt1s\bansystem\provider;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;

class MySQLProvider implements Provider {

    private DataConnector $connector;

    public function load(): void {
        $this->connector = libasynql::create(BanSystem::getInstance(), BanSystem::getInstance()->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ], true);
        $this->connector->executeGeneric("table.bans", onSuccess: fn() => BanManager::getInstance()->load());
        $this->connector->executeGeneric("table.mutes", onSuccess: fn() => MuteManager::getInstance()->load());
        $this->connector->executeGeneric("table.players");
        $this->connector->executeGeneric("table.banlogs");
        $this->connector->executeGeneric("table.mutelogs");
    }

    public function addBan(Ban $ban): void {
        $this->connector->executeInsert("bans.add", [
            "player" => $ban->getPlayer(), "moderator" => $ban->getModerator(),
            "reason" => $ban->getReason(),
            "time" => $ban->getTime()->format("Y-m-d H:i:s"), "expire" => $ban->getExpire()?->format("Y-m-d H:i:s") ?? null
        ]);
    }

    public function addBanLog(Ban $ban): void {
        $this->connector->executeInsert("bans.addLog", [
            "player" => $ban->getPlayer(), "moderator" => $ban->getModerator(),
            "reason" => $ban->getReason(),
            "time" => $ban->getTime()->format("Y-m-d H:i:s"), "expire" => $ban->getExpire()?->format("Y-m-d H:i:s") ?? null
        ]);
    }

    public function removeBan(Ban $ban): void {
        $this->connector->executeInsert("bans.remove", [
            "player" => $ban->getPlayer()
        ]);
    }

    public function editBan(Ban $ban, ?string $newTime): void {
        $this->connector->executeChange("bans.edit", [
            "player" => $ban->getPlayer(), "newTime" => $newTime
        ]);
    }

    public function getBan(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("bans.get", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            if (count($rows) == 0) {
                $resolver->reject();
                return;
            }

            if (($ban = Ban::fromArray($rows[0])) !== null) {
                $resolver->resolve($ban);
            } else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getBans(): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("bans.getAll", [], function (array $rows) use($resolver): void {
            $bans = [];
            foreach ($rows as $banData) {
                if (($ban = Ban::fromArray($banData)) !== null) {
                    $bans[$ban->getPlayer()] = $ban;
                }
            }

            $resolver->resolve($bans);
        });

        return $resolver->getPromise();
    }

    public function addMute(Mute $mute): void {
        $this->connector->executeInsert("mutes.add", [
            "player" => $mute->getPlayer(), "moderator" => $mute->getModerator(),
            "reason" => $mute->getReason(),
            "time" => $mute->getTime()->format("Y-m-d H:i:s"), "expire" => $mute->getExpire()?->format("Y-m-d H:i:s") ?? null
        ]);
    }

    public function addMuteLog(Mute $mute): void {
        $this->connector->executeInsert("mutes.addLog", [
            "player" => $mute->getPlayer(), "moderator" => $mute->getModerator(),
            "reason" => $mute->getReason(),
            "time" => $mute->getTime()->format("Y-m-d H:i:s"), "expire" => $mute->getExpire()?->format("Y-m-d H:i:s") ?? null
        ]);
    }

    public function removeMute(Mute $mute): void {
        $this->connector->executeGeneric("mutes.remove", [
            "player" => $mute->getPlayer()
        ]);
    }

    public function editMute(Mute $mute, ?string $newTime): void {
        $this->connector->executeChange("mutes.edit", [
            "player" => $mute->getPlayer(), "newTime" => $newTime
        ]);
    }

    public function getMute(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("mutes.get", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            if (count($rows) == 0) {
                $resolver->reject();
                return;
            }

            if (($mute = Mute::fromArray($rows[0])) !== null) {
                $resolver->resolve($mute);
            } else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getMutes(): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("mutes.getAll", [], function (array $rows) use($resolver): void {
            $mutes = [];
            foreach ($rows as $muteData) {
                if (($mute = Mute::fromArray($muteData)) !== null) {
                    $mutes[$mute->getPlayer()] = $mute;
                }
            }

            $resolver->resolve($mutes);
        });

        return $resolver->getPromise();
    }

    public function createPlayer(Player $player): void {
        $this->connector->executeInsert("player.create", [
            "player" => $player->getName()
        ]);
    }

    public function setNotifications(string $username, bool $value): void {
        $this->connector->executeChange("player.updateNotifications", [
            "player" => $username, "notifications" => (int) $value
        ]);
    }

    public function addBanPoint(string $username): void {
        $this->connector->executeChange("player.addBanPoint", [
            "player" => $username
        ]);
    }

    public function removeBanPoint(string $username): void {
        $this->connector->executeChange("player.subBanPoint", [
            "player" => $username
        ]);
    }

    public function addMutePoint(string $username): void {
        $this->connector->executeChange("player.addMutePoint", [
            "player" => $username
        ]);
    }

    public function removeMutePoint(string $username): void {
        $this->connector->executeChange("player.subMutePoint", [
            "player" => $username
        ]);
    }

    public function getBanPoints(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("player.getBanPoints", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            if (count($rows) == 0) {
                $resolver->reject();
                return;
            }

            if (is_numeric($rows[0]["ban_points"])) $resolver->resolve((int) $rows[0]["ban_points"]);
            else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getMutePoints(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("player.getMutePoints", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            if (count($rows) == 0) {
                $resolver->reject();
                return;
            }

            if (is_numeric($rows[0]["mute_points"])) $resolver->resolve((int) $rows[0]["mute_points"]);
            else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getBanLogs(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("bans.getLog", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            $bans = [];
            foreach ($rows as $banData) {
                if (($ban = Ban::fromArray($banData)) !== null) {
                    $bans[] = $ban;
                }
            }

            $resolver->resolve($bans);
        });

        return $resolver->getPromise();
    }

    public function getMuteLogs(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("mutes.getLog", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            $mutes = [];
            foreach ($rows as $muteData) {
                if (($mute = Mute::fromArray($muteData)) !== null) {
                    $mutes[] = $mute;
                }
            }

            $resolver->resolve($mutes);
        });

        return $resolver->getPromise();
    }

    public function isBanned(string $username): Promise {
        $resolver = new PromiseResolver();
        $this->connector->executeSelect("bans.check", [
            "player" => $username
        ], fn (array $rows) => $resolver->resolve((bool) array_values($rows[0])[0]));
        return $resolver->getPromise();
    }

    public function isMuted(string $username): Promise {
        $resolver = new PromiseResolver();
        $this->connector->executeSelect("mutes.check", [
            "player" => $username
        ], fn (array $rows) => $resolver->resolve((bool) array_values($rows[0])[0]));
        return $resolver->getPromise();
    }

    public function checkPlayer(string $username): Promise {
        $resolver = new PromiseResolver();
        $this->connector->executeSelect("player.check", [
            "player" => $username
        ], fn (array $rows) => $resolver->resolve((bool) array_values($rows[0])[0]));
        return $resolver->getPromise();
    }

    public function hasNotifications(string $username): Promise {
        $resolver = new PromiseResolver();

        $this->connector->executeSelect("player.getNotifications", [
            "player" => $username
        ], function (array $rows) use($resolver): void {
            if (count($rows) == 0) {
                $resolver->reject();
                return;
            }

            if (is_numeric($rows[0]["notifications"])) $resolver->resolve((bool) $rows[0]["notifications"]);
            else $resolver->reject();
        });

        return $resolver->getPromise();
    }
}