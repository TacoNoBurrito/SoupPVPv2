<?php namespace Taco\SP\tasks;

use pocketmine\scheduler\Task;
use Taco\SP\classes\Classes;
use Taco\SP\classes\ClassManager;
use Taco\SP\Loader;
use pocketmine\utils\TextFormat as TF;

class ClassPowerupCooldownTick extends Task {

    public function onRun(int $currentTick) : void {
        $classes = [];
        foreach(Classes::CLASSES as $name => $i) {
            $classes[] = $name;
        }
        foreach(ClassManager::$cooldowns as $i => $cooldowns) {
            foreach ($classes as $class) {
                $time = $cooldowns[$class];
                if ($time > 0) {
                    ClassManager::$cooldowns[$i][$class]--;
                    if (ClassManager::$cooldowns[$i][$class] < 1) {
                        $player = Loader::getInstance()->getServer()->getPlayer($i);
                        $player->sendMessage(TF::WHITE . "Your " . TF::AQUA . $class . TF::WHITE . " power-up cool down expired!");
                    }
                }
            }
        }
    }

}