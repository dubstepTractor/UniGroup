<?php declare(strict_types = 1); namespace uni\group\data\configuration;

use uni\group\Manager;

use uni\group\data\Group;
use uni\group\data\configuration\Configuration;

class GroupConfiguration extends Configuration {

	protected const FILENAME = 'group_list.yml';

	private const INDEX_LIST = 'list';

	/**
	 *                    __ _                      _   _
	 *   ___  ___  _ __ _/ _(_) __ _ _   _ _ ____ _| |_(_) ___  _ __
	 *  / __\/ _ \| '_ \   _| |/ _' | | | | '_/ _' |  _| |/ _ \| '_ \
	 * | (__| (_) | | | | | | | (_) | |_| | || (_) | |_| | (_) | | | |
	 *  \___/\___/|_| |_|_| |_|\__  |\___/|_| \__,_|\__|_|\___/|_| |_|
	 *                         /___/
	 *
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		parent::__construct($main);

		Group::initialize($this->getList());
	}

	/**
	 * @return mixed[][]
	 */
	public function getList(): array {
		return $this->getValue(self::INDEX_LIST);
	}
}