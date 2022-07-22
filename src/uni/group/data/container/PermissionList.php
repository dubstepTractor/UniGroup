<?php declare(strict_types = 1); namespace uni\group\data\container;

use pocketmine\permission\PermissionManager;

use function trim;
use function substr;
use function strval;
use function strtolower;

class PermissionList {

	public const CHAR_ALL      = '*';
	public const CHAR_NEGATIVE = '-';

	/**
	 * @param  mixed[] $data
	 *
	 * @return PermissionList
	 */
	public static function fromData(array $data): PermissionList {
		$list = [];

		foreach($data as $permission) {
			$list[] = strval($permission);
		}

		return new PermissionList(...$list);
	}

	/**
	 * @var string[]
	 */
	private $list = [];

	/**
	 * @var bool[]
	 */
	private $sorted_list = [];

	/**
	 *                   _        _
	 *   ___  ___  _ __ | |____ _(_)_ __   ___ _ __
	 *  / __\/ _ \| '_ \|  _/ _' | | '_ \ / _ \ '_/
	 * | (__| (_) | | | | || (_) | | | | |  __/ |
	 *  \___/\___/|_| |_|\__\__,_|_|_| |_|\___|_|
	 *
	 *
	 * @param string[] $permission_list
	 */
	public function __construct(string ...$permission_list) {
		$list = [];

		foreach($permission_list as $permission) {
			$permission = strtolower(trim($permission));

			foreach($list as $entry) {
				if($permission === $entry) {
					continue 2;
				}
			}

			$list[] = $permission;
		}

		$this->list = $list;
	}

	/**
	 * @return string[]
	 */
	public function getAll(): array {
		return $this->list;
	}

	/**
	 * we can't sort permission list in {@link PermissionList#__construct}
	 * due to plugin load order...
	 *
	 * @param  bool $ignore_cache
	 *
	 * @return bool[]
	 */
	public function getAllSorted(bool $ignore_cache = false): array {
		/**
		 * ...but we can cache sorted list for faster access (not safe)
		 */
		if(!$ignore_cache and !empty($this->sorted_list)) {
			return $this->sorted_list;
		}

		$list = [];

		foreach($this->getAll() as $permission) {
			if($permission === self::CHAR_ALL) {
				foreach(PermissionManager::getInstance()->getPermissions() as $entry) {
					$list[$entry->getName()] = true;
				}

				continue;
			}

			$status = true;

			if($permission[0] === self::CHAR_NEGATIVE) {
				$permission = substr($permission, 1);
				$status     = false;
			}

			$list[$permission] = $status;
		}

		return $this->sorted_list = $list;
	}

	/**
	 * @param  string $permission
	 *
	 * @return bool
	 */
	public function get(string $permission): bool {
		$permission = strtolower($permission);
		$list       = $this->getAllSorted();

		return $list[$permission] ?? false;
	}

	/**
	 * @param  string $permission
	 *
	 * @return bool
	 */
	public function exists(string $permission): bool {
		$permission = strtolower($permission);
		$list       = $this->getAllSorted();

		return isset($list[$permission]);
	}

	/**
	 * @return string[]
	 */
	public function toData(): array {
		return $this->getAll();
	}
}