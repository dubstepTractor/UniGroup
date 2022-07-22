<?php declare(strict_types = 1); namespace uni\group\command\argument;

use uni\group\Manager;
use uni\group\util\Declinator;
use uni\group\data\PlayerGroup;
use uni\group\command\argument\Argument;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use function intval;
use function is_numeric;
use function strlen;
use function strtolower;
use function mb_strtolower;
use function array_shift;

class SetArgument extends Argument {

	protected const NAME = 'set';

	protected const PERMISSION               = 'unigroup.command.group-set';
	protected const PERMISSION_PLAYER        = 'unigroup.command.group-set-player';
	protected const PERMISSION_PLAYER_EXPIRE = 'unigroup.command.group-set-player-expire';

	protected const DESCRIPTION               = 'Задает группу отправителю';
	protected const DESCRIPTION_PLAYER        = 'Задает группу указанному игроку';
	protected const DESCRIPTION_PLAYER_EXPIRE = 'Задает группу указанному игроку на указанное количество дней';

	protected const PERMISSION_LIST = [
		self::PERMISSION               => self::DESCRIPTION,
		self::PERMISSION_PLAYER        => self::DESCRIPTION_PLAYER,
		self::PERMISSION_PLAYER_EXPIRE => self::DESCRIPTION_PLAYER_EXPIRE
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

		if(empty($args)) {
			$sender->sendMessage(Manager::PREFIX_INFO. "Используйте: §e/group set <группа> [игрок] [кол-во_дн]");
			return true;
		}

		$id    = mb_strtolower(array_shift($args));
		$group = is_numeric($id) ? PlayerGroup::get(intval($id)) : PlayerGroup::getByName($id);

		if(!isset($group)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Группа §c$id §rне найдена");
			return true;
		}

		if($group->isRestricted() and $sender instanceof Player) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Вы не можете выдать группу §c$id");
			return true;
		}

		$name = $group->getName();
		$main = $this->getManager();

		if(empty($args)) {
			if(!$sender instanceof Player) {
				$sender->sendMessage(Manager::PREFIX_INFO. "Используйте: /group set <группа> <игрок> [кол-во_дн]");
				return true;
			}

			$main->setPlayerGroup($group, $sender->getLowerCaseName());

			$sender->sendMessage(Manager::PREFIX_INFO. "Ваша группа изменена. Вы получили группу §e$name §rна неограниченный срок");
			return true;
		}

		if(!$sender->hasPermission(self::PERMISSION_PLAYER)) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Недостаточно прав");
			return true;
		}

		$nick = strtolower(array_shift($args));

		if(strlen($nick) > 16) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Никнейм не является действительным");
			return true;
		}

		if(strlen($nick) < 3) {
			$player = $main->getServer()->getPlayer($nick);

			if(!isset($player)) {
				$sender->sendMessage(Manager::PREFIX_ERROR. "Указанный игрок не найден. Введите никнейм полностью");
				return true;
			}

			$nick = $player->getLowerCaseName();
		}

		if(empty($args)) {
			$main->setPlayerGroup($group, $nick);

			$sender->sendMessage(Manager::PREFIX_INFO. "§e$nick §rполучил группу §e$name §rна неограниченный срок");
			return true;
		}

		if($group->isDefault()) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Группа по-умолчанию не может быть временной");
			return true;
		}

		$period = array_shift($args);

		if(!is_numeric($period) or $period <= PlayerGroup::TIMESTAMP_UNDEFINED) {
			$sender->sendMessage(Manager::PREFIX_ERROR. "Период не является действительным");
			return true;
		}

		$period = intval($period);
		$group  = PlayerGroup::fromObject($group, $nick)->setExpireTimestamp(time() + 60 * 60 * 24 * $period);
		$expire = Declinator::formDeclension($period, 'день', 'дня', 'дней');

		$main->setPlayerGroup($group);

		$sender->sendMessage(Manager::PREFIX_INFO. "§e$nick §rполучил группу §e$name §rна §e$expire");
		return true;
	}
}