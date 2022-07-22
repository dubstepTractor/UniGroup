<?php declare(strict_types = 1); namespace uni\group\command\argument;

use uni\group\Manager;
use uni\group\command\argument\Argument;

use pocketmine\command\CommandSender;

class ListArgument extends Argument {

	protected const NAME = 'list';

	protected const PERMISSION    = 'unigroup.command.group-list';
	protected const PERMISSION_ID = 'unigroup.command.group-list-id';

	protected const DESCRIPTION    = 'Показывает список групп игроков, отличных от группы по-умолчанию';
	protected const DESCRIPTION_ID = 'Показывает список групп игроков, соответствующих идентификатору';

	protected const PERMISSION_LIST = [
		self::PERMISSION    => self::DESCRIPTION,
		self::PERMISSION_ID => self::DESCRIPTION_ID
	];

	/**
	 *                                          _
	 *   __ _ _ ____ _ _   _ _ __ _   ___ _ __ | |__
	 *  / _' | '_/ _' | | | | '  ' \ / _ \ '_ \|  _/
	 * | (_) | || (_) | |_| | || || |  __/ | | | |_
	 *  \__,_|_| \__, |\___/|_||_||_|\___|_| |_|\__\
	 *           /___/
	 *
	 * @param  CommandSender $sender
	 * @param  string[]      $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, array $args) {
		if(!$sender->hasPermission(self::PERMISSION)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
			return true;
		}

		$sender->sendMessage("позже");
		return true;
	}
}