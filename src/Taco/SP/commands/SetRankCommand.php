<?php namespace Taco\SP\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\Loader;

class SetRankCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("setrank", $owner);
        $this->setDescription("Change a players rank. ".TF::RED."[ADMIN]");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args) : bool {
        if ($player->isOp() or (!$player instanceof Player)) {
            if (empty($args[0]) or empty($args[1])) {
                $player->sendMessage(TF::RED."You need to provide the player and the rank.");
                return false;
            }
            $tc = $args[0];
            $rank = $args[1];
            $toChange = Loader::getInstance()->getServer()->getPlayer($tc);
            if ($toChange == null) {
                $player->sendMessage(TF::RED."This player is not online, or does not exist.");
                return false;
            }
            Loader::getInstance()->playerData[$toChange->getLowerCaseName()]["rank"] = $rank;
            $player->sendMessage("Successfully changed ".TF::AQUA.$toChange->getName().TF::WHITE."'s rank to ".TF::AQUA.$rank.".");
            $toChange->sendMessage("Your rank has been updated. Your new rank is now: ".TF::AQUA.$rank."!");
        } else {
            $player->sendMessage(Loader::NO_COMMAND_PERMISSION);
        }
        return true;
    }

}