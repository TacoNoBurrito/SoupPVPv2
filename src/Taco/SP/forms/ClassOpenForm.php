<?php namespace Taco\SP\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use Taco\SP\Loader;

class ClassOpenForm {

    public static function open(Player $player) : void {
        $buttons = ["aha" => "lmaofunny"];
        foreach(Loader::getClassManager()->getUnlockedClasses($player) as $class) {
            $buttons[$class] = [
                "icon" => Loader::getClassManager()->getIcon($class)
            ];
        }
        $buttons["Fighter"] = ["icon" => "textures/items/diamond_sword"];
        $form = new SimpleForm(function (Player $player, int $data = null) use ($buttons) {
            if ($data == 0) return;
            $num = 0;
            $text = "";
            foreach($buttons as $name => $inf0) {
                if ($num == $data) {
                    $text = $name;
                    break;
                }
                $num++;
            }
            Loader::getClassManager()->giveClassToPlayer($player, $text);
        });
        $form->setTitle("Class Selection Menu");
        $form->setContent("Choose a class to equip.");
        foreach ($buttons as $name => $info) {
            $form->addButton($name, 0, $info["icon"]);
        }
        $form->addButton("Close Menu.");
        $player->sendForm($form);
    }

}