<?php declare(strict_types = 1); namespace uni\group\task\async;

use uni\group\data\provider\mysql\PlayerGroupBridge;

use pocketmine\scheduler\AsyncTask;

class PlayerGroupDeleteTask extends AsyncTask {

	/**
	 * @var string
	 */
	private $nickname;

	/**
	 *  _            _
	 * | |____ _ ___| | __
	 * |  _/ _' / __| |/ /
	 * | || (_) \__ \   <
	 *  \__\__,_|___/_|\_\
	 *
	 *
	 * @param string $nick
	 */
	function __construct(string $nick) {
		$this->nickname = $nick;
	}

	function onRun() {
		PlayerGroupBridge::deletePlayerGroup($this->nickname);
	}
}