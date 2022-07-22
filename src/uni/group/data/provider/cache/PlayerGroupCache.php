<?php declare(strict_types = 1); namespace uni\group\data\provider\cache;

use uni\group\data\PlayerGroup;

use function strtolower;

class PlayerGroupCache {

	/**
	 * @var PlayerGroup[]
	 */
	private $list = [];

	/**
	 *                  _
	 *   ___  __ _  ___| |__   ___
	 *  / __\/ _' |/ __| '_ \ / _ \
	 * | (__| (_) | (__| | | |  __/
	 *  \___/\__,_|\___|_| |_|\___/
	 *
	 *
	 * @param PlayerGroup[] $list
	 */
	public function __construct(PlayerGroup ...$list) {
		foreach($list as $group) {
			$this->set($group);
		}
	}

	/**
	 * @return PlayerGroup[]
	 */
	public function getAll(): array {
		return $this->list;
	}

	/**
	 * @param  string $nick
	 *
	 * @return PlayerGroup|null
	 */
	public function get(string $nick): ?PlayerGroup {
		$nick = strtolower($nick);

		if(!isset($this->list[$nick])) {
			return null;
		}

		return $this->list[$nick];
	}

	/**
	 * @param  PlayerGroup $group
	 *
	 * @return PlayerGroupCache
	 */
	public function set(PlayerGroup $group): PlayerGroupCache {
		$this->list[$group->getNickname()] = $group;

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return PlayerGroupCache
	 */
	public function remove(string $nick): PlayerGroupCache {
		$nick = strtolower($nick);

		if(isset($this->list[$nick])) {
			unset($this->list[$nick]);
		}

		return $this;
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public function exists(string $nick): bool {
		return isset($this->list[strtolower($nick)]);
	}
}