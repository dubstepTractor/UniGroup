<?php declare(strict_types = 1); namespace uni\group\data\provider\mysql;

use uni\group\util\SxQL;
use uni\group\data\PlayerGroup;

use mysqli_stmt;

use function strtolower;
use function str_replace;
use function mysqli_fetch_assoc;
use function mysqli_free_result;
use function mysqli_num_rows;

/**
 * @todo remove hardcoded values
 * @todo reformat all mysql-related stuff
 */
class PlayerGroupBridge extends SxQL {

	private const QUERY_SELECT = "SELECT * FROM `unigroup_group` WHERE `nickname` = ':nickname' LIMIT 1";
	private const QUERY_UPDATE = "UPDATE `unigroup_group` SET `group_id` = ':group_id', `hided` = ':hided', `expire_timestamp` = ':expire_timestamp' WHERE `nickname` = ':nickname'";
	private const QUERY_INSERT = "INSERT INTO `unigroup_group` (`nickname`, `group_id`, `hided`, `expire_timestamp`) VALUES (':nickname', ':group_id', ':hided', ':expire_timestamp')";
	private const QUERY_DELETE = "DELETE FROM `unigroup_group` WHERE `nickname` = ':nickname'";
	private const QUERY_TABLE_CREATE = "CREATE TABLE IF NOT EXISTS `unigroup_group` (`id` INT(16) PRIMARY KEY NOT NULL AUTO_INCREMENT, `nickname` VARCHAR(16) UNIQUE KEY NOT NULL, `group_id` SMALLINT(4) NOT NULL, `hided` BOOLEAN NOT NULL, `expire_timestamp` INT(32) NOT NULL)";
	private const QUERY_TABLE_CLEAR  = "DELETE FROM `unigroup_group`";

	/**
	 *                                    _     _         _     _
	 *   __ _  ___  ___  ___  _   _ _ __ | |__ | |__  _ _(_) __| | __ _  ___
	 *  / _' |/ __\/ __\/ _ \| | | | '_ \|  _/ | '_ \| '_| |/ _' |/ _' |/ _ \
	 * | (_) | (__| (__| (_) | |_| | | | | |_  | (_) | | | | (_) | (_) |  __/
	 *  \__,_|\___/\___/\___/ \___/|_| |_|\__\ |_,__/|_| |_|\__,_|\__, |\___/
	 *                                                            /___/
	 *
	 * @param  string $nick
	 *
	 * @return PlayerGroup|null
	 */
	public static function selectPlayerGroup(string $nick): ?PlayerGroup {
		$nick = strtolower($nick);
		$sql  = self::buildPlayerGroupQuery(self::QUERY_SELECT, $nick);

		$result = self::query($sql);
		$data   = mysqli_fetch_assoc($result);

		mysqli_free_result($result);

		if(!isset($data)) {
			return null;
		}

		return PlayerGroup::fromData($data);
	}

	/**
	 * @param  PlayerGroup $group
	 *
	 * @return bool
	 */
	public static function updatePlayerGroup(PlayerGroup $group): bool {
		$nick = $group->getNickname();
		$sql  = self::isPlayerGroupExists($nick) ? self::QUERY_UPDATE : self::QUERY_INSERT;
		$sql  = self::buildPlayerGroupQuery($sql, $nick, $group);

		return self::query($sql);
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public static function deletePlayerGroup(string $nick): bool {
		$nick = strtolower($nick);
		$sql  = self::buildPlayerGroupQuery(self::QUERY_DELETE, $nick);

		return self::query($sql);
	}

	/**
	 * @param  string $nick
	 *
	 * @return bool
	 */
	public static function isPlayerGroupExists(string $nick): bool {
		$nick = strtolower($nick);

		$sql    = self::buildPlayerGroupQuery(self::QUERY_SELECT, $nick);
		$result = self::query($sql);
		$count  = mysqli_num_rows($result);

		mysqli_free_result($result);

		return $count > 0;
	}

	/**
	 * @return bool
	 */
	public static function createPlayerGroupTable(): bool {
		return self::query(self::QUERY_TABLE_CREATE);
	}

	/**
	 * @return bool
	 */
	public static function clearPlayerGroupTable(): bool {
		return self::query(self::QUERY_TABLE_CLEAR);
	}

	/**
	 * @todo optimize ASAP
	 *
	 * @param  string      $sql
	 * @param  string      $nick
	 * @param  PlayerGroup $group
	 *
	 * @return string
	 */
	private static function buildPlayerGroupQuery(string $sql, string $nick, PlayerGroup $group = null): string {
		$sql = str_replace(':nickname', strtolower($nick), $sql);

		if(isset($group)) {
			foreach($group->toDataEntry() as $index => $value) {
				$sql = str_replace(":$index", $value, $sql);
			}
		}

		return $sql;
	}
}