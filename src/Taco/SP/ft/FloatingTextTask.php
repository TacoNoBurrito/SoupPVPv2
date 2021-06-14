<?php namespace Taco\SP\ft;

use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\scheduler\Task;
use Taco\SP\Loader;

class FloatingTextTask extends Task {

    private int $time = 0;

    public function onRun(int $currentTick) : void {
        $this->time++;
        $texts = Loader::getFloatingTextManager()->floatingTexts;
        foreach ($texts as $name => $object) {
            if ($object instanceof FloatingTextParticle) {
                $info = FloatingText::FLOATING_TEXT[$name];
                if ($info["does-update"]) {
                    $update = $info["update-every"];
                    if (is_int($this->time / $update)) {
                        if ($info["per-player"]) {
                            foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
                                $object->setTitle(str_replace(["{name}"], [$player->getName()], $info["text"]));
                                Loader::getInstance()->getServer()->getDefaultLevel()->addParticle($object, [$player]);
                            }
                            return;
                        }
                        Loader::getInstance()->getServer()->getDefaultLevel()->addParticle($object);
                    }
                }
            }
        }
    }

}