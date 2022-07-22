<?php declare(strict_types = 1); namespace uni\group\event\listener\player;

use uni\group\event\listener\ManagedListener;

use pocketmine\event\player\PlayerQuitEvent as Event;

class PlayerQuitListener extends ManagedListener {

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
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	public function onCall(Event $event): void {
		$nick    = $event->getPlayer()->getLowerCaseName();
		$account = $this->getManager()->getAccount($nick, true);

		if(!isset($account)) {
			return;
		}

		$this->getManager()->removeAccount($nick, true);
	}
}