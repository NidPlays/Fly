<?php

namespace Fly;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;


class Fly extends PluginBase implements Listener{

    public $flying;
    public $antiDamage;    

public function onEnable(){
		$this->getLogger()->info(TextFormat::GREEN."Fly Plugin for PKRealms has been Enabled!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->flying = [];
		$this->antiDamage = [];
	}
	
	public function onDisable(){
		$this->getLogger()->info(TextFormat::RED."Fly Plugin for PKRealms has been Disabled!!");
	}
	
	 
public function onQuit(PlayerQuitEvent $event){
	$name = $event->getPlayer()->getName();
	if(isset($this->flying[$name])){
		unset($this->flying[$name]);
		unset($this->antiDamage[$name]);
	}
}

public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(strtolower($command->getName('fly'))){
			if($sender->hasPermission("fly.command")){
				$this->toggleFly($sender);
			}
	}
}

public function toggleFly(Player $p){

	if(isset($this->flying[$p->getName()])){
		unset($this->flying[$p->getName()]);
		$p->sendMessage("§6PKFactions >> §cFly has been Disabled");
		$p->setAllowFlight(false);
	}else{
		$this->flying[$p->getName()] = $p;
		$p->sendMessage("§6PKFactions >> §aFly has been Enabled");
		$p->setAllowFlight(true);
		$this->antiDamage[$p->getName()] = $p;
	}
}

public function onDamage(EntityDamageEvent $event){
	$player = $event->getEntity();
	if ($player instanceof Player){
	$cause = $event->getCause();
    if(isset($this->flying[$player->getName()]) OR isset($this->antiDamage[$player->getName()])){
    if($cause === EntityDamageEvent::CAUSE_FALL){
    if(isset($this->flying[$player->getName()])){
    	$event->setCancelled(true);
    	$this->toggleFly($player);
    }
	}
}
    if(isset($this->antiDamage[$player->getName()]) && !isset($this->flying[$player->getName()])){
    	$event->setCancelled(true);
    	unset($this->antiDamage[$player->getName()]);
    }
  }else{
	  $cause = $event->getEntity()->getLastDamageCause();
  	  $issuer = $cause->getDamager();
    	if($issuer instanceof Player){
    		if(isset($this->flying[$player])){
    			$this->toggleFly($player);
    		}
    		if(isset($this->flying[$issuer])){
    			$this->toggleFly($issuer);
    		}
    	}
}
}
}
