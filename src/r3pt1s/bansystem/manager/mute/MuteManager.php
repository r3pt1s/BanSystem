<?php

namespace r3pt1s\bansystem\manager\mute;

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

class MuteManager {
    use SingletonTrait;

    private IHandler $muteHandler;
    /** @var array<Mute> */
    private array $mutes = [];

    public function __construct() {
        self::setInstance($this);
        $this->muteHandler = new MuteHandler();
    }

    /** @internal */
    public function load(): void {
        BanSystem::getInstance()->getProvider()->getMutes()->onCompletion(fn(array $mutes) => $this->mutes = $mutes, fn() => BanSystem::getInstance()->getLogger()->emergency("§cFailed to fetch mutes"));
    }

    public function addMute(Mute $mute, bool $automatic = false): int {
        if ($this->isMuted($mute->getPlayer())) return BanSystem::FAILED_ALREADY;
        ($ev = new PlayerMuteEvent($mute))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $this->mutes[$mute->getPlayer()] = $mute;

        if ($automatic) {
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $mute->getPlayer() . " §7has been automatically §cmuted§7.");
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Reason: §e" . $mute->getReason());
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Until: §e" . ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§l§cPERMANENTLY"));
        } else {
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $mute->getPlayer() . " §7has been §cmuted §7by §e" . $mute->getModerator() . "§7.");
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Reason: §e" . $mute->getReason());
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Until: §e" . ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§l§cPERMANENTLY"));
        }

        BanSystem::getInstance()->getProvider()->addMute($mute);
        BanSystem::getInstance()->getProvider()->addMutePoint($mute->getPlayer());
        BanSystem::getInstance()->getProvider()->addMuteLog($mute);

        return BanSystem::SUCCESS;
    }

    public function editMute(Mute $mute, CommandSender $moderator, \DateTime $newTime): int {
        if (!$this->isMuted($mute->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerMuteEditEvent($mute, $moderator, $newTime))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        $better = $mute->getExpire() > $newTime;
        $mute->setExpire($newTime);

        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7The mute of §e" . $mute->getPlayer() . " §7has been §" . ($better ? "a" : "c") . "edited §7by §e" . $moderator->getName() . "§7.");
        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7New duration: §e" . $newTime->format("Y-m-d H:i:s"));

        BanSystem::getInstance()->getProvider()->editMute($mute, $newTime->format("Y-m-d H:i:s"));

        return BanSystem::SUCCESS;
    }

    public function removeMute(Mute $mute, ?CommandSender $moderator, bool $mistake): int {
        if (!$this->isMuted($mute->getPlayer())) return BanSystem::FAILED_NOT;
        ($ev = new PlayerUnmuteEvent($mute, $moderator, $mistake))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        unset($this->mutes[$mute->getPlayer()]);

        if ($moderator === null) {
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $mute->getPlayer() . " §7has been automatically §aunmuted§7.");
        } else {
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $mute->getPlayer() . " §7has been §aunmuted §7by §e" . $moderator->getName() . "§7.");
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Mistake: §" . ($mistake ? "aYes" : "cNo"));
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