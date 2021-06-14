<?php namespace Taco\SP;

use DateTime;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ZippedResourcePack;
use ReflectionException;
use Taco\SP\announcements\AnnouncementsTask;
use Taco\SP\classes\ClassManager;
use Taco\SP\commands\ForceClassCommand;
use Taco\SP\commands\GiveCreditsCommand;
use Taco\SP\commands\MenuCommand;
use Taco\SP\commands\SetRankCommand;
use Taco\SP\commands\SudoCommand;
use Taco\SP\entities\classEntities\ClassEntitiesManager;
use Taco\SP\entities\classEntities\types\NecromancerZombie;
use Taco\SP\entities\npc\NPCEntity;
use Taco\SP\entities\npc\NPCManager;
use Taco\SP\ft\FloatingTextManager;
use Taco\SP\ft\FloatingTextTask;
use Taco\SP\listeners\BaseListener;
use Taco\SP\tasks\ClassPowerupCooldownTick;
use pocketmine\utils\TextFormat as TF;
use Taco\SP\tasks\ScoreboardUpdateTick;
use Taco\SP\utils\Utils;

class Loader extends PluginBase {

    protected static Loader $instance;

    protected static ClassManager $classManager;

    protected static Utils $utils;

    protected static ClassEntitiesManager $cem;

    protected static FloatingTextManager $ftM;

    public array $playerData = [];

    public array $lastChosenClass = [];

    public array $lud = [];

    public array $config = [];

    public const NO_COMMAND_PERMISSION = TF::RED."You do not have permission to execute this command.";

    /**
     * @throws ReflectionException
     */
    public function onLoad() : void {
        $this->loadResourcePack();
    }

    public function onEnable() : void {
        self::$instance = $this;
        self::$classManager = new ClassManager();
        self::$utils = new Utils();
        self::$cem = new ClassEntitiesManager();
        self::$ftM = new FloatingTextManager();
        $this->config = (array)$this->getConfig()->getAll();
        $this->registerManagers();
        $this->registerTasks();
        $this->registerLatestUpdateData();
        $this->registerCommands();
        $this->registerEntities();
        $this->registerListeners();
        $this->registerDatabase();
        $this->getLogger()->info("SoupPvP: Created By Taco!#1305.");
    }

    /**
     * @throws ReflectionException
     */
    public function onDisable() : void {
        $manager = $this->getServer()->getResourcePackManager();
        $pack = new ZippedResourcePack($this->getDataFolder()."soup.zip");
        $reflection = new \ReflectionClass($manager);
        $property = $reflection->getProperty("resourcePacks");
        $property->setAccessible(true);
        $currentResourcePacks = $property->getValue($manager);
        $key = array_search($pack, $currentResourcePacks);
        if($key !== false){
            unset($currentResourcePacks[$key]);
            $property->setValue($manager, $currentResourcePacks);
        }
        $property = $reflection->getProperty("uuidList");
        $property->setAccessible(true);
        $currentUUIDPacks = $property->getValue($manager);
        if(isset($currentResourcePacks[strtolower($pack->getPackId())])) {
            unset($currentUUIDPacks[strtolower($pack->getPackId())]);
            $property->setValue($manager, $currentUUIDPacks);
        }
        unlink($this->getDataFolder()."soup.zip");
    }

    public function registerLatestUpdateData() : void {
        $date = new DateTime();
        $form = $date->format("d/m/y");
        $this->lud = [
            "Today's date: ".$form,
            " ",
            "New Updates: ",
            " - No new updates."
        ];
    }

    public function registerDatabase() : void {
        $db = mysqli_connect("45.134.8.173", "u10434_KpMSJ8Xshq", "7G^KUgP^Zs6Do+cIBP@e9@oE", "s10434_soupdb", 3306);
        $db->query("CREATE TABLE IF NOT EXISTS users(username VARCHAR(20) PRIMARY KEY, kills INT, deaths INT, killstreak INT, credits INT, classesPurchased VARCHAR(500), rank VARCHAR(20));");
        $db->close();
    }

    public function registerListeners() : void {
        $this->getServer()->getPluginManager()->registerEvents(new BaseListener(), $this);
    }

    public function registerManagers() : void {
        ClassManager::init();
        FloatingTextManager::init();
        NPCManager::init();
    }

    public function registerCommands() : void {
        $this->getServer()->getCommandMap()->registerAll("SoupPvP", [
            new SetRankCommand($this),
            new MenuCommand($this),
            new SudoCommand($this),
            new GiveCreditsCommand($this),
            new ForceClassCommand($this)
        ]);
    }

    public function registerEntities() : void {
        Entity::registerEntity(NPCEntity::class);
        Entity::registerEntity(NecromancerZombie::class);
    }

    public function registerTasks() : void {
        $this->getScheduler()->scheduleRepeatingTask(new ClassPowerupCooldownTick(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardUpdateTick(), 60);
        $this->getScheduler()->scheduleRepeatingTask(new FloatingTextTask(), 60 * 20);
        $this->getScheduler()->scheduleRepeatingTask(new AnnouncementsTask(), 120 * 20);
    }

    /**
     * @throws ReflectionException
     */
    public function loadResourcePack() : void {
        $this->saveResource("soup.zip", true);
        $manager = $this->getServer()->getResourcePackManager();
        $pack = new ZippedResourcePack($this->getDataFolder()."soup.zip");
        $reflection = new \ReflectionClass($manager);
        $property = $reflection->getProperty("resourcePacks");
        $property->setAccessible(true);
        $currentResourcePacks = $property->getValue($manager);
        $currentResourcePacks[] = $pack;
        $property->setValue($manager, $currentResourcePacks);
        $property = $reflection->getProperty("uuidList");
        $property->setAccessible(true);
        $currentUUIDPacks = $property->getValue($manager);
        $currentUUIDPacks[strtolower($pack->getPackId())] = $pack;
        $property->setValue($manager, $currentUUIDPacks);
        $property = $reflection->getProperty("serverForceResources");
        $property->setAccessible(true);
        $property->setValue($manager, true);
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public static function getClassManager() : ClassManager {
        return self::$classManager;
    }

    public static function getUtils() : Utils {
        return self::$utils;
    }

    public static function getEntitiesClassManager() : ClassEntitiesManager {
        return self::$cem;
    }

    public static function getFloatingTextManager() : FloatingTextManager {
        return self::$ftM;
    }

}