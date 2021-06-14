<?php namespace Taco\SP\tasks\async;

use pocketmine\scheduler\AsyncTask;

class SavePlayer extends AsyncTask {

    private string $player = "";

    private int $kills = 0;

    private int $deaths = 0;

    private int $killstreak = 0;

    private string $rank = "Default";

    private string $classesPurchased = "";

    private int $credits = 0;

    public function __construct(string $player, int $kills, int $deaths, int $killstreak, string $rank, string $classesPurchased, int $credits) {
        $this->kills = $kills;
        $this->deaths = $deaths;
        $this->killstreak = $killstreak;
        $this->rank = $rank;
        $this->classesPurchased = $classesPurchased;
        $this->credits = $credits;
        $this->player = $player;
    }

    public function onRun() : void {
        $name = trim($this->player);
        $db = mysqli_connect("45.134.8.173", "u10434_KpMSJ8Xshq", "7G^KUgP^Zs6Do+cIBP@e9@oE", "s10434_soupdb", 3306);
        $insert = $db->prepare("UPDATE users SET kills=?, deaths=?, killstreak=?, rank=?, classesPurchased=?, credits=? WHERE username=?");
        $kills = $this->kills;
        $deaths = $this->deaths;
        $killstreak = $this->killstreak;
        $rank = $this->rank;
        $classesPurchased = $this->classesPurchased;
        $credits = $this->credits;
        $insert->bind_param("iiissis",$kills, $deaths, $killstreak, $rank,$classesPurchased , $credits, $name);
        $insert->execute();
        $insert->close();
        $db->close();
    }

}