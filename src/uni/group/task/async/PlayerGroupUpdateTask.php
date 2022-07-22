<?php declare(strict_types = 1); namespace uni\group\task\async;

use uni\group\data\PlayerGroup;
use uni\group\data\provider\mysql\PlayerGroupBridge;

use pocketmine\scheduler\AsyncTask;

use function serialize;
use function unserialize;

class PlayerGroupUpdateTask extends AsyncTask {

	/**
	 * @var string
	 */
	private $serialized_group;

	/**
	 *  _            _
	 * | |____ _ ___| | __
	 * |  _/ _' / __| |/ /
	 * | || (_) \__ \   <
	 *  \__\__,_|___/_|\_\
	 *
	 *
	 * @param PlayerGroup $group
	 */
	function __construct(PlayerGroup $group) {
		$this->serialized_group = serialize($group);
	}

	function onRun() {
		PlayerGroupBridge::updatePlayerGroup(unserialize($this->serialized_group));
	}
}