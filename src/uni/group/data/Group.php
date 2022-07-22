<?php declare(strict_types = 1); namespace uni\group\data;

use uni\group\data\container\PermissionList;

use Exception;
use InvalidArgumentException;

use function floor;
use function intval;
use function strval;
use function boolval;
use function mb_strtolower;

class Group {

	public const ID_DEFAULT       = 0;
	public const ID_VIP           = 10;
	public const ID_PREMIUM       = 20;
	public const ID_CREATIVE      = 30;
	public const ID_MODERATOR     = 40;
	public const ID_ADMINISTRATOR = 50;
	public const ID_ANTIGRIEFER   = 60;
	public const ID_OPERATOR      = 70;
	public const ID_BOSS          = 80;
	public const ID_SPONSOR       = 90;
	public const ID_LORD          = 100;
	public const ID_GOD           = 110;
	public const ID_YOUTUBE       = 111;
	public const ID_HELPER        = 120;
	public const ID_MAINTAINER    = 130;

	protected const INDEX_ID              = 'id';
	protected const INDEX_PARENT_ID       = 'parent_id';
	protected const INDEX_NAME            = 'name';
	protected const INDEX_DISPLAY_NAME    = 'display_name';
	protected const INDEX_NAME_TAG        = 'name_tag';
	protected const INDEX_CHAT_FORMAT     = 'chat_format';
	protected const INDEX_RESTRICTED      = 'restricted';
	protected const INDEX_PERMISSION_LIST = 'permission_list';

	/**
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * @var Group[]
	 */
	private static $list = [
		self::ID_DEFAULT       => null,
		self::ID_VIP           => null,
		self::ID_PREMIUM       => null,
		self::ID_CREATIVE      => null,
		self::ID_MODERATOR     => null,
		self::ID_ADMINISTRATOR => null,
		self::ID_ANTIGRIEFER   => null,
		self::ID_OPERATOR      => null,
		self::ID_BOSS          => null,
		self::ID_SPONSOR       => null,
		self::ID_LORD          => null,
		self::ID_GOD           => null,
		self::ID_YOUTUBE       => null,
		self::ID_HELPER        => null,
		self::ID_MAINTAINER    => null
	];

	/**
	 * @return bool
	 */
	public static function isInitialized(): bool {
		return self::$initialized;
	}

	/**
	 * sorts group config data in static storage of group objects
	 *
	 * @param  mixed[] $data
	 *
	 * @throws Exception
	 */
	public static function initialize(array $data): void {
		if(self::isInitialized()) {
			throw new Exception("Groups are already initialized!");
		}

		foreach($data as $id => $group_data) {
			$group = self::fromDataEntry(intval($id), $group_data);
			$id    = $group->getId();

			if(isset(self::$list[$id])) {
				throw new Exception("Group with ID $id already initialized!");
			}

			foreach(self::$list as $entry_id => $entry) {
				if(isset(self::$list[$entry_id])) {
					continue;
				}

				if($entry_id === $id) {
					break;
				}

				throw new Exception("Invalid group data sequence! Check Group::\$list.");
			}

			self::$list[$id] = clone $group;
		}

		self::$initialized = true;
	}

	/**
	 * @param  int  $id
	 * @param  bool $floor
	 *
	 * @return Group|null
	 */
	public static function get(int $id, bool $floor = false): ?Group {
		if($floor) {
			/**
			 * @todo find id with existing group
			 */
			$id = floor($id);
		}

		if(!isset(self::$list[$id])) {
			return null;
		}

		return clone self::$list[$id];
	}

	/**
	 * @return Group
	 *
	 * @throws Exception
	 */
	public static function getDefault(): Group {
		$group = self::get(self::ID_DEFAULT);

		if(!isset($group)) {
			throw new Exception("There is no default group!");
		}

		return $group;
	}

	/**
	 * @param  string $name
	 *
	 * @return Group|null
	 */
	public static function getByName(string $name): ?Group {
		$name = mb_strtolower($name);

		foreach(self::$list as $group) {
			if($group->getName() != $name) {
				continue;
			}

			return clone $group;
		}

		return null;
	}

	/**
	 * returns first group with permission
	 *
	 * @param  string $permission
	 *
	 * @return Group|null
	 */
	public static function getByPermission(string $permission): ?Group {
		foreach(self::$list as $group) {
			if(!$group->getPermissionList()->get($permission)) {
				continue;
			}

			return clone $group;
		}

		return null;
	}

	/**
	 * @todo   validate data types
	 *
	 * @param  int     $id
	 * @param  mixed[] $data
	 *
	 * @return Group
	 */
	public static function fromDataEntry(int $id, array $data): Group {
		$data[self::INDEX_ID] = $id;

		return self::fromData($data);
	}

	/**
	 * @todo   validate data types
	 *
	 * @param  mixed[] $data
	 *
	 * @return Group
	 *
	 * @throws Exception
	 */
	public static function fromData(array $data): Group {
		$id = self::ejectDataValue($data, self::INDEX_ID);

		if(isset($data[self::INDEX_PARENT_ID])) {
			$parent_id = intval($data[self::INDEX_PARENT_ID]);

			if($parent_id === $id) {
				throw new Exception("Invalid parent group ID!");
			}

			$parent = null;

			foreach(self::$list as $group) {
				if(!isset($group)) {
					continue;
				}

				if($group->getId() !== $parent_id) {
					continue;
				}

				$parent = $group;

				break;
			}

			if(isset($parent)) {
				$name        = $parent->getName();
				$display     = $parent->getDisplayName();
				$tag         = $parent->getNameTag();
				$format      = $parent->getChatFormat();
				$restricted  = $parent->isRestricted();
				$parent_list = $parent->getPermissionList()->getAll();
			}
		}

		$name       = self::ejectDataValue($data, self::INDEX_NAME,         $name       ?? null);
		$display    = self::ejectDataValue($data, self::INDEX_DISPLAY_NAME, $display    ?? null);
		$tag        = self::ejectDataValue($data, self::INDEX_NAME_TAG,     $tag        ?? null);
		$format     = self::ejectDataValue($data, self::INDEX_CHAT_FORMAT,  $format     ?? null);
		$restricted = self::ejectDataValue($data, self::INDEX_RESTRICTED,   $restricted ?? false);

		$list = self::ejectDataValue($data, self::INDEX_PERMISSION_LIST, []);

		if(!is_array($list)) {
			$list = [];
		}

		if(isset($parent_list)) {
			$list += $parent_list;
		}

		return new Group(
			intval($id),
			strval($name),
			strval($display),
			strval($tag),
			strval($format),
			boolval($restricted),
			PermissionList::fromData($list)
		);
	}

	/**
	 * @param  mixed[] $data
	 * @param  string  $index
	 * @param  mixed   $default
	 *
	 * @return mixed|null
	 *
	 * @throws Exception
	 */
	protected static function ejectDataValue(array $data, string $index, $default = null) {
		$value = null;

		if(isset($data[$index])) {
			$value = $data[$index];
		}

		if(!isset($value)) {
			if(!isset($default)) {
				throw new Exception("Index '$index' does not exists!");
			}

			$value = $default;
		}

		return $value;
	}

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $display_name;

	/**
	 * @var string
	 */
	private $name_tag;

	/**
	 * @var string
	 */
	private $chat_format;

	/**
	 * @var bool
	 */
	private $restricted;

	/**
	 * @var PermissionList
	 */
	private $permission_list;

	/**
	 *
	 *   __ _ _ _____  _   _ _ __
	 *  / _' | '_/ _ \| | | | '_ \
	 * | (_) | || (_) | |_| | (_) |
	 *  \__, |_| \___/ \___/| ,__/
	 *  /___/               |_|
	 *
	 * @param int            $id
	 * @param string         $name
	 * @param string         $display
	 * @param string         $tag
	 * @param string         $format
	 * @param bool           $restricted
	 * @param PermissionList $list
	 */
	protected function __construct(int $id, string $name, string $display, string $tag, string $format, bool $restricted, PermissionList $list) {
		$this->id              = $id;
		$this->name            = $name;
		$this->display_name    = $display;
		$this->name_tag        = $tag;
		$this->chat_format     = $format;
		$this->restricted      = $restricted;
		$this->permission_list = $list;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function isDefault(): bool {
		return $this->getId() === self::ID_DEFAULT;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string {
		return $this->display_name;
	}

	/**
	 * @return string
	 */
	public function getNameTag(): string {
		return $this->name_tag;
	}

	/**
	 * @return string
	 */
	public function getChatFormat(): string {
		return $this->chat_format;
	}

	/**
	 * @return bool
	 */
	public function isRestricted(): bool {
		return $this->restricted;
	}

	/**
	 * @return PermissionList
	 */
	public function getPermissionList(): PermissionList {
		return $this->permission_list;
	}
}