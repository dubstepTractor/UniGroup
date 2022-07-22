<?php declare(strict_types = 1); namespace uni\group\data\configuration;

use uni\group\Manager;

use pocketmine\utils\Config;

use function mkdir;
use function is_dir;
use function strtolower;

/**
 * @todo check alternatives
 */
abstract class Configuration {

	protected const FILENAME = '';

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Manager $main
	 */
	public function __construct(Manager $main) {
		$location = $main->getDataFolder();

		if(!is_dir($location)) {
			mkdir($location, 0777, true);
		}

		$main->saveResource(static::FILENAME);

		$this->config = new Config($location. static::FILENAME);
	}

	/**
	 *                    __ _                      _   _
	 *   ___  ___  _ __ _/ _(_) __ _ _   _ _ ____ _| |_(_) ___  _ __
	 *  / __\/ _ \| '_ \   _| |/ _' | | | | '_/ _' |  _| |/ _ \| '_ \
	 * | (__| (_) | | | | | | | (_) | |_| | || (_) | |_| | (_) | | | |
	 *  \___/\___/|_| |_|_| |_|\__  |\___/|_| \__,_|\__|_|\___/|_| |_|
	 *                         /___/
	 *
	 * @param  string $index
	 *
	 * @return mixed
	 */
	protected function getValue(string $index) {
		$index  = strtolower($index);
		$result = $this->getConfig()->get($index, null);

		if(!isset($result)) {
			throw new Exception("Index $index does not exists!");
		}

		return $result;
	}

	/**
	 * @return Config
	 */
	private function getConfig(): Config {
		return $this->config;
	}
}