<?php

namespace ItsRealNise\RealCapes\event;

use pocketmine\entity\Skin;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerChangeSkinEvent, PlayerJoinEvent};

use ItsRealNise\RealCapes\Main;

class EventListener implements Listener{
    
    /** @var Main $plugin */
    public $plugin;
    
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $this->plugin->skin[$player->getName()] = $player->getSkin();
        if(file_exists($this->plugin->getDataFolder() . $this->plugin->data->get($player->getName()) . ".png")){
            $oldSkin = $player->getSkin();
            $capeData = $this->plugin->createCapes($this->plugin->data->get($player->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $player->setSkin($setCape);
            $player->sendSkin();
        }else{
            $this->plugin->data->remove($player->getName());
            $this->plugin->data->save();
        }
    }
    
    public function onChangeSkin(PlayerChangeSkinEvent $event){
        $player = $event->getPlayer();
        $this->plugin->skin[$player->getName()] = $player->getSkin();
    }
}
