<?php declare(strict_types = 1); namespace uni\group\event\listener\player;

use uni\group\Manager;
use uni\group\util\Declinator;
use uni\group\event\listener\ManagedListener;

use pocketmine\event\player\PlayerJoinEvent as Event;

class PlayerJoinListener extends ManagedListener {

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
		$main = $this->getManager();

		$player = $event->getPlayer();
		$nick   = $player->getLowerCaseName();
		$group  = $main->getPlayerGroup($nick);

		if(!$group->isTemporary()) {
			return;
		}

		if($group->iunipired()) {
			$main->setPlayerGroup($main->getDefaultGroup(), $nick);

			$player->sendMessage(Manager::PREFIX_INFO. "Срок действия Вашей группы истек");
			return;
		}

		$expire = $group->getExpire();

		if($expire > $main->getBaseConfiguration()->getNotificationGroupExpire()) {
			return;
		}

		$expire = Declinator::formDate($expire);

		$player->sendMessage(Manager::PREFIX_INFO. "До окончания срока действия группы §e" . $expire);
	}
}