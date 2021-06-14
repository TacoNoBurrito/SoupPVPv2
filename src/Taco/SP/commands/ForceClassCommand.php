<?php namespace Taco\SP\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\forms\ClassOpenForm;
use Taco\SP\forms\ClassPurchaseForm;
use Taco\SP\Loader;

class ForceClassCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("forceclass", $owner);
        $this->setDescription("Force a class onto a player.");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args) : bool {
        if ($player->isOp() or (!$player instanceof Player)) {
            if (empty($args[0]) or empty($args[1])) {
                $player->sendMessage(TF::RED . "Provide a player to give change class to, and the class.");
                return true;
            }
            $player1 = Loader::getInstance()->getServer()->getPlayer($args[0]);
            if ($player1 == null) {
                $player->sendMessage(TF::RED . "This player is not online or doesn't exist.");
                return true;
            }
            Loader::getClassManager()->giveClassToPlayer($player1, $args[1]);
            $player->sendMessage(TF::WHITE . "Command successfully executed.");
        } else {
            $player->sendMessage(Loader::NO_COMMAND_PERMISSION);
        }
        return false;
    }

}