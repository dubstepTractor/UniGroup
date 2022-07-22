<?php declare(strict_types = 1); namespace uni\group\util;

use function intval;
use function implode;

abstract class Declinator {

	/**
	 * @param  int    $time
	 * @param  string $glue
	 *
	 * @return string
	 */
	public static function formDate(int $time, string $glue = ', '): string {
		$part = [];

		if($year = intval($time / 31536000)) {
			$part[] = self::formDeclension($year, 'год', 'года', 'лет');
			$time  -= $year * 31536000;
		}

		if($month = intval($time / 2592000)) {
			$part[] = self::formDeclension($month, 'месяц', 'месяца', 'месяцев');
			$time  -= $month * 2592000;
		}

		if($week = intval($time / 604800)) {
			$part[] = self::formDeclension($week, 'неделя', 'недели', 'недель');
			$time  -= $week * 604800;
		}

		if($day = intval($time / 86400)) {
			$part[] = self::formDeclension($day, 'день', 'дня', 'дней');
			$time  -= $day * 86400;
		}

		if($hour = intval($time / 3600)) {
			$part[] = self::formDeclension($hour, 'час', 'часа', 'часов');
			$time  -= $hour * 3600;
		}

		if($minute = intval($time / 60)) {
			$part[] = self::formDeclension($minute, 'минута', 'минуты', 'минут');
			$time  -= $minute * 60;
		}

		return implode($glue, $part);
	}

	/**
	 * @param  int    $num
	 * @param  string $first
	 * @param  string $second
	 * @param  string $third
	 *
	 * @return string
	 */
	public static function formDeclension(int $num, string $first, string $second, string $third): string {
		return "$num ". self::decline($num, $first, $second, $third);
	}

	/**
	 * @param  int    $num
	 * @param  string $first
	 * @param  string $second
	 * @param  string $third
	 *
	 * @return string
	 */
	public static function decline(int $num, string $first, string $second, string $third): string {
		$num = $num % 100;

		if($num > 10 and $num < 20) {
			return $third;
		}
		
		$num = $num % 10;

		switch($num) {
			case 1: return $first;
			case 2:
			case 3:
			case 4: return $second;
		}

		return $third;
	}
}