<?php namespace Taco\SP\listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Bowl;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use Taco\SP\classes\Classes;
use Taco\SP\classes\ClassManager;
use Taco\SP\entities\classEntities\types\NecromancerZombie;
use Taco\SP\entities\npc\NPCEntity;
use Taco\SP\forms\ClassOpenForm;
use Taco\SP\forms\ClassPurchaseForm;
use Taco\SP\forms\JoinForm;
use Taco\SP\Loader;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\tasks\async\LoadPlayer;
use Taco\SP\tasks\async\SavePlayer;

class BaseListener implements Listener {

    private array $lastHitBy = [];

    private array $hubCooldown = [];

    public function giveIdleKit(Player $player) : void {
        ClassManager::$equippedClasses[$player->getLowerCaseName()] = "none";
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        $player->setGamemode(0);
        $player->setFlying(false);
        $player->setFood(20);
        $player->setHealth(20);
        $player->getInventory()->setItem(4, Item::get(ItemIds::COMPASS)->setCustomName(TF::RESET.TF::AQUA."Last Chosen Kit"));
        $player->getInventory()->setItem(0, Item::get(ItemIds::DIAMOND_SWORD)->setCustomName(TF::RESET.TF::AQUA."Choose a Kit"));
        $player->getInventory()->setItem(1, Item::get(ItemIds::EMERALD)->setCustomName(TF::RESET.TF::AQUA."Purchase a Kit"));
    }

    public function onIdleInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if (Loader::getClassManager()->getClass($player) == "none") {
            if (time() - $this->hubCooldown[$player->getLowerCaseName()] < 1) return;
            else $this->hubCooldown[$player->getLowerCaseName()] = time();
            switch($item->getCustomName()) {
                case TF::RESET.TF::AQUA."Last Chosen Kit":
                    if (Loader::getInstance()->lastChosenClass[$player->getLowerCaseName()] == "") {
                        $player->sendMessage(TF::RED."You have not chosen a class yet in this session.");
                        return;
                    }
                    Loader::getClassManager()->giveClassToPlayer($player, Loader::getInstance()->lastChosenClass[$player->getLowerCaseName()]);
                    break;
                case TF::RESET.TF::AQUA."Choose a Kit":
                    ClassOpenForm::open($player);
                    break;
                case TF::RESET.TF::AQUA."Purchase a Kit":
                    ClassPurchaseForm::open($player);
                    break;
            }
        }
    }

    public function onLoad(PlayerPreLoginEvent $event) : void {
        $player = $event->getPlayer();
        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new LoadPlayer($player->getLowerCaseName()));
        $this->lastHitBy[$player->getLowerCaseName()] = "";
        Loader::getInstance()->lastChosenClass[$player->getLowerCaseName()] = "";
        foreach(Classes::CLASSES as $name => $garble) {
            ClassManager::$cooldowns[$player->getLowerCaseName()][$name] = 0;
        }
        $this->hubCooldown[$player->getLowerCaseName()] = 0;
    }

    public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $event->setJoinMessage(TF::WHITE." + ".TF::AQUA.$player->getName());
        $this->giveIdleKit($player);
        $player->teleport(Loader::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
        JoinForm::open($player);
    }

    public function onQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        unset($this->lastHitBy[$player->getLowerCaseName()]);
        unset(Loader::getInstance()->lastChosenClass[$player->getLowerCaseName()]);
        $event->setQuitMessage(TF::WHITE." - ".TF::AQUA.$player->getName());
        $data = Loader::getInstance()->playerData[$player->getLowerCaseName()];
        Loader::getInstance()->getServer()->getAsyncPool()->submitTask(new SavePlayer($player->getLowerCaseName(),
            $data["kills"],
            $data["deaths"],
            $data["killstreak"],
            $data["rank"],
            $data["classesPurchased"],
            $data["credits"]
        ));
        unset(Loader::getInstance()->playerData[$player->getLowerCaseName()]);
        unset($this->hubCooldown[$player->getLowerCaseName()]);
    }


    public function regularDeath(Player $player, Player $killer) : void {
        $killstreak = Loader::getInstance()->playerData[$player->getLowerCaseName()]["killstreak"];
        $player->sendMessage(TF::WHITE."You have lost your killstreak of ".$killstreak."!");
        Loader::getInstance()->playerData[$killer->getLowerCaseName()]["kills"] += 1;
        Loader::getInstance()->playerData[$killer->getLowerCaseName()]["killstreak"] += 1;
        Loader::getInstance()->playerData[$player->getLowerCaseName()]["deaths"] += 1;
        Loader::getInstance()->playerData[$player->getLowerCaseName()]["killstreak"] = 0;
        $killstreak = Loader::getInstance()->playerData[$killer->getLowerCaseName()]["killstreak"];
        $killer->sendMessage("You now have a killstreak of ".TF::AQUA.$killstreak."!");
        if (is_int($killstreak/5) and $killstreak > 1) {
            Loader::getInstance()->getServer()->broadcastMessage(TF::AQUA.$killer->getName().TF::WHITE." has gotten a killstreak of ".TF::AQUA.$killstreak."!");
        }
    }

    public function dieByClassEntity(Player $player, Player $killer, string $type) : void {
        Loader::getInstance()->getServer()->broadcastMessage(TF::AQUA . $killer->getName() . TF::WHITE . "'s $type has killed " . TF::AQUA . $player->getName()."!");
        $this->regularDeath($player, $killer);
    }

    public function die(Player $player, $killer = "") : void {
        $this->giveIdleKit($player);
        $player->teleport(Loader::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
        if ($killer == "") return;
        if ($killer == "fall") {
            $lastHitBy = $this->lastHitBy[$player->getLowerCaseName()];
            $isNull = false;
            if ($lastHitBy == "") $isNull = true;
            $lhb = Loader::getInstance()->getServer()->getPlayer($lastHitBy);
            if ($lhb == null) $isNull = true;
            if ($isNull) {
                Loader::getInstance()->getServer()->broadcastMessage(TF::AQUA.$killer->getName().TF::WHITE." has fell to their death.");
                $killstreak = Loader::getInstance()->playerData[$player->getLowerCaseName()]["killstreak"];
                $player->sendMessage("You have lost your killstreak of ".TF::AQUA.$killstreak."!");
                Loader::getInstance()->playerData[$player->getLowerCaseName()]["deaths"] += 1;
                Loader::getInstance()->playerData[$player->getLowerCaseName()]["killstreak"] = 0;
                return;
            }
            Loader::getInstance()->getServer()->broadcastMessage(TF::AQUA.$killer->getName().TF::WHITE." fell due to ".TF::AQUA.$lhb->getName()."!");
            $this->regularDeath($player,$lhb);
            return;
        }
        if ($killer instanceof Player) {
            Loader::getInstance()->getServer()->broadcastMessage(TF::AQUA . $killer->getName() . TF::WHITE . " has killed " . TF::AQUA . $player->getName() . TF::WHITE . " using class " . TF::AQUA . Loader::getClassManager()->getClass($killer) . "!");
            $this->regularDeath($player,$killer);
        }
    }
    /* This ended up triggering when using things like setItem() because i was dumb.
    public function onInventoryMoveItem(EntityInventoryChangeEvent $event) : void {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if ($event->getOldItem() instanceof Bowl or (!Loader::getClassManager()->getClass($player) == "none")) return;
            $event->setCancelled(true);
        }
    }
    */
    public function onDrop(PlayerDropItemEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item instanceof Bowl) return;
        $player->sendMessage(TF::RED."You can only drop bowls!");
        $event->setCancelled(true);
    }

    public function onEntityDamage(EntityDamageEvent $event) : void {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (Loader::getClassManager()->getClass($player) == "none") {
                $event->setCancelled(true);
                return;
            }
            $cause = $event->getCause();
            if ($cause == EntityDamageEvent::CAUSE_VOID) {
                $this->die($player);
                $event->setCancelled(true);
            }
        } else if ($player instanceof NPCEntity) $event->setCancelled(true);
    }

    public function onEntityMotion(EntityMotionEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof NPCEntity) $event->setCancelled(true);
    }

    public function onDamage(EntityDamageByEntityEvent $event) : void {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($player instanceof Player and $damager instanceof Player) {
            if ($player->getHealth() - $event->getFinalDamage() < 0.1) {
                $event->setCancelled(true);
                $this->die($player, $damager);
            }
        } else {
            if ($player instanceof NPCEntity) {
                if ($damager instanceof Player) {
                    $damager->chat($player->getCommand());
                    $event->setCancelled(true);
                    return;
                }
            }
            if ($player instanceof Player and $player->getHealth() - $event->getFinalDamage() < 0.1) $this->die($player);
        }
        if ($damager instanceof NecromancerZombie) {
            if ($player->getHealth() - $event->getFinalDamage() < 0.1) {
                $this->dieByClassEntity($player, $damager->getOwner(), "Zombie");
                $event->setCancelled(true);
            }
        }
    }

    public function onChat(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();
        $event->setFormat(TF::RESET.$player->getName().": ".TF::AQUA.$event->getMessage());
    }

    public function onHunger(PlayerExhaustEvent $event) : void {
        $event->setCancelled(true);
    }

    public function onBreak(BlockBreakEvent $event) : void {
        $player = $event->getPlayer();
        if (!$player->isOp()) $event->setCancelled(true);
    }

    public function onPlace(BlockPlaceEvent $event) : void {
        $player = $event->getPlayer();
        if (!$player->isOp()) $event->setCancelled(true);
    }

    public function onInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();
        if ($action == PlayerInteractEvent::RIGHT_CLICK_AIR or $action == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if ($item->getId() == ItemIds::MUSHROOM_STEW) {
                $player->getInventory()->setItemInHand(Item::get(ItemIds::BOWL));
                $player->setHealth($player->getHealth()+4);
            }
        }
    }

}