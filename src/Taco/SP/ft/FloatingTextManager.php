<?php namespace Taco\SP\ft;

use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use Taco\SP\Loader;

class FloatingTextManager {

    public array $floatingTexts = [];

    public static function init() : void {
        $texts = FloatingText::FLOATING_TEXT;
        foreach ($texts as $name => $info) {
            $particle = new FloatingTextParticle(
                new Vector3(
                    $info["x"],
                    $info["y"],
                    $info["z"]
                ),
                $info["text"]
            );
            Loader::getInstance()->getServer()->getDefaultLevel()->addParticle($particle);
            Loader::getFloatingTextManager()->floatingTexts[$name] = $particle;
        }
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new FloatingTextTask(), 20);
    }

}