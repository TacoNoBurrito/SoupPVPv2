<?php namespace Taco\SP\classes;

use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\classes\tasks\NecromancerErectZombiesTask;
use Taco\SP\Loader;

class ClassManager {

    public static array $equippedClasses = [];

    public static array $cooldowns = [];

    public static function init() : void {
        Loader::getInstance()->getServer()->getPluginManager()->registerEvents(new ClassPowerUpListener(), Loader::getInstance());
    }

    public function giveClassToPlayer(Player $player, string $classs) : void {
        $classes = Classes::CLASSES;
        $class = $classes[$classs];
        Loader::getInstance()->lastChosenClass[$player->getLowerCaseName()] = $class;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
        $player->setGamemode(0);
        $player->setFlying(false);
        $helmet = explode(":", $class["helmet"]);
        $chestplate = explode(":", $class["chestplate"]);
        $leggings = explode(":", $class["leggings"]);
        $boots = explode(":", $class["boots"]);
        $sword = explode(":", $class["weapon"]);
        $powerup = explode(":", $class["powerup"]);
        if ($class["hasEffects"]) {
            foreach ($class["effects"] as $effect) {
                $eff = explode(":", $effect);
                $instance = new EffectInstance(Effect::getEffect((int)$eff[0]), INT32_MAX, (int)$eff[1], false, false);
                $player->addEffect($instance);
            }
        }
        $player->getInventory()->setItem(0, Item::get((int)$sword[0], (int)$sword[1]));
        $powerItem = $powerup[2] == "noName" ? Item::get(ItemIds::MUSHROOM_STEW) : Item::get((int)$powerup[0], (int)$powerup[1])->setCustomName($powerup[2]);
        if (!$powerItem->getId() == ItemIds::MUSHROOM_STEW) {
            $nbt = $powerItem->getNamedTag();
            $nbt->setInt("powerup",0,true);
            $powerItem->setNamedTag($nbt);
        }
        $player->getInventory()->setItem(1, $powerItem);
        $player->getArmorInventory()->setHelmet(Item::get((int)$helmet[0], (int)$helmet[1]));
        $player->getArmorInventory()->setChestplate(Item::get((int)$chestplate[0], (int)$chestplate[1]));
        $player->getArmorInventory()->setLeggings(Item::get((int)$leggings[0], (int)$leggings[1]));
        $player->getArmorInventory()->setBoots(Item::get((int)$boots[0], (int)$boots[1]));
        for ($i = 0; $i <= 35; $i++) {
            if ($player->getInventory()->canAddItem(Item::get(282,0,1))) $player->getInventory()->addItem(Item::get(282,0,1));
        }
        self::$equippedClasses[$player->getLowerCaseName()] = $classs;
        $player->sendMessage(TF::WHITE."Successfully equipped: ".TF::AQUA.$classs."!");
        Loader::getUtils()->enhance($player);
    }

    public function getClassCooldown(string $class) : int {
        return Classes::CLASSES[$class]["powerupCooldown"];
    }

    public function setClassCooldown(Player $player) : void {
        self::$cooldowns[$player->getLowerCaseName()][$this->getClass($player)] = $this->getClassCooldown($this->getClass($player));
    }

    public function getCooldownOfPlayerOnClass(Player $player, string $class) : int {
        return self::$cooldowns[$player->getLowerCaseName()][$class];
    }

    public function isPowerupItem(Item $item) : bool {
        $bool = false;
        $cn = $item->getCustomName();
        foreach($this->getClassList() as $name) {
            $exp = explode(":", Classes::CLASSES[$name]["powerup"]);
            if ($cn == $exp[2]) $bool = true;
        }
        return $bool;
    }

    public function getClass(Player $player) : string {
        return self::$equippedClasses[$player->getLowerCaseName()];
    }

    public function doPowerup(Player $player, string $class, Player $hit = null) : void {
        $powerup = Classes::POWERUPS[$class];
        if ($powerup["hasEffects"]) {
            foreach($powerup["effects"] as $effect) {
                $eff = explode(":", $effect);
                $instance = new EffectInstance(Effect::getEffect((int)$eff[0]), (int)$eff[2], (int)$eff[1], false, false);
                $player->addEffect($instance);
            }
        }
        if ($powerup["hasSpecialAbility"]) $this->doSpecialAbility($player, $class, $hit);
    }

    public function doSpecialAbility(Player $player, string $class, Player $hit = null) : void {
        switch ($class) {
            case "Necromancer":
                $effect = new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 5, 2, false);
                $hit->addEffect($effect);
                $hit->sendTitle(TF::YELLOW."  ");
                $hit->sendSubTitle(TF::RED.TF::ITALIC.TF::UNDERLINE."The Necromancer Has Cursed You");
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new NecromancerErectZombiesTask($player, $hit), 20);
                break;
        }
    }

    public function getClassPrice(string $class) : int {
        return Classes::CLASSES[$class]["price"];
    }


    public function isInteractPowerup(string $class) : bool {
        return Classes::CLASSES[$class]["isInteractPowerup"];
    }

    public function giveClass(Player $player, string $class) : void {
        if (Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"] == "") {
            $new = Loader::getUtils()->encodeData([$class]);
            Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"] = $new;
            return;
        }
        $classesPurchased = Loader::getUtils()->decodeData(Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"]);
        $classesPurchased[] = $class;
        $new = Loader::getUtils()->encodeData($classesPurchased);
        Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"] = $new;
    }

    public function getIcon(string $class) : string {
        return Classes::CLASSES[$class]["icon"];
    }

    public function getClassList() : array {
        $array = [];
        foreach(Classes::CLASSES as $name => $iReallyCouldCareLessAboutThisVariable) {
            $array[] = $name;
        }
        return $array;
    }

    public function getUnlockedClasses(Player $player) : array {
        if (Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"] == "") return [];
        $classesPurchased = Loader::getUtils()->decodeData(Loader::getInstance()->playerData[$player->getLowerCaseName()]["classesPurchased"]);
        if (!is_array($classesPurchased)) return [];
        return $classesPurchased;
    }

    public function getLockedClasses(Player $player) : array {
        $array = [];
        $unLocked = $this->getUnlockedClasses($player);
        foreach (Classes::CLASSES as $name => $info) {
            if (in_array($name, $unLocked)) continue;
            if (!$info["isPurchaseable"]) continue;
            $array[] = $name;
        }
        return $array;
    }

}