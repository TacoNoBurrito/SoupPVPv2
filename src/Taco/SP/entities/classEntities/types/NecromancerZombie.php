<?php namespace Taco\SP\entities\classEntities\types;

use pocketmine\block\Flowable;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\FlameParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\Loader;

class NecromancerZombie extends Zombie {

    const NETWORK_ID = self::ZOMBIE;

    public $width = 0.6;
    public $height = 1.8;

    private ?Player $target = null;

    private ?Player $owner = null;

    private int $attackCooldownTicks = 0;
    private int $jumpTicks = 0;

    public function getType() : string {
        return "Zombie";
    }

    public function getName() : string {
        return "Zombie";
    }

    public function getOwner() : Player {
        return $this->owner;
    }

    public function getNameTag() : string {
        return TF::BOLD.TF::RED."ZOMBIE";
    }

    public function __construct(Level $level, CompoundTag $nbt, Player $target = null, Player $owner = null) {
        if ($target == null) return;
        if ($owner == null) return;
        parent::__construct($level, $nbt);
        $this->target = $target;
        $this->owner = $owner;
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        if ($this->isAlive()) {
            if ($this->owner == null) {
                if (!$this->isClosed()) $this->flagForDespawn();
                return $this->isAlive();
            }
            if ($this->attackCooldownTicks > 0) $this->attackCooldownTicks--;
            if ($this->jumpTicks > 0) $this->jumpTicks--;
                if(!$this->isOnGround()){
                if($this->motion->y > -$this->gravity * 4){
                    $this->motion->y = -$this->gravity * 4;
                }else{
                    $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
                }
            }else{
                $this->motion->y -= $this->gravity;
            }
            $this->move($this->motion->x, $this->motion->y, $this->motion->z);
            $this->updateMovement(false);
            $position = $this->target;
            if ($position == null or (!$position->isAlive()) or Loader::getClassManager()->getClass($this->target) == "none") {
                $this->findNewTarget();
                $position = $this->target;
            }
            $x = $position->x - $this->getX();
            $z = $position->z - $this->getZ();
            if($x * $x + $z * $z < 4 + $this->getScale()) {
                $this->motion->x = 0;
                $this->motion->z = 0;
            } else {
                $this->motion->x = 1 * 0.15 * ($x / (abs($x) + abs($z)));
                $this->motion->z = 1 * 0.15 * ($z / (abs($x) + abs($z)));
            }
            $this->yaw = rad2deg(atan2(-$x, $z));
            $this->pitch = 0;
            $this->move($this->motion->x, $this->motion->y, $this->motion->z);
            $this->getLevel()->addParticle(new FlameParticle($this->asVector3()->add(0, 2.3)));
            if ($this->shouldJump()) {
                $this->jump();
                $this->updateMovement();
            }
            $this->updateMovement(false);
            if ($this->attackCooldownTicks == 0) {
                if ($this->distance($this->target) < 6) {
                    $event = new EntityDamageByEntityEvent($this, $this->target, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, 4.5);
                    $this->target->attack($event);
                    $this->broadcastEntityEvent(4);
                    $this->attackCooldownTicks = 35;
                }
            }
        } else {
            if (!$this->isClosed()) $this->flagForDespawn();
        }
        $this->updateMovement();
        $this->setNameTag($this->getNameTag());
        return parent::entityBaseTick();
    }



    public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4) : void{
        parent::knockBack($attacker, $damage, $x, $z, $base);
    }

    public function updateMovement(bool $teleport = false) : void{
        if(!$this->isClosed() && $this->getLevel() !== null){
            parent::updateMovement($teleport);
        }
    }

    public function attack(EntityDamageEvent $source) :    void {
        $source->setAttackCooldown(10);
        parent::attack($source);
    }

    public function findNewTarget() : void {
        $toChoose = [];
        foreach($this->getLevel()->getEntities() as $entity) {
            if ($entity instanceof Player) {
                if ($entity->getName() == $this->owner->getName()) continue;
                if (Loader::getClassManager()->getClass($entity) !== "none") {
                    $toChoose[$entity->getName()] = $entity->distance($this);
                }
            }
        }
        arsort($toChoose);
        if (count($toChoose) < 1) {
            $this->flagForDespawn();
            return;
        }
        foreach ($toChoose as $name => $garble) {
            $this->target = Loader::getInstance()->getServer()->getPlayer($name);
            break;
        }
    }

    public function getFrontBlock($y = 0){
        $dv = $this->getDirectionVector();
        $pos = $this->asVector3()->add($dv->x * $this->getScale(), $y + 1, $dv->z * $this->getScale())->round();
        return $this->getLevel()->getBlock($pos);
    }

    public function shouldJump() : bool {
        if($this->jumpTicks > 0) return false;
        return $this->isCollidedHorizontally ||
            ($this->getFrontBlock()->getId() != 0 || $this->getFrontBlock(-1) instanceof Stair) ||
            ($this->getLevel()->getBlock($this->asVector3()->add(0,-0,5)) instanceof Slab &&
                (!$this->getFrontBlock(-0.5) instanceof Slab && $this->getFrontBlock(-0.5)->getId() != 0)) &&
            $this->getFrontBlock(1)->getId() == 0 &&
            $this->getFrontBlock(2)->getId() == 0 &&
            !$this->getFrontBlock() instanceof Flowable &&
            $this->jumpTicks == 0;
    }

    public function jump() : void {
        $this->motion->y = $this->gravity * 16;
        $this->move($this->motion->x * 1.25, $this->motion->y, $this->motion->z * 1.25);
        $this->jumpTicks = 5;
    }

}