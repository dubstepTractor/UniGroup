<?php declare(strict_types = 1); namespace uni\group\event\group;

use uni\group\Manager;
use uni\group\data\PlayerGroup;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

use pocketmine\Player;

class PlayerGroupUpdateEvent extends PluginEvent implements Cancellable {

	static $handlerList = null;

	/**
	 * @var PlayerGroup
	 */
	private $group;

	/**
	 *                        _
	 *   _____    _____ _ __ | |__
	 *  / _ \ \  / / _ \ '_ \|  _/
	 * |  __/\ \/ /  __/ | | | |_
	 *  \___/ \__/ \___|_| |_|\__\
	 *
	 *
	 * @param Manager     $main
	 * @param PlayerGroup $group
	 */
	public function __construct(Manager $main, PlayerGroup $group) {
		parent::__construct($main);

		$this->group = $group;
	}

	/**
	 * @return PlayerGroup
	 */
	public function getPlayerGroup(): PlayerGroup {
		return $this->group;
	}

	/**
	 * @return Player|null
	 */
	public function getPlayer(): ?Player {
		return $this->getPlugin()->getServer()->getPlayerExact($this->getPlayerGroup()->getNickname());
	}
}