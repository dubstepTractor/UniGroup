<?php declare(strict_types = 1); namespace uni\group;

use uni\group\command\GroupCommand;

use uni\group\data\Group;
use uni\group\data\PlayerGroup;
use uni\group\data\provider\PlayerGroupProvider;

use uni\group\data\configuration\BaseConfiguration;
use uni\group\data\configuration\GroupConfiguration;

use uni\group\event\group\PlayerGroupUpdateEvent;
use uni\group\event\listener\player\PlayerJoinListener;
use uni\group\event\listener\player\PlayerLoginListener;

use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

use InvalidArgumentException;

class Manager extends PluginBase {

	/**
	 *                    __ _                      _   _
	 *   ___  ___  _ __ _/ _(_) __ _ _   _ _ ____ _| |_(_) ___  _ __
	 *  / __\/ _ \| '_ \   _| |/ _' | | | | '_/ _' |  _| |/ _ \| '_ \
	 * | (__| (_) | | | | | | | (_) | |_| | || (_) | |_| | (_) | | | |
	 *  \___/\___/|_| |_|_| |_|\__  |\___/|_| \__,_|\__|_|\___/|_| |_|
	 *                         /___/
	 *
	 * @todo create config?
	 */
	public const MYSQL_HOSTNAME = '127.0.0.1';
	public const MYSQL_USERNAME = 'ark';
	public const MYSQL_PASSWORD = 'gK8M3lb5TCkf6e38Vgovd7';
	public const MYSQL_DATABASE = 'grendfin';

	/**
	 * @todo implement Formatter
	 */
	public const PREFIX_SUCCESS = "§l§a(!)§r ";
	public const PREFIX_ERROR   = "§l§c(!)§r ";
	public const PREFIX_INFO    = "§l§e(!)§r ";

	/**
	 * @var BaseConfiguration
	 */
	private $base_configuration;

	/**
	 * @var GroupConfiguration
	 */
	private $group_configuration;

	/**
	 * @var PlayerGroupProvider
	 */
	private $group_provider;

	/**
	 *
	 *  _ __ _   __ _ _ __   __ _  __ _  ___ _ __
	 * | '  ' \ / _' | '_ \ / _' |/ _' |/ _ \ '_/
	 * | || || | (_) | | | | (_) | (_) |  __/ |
	 * |_||_||_|\__,_|_| |_|\__,_|\__, |\___|_|
	 *                            /___/
	 *
	 */
	public function onEnable(): void {
		$this->loadConfiguration();
		$this->loadPlayerGroupProvider();
		$this->loadListener();
		$this->loadCommand();
	}

	private function loadConfiguration(): void {
		$this->base_configuration  = new BaseConfiguration($this);
		$this->group_configuration = new GroupConfiguration($this);
	}

	private function loadPlayerGroupProvider(): void {
		$this->group_provider = new PlayerGroupProvider();
	}

	private function loadListener(): void {
		$list = [
			new PlayerJoinListener($this),
			new PlayerLoginListener($this)
		];

		foreach($list as $listener) {
			$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		}
	}

	private function loadCommand(): void {
		$list = [
			new GroupCommand($this)
		];

		foreach($list as $command) {
			$map     = $this->getServer()->getCommandMap();
			$replace = $map->getCommand($command->getName());

			if(isset($replace)) {
				$replace->setLabel('');
				$replace->unregister($map);
			}

			$map->register($this->getName(), $command);
		}
	}

	/**
	 * @return BaseConfiguration
	 */
	public function getBaseConfiguration(): BaseConfiguration {
		return $this->base_configuration;
	}

	/**
	 * @return GroupConfiguration
	 */
	public function getGroupConfiguration(): GroupConfiguration {
		return $this->group_configuration;
	}

	/**
	 * @return PlayerGroupProvider
	 */
	private function getPlayerGroupProvider(): PlayerGroupProvider {
		return $this->group_provider;
	}

	/**
	 * @todo  reformat
	 *
	 * @param Player     $player
	 * @param Group|null $group
	 */
	public function updatePermissionList(Player $player, ?Group $group = null): void {
		$group = $group ?? $this->getPlayerGroup($player->getLowerCaseName());
		$list  = $group->getPermissionList()->getAllSorted();

		$attachment = $player->addAttachment($this);

		$attachment->clearPermissions();
		$attachment->setPermissions($list);
	}

	/**
	 *              _
	 *   __ _ ____ (_)
	 *  / _' |  _ \| |
	 * | (_) | (_) | |
	 *  \__,_|  __/|_|
	 *       |_|
	 *
	 * @param  int $id
	 *
	 * @return Group|null
	 */
	public function getGroup(int $id): ?Group {
		return Group::get($id);
	}

	/**
	 * @return Group
	 */
	public function getDefaultGroup(): Group {
		return Group::getDefault();
	}

	/**
	 * @param  string $name
	 *
	 * @return Group|null
	 */
	public function getGroupByName(string $name): ?Group {
		return Group::getByName($name);
	}

	/**
	 * @param  string $permission
	 *
	 * @return Group|null
	 */
	public function getGroupByPermission(string $permission): ?Group {
		return Group::getByPermission($permission);
	}

	/**
	 * @param  string $nick
	 *
	 * @return PlayerGroup
	 */
	public function getPlayerGroup(string $nick): PlayerGroup {
		$group = $this->getPlayerGroupProvider()->getPlayerGroup($nick);

		if(!isset($group)) {
			/**
			 * @todo check timings
			 */
			$group = PlayerGroup::fromObject($this->getDefaultGroup(), $nick);
		}

		return $group;
	}

	/**
	 * @todo  check alternatives
	 *
	 * @param PlayerGroup|Group $group
	 * @param string            $nick
	 * @param bool              $hided
	 * @param int               $expire
	 */
	public function setPlayerGroup(Group $group, string $nick = '', bool $hided = false, int $expire = PlayerGroup::TIMESTAMP_UNDEFINED): void {
		if(!$group instanceof PlayerGroup) {
			if(empty($nick)) {
				throw new InvalidArgumentException("Nickname is not defined!");
			}

			$group = PlayerGroup::fromObject($group, $nick, $hided, $expire);
		}

		$event = new PlayerGroupUpdateEvent($this, $group);

		$event->call();

		if($event->isCancelled()) {
			return;
		}

		$group  = $event->getPlayerGroup();
		$player = $event->getPlayer();

		$this->getPlayerGroupProvider()->setPlayerGroup($group);

		if(!isset($player)) {
			return;
		}

		$this->updatePermissionList($player, $group);
	}

	/**
	 *      _                               _           _
	 *   __| | ___ _ __  _ _____  ___  __ _| |_____  __| |
	 *  / _' |/ _ \ '_ \| '_/ _ \/ __\/ _' |  _/ _ \/ _' |
	 * | (_) |  __/ (_) | ||  __/ (__| (_) | ||  __/ (_) |
	 *  \__,_|\___| ,__/|_| \___/\___/\__,_|\__\___\\__,_|
	 *            |_|
	 *
	 * @deprecated Use permissions instead.
	 *
	 * @param  string $nick
	 * @param  int    $id
	 *
	 * @return bool
	 */
	public function hasAccess(string $nick, int $id, bool $ignore_hide = false): bool {
		$current = 0;
		$group   = $this->getPlayerGroup($nick);

		if(!$ignore_hide and !$group->isHided()) {
			$current = $group->getId();
		}

		return $current >= $id;
	}
}