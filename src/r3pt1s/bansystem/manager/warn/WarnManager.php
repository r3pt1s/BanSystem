<?php

namespace r3pt1s\bansystem\manager\warn;

use JetBrains\PhpStorm\Pure;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\event\warn\PlayerWarnEvent;
use r3pt1s\bansystem\event\warn\PlayerWarnRemoveEvent;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Utils;

class WarnManager {
    use SingletonTrait;

    private array $warns = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function addWarn(Warn $warn): int {
        ($ev = new PlayerWarnEvent($warn))->call();
        if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

        if (!isset($this->warns[$warn->getPlayer()->getName()])) $this->warns[$warn->getPlayer()->getName()] = [];
        $this->warns[$warn->getPlayer()->getName()][] = $warn;

        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§e" . $warn->getPlayer()->getName() . " §7has been §cwarned §7by §e" . $warn->getModerator() . "§7.");
        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Reason: §e" . ($warn->getReason() ?? "No reason provided"));

        $this->check($warn->getPlayer());

        return BanSystem::SUCCESS;
    }

    public function removeWarn(Warn $warn, Player $moderator): int {
        if (isset($this->warns[$warn->getPlayer()->getName()]) && in_array($warn, $this->warns[$warn->getPlayer()->getName()])) {
            ($ev = new PlayerWarnRemoveEvent($warn, $moderator))->call();
            if ($ev->isCancelled()) return BanSystem::FAILED_CANCELLED;

            unset($this->warns[$warn->getPlayer()->getName()][array_search($warn, $this->warns[$warn->getPlayer()->getName()])]);
            $this->warns[$warn->getPlayer()->getName()] = array_values($this->warns[$warn->getPlayer()->getName()]);

            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7The warn from §e" . $warn->getModerator() . " §7given to §e" . $warn->getPlayer()->getName() . " §7has been §aremoved §7by §e" . $moderator->getName() . "§7.");
            NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Reason for the warn: §e" . ($warn->getReason() ?? "No reason provided"));

            return BanSystem::SUCCESS;
        }

        return BanSystem::FAILED_NOT;
    }

    public function clearWarns(Player $player, CommandSender $moderator): void {
        $this->warns[$player->getName()] = [];

        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7The warns of §e" . $player->getName() . " §7were §acleared§7.");
        NotifyManager::getInstance()->sendNotification(BanSystem::getPrefix() . "§7Moderator: §e" . $moderator->getName());
    }

    public function check(Player $player): void {
        $warnCount = count($this->getWarns($player));
        if (Configuration::getInstance()->getMaxWarns() > 0) {
            if ($warnCount >= Configuration::getInstance()->getMaxWarns()) {
                $this->warns[$player->getName()] = [];
                $duration = Configuration::getInstance()->getMaxWarnsActionDuration() === null ? null : (($expire = Utils::convertStringToDateFormat(Configuration::getInstance()->getMaxWarnsActionDuration())) !== null ? $expire : null);
                if (Configuration::getInstance()->getMaxWarnsAction() == "ban") {
                    BanManager::getInstance()->addBan(new Ban(
                        $player->getName(),
                        "",
                        Configuration::getInstance()->getMaxWarnsActionReason(),
                        new \DateTime(),
                        $duration
                    ), true);
                } else if (Configuration::getInstance()->getMaxWarnsAction() == "mute") {
                    MuteManager::getInstance()->addMute(new Mute(
                        $player->getName(),
                        "",
                        Configuration::getInstance()->getMaxWarnsActionReason(),
                        new \DateTime(),
                        $duration
                    ), true);
                }
            }
        }
    }

    #[Pure] public function getWarns(Player|string $player): array {
        $player = $player instanceof Player ? $player->getName() : $player;
        return $this->warns[$player] ?? [];
    }

    public static function getInstance(): self {
        return self::$instance ??= new self;
    }
}