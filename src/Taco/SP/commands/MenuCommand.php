<?php namespace Taco\SP\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\forms\ClassOpenForm;
use Taco\SP\forms\ClassPurchaseForm;
use Taco\SP\Loader;

class MenuCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("menu", $owner);
        $this->setDescription("Open a menu.");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args) : bool {
        if (empty($args[0])) {
            $player->sendMessage(TF::RED."Please provide a menu type to open!");
            return true;
        }
        if ($player instanceof Player) {
            switch (strtolower($args[0])) {
                case "classes":
                    ClassOpenForm::open($player);
                    break;
                case "purchaseclass":
                    ClassPurchaseForm::open($player);
                    break;
            }
        }
        return false;
    }

}