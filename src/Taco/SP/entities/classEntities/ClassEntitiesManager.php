<?php namespace Taco\SP\entities\classEntities;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\Player;
use Taco\SP\entities\classEntities\types\NecromancerZombie;
use Taco\SP\Loader;

class ClassEntitiesManager {

    public function spawn(Vector3 $pos, string $type, Player $owner = null, Player $toAttack = null) : void {
        switch($type) {
            case "necromancerZombie":
                $nbt = Entity::createBaseNBT($pos, null, 2, 2);
                $entity = new NecromancerZombie(Loader::getInstance()->getServer()->getDefaultLevel(), $nbt, $toAttack, $owner);
                $entity->setNameTagAlwaysVisible(true);
                $entity->spawnToAll();
                break;
        }
    }

}