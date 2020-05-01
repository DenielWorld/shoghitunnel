<?php

namespace DenielWorld\shoghitunnel;

use DenielWorld\shoghitunnel\antibook\AntiBookExploitListener;
use DenielWorld\shoghitunnel\antigopher\AntiGopherListener;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\session\PlayerManager;
use muqsit\invmenu\session\PlayerSession;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;

class Loader extends PluginBase{

    /** @var Config */
    private static $settings;

    public function onEnable()
    {
        @mkdir($this->getDataFolder());

        if(!file_exists("settings.yml"))
            $this->saveResource("settings.yml");

        self::$settings = new Config($this->getDataFolder() . "settings.yml", Config::YAML);

        $this->parseSettings();

        if(self::getSettings()->get("anti-invmenu-dupe")) {
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(
                function (int $currentTick): void {
                    if(!InvMenuHandler::isRegistered())
                        throw new PluginException("anti-invmenu-dupe is enabled but InvMenu is not registered!");
                }
            ), 20);
        }
    }

    private function parseSettings() : void{
        if((bool)self::getSettings()->get("force-antigopher"))
            $this->getServer()->getPluginManager()->registerEvents(new AntiGopherListener(), $this);

        if((bool)self::getSettings()->get("anti-book"))
            $this->getServer()->getPluginManager()->registerEvents(new AntiBookExploitListener(), $this);
    }

    public static function getSettings() : Config{
        return self::$settings;
    }

    public function onDisable()
    {
        if((bool)self::getSettings()->get("anti-invmenu-dupe")){
            $reflec = new \ReflectionProperty(PlayerManager::class, "sessions");
            $reflec->setAccessible(true);

            /** @var PlayerSession $session */
            foreach ($reflec->getValue() as $uuid => $session){
                $session->removeWindow();//This should correctly trigger the close listener before players get kicked
            }
        }
    }
}