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
	private static $typeMapping  = [
		'apache'     => 'apache',
		'nginx'      => 'nginx',
		'postgresql' => 'postgresql',
		'pgsql'      => 'postgresql',
		'pdopgsql'   => 'postgresql',
		'mysql'      => 'mysql',
		'mysqli'     => 'mysql',
		'pdomysql'   => 'mysql',
		'php'        => 'php',
	];

	private static $classMapping = [
		'apache'     => '\\Joomla\\Virtualisation\\Service\\Apache',
		'nginx'      => '\\Joomla\\Virtualisation\\Service\\Nginx',
		'postgresql' => '\\Joomla\\Virtualisation\\Service\\PostgreSql',
		'mysql'      => '\\Joomla\\Virtualisation\\Service\\MySql',
		'php'        => '\\Joomla\\Virtualisation\\Service\\PhpFpm',
	];

	public static function getClass($type)
	{
		return self::$classMapping[self::getType($type)];
	}

	public static function getType($type)
	{
		$type = strtolower($type);

		if (!isset(self::$typeMapping[$type])) {
			throw new \RuntimeException("Unknown service type $type");
		}

		return self::$typeMapping[$type];
	}
}
