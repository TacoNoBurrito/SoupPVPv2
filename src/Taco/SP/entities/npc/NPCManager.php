<?php namespace Taco\SP\entities\npc;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use Taco\SP\Loader;

class NPCManager {

    public static function init() : void {
        foreach(NPCS::NPC as $codeName => $info) {
            $nbt = Entity::createBaseNBT(new Vector3($info["x"], $info["y"], $info["z"]), null, 0, 0);
            $entity = new NPCEntity(Loader::getInstance()->getServer()->getDefaultLevel(), $nbt, $info["name"], $info["command"], $codeName);
            $entity->setNameTagAlwaysVisible(true);
            $entity->spawnToAll();
        }
    }

}