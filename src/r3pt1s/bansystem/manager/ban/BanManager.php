<?php

namespace r3pt1s\bansystem\manager\ban;

use alemiz\sga\StarGateAtlantis;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\event\ban\PlayerBanEditEvent;
use r3pt1s\bansystem\event\ban\PlayerBanEvent;
use r3pt1s\bansystem\event\ban\PlayerUnbanEvent;
use r3pt1s\bansystem\handler\BanHandler;
use r3pt1s\bansystem\handler\IHandler;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use r3pt1s\bansystem\network\BansSyncPacket;
use r3pt1s\bansystem\network\PlayerKickPacket;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanManager {
    use SingletonTrait;

    private IHandler $banHandler;
    /** @var array<Ban> */
    private array $bans = [];

    public function __construct() {
        $this->banHandler = new BanHandler();
    }

    /** @internal */
    public function sync(array $bans): void {
        $this->bans = $bans;
    }

    /** @internal */
    public function load(): void {
        BanSystem::getInstance()->getProvider()->getBans()->onCompletion(function(array $bans): void {
            self::setInstance($this);
            $this->bans = $bans;
        }, fn() => BanSystem::getInstance()->getLogger()->emergency("§cFailed to fetch bans"));
    }

    public function addBan(Ban $ban, bool $automatic = false): int {
        if ($this->isBanned($ban->getPlayer())) return BanSystem::FAILED_ALREADY;
        ($ev = new PlayerBanEvent($ban))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $this->bans[$ban->getPlayer()] = $ban;
        if (BanSystem::getInstance()->isUsingStarGate()) {
            StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(BansSyncPacket::create());
            StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(PlayerKickPacket::create($ban->getPlayer(), $this->getBanHandler()->handle($ban->getPlayer())));
        } else {
            if (($player = Server::getInstance()->getPlayerExact($ban->getPlayer())) !== null) $player->kick($this->getBanHandler()->handle($player->getName()));
        }

        if ($automatic) {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_BAN_AUTO, $ban->getPlayer(), $ban->getReason(), ($ban->getExpire()?->format("Y-m-d H:i:s") ?? "§l§c" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
        } else {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_BAN_MANUAL, $ban->getPlayer(), $ban->getModerator(), $ban->getReason(), ($ban->getExpire()?->format("Y-m-d H:i:s") ?? "§l§c" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
        }

        BanSystem::getInstance()->getProvider()->addBan($ban);
        BanSystem::getInstance()->getProvider()->addBanPoint($ban->getPlayer());
        if (Configuration::getInstance()->isMakeBanMuteLogs()) BanSystem::getInstance()->getProvider()->addBanLog($ban);

        return BanSystem::SUCCESS;
    }

    public function editBan(Ban $ban, CommandSender $moderator, \DateTime $newTime): int {
        if (!$this->isBanned($ban->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerBanEditEvent($ban, $moderator, $newTime))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $this->bans[$ban->getPlayer()]->setExpire($newTime);
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(BansSyncPacket::create());

        NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_BAN_EDITED, $ban->getPlayer(), $moderator->getName(), $newTime->format("Y-m-d H:i:s")));

        BanSystem::getInstance()->getProvider()->editBan($ban, $newTime->format("Y-m-d H:i:s"));

        return BanSystem::SUCCESS;
    }

    public function removeBan(Ban $ban, ?CommandSender $moderator, bool $mistake): int {
        if (!$this->isBanned($ban->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerUnbanEvent($ban, $moderator, $mistake))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        unset($this->bans[$ban->getPlayer()]);
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(BansSyncPacket::create());#

        if ($moderator === null) {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_UNBAN_AUTO, $ban->getPlayer()));
        } else {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_UNBAN_MANUAL, $ban->getPlayer(), $moderator->getName(), Language::get()->translate($mistake ? LanguageKeys::RAW_YES : LanguageKeys::RAW_NO)));
        }

        BanSystem::getInstance()->getProvider()->removeBan($ban);
        if ($mistake) BanSystem::getInstance()->getProvider()->removeBanPoint($ban->getPlayer());

        return BanSystem::SUCCESS;
    }

    public function check(): void {
        foreach ($this->bans as $ban) {
            if ($ban->isExpired()) {
                $this->removeBan($ban, null, false);
            }
        }
    }

    public function setBanHandler(IHandler $banHandler): void {
        $this->banHandler = $banHandler;
    }

    public function getBan(Player|string $username): ?Ban {
        $username = $username instanceof Player ? $username->getName() : $username;
        return $this->bans[$username] ?? null;
    }

    public function isBanned(Player|string $username): bool {
        $username = $username instanceof Player ? $username->getName() : $username;
        return isset($this->bans[$username]);
    }

    public function getBanHandler(): IHandler {
        return $this->banHandler;
    }

    public function getBans(): array {
        return $this->bans;
    }
}