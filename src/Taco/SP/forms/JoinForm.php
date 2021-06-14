<?php namespace Taco\SP\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\level\sound\Sound;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use Taco\SP\Loader;

class JoinForm {

    public static function open(Player $player) : void {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $player->sendTitle("Welcome");
            return;
        });
        $form->setTitle("Welcome, ".$player->getName().".");
        $content = "";
        foreach(Loader::getInstance()->lud as $data) {
            $content .= $data."\n";
        }
        $form->setContent($content);
        $form->addButton("Close Menu.");
        $player->sendForm($form);
    }

}