<?php namespace Taco\SP\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\Loader;

class SudoCommand extends PluginCommand {

    public function __construct(Plugin $owner) {
        parent::__construct("sudo", $owner);
        $this->setDescription("Force a player to say something.".TF::RED."[ADMIN]");
    }

    public function execute(CommandSender $player, string $commandLabel, array $args) : bool {
        if ($player->isOp() or (!$player instanceof Player)) {
            if (empty($args[0]) or empty($args[1])) {
                $player->sendMessage(TF::RED."You need to provide the player and the message");
                return false;
            }
            $tc = $args[0];
            $toChange = Loader::getInstance()->getServer()->getPlayer($tc);
            if ($toChange == null) {
                $player->sendMessage(TF::RED."This player is not online, or does not exist.");
                return false;
            }
            unset($args[0]);
            $message = join(" ", $args);
            $toChange->chat($message);
            $player->sendMessage(TF::WHITE."Successfully forced chat onto: ".TF::AQUA.$toChange->getName());
        } else {
            $player->sendMessage(Loader::NO_COMMAND_PERMISSION);
        }
        return true;
    }

}