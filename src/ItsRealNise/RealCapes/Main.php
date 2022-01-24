<?php

namespace ItsRealNise\RealCapes;

use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use ItsRealNise\RealCapes\event\EventListener;
use ItsRealNise\RealCapes\command\RealCapesCommand;

class Main extends PluginBase {
    
    public $form;

    /** @var Config $config */
    public $config;
    
    /** @var Config $data */
    public $data;
    
    /** @var array $skin */
    public $skin = [];
    
    public function onEnable() : void{
        $this->saveDefaultConfig();
        $this->form = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if($this->form == null) {
            $this->getServer()->getLogger()->alert("Can't find FormAPI please download first");
            $this->getServer()->shutdown();
        }
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->getServer()->getCommandMap()->register("RealCapes", new RealCapesCommand("capes", $this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if(is_array($this->config->get("capes_list"))) {
            foreach($this->config->get("capes_list") as $cape){
            $this->saveResource("$cape.png");
        }
        $this->config->set("capes_list", "done");
        $this->config->save();
        }
    }
    
    public function createCapes($capeName){
        $path = $this->getDataFolder() . "{$capeName}.png";
        $img = @imagecreatefrompng($path);
        $bytes = '';
        $l = (int)@getimagesize($path)[1];
        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $bytes;
    }
    
    public function openCapesUI($player) {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function(Player $player, int $data = null) {
            if ($data === null) {
                return true;
            }
            switch ($data) {
            case 0:
            $oldSkin = $player->getSkin();
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), "", $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $player->setSkin($setCape);
            $player->sendSkin();
            if($this->data->get($player->getName()) !== null){
               $this->data->remove($player->getName());
               $this->data->save();
            }
               $player->sendMessage($this->config->get("reset-skin"));
            break;
            case 1:
            $this->openCapeListUI($player);
            break;
            }
         });
            $form->setTitle($this->config->get("Cape-Title"));
            $form->addButton("§cReset your capes", 0, "textures/ui/trash");
            $form->addButton("§aSelect a capes", 0, "textures/ui/dressing_room_capes");
            $form->sendToPlayer($player);
            }
                        
    public function openCapeListUI($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function(Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            $cape = $data;
            if(!file_exists($this->getDataFolder() . $data . ".png")) {
                $player->sendMessage("The choosen Skin is not available!");
            }else{
                if (!$player->hasPermission("$cape.cape")) {
                     $player->sendMessage($this->config->get("no-permissions"));
           } else {
            $oldSkin = $player->getSkin();
            $capeData = $this->createCapes($cape);
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $player->setSkin($setCape);
            $player->sendSkin();
            $msg = $this->config->get("cape-on");
            $msg = str_replace("{name}", $cape, $msg);
            $player->sendMessage($msg);
            $this->data->set($player->getName(), $cape);
            $this->data->save();
           }
        }
    });
        $form->setTitle($this->config->get("UI-Title"));
        $form->setContent($this->config->get("UI-Content"));
        foreach($this->getCapes() as $capes){
            if($player->hasPermission("$capes.cape")){
                $form->addButton("$capes\n§aUnlocked", -1, "", $capes);
                } else {
                    $form->addButton("$capes\n§cLocked", -1, "", $capes);
                }
        }
        $form->sendToPlayer($player);
    }
                        
    public function getCapes(){
    $list = array();
     foreach(array_diff(scandir($this->getDataFolder()), ["..", "."]) as $data){
             $dat = explode(".", $data);
             if($dat[1] == "png"){
                array_push($list, $dat[0]);
                }
            }
    return $list;
    }
}
