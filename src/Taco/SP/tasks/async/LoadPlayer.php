<?php namespace Taco\SP\tasks\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Taco\SP\Loader;

class LoadPlayer extends AsyncTask {

    private string $player = "";

    private int $kills = 0;

    private int $deaths = 0;

    private int $killstreak = 0;

    private string $rank = "Default";

    private string $classesPurchased = "";

    private int $credits = 0;

    public function __construct(string $player) {
        $this->player = $player;
    }

    public function onRun() : void {
        $name = trim($this->player);
        $db = mysqli_connect("45.134.8.173", "u10434_KpMSJ8Xshq", "7G^KUgP^Zs6Do+cIBP@e9@oE", "s10434_soupdb", 3306);
        $query = $db->prepare("SELECT * FROM users WHERE username = ?");
        $query->bind_param("s", $name);
        $query->execute();
        $query->store_result();
        if ($query->num_rows > 0) {
            $q = mysqli_query($db, "SELECT * FROM users WHERE username = '$name'");
            $fetched = $q->fetch_assoc();
            $this->kills = (int)$fetched["kills"];
            $this->deaths = (int)$fetched["deaths"];
            $this->killstreak = (int)$fetched["killstreak"];
            $this->credits = (int)$fetched["credits"];
            $this->rank = (string)$fetched["rank"];
            $this->classesPurchased = (string)$fetched["classesPurchased"];
            $q->close();
        }
        $query->close();
        mysqli_close($db);
    }

    public function onCompletion(Server $server) : void {
        Loader::getInstance()->playerData[$this->player] = [
            "kills" => $this->kills,
            "deaths" => $this->deaths,
            "killstreak" => $this->killstreak,
            "rank" => $this->rank,
            "credits" => $this->credits,
            "classesPurchased" => $this->classesPurchased
        ];
    }

}