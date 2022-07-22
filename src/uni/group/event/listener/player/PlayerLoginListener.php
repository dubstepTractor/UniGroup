<?php declare(strict_types = 1); namespace uni\group\event\listener\player;

use uni\group\event\listener\ManagedListener;

use pocketmine\event\player\PlayerLoginEvent as Event;

class PlayerLoginListener extends ManagedListener {

	/**
	 *  _ _      _
	 * | (_)____| |_____ _ __   ___ _ __
	 * | | / __/   _/ _ \ '_ \ / _ \ '_/
	 * | | \__ \| ||  __/ | | |  __/ |
	 * |_|_|___/ \__\___|_| |_|\___|_|
	 *
	 *
	 * @param Event $event
	 *
	 * @priority        LOWEST
	 * @ignoreCancelled FALSE
	 */
	public function onCall(Event $event): void {
		$this->getManager()->updatePermissionList($event->getPlayer());
	}
}