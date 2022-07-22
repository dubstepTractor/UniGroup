<?php declare(strict_types = 1); namespace uni\group\command\argument;

use uni\group\Manager;
use uni\group\data\Group;
use uni\group\util\Declinator;
use uni\group\command\argument\Argument;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use function strtolower;
use function array_shift;

class InfoArgument extends Argument {

	protected const NAME = 'info';

	protected const PERMISSION        = 'unigroup.command.group-info';
	protected const PERMISSION_PLAYER = 'unigroup.command.group-info-player';

	protected const DESCRIPTION        = 'Показывает информацию о группе отправителя';
	protected const DESCRIPTION_PLAYER = 'Показывает информацию о группе игрока';

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
				$sender->sendMessage(Manager::PREFIX_INFO. "Используйте: §e/group info <игрок>");
				return true;
			}

			$group = $main->getPlayerGroup($sender->getLowerCaseName());

			/**
			 * yo holy shit
			 */
			$base  = Group::get($group->getId());
			$hided = $group->isHided();

			if(!$sender->hasPermission(self::PERMISSION) and !$hided) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
				return true;
			}

			$id         = $base->getId();
			$name       = $base->getName();
			$restricted = $base->isRestricted() ? "да" : "нет";
			$hided      = $hided ? "включен" : "отключен";

			$sender->sendMessage("--- ---");
			$sender->sendMessage("Группа: $name");
			$sender->sendMessage("Идентификатор: $id");
			$sender->sendMessage("Ограниченное пользование: $restricted");
			$sender->sendMessage("Скрытый режим: $hided");

			if($group->isTemporary()) {
				/**
				 * @todo reformat
				 */
				$expire = $group->isExpired() ? "истек" : Declinator::formDate($group->getExpire());

				$sender->sendMessage("Срок годности: $expire");
			}

			$sender->sendMessage("--- ---");
			return true;
		}

		if(!$sender->hasPermission(self::PERMISSION_PLAYER)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
			return true;
		}

		$nick  = strtolower(array_shift($args));
		$group = $main->getPlayerGroup($nick);

		/**
		 * yo holy shit
		 */
		$base = Group::get($group->getId());

		$id         = $base->getId();
		$name       = $base->getName();
		$restricted = $base->isRestricted() ? "да" : "нет";
		$hided      = $group->isHided() ? "включен" : "отключен";

		$sender->sendMessage("--- $nick ---");
		$sender->sendMessage("Группа: $name");
		$sender->sendMessage("Идентификатор: $id");
		$sender->sendMessage("Ограниченное пользование: $restricted");
		$sender->sendMessage("Скрытый режим: $hided");

		if($group->isTemporary()) {
			/**
			 * @todo reformat
			 */
			$expire = $group->isExpired() ? "истек" : Declinator::formDate($group->getExpire());

			$sender->sendMessage("Срок годности: $expire");
		}

		$sender->sendMessage("--- ---");
		return true;
	}
}