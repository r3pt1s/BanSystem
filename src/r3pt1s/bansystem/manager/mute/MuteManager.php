<?php

namespace r3pt1s\bansystem\manager\mute;

use alemiz\sga\StarGateAtlantis;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\event\mute\PlayerMuteEditEvent;
use r3pt1s\bansystem\event\mute\PlayerMuteEvent;
use r3pt1s\bansystem\event\mute\PlayerUnmuteEvent;
use r3pt1s\bansystem\handler\IHandler;
use r3pt1s\bansystem\handler\MuteHandler;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use r3pt1s\bansystem\network\MutesSyncPacket;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class MuteManager {
    use SingletonTrait;

    private IHandler $muteHandler;
    /** @var array<Mute> */
    private array $mutes = [];

    public function __construct() {
        $this->muteHandler = new MuteHandler();
    }

    /** @internal */
    public function sync(array $mutes): void {
        $this->mutes = $mutes;
    }

    /** @internal */
    public function load(): void {
        BanSystem::getInstance()->getProvider()->getMutes()->onCompletion(function(array $mutes): void {
            self::setInstance($this);
            $this->mutes = $mutes;
        }, fn() => BanSystem::getInstance()->getLogger()->emergency("§cFailed to fetch mutes"));
    }

    public function addMute(Mute $mute, bool $automatic = false): int {
        if ($this->isMuted($mute->getPlayer())) return BanSystem::FAILED_ALREADY;
        ($ev = new PlayerMuteEvent($mute))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $this->mutes[$mute->getPlayer()] = $mute;
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(MutesSyncPacket::create());

        if ($automatic) {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_MUTE_AUTO, $mute->getPlayer(), $mute->getReason(), ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§l§c" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
        } else {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_MUTE_MANUAL, $mute->getPlayer(), $mute->getModerator(), $mute->getReason(), ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§l§c" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
        }

        BanSystem::getInstance()->getProvider()->addMute($mute);
        BanSystem::getInstance()->getProvider()->addMutePoint($mute->getPlayer());
        if (Configuration::getInstance()->isMakeBanMuteLogs()) BanSystem::getInstance()->getProvider()->addMuteLog($mute);

        return BanSystem::SUCCESS;
    }

    public function editMute(Mute $mute, CommandSender $moderator, \DateTime $newTime): int {
        if (!$this->isMuted($mute->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerMuteEditEvent($mute, $moderator, $newTime))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $this->mutes[$mute->getPlayer()]->setExpire($newTime);
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(MutesSyncPacket::create());

        NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_MUTE_EDITED, $mute->getPlayer(), $moderator->getName(), $newTime->format("Y-m-d H:i:s")));

        BanSystem::getInstance()->getProvider()->editMute($mute, $newTime->format("Y-m-d H:i:s"));

        return BanSystem::SUCCESS;
    }

    public function removeMute(Mute $mute, ?CommandSender $moderator, bool $mistake): int {
        if (!$this->isMuted($mute->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerUnmuteEvent($mute, $moderator, $mistake))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        unset($this->mutes[$mute->getPlayer()]);
        if (BanSystem::getInstance()->isUsingStarGate()) StarGateAtlantis::getInstance()->getDefaultClient()->sendPacket(MutesSyncPacket::create());

        if ($moderator === null) {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_UNMUTE_AUTO, $mute->getPlayer()));
        } else {
            NotifyManager::getInstance()->sendNotification(Language::get()->translate(LanguageKeys::NOTIFY_UNMUTE_MANUAL, $mute->getPlayer(), $moderator->getName(), Language::get()->translate($mistake ? LanguageKeys::RAW_YES : LanguageKeys::RAW_NO)));
        }

        BanSystem::getInstance()->getProvider()->removeMute($mute);
        if ($mistake) BanSystem::getInstance()->getProvider()->removeMutePoint($mute->getPlayer());

        return BanSystem::SUCCESS;
    }

    public function check(): void {
        foreach ($this->mutes as $mute) {
            if ($mute->isExpired()) {
                $this->removeMute($mute, null, false);
            }
        }
    }

    public function setMuteHandler(IHandler $muteHandler): void {
        $this->muteHandler = $muteHandler;
    }

    public function getMute(Player|string $username): ?Mute {
        $username = $username instanceof Player ? $username->getName() : $username;
        return $this->mutes[$username] ?? null;
    }

    public function isMuted(Player|string $username): bool {
        $username = $username instanceof Player ? $username->getName() : $username;
        return isset($this->mutes[$username]);
    }

    public function getMuteHandler(): IHandler {
        return $this->muteHandler;
    }

    public function getMutes(): array {
        return $this->mutes;
    }
}