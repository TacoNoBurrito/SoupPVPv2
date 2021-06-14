<?php namespace Taco\SP\announcements;

use pocketmine\scheduler\Task;
use Taco\SP\Loader;

class AnnouncementsTask extends Task {

    public function onRun(int $currentTick) : void {
        $a = Announcements::ANNOUNCEMENTS;
        $r = $a[array_rand($a)];
        Loader::getInstance()->getServer()->broadcastMessage($r);
    }

}