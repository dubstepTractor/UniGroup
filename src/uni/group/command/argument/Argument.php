<?php declare(strict_types = 1); namespace uni\group\command\argument;

use uni\group\Manager;

use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;

use pocketmine\command\CommandSender;

abstract class Argument {

	protected const NAME = '';

	protected const PERMISSION_LIST = [];

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 *                                          _
	 *   __ _ _ ____ _ _   _ _ __ _   ___ _ __ | |__
	 *  / _' | '_/ _' | | | | '  ' \ / _ \ '_ \|  _/
	 * | (_) | || (_) | |_| | || || |  __/ | | | |_
	 *  \__,_|_| \__, |\___/|_||_||_|\___|_| |_|\__\
	 *           /___/
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		$this->manager = $main;

		foreach(static::PERMISSION_LIST as $permission => $description) {
			$permission = new Permission($permission, $description);

			PermissionManager::getInstance()->addPermission($permission);
		}
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return static::NAME;
	}

	/**
	 * @param  CommandSender $sender
	 * @param  string[]      $args
	 *
	 * @return mixed
	 */
	abstract public function execute(CommandSender $sender, array $args);

	/**
	 * @return Manager
	 */
	protected function getManager(): Manager {
		return $this->manager;
	}
}