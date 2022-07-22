<?php declare(strict_types = 1); namespace uni\group\data;

use uni\group\data\Group;
use uni\group\data\container\PermissionList;

use Exception;
use InvalidArgumentException;

use function time;
use function intval;
use function strval;
use function boolval;
use function strtolower;

class PlayerGroup extends Group {

	protected const INDEX_NICKNAME         = 'nickname';
	protected const INDEX_GROUP_ID         = 'group_id';
	protected const INDEX_HIDED            = 'hided';
	protected const INDEX_EXPIRE_TIMESTAMP = 'expire_timestamp';

	public const TIMESTAMP_UNDEFINED = 0;

	/**
	 * @todo   validate data types
	 *
	 * @param  mixed[] $data
	 *
	 * @return PlayerGroup
	 */
	public static function fromData(array $data): Group {
		$nick   = self::ejectDataValue($data, self::INDEX_NICKNAME);
		$id     = self::ejectDataValue($data, self::INDEX_GROUP_ID);
		$hided  = self::ejectDataValue($data, self::INDEX_HIDED);
		$expire = self::ejectDataValue($data, self::INDEX_EXPIRE_TIMESTAMP);

		return self::fromObject(
			self::get(intval($id)) ?? self::getDefault(),
			strval($nick),
			boolval($hided),
			intval($expire)
		);
	}

	/**
	 * @param  Group  $group
	 * @param  string $nick
	 * @param  bool   $hided
	 * @param  int    $expire
	 *
	 * @return PlayerGroup
	 */
	public static function fromObject(Group $group, string $nick, bool $hided = false, int $expire = self::TIMESTAMP_UNDEFINED): PlayerGroup {
		return new PlayerGroup(
			$group->getId(),
			$nick,
			$hided,
			$expire
		);
	}

	/**
	 * @var string
	 */
	private $nickname;

	/**
	 * @var bool
	 */
	private $hided = false;

	/**
	 * @var int
	 */
	private $expire_timestamp = self::TIMESTAMP_UNDEFINED;

	/**
	 *
	 *   __ _ _ _____  _   _ _ __
	 *  / _' | '_/ _ \| | | | '_ \
	 * | (_) | || (_) | |_| | (_) |
	 *  \__, |_| \___/ \___/| ,__/
	 *  /___/               |_|
	 *
	 * @param  int    $id
	 * @param  string $nick
	 * @param  bool   $hided
	 * @param  int    $expire
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct(int $id, string $nick, bool $hided = false, int $expire = self::TIMESTAMP_UNDEFINED) {
		$group = self::get($id);

		if(!isset($group)) {
			throw new InvalidArgumentException("Group with ID $id does not exists!");
		}

		parent::__construct(
			$group->getId(),
			$group->getName(),
			$group->getDisplayName(),
			$group->getNameTag(),
			$group->getChatFormat(),
			$group->isRestricted(),
			$group->getPermissionList()
		);

		$this->nickname = strtolower($nick);

		if($this->isDefault()) {
			return;
		}

		$this->hided            = $hided;
		$this->expire_timestamp = $expire;
	}

	/**
	 * @return string
	 */
	public function getNickname(): string {
		return $this->nickname;
	}

	/**
	 * @return bool
	 */
	public function isHided(): bool {
		return $this->hided;
	}

	/**
	 * @param  bool $status
	 *
	 * @return PlayerGroup
	 */
	public function setHided(bool $status = true): PlayerGroup {
		if($this->isDefault()) {
			throw new Exception("Default group cannot be hided!");
		}

		$this->hided = $status;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getExpireTimestamp(): int {
		return $this->expire_timestamp;
	}

	/**
	 * @param  int $time
	 *
	 * @return PlayerGroup
	 */
	public function setExpireTimestamp(int $time = self::TIMESTAMP_UNDEFINED): PlayerGroup {
		if($this->isDefault()) {
			throw new Exception("Default group cannot be temporary!");
		}

		$this->expire_timestamp = $time <= time() ? self::TIMESTAMP_UNDEFINED : $time;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getExpire(): int {
		return $this->getExpireTimestamp() - time();
	}

	/**
	 * @return bool
	 */
	public function isTemporary(): bool {
		return $this->getExpireTimestamp() !== self::TIMESTAMP_UNDEFINED;
	}

	/**
	 * @return bool
	 */
	public function isExpired(): bool {
		return $this->isTemporary() and time() >= $this->getExpireTimestamp();
	}

	/**
	 * @return bool
	 *
	public function isDefault(): bool {
		return $this->isHided() ? self::getDefault()->isDefault() : parent::isDefault(); // it's ok
	}*/

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->isHided() ? self::getDefault()->getName() : parent::getName();
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string {
		return $this->isHided() ? self::getDefault()->getDisplayName() : parent::getDisplayName();
	}

	/**
	 * @return string
	 */
	public function getNameTag(): string {
		return $this->isHided() ? self::getDefault()->getNameTag() : parent::getNameTag();
	}

	/**
	 * @return string
	 */
	public function getChatFormat(): string {
		return $this->isHided() ? self::getDefault()->getChatFormat() : parent::getChatFormat();
	}

	/**
	 * @return bool
	 *
	public function isRestricted(): bool {
		return $this->isHided() ? self::getDefault()->isRestricted() : parent::isRestricted();
	}*/

	/**
	 * @return PermissionList
	 */
	public function getPermissionList(): PermissionList {
		return $this->isHided() ? self::getDefault()->getPermissionList() : parent::getPermissionList();
	}

	/**
	 * @return mixed[]
	 */
	public function toDataEntry(): array {
		return [
			self::INDEX_GROUP_ID         => $this->getId(),
			self::INDEX_HIDED            => intval($this->isHided()),
			self::INDEX_EXPIRE_TIMESTAMP => $this->getExpireTimestamp()
		];
	}

	/**
	 * @return mixed[]
	 */
	public function toData(): array {
		$data = [
			self::INDEX_NICKNAME => $this->getNickname()
		];

		return $data + $this->toDataEntry();
	}
}