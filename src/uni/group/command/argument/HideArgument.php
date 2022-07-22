<?php declare(strict_types = 1); namespace uni\group\command\argument;

use uni\group\Manager;
use uni\group\command\argument\Argument;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use function strtolower;
use function array_shift;

class HideArgument extends Argument {

	protected const NAME = 'hide';

	protected const PERMISSION        = 'unigroup.command.group-hide';
	protected const PERMISSION_PLAYER = 'unigroup.command.group-hide-player';

	protected const DESCRIPTION        = 'Скрывает группу отправителю';
	protected const DESCRIPTION_PLAYER = 'Скрывает группу указанному игроку';

	protected const PERMISSION_LIST = [
		self::PERMISSION        => self::DESCRIPTION,
		self::PERMISSION_PLAYER => self::DESCRIPTION_PLAYER
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
		$main = $this->getManager();

		if(empty($args)) {
			if(!$sender instanceof Player) {
				$sender->sendMessage(Manager::PREFIX_INFO. "Используйте: §e/group hide <игрок>");
				return true;
			}

			$group = $main->getPlayerGroup($sender->getLowerCaseName());
			$hided = $group->isHided();

			if(!$sender->hasPermission(self::PERMISSION) and !$hided) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
				return true;
			}

			if($group->isDefault()) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Невозможно скрыть группу по-умолчанию");
				return true;
			}

			$main->setPlayerGroup($group->setHided(!$hided));

			if($hided) {
				$sender->sendMessage(Manager::PREFIX_SUCCESS. "Скрытый режим выключен");
				return true;
			}

			$sender->sendMessage(Manager::PREFIX_SUCCESS. "Скрытый режим включен");
			return true;
		}

		if(!$sender->hasPermission(self::PERMISSION_PLAYER)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
			return true;
		}

		$nick  = strtolower(array_shift($args));
		$group = $main->getPlayerGroup($nick);

		if($group->isDefault()) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Невозможно скрыть группу по-умолчанию");
			return true;
		}

		$hided = $group->isHided();

		$main->setPlayerGroup($group->setHided(!$hided));

		if($hided) {
			$sender->sendMessage(Manager::PREFIX_SUCCESS. "Скрытый режим для §a$nick §rвыключен");
			return true;
		}

		$sender->sendMessage(Manager::PREFIX_SUCCESS. "Скрытый режим для §a$nick §rактивирован");
		return true;
	}
}