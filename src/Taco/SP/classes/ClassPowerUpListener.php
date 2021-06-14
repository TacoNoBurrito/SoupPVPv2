<?php namespace Taco\SP\classes;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\Loader;

class ClassPowerUpListener implements Listener {

    public function onInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $cm = Loader::getClassManager();
        if ($cm->isPowerupItem($item)) {
            if ($cm->isInteractPowerup($cm->getClass($player))) {
                if ($cm->getCooldownOfPlayerOnClass($player, $cm->getClass($player)) > 0) {
                    $player->sendMessage(TF::WHITE . "This power-up is still on a cool down for " . TF::AQUA . $cm->getCooldownOfPlayerOnClass($player, $cm->getClass($player)) . "s.");
                    return;
                }
                $cm->setClassCooldown($player);
                $player->sendMessage(TF::WHITE . "Successfully used your power-up! You are now on cool down for " . TF::AQUA . $cm->getClassCooldown($cm->getClass($player)) . "!");
                $cm->doPowerup($player, $cm->getClass($player));
            }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event) : void {
        $player = $event->getEntity();
        $killer = $event->getDamager();
        if ($player instanceof Player and $killer instanceof Player) {
            $item = $killer->getInventory()->getItemInHand();
            $cm = Loader::getClassManager();
            if ($cm->isPowerupItem($item)) {
                if (!$cm->isInteractPowerup($cm->getClass($killer))) {
                    if ($cm->getCooldownOfPlayerOnClass($killer, $cm->getClass($killer)) > 0) {
                        $player->sendMessage(TF::WHITE . "This power-up is still on a cool down for " . TF::AQUA . $cm->getCooldownOfPlayerOnClass($player, $cm->getClass($killer)) . "s.");
                        return;
                    }
                    $cm->setClassCooldown($killer);
                    $player->sendMessage(TF::WHITE . "Successfully used your power-up! You are now on cool down for " . TF::AQUA . $cm->getClassCooldown($cm->getClass($killer)) . "!");
                    $cm->doPowerup($killer, $cm->getClass($killer), $player);
                }
            }
        }
    }

}