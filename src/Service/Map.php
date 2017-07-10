<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

/**
 * Class Map
 *
 * Provides a mapping of drivers to engines
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
abstract class Map
{
	private static $typeMapping = [
		'pgsql'      => 'postgresql',
		'pdopgsql'   => 'postgresql',
		'mysqli'     => 'mysql',
		'pdomysql'   => 'mysql',
	];

	private static $classMapping = [
		'apache'     => '\\Joomla\\Virtualisation\\Service\\Apache',
		'nginx'      => '\\Joomla\\Virtualisation\\Service\\Nginx',
		'postgresql' => '\\Joomla\\Virtualisation\\Service\\PostgreSql',
		'mysql'      => '\\Joomla\\Virtualisation\\Service\\MySql',
		'php'        => '\\Joomla\\Virtualisation\\Service\\PhpFpm',
		'joomla'     => '\\Joomla\\Virtualisation\\Service\\Joomla',
		'proxy'      => '\\Joomla\\Virtualisation\\Service\\Proxy',
		'selenium'   => '\\Joomla\\Virtualisation\\Service\\Selenium',
		'selenium-container'   => '\\Joomla\\Virtualisation\\Service\\SeleniumContainer',
	];

	public static function getClass($type)
	{
		$type = self::getType($type);

		if (!isset(self::$classMapping[$type]))
		{
			throw new \RuntimeException("Unknown service type $type");
		}

		return self::$classMapping[$type];
	}

	public static function getType($type)
	{
		$type = strtolower($type);

		if (isset(self::$typeMapping[$type]))
		{
			$type = self::$typeMapping[$type];
		}

		return $type;
	}
}
