<?php declare(strict_types = 1); namespace uni\group\event\listener;

use uni\group\Manager;

use pocketmine\event\Listener;

abstract class ManagedListener implements Listener {

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 *  _ _      _
	 * | (_)____| |_____ _ __   ___ _ __
	 * | | / __/   _/ _ \ '_ \ / _ \ '_/
	 * | | \__ \| ||  __/ | | |  __/ |
	 * |_|_|___/ \__\___|_| |_|\___|_|
	 *
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		$this->manager = $main;
	}

	/**
	 * @return Manager
	 */
	protected function getManager(): Manager {
		return $this->manager;
	}
}