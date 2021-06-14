<?php namespace Taco\SP\entities\npc;

use pocketmine\entity\Entity;
use pocketmine\entity\Villager;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class NPCEntity extends Villager {

    const NETWORK_ID = self::VILLAGER;

    public $width = 0.6;
    public $height = 1.8;

    private string $customName = "";
    private string $customCommand = "";

    public function getType() : string {
        return "Villager";
    }

    public function getName() : string{
        return "Villager";
    }

    public function getNameTag() : string {
        return $this->customName;
    }

    public function getCommand() : string {
        return $this->customCommand;
    }

    public function tryChangeMovement():void{}

    public function __construct(Level $level, CompoundTag $nbt, string $name, string $command, string $codename) {
        parent::__construct($level, $nbt);
        $this->customName = $name;
        $this->customCommand = $command;
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
        $this->setGenericFlag(Entity::DATA_FLAG_SILENT, true);
        $this->setGenericFlag(Entity::DATA_FLAG_MOVING, false);
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {return parent::entityBaseTick($tickDiff);}

}