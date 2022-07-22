<?php declare(strict_types = 1); namespace uni\group\data\configuration;

use uni\group\data\configuration\Configuration;

class BaseConfiguration extends Configuration {

	protected const FILENAME = 'config.yml';

	private const INDEX_NOTIFICATION_GROUP_EXPIRE = 'notification_group_expire';

	/**
	 *                    __ _                      _   _
	 *   ___  ___  _ __ _/ _(_) __ _ _   _ _ ____ _| |_(_) ___  _ __
	 *  / __\/ _ \| '_ \   _| |/ _' | | | | '_/ _' |  _| |/ _ \| '_ \
	 * | (__| (_) | | | | | | | (_) | |_| | || (_) | |_| | (_) | | | |
	 *  \___/\___/|_| |_|_| |_|\__  |\___/|_| \__,_|\__|_|\___/|_| |_|
	 *                         /___/
	 *
	 * @return int
	 */
	public function getNotificationGroupExpire(): int {
		return $this->getValue(self::INDEX_NOTIFICATION_GROUP_EXPIRE);
	}
}