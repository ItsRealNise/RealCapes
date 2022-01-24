<?php

namespace ItsRealNise\RealCapes\command;

use pocketmine\command\{CommandSender, Command};

use pocketmine\plugin\PluginOwned;

use pocketmine\Server;

use pocketmine\player\Player;

use pocketmine\plugin\Plugin;

use ItsRealNise\RealCapes\Main;

use pocketmine\utils\TextFormat;

/**
 * Class RealCapesCommand
 * @package ItsRealNise\RealCapes\command
 */

class RealCapesCommand extends Command implements PluginOwned {

    /** @var Main $plugin */

    protected $plugin;

    /**
     * UltraCapesCommand constructor.
     * @param Main $plugin
     */

    public function __construct($cmd, Main $plugin) {

        $this->plugin = $plugin;
        parent::__construct($cmd, "Capes command", null, ["capes"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed|void
     */

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender instanceof Player){

            $sender->sendMessage($this->plugin->config->get("ingame"));


             return false;
        }

        $this->plugin->openCapesUI($sender);

        return true;

    }

    public function getOwningPlugin(): Plugin
    {

        return $this->plugin;
    }
}
