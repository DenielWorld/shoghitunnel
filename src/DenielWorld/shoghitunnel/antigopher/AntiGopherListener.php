<?php

namespace DenielWorld\shoghitunnel\antigopher;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\Utils;

class AntiGopherListener implements Listener{

    public const GOPHER_DEVICE_ID = "2047319603";//Sorry nintendo switch ;(

    public function handleGopherPlayers(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();

        if($packet instanceof LoginPacket){
            foreach ($packet->chainData["chain"] as $chainDatum){
                $webtoken = Utils::decodeJWT($chainDatum);
                if(isset($webtoken["extraData"])) {
                    if ($webtoken["extraData"]["titleId"] == self::GOPHER_DEVICE_ID) {
                        $event->getPlayer()->kick(
                            "Shoghi does not like gophers! \n P.S. Sorry if you use a Nintendo Switch, you cannot play on this server.",
                            false
                        );
                    }
                }
            }
        }
    }
}