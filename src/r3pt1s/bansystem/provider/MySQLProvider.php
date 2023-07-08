<?php

namespace r3pt1s\bansystem\provider;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\util\AsyncExecutor;
use r3pt1s\bansystem\util\Database;

class MySQLProvider implements Provider {

    public function __construct() {
        AsyncExecutor::execute(fn(Database $database) => $database->initializeTable(), function(): void {
            BanSystem::getInstance()->getBanManager()->load();
            BanSystem::getInstance()->getMuteManager()->load();
        });
    }

    public function addBan(Ban $ban): void {
        $data = $ban->toArray();
        AsyncExecutor::execute(fn(Database $database) => $database->insert("bans", $data));
    }

    public function addBanLog(Ban $ban): void {
        $data = $ban->toArray();
        AsyncExecutor::execute(fn(Database $database) => $database->insert("banlogs", $data));
    }

    public function removeBan(Ban $ban): void {
        $player = $ban->getPlayer();
        AsyncExecutor::execute(fn(Database $database) => $database->delete("bans", ["player" => $player]));
    }

    public function editBan(Ban $ban, ?string $newTime): void {
        $player = $ban->getPlayer();
        AsyncExecutor::execute(fn(Database $database) => $database->update("bans", ["expire" => $newTime], ["player" => $player]));
    }

    public function getBan(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->get("bans", "*", ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            if (($ban = Ban::fromArray($data)) !== null) {
                $resolver->resolve($ban);
            } else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getBans(): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->select("bans", ["player", "reason", "moderator", "expire", "time"], "*"), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            $bans = [];
            foreach ($data as $banData) {
                if (($ban = Ban::fromArray($banData)) !== null) {
                    $bans[$ban->getPlayer()] = $ban;
                }
            }

            $resolver->resolve($bans);
        });

        return $resolver->getPromise();
    }

    public function addMute(Mute $mute): void {
        $data = $mute->toArray();
        AsyncExecutor::execute(fn(Database $database) => $database->insert("mutes", $data));
    }

    public function addMuteLog(Mute $mute): void {
        $data = $mute->toArray();
        AsyncExecutor::execute(fn(Database $database) => $database->insert("mutelogs", $data));
    }

    public function removeMute(Mute $mute): void {
        $player = $mute->getPlayer();
        AsyncExecutor::execute(fn(Database $database) => $database->delete("mutes", ["player" => $player]));
    }

    public function editMute(Mute $mute, ?string $newTime): void {
        $player = $mute->getPlayer();
        AsyncExecutor::execute(fn(Database $database) => $database->update("mutes", ["expire" => $newTime], ["player" => $player]));
    }

    public function getMute(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->get("mutes", "*", ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            if (($mute = Mute::fromArray($data)) !== null) {
                $resolver->resolve($mute);
            } else $resolver->reject();
        });

        return $resolver->getPromise();
    }

    public function getMutes(): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->select("mutes", ["player", "reason", "moderator", "expire", "time"], "*"), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            $mutes = [];
            foreach ($data as $muteData) {
                if (($mute = Mute::fromArray($muteData)) !== null) {
                    $mutes[$mute->getPlayer()] = $mute;
                }
            }

            $resolver->resolve($mutes);
        });

        return $resolver->getPromise();
    }

    public function createPlayer(Player $player): void {
        $username = $player->getName();
        AsyncExecutor::execute(fn(Database $database) => $database->insert("players", ["player" => $username, "ban_points" => 0, "mute_points" => 0, "notifications" => 0]));
    }

    public function setNotifications(string $username, bool $value): void {
        AsyncExecutor::execute(fn(Database $database) => $database->update("players", ["notifications" => $value ? 1 : 0], ["player" => $username]));
    }

    public function addBanPoint(string $username): void {
        AsyncExecutor::execute(fn(Database $database) => $database->update("players", ["ban_points[+]" => 1], ["player" => $username]));
    }

    public function removeBanPoint(string $username): void {
        AsyncExecutor::execute(fn(Database $database) => $database->update("players", ["ban_points[-]" => 1], ["player" => $username]));
    }

    public function addMutePoint(string $username): void {
        AsyncExecutor::execute(fn(Database $database) => $database->update("players", ["mute_points[+]" => 1], ["player" => $username]));
    }

    public function removeMutePoint(string $username): void {
        AsyncExecutor::execute(fn(Database $database) => $database->update("players", ["mute_points[-]" => 1], ["player" => $username]));
    }

    public function getBanPoints(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->get("players", ["ban_points"], ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            if (!is_numeric($data["ban_points"])) {
                $resolver->reject();
                return;
            }

            $resolver->resolve(intval($data["ban_points"]));
        });

        return $resolver->getPromise();
    }

    public function getMutePoints(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->get("players", ["mute_points"], ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            if (!is_numeric($data["mute_points"])) {
                $resolver->reject();
                return;
            }

            $resolver->resolve(intval($data["mute_points"]));
        });

        return $resolver->getPromise();
    }

    public function getBanLogs(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->select("banlogs", ["player", "reason", "moderator", "expire", "time"], ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            $bans = [];
            foreach ($data as $banData) {
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

        AsyncExecutor::execute(fn(Database $database) => $database->select("mutelogs", ["player", "reason", "moderator", "expire", "time"], ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            $mutes = [];
            foreach ($data as $muteData) {
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
        AsyncExecutor::execute(fn(Database $database) => $database->has("bans", ["player" => $username]), fn(bool $banned) => $resolver->resolve($username));
        return $resolver->getPromise();
    }

    public function isMuted(string $username): Promise {
        $resolver = new PromiseResolver();
        AsyncExecutor::execute(fn(Database $database) => $database->has("mutes", ["player" => $username]), fn(bool $banned) => $resolver->resolve($username));
        return $resolver->getPromise();
    }

    public function checkPlayer(string $username): Promise {
        $resolver = new PromiseResolver();
        AsyncExecutor::execute(fn(Database $database) => $database->has("players", ["player" => $username]), fn(bool $exists) => $resolver->resolve($exists));
        return $resolver->getPromise();
    }

    public function hasNotifications(string $username): Promise {
        $resolver = new PromiseResolver();

        AsyncExecutor::execute(fn(Database $database) => $database->get("players", ["notifications"], ["player" => $username]), function(?array $data) use($resolver): void {
            if (!is_array($data)) {
                $resolver->reject();
                return;
            }

            if (!is_numeric($data["notifications"])) {
                $resolver->reject();
                return;
            }

            $resolver->resolve(boolval(intval($data["notifications"])));
        });

        return $resolver->getPromise();
    }
}