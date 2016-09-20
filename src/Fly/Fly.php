<?php
namespace Fly;

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
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

class Fly extends PluginBase implements Listener
{
	const FLY_ENABLE = 1;
	const FLY_TOGGLE = 2;
	const FLY_DISABLE = 3;
	private $falling = [];

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable()
	{

	}


	public function onQuit(PlayerQuitEvent $event)
	{
		if (isset($this->falling[$event->getPlayer()->getName()])) unset($this->falling[$event->getPlayer()->getName()]);
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		if (strtolower($command->getName()) == 'fly' AND $sender->hasPermission("fly.command") AND $sender instanceof Player) {
			if (empty($args[1])) {
				switch ($args[1]) {
					case "on":
					case "enable":
						$this->fly($sender, self::FLY_ENABLE);
						break;
					case "off":
					case "disable":
						$this->fly($sender, self::FLY_DISABLE);
						break;
					case "toggle":
					case "switch":
						$this->fly($sender, self::FLY_TOGGLE);
						break;
					default:
						$this->fly($sender, self::FLY_TOGGLE);
						break;
				}
			} else {
				$this->fly($sender, self::FLY_TOGGLE);
			}
		}
	}

	public function fly(Player $player, $state = self::FLY_TOGGLE, $addFalling = true)
	{
		if ($player->getAllowFlight() AND $state == self::FLY_TOGGLE) $state = self::FLY_DISABLE; else $state = self::FLY_ENABLE;
		switch ($state) {
			case self::FLY_ENABLE:
				$player->sendMessage(TextFormat::GREEN . ">> Fly has been Enabled");
				$player->setAllowFlight(true);
				break;
			case self::FLY_DISABLE;
				$player->sendMessage(TextFormat::RED . ">> Fly has been Disabled");
				$player->setAllowFlight(false);
				if ($addFalling) $this->falling[$player->getName()] = $player;
				break;
		}
	}

	public function onDamage(EntityDamageEvent $event)
	{
		if ($player = $event->getEntity() AND $player instanceof Player) {
			if ($event->getCause() === EntityDamageEvent::CAUSE_FALL AND isset($this->falling[$player->getName()])) {
				unset($this->falling[$player->getName()]);
				$event->setCancelled(true);
				$this->fly($player, self::FLY_DISABLE, false);
			} else $this->fly($player, self::FLY_DISABLE);
		}
		if ($event instanceof EntityDamageByEntityEvent) {
			$attacker = $event->getDamager();
			$attacked = $event->getEntity();
			if ($attacker instanceof Player) {
				$this->fly($player, self::FLY_DISABLE);
			}
			if ($attacked instanceof Player) {
				$this->fly($player, self::FLY_DISABLE);
			}
		}
	}
}
