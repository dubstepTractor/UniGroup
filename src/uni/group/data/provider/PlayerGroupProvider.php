<?php declare(strict_types = 1); namespace uni\group\data\provider;

use uni\group\task\async\PlayerGroupDeleteTask;
use uni\group\task\async\PlayerGroupUpdateTask;

use uni\group\data\PlayerGroup;
use uni\group\data\provider\cache\PlayerGroupCache;
use uni\group\data\provider\mysql\PlayerGroupBridge;

use pocketmine\Server;

use function strtolower;

/**
 * @todo reformat all mysql-related stuff
 */
class PlayerGroupProvider {

	/**
	 * @var bool
	 */
	private $synchronized;

	/**
	 * @var PlayerGroupCache
	 */
	private $cache;

	/**
	 *                       _     _
	 *  _ __  _ _______    _(_) __| | ___ _ __
	 * | '_ \| '_/ _ \ \  / | |/ _' |/ _ \ '_/
	 * | (_) | || (_) \ \/ /| | (_) |  __/ |
	 * | ,__/|_| \___/ \__/ |_|\__,_|\___|_|
	 * |_|
	 *
	 * @param bool $sync
	 */
	public function __construct(bool $sync = false) {
		$this->synchronized = $sync;
		$this->cache        = new PlayerGroupCache();

		PlayerGroupBridge::createPlayerGroupTable();
	}

	public function __destruct() {
		PlayerGroupBridge::close();
	}

	/**
	 * @param  string $nick
	 * @param  bool   $ignore_storage
	 *
	 * @return PlayerGroup|null
	 */
	public function getPlayerGroup(string $nick, bool $ignore_storage = false): ?PlayerGroup {
		$nick  = strtolower($nick);
		$group = $this->getCache()->get($nick);

		if(isset($group)) {
			if($this->isPlayerOnline($nick)) {
				return $group;
			}

			$this->removePlayerGroup($nick, true);
		}

		if($ignore_storage) {
			return null;
		}

		$group = PlayerGroupBridge::selectPlayerGroup($nick);

		if(!isset($group)) {
			return null;
		}

		$this->setPlayerGroup($group, true);
		return $group;
	}

	/**
	 * @param  PlayerGroup $group
	 * @param  bool        $ignore_storage
	 *
	 * @return PlayerGroupProvider
	 */
	public function setPlayerGroup(PlayerGroup $group, bool $ignore_storage = false): PlayerGroupProvider {
		if($this->isPlayerOnline($group->getNickname())) {
			$this->getCache()->set($group);
		}

		$this->getCache()->set($group);

		if($ignore_storage) {
			return $this;
		}

		if($this->isSynchronized()) {
			PlayerGroupBridge::updatePlayerGroup($group);
		} else {
			Server::getInstance()->getAsyncPool()->submitTask(new PlayerGroupUpdateTask($group));
		}

		return $this;
	}

	/**
	 * @param  string $nick
	 * @param  bool   $ignore_storage
	 *
	 * @return PlayerGroupProvider
	 */
	public function removePlayerGroup(string $nick, bool $ignore_storage = false): PlayerGroupProvider {
		$nick = strtolower($nick);

		$this->getCache()->remove($nick);

		if($ignore_storage) {
			return $this;
		}

		if($this->isSynchronized()) {
			PlayerGroupBridge::deletePlayerGroup($nick);
		} else {
			Server::getInstance()->getAsyncPool()->submitTask(new PlayerGroupDeleteTask($nick));
		}

		return $this;
	}

	/**
	 * @return PlayerGroupProvider
	 */
	public function clearStorage(): MysqlProvider {
		PlayerGroupBridge::clearPlayerGroupTable();

		return $this->clearCache();
	}

	/**
	 * @return PlayerGroupProvider
	 */
	public function clearCache(): MysqlProvider {
		$this->cache = new PlayerGroupCache();

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	private function isPlayerOnline(string $nick): bool {
		return Server::getInstance()->getPlayerExact($nick) !== null;
	}

	/**
	 * @return bool
	 */
	private function isSynchronized(): bool {
		return $this->synchronized;
	}

	/**
	 * @return PlayerGroupCache
	 */
	private function getCache(): PlayerGroupCache {
		return $this->cache;
	}
}