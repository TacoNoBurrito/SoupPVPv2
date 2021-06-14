<?php namespace Taco\SP\forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use Taco\SP\classes\Classes;
use Taco\SP\Loader;
use pocketmine\utils\TextFormat as TF;

class ClassPurchaseForm {

    public static function open(Player $player) : void {
        $buttons = ["bruas" => "hehe"];
        foreach(Loader::getClassManager()->getLockedClasses($player) as $class) {
            $buttons[$class] = [
                "icon" => Loader::getClassManager()->getIcon($class),
                "price" => Loader::getClassManager()->getClassPrice($class)
            ];
        }
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
            $class = Classes::CLASSES[$text];
            $money = Loader::getInstance()->playerData[$player->getLowerCaseName()]["credits"];
            $price = $class["price"];
            if ($money >= $price) {
                $player->sendMessage(TF::WHITE."Successfully purchased the class: ".TF::AQUA.$text.TF::WHITE." for ".TF::AQUA.$price.TF::WHITE." credits!");
                Loader::getInstance()->playerData[$player->getLowerCaseName()]["credits"] -= $price;
                Loader::getClassManager()->giveClass($player, $text);
            } else {
                $player->sendMessage(TF::RED."You need ".TF::GREEN.($price-$money).TF::RED." more credits to purchase this class!");
            }
        });
        $form->setTitle("Class Purchase Menu");
        $form->setContent("Choose a class to purchase.");
        foreach ($buttons as $name => $info) {
            $form->addButton($name."\n".TF::AQUA."Price: ".$info["price"]."C", 0, $info["icon"]);
        }

        $form->addButton("Close Menu.");
        $player->sendForm($form);
    }

}