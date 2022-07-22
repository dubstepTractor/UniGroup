<?php declare(strict_types = 1); namespace uni\group\command;

use uni\group\Manager;

use uni\group\command\argument\Argument;
use uni\group\command\argument\SetArgument;
use uni\group\command\argument\HideArgument;
use uni\group\command\argument\InfoArgument;
use uni\group\command\argument\ListArgument;

use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;

use function strtolower;
use function array_shift;

class GroupCommand extends Command {

	private const NAME        = 'group';
	private const PERMISSION  = 'unigroup.command.group';
	private const DESCRIPTION = '§eПоказывает помощь или список команд управления группами';

	private const PERMISSION_LIST = [
		self::PERMISSION => self::DESCRIPTION
	];

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 * @var Argument[]
	 */
	private $argument_list = [
		SetArgument::class,
		HideArgument::class,
		InfoArgument::class,
		ListArgument::class
	];

	/**
	 *                                             _
	 *   ___  ___  _ __ _  _ __ _   __ _ _ __   __| |
	 *  / __\/ _ \| '  ' \| '  ' \ / _' | '_ \ / _' |
	 * | (__| (_) | || || | || || | (_) | | | | (_) |
	 *  \___/\___/|_||_||_|_||_||_|\__,_|_| |_|\__,_|
	 *
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		parent::__construct(self::NAME, self::DESCRIPTION);

		$this->manager = $main;

		foreach($this->argument_list as $index => $class) {
			$this->argument_list[$index] = new $class($main);
		}

		foreach(self::PERMISSION_LIST as $permission => $description) {
			$permission = new Permission($permission, $description);

			PermissionManager::getInstance()->addPermission($permission);
		}

		$this->setPermission(self::PERMISSION);
	}

	/**
	 * @param  CommandSender $sender
	 * @param  string        $label
	 * @param  string[]      $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, string $label, array $args) {
		$main = $this->getManager();

		if($sender instanceof Player) {
			$group = $this->getManager()->getPlayerGroup($sender->getLowerCaseName());
			$hided = $group->isHided();

			if(!$sender->hasPermission(self::PERMISSION) and !$hided) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
				return true;
			}
		}

		if(!empty($args)) {
			$argument = $this->getArgument(array_shift($args));

			if(isset($argument)) {
				return $argument->execute($sender, $args);
			}
		}

		$sender->sendMessage(Manager::PREFIX_INFO. "Используйте: §e/group <set/list/info/hide>");
		return true;
	}

	/**
	 * @param  string $name
	 *
	 * @return Argument|null
	 */
	private function getArgument(string $name): ?Argument {
		$name = strtolower($name);

		foreach($this->getArgumentList() as $argument) {
			if($argument->getName() !== $name) {
				continue;
			}

			return $argument;
		}

		return null;
	}

	/**
	 * @return Manager
	 */
	private function getManager(): Manager {
		return $this->manager;
	}

	/**
	 * @return Argument[]
	 */
	private function getArgumentList(): array {
		return $this->argument_list;
	}
}