<?php namespace Taco\SP\utils;

use pocketmine\item\Durable;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class Utils {

    public function decodeData($data){
        $data = unserialize(base64_decode(zlib_decode(hex2bin($data))));
        if(is_array($data)) return $data;
        return zlib_decode($data);
    }

    public function encodeData($data){
        return bin2hex(zlib_encode(base64_encode(serialize($data)), ZLIB_ENCODING_DEFLATE, 1));
    }

    public function enhance(Player $player) : void {
        $armorInv = $player->getArmorInventory();
        foreach ($player->getArmorInventory()->getContents() as $contents) {
            if ($contents instanceof Durable) {
                if ($contents->getId() == $armorInv->getHelmet()->getId()) {
                    $contents->setUnbreakable(true);
                    $newHelmet = $contents;
                } else if ($contents->getId() == $armorInv->getChestplate()->getId()) {
                    $contents->setUnbreakable(true);
                    $newChestplate = $contents;
                } else if ($contents->getId() == $armorInv->getLeggings()->getId()) {
                    $contents->setUnbreakable(true);
                    $newLeggings = $contents;
                } else if ($contents->getId() == $armorInv->getBoots()->getId()) {
                    $contents->setUnbreakable(true);
                    $newBoots = $contents;
                }
            }
        }
        foreach ($player->getInventory()->getContents() as $contents) {
            if ($contents->getId() == $player->getInventory()->getItem(0)->getId()) {
                $contents->setUnbreakable(true);
                $newSword = $contents;
            }
        }
        $player->getArmorInventory()->setHelmet($newHelmet);
        $player->getArmorInventory()->setChestplate($newChestplate);
        $player->getArmorInventory()->setLeggings($newLeggings);
        $player->getArmorInventory()->setBoots($newBoots);
        $player->getInventory()->setItem(0, $newSword);
        $packet = new TextPacket();
        $packet->type = TextPacket::TYPE_JUKEBOX_POPUP;
        $packet->message = TF::ITALIC.TF::AQUA."System: Inventory Enhanced.";
        $player->dataPacket($packet);
    }

}