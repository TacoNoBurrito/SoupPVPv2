<?php namespace Taco\SP\tasks;

use DateTime;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Taco\SP\Loader;
use pocketmine\utils\TextFormat as TF;

class ScoreboardUpdateTick extends Task {

    private array $line = [];

    public function onRun(int $currentTick) : void {
        foreach(Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(Loader::getInstance()->playerData[$player->getLowerCaseName()])) continue;
            $data = Loader::getInstance()->playerData[$player->getLowerCaseName()];
            $this->removeScoreboard($player);
            $this->clearLines($player);
            $this->showScoreboard($player);
            $this->addLine(TF::GRAY."-------------------", $player);
            $this->addLine(TF::AQUA."Credits: ".TF::WHITE.$data["credits"], $player);
            $this->addLine(TF::AQUA."K: ".TF::WHITE.$data["kills"].TF::AQUA." D: ".TF::WHITE.$data["deaths"], $player);
            if ($data["kills"] == 0 or $data["deaths"] == 0) $kdr = 0;
            else $kdr = $data["kills"] / $data["deaths"];
            $this->addLine(TF::AQUA."KDR: ".TF::WHITE.$kdr,$player);
            $this->addLine(TF::AQUA."Killstreak: ".TF::WHITE.$data["killstreak"], $player);
            $this->addLine(TF::AQUA."Rank: ".TF::WHITE.$data["rank"], $player);
            $this->addLine(TF::AQUA.TF::GRAY."  ", $player);
            $this->addLine(TF::AQUA."", $player);
            $this->addLine(TF::GRAY."-------------------".TF::GRAY."".TF::AQUA."", $player);
        }
    }

    public function showScoreboard(Player $player) : void {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $player->getName();
        $pk->displayName = TF::UNDERLINE.TF::BOLD.TF::AQUA."SoupPVP";
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);
    }

    public function addLine(string $line, Player $player) : void {
        $score = count($this->line) + 1;
        $this->setLine($score, $line, $player);
    }

    public function removeScoreboard(Player $player) : void {
        $objectiveName = $player->getName();
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName;
        $player->sendDataPacket($pk);
    }

    public function clearLines(Player $player) : void {
        for ($line = 0; $line <= 15; $line++) {
            $this->removeLine($line, $player);
        }
    }

    public function setLine(int $loc, string $msg, Player $player) : void {
        $pk = new ScorePacketEntry();
        $pk->objectiveName = $player->getName();
        $pk->type = $pk::TYPE_FAKE_PLAYER;
        $pk->customName = $msg;
        $pk->score = $loc;
        $pk->scoreboardId = $loc;
        if (isset($this->line[$loc])) {
            unset($this->line[$loc]);
            $pkt = new SetScorePacket();
            $pkt->type = $pkt::TYPE_REMOVE;
            $pkt->entries[] = $pk;
            $player->sendDataPacket($pkt);
        }
        $pkt = new SetScorePacket();
        $pkt->type = $pkt::TYPE_CHANGE;
        $pkt->entries[] = $pk;
        $player->sendDataPacket($pkt);
        $this->line[$loc] = $msg;
    }

    public function removeLine(int $line, Player $player) : void {
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_REMOVE;
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $player->getName();
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
        if (isset($this->line[$line])) {
            unset($this->line[$line]);
        }
    }


}