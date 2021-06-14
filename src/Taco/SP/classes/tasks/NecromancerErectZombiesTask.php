<?php namespace Taco\SP\classes\tasks;

use pocketmine\block\Liquid;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Taco\SP\Loader;

class NecromancerErectZombiesTask extends Task {

    private ?Player $owner = null;

    private ?Player $target = null;

    private int $erected = 0;

    private int $randomErect = 0;

    public function __construct(Player $owner, Player $target) {
        $this->owner = $owner;
        $this->target = $target;
        $this->randomErect = mt_rand(2, 5);
    }

    public function onRun(int $currentTick) : void {
        $this->erected++;
        if ($this->erected > $this->randomErect) {
            Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if ($this->target == null or $this->owner == null) {
            Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $pos = $this->generateRandomPositionFrom($this->target);
        Loader::getEntitiesClassManager()->spawn($pos, "necromancerZombie", $this->owner, $this->target);
    }

    public function generateRandomPositionFrom(Player $player) : Vector3 {
        $minX = $player->getFloorX() - 8;
        $minY = $player->getFloorY() - 8;
        $minZ = $player->getFloorZ() - 8;
        $maxX = $minX + 16;
        $maxY = $minY + 16;
        $maxZ = $minZ + 16;
        $level = $player->getLevel();
        for($attempts = 0; $attempts < 16; ++$attempts){
            $x = mt_rand($minX, $maxX);
            $y = mt_rand($minY, $maxY);
            $z = mt_rand($minZ, $maxZ);
            while($y >= 0 and !$level->getBlockAt($x, $y, $z)->isSolid()){
                $y--;
            }
            if($y < 0){
                continue;
            }
            $blockUp = $level->getBlockAt($x, $y + 1, $z);
            $blockUp2 = $level->getBlockAt($x, $y + 2, $z);
            if($blockUp->isSolid() or $blockUp instanceof Liquid or $blockUp2->isSolid() or $blockUp2 instanceof Liquid){
                continue;
            }
            break;
        }
        return new Vector3($x, $y + 1, $z);
    }


}