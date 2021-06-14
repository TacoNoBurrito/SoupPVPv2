<?php namespace Taco\SP\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\forms\ClassOpenForm;
use Taco\SP\forms\ClassPurchaseForm;
use Taco\SP\Loader;

class GiveCreditsCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("givecredits", $owner);
        $this->setDescription("Give credits to a player.");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args) : bool {
        if ($player->isOp() or (!$player instanceof Player)) {
            if (empty($args[0]) or empty($args[1])) {
                $player->sendMessage(TF::RED . "Provide a player to give credits to, and an amount.");
                return true;
            }
            $player1 = Loader::getInstance()->getServer()->getPlayer($args[0]);
            if ($player1 == null) {
                $player->sendMessage(TF::RED . "This player is not online or doesn't exist.");
                return true;
            }
            Loader::getInstance()->playerData[$player1->getLowerCaseName()]["credits"] += (int)$args[1];
            $player1->sendMessage(TF::GREEN . "System: You have been given " . $args[1] . " credits.");
            $player->sendMessage(TF::WHITE . "Command successfully executed.");
        } else {
            $player->sendMessage(Loader::NO_COMMAND_PERMISSION);
        }
        return false;
    }

}