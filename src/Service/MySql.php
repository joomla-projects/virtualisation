<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\Template;

/**
 * Class MySql
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class MySql extends AbstractService
{
	protected $setup = [];

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$config             = reset($this->configs);
		$name               = 'mysql-' . $this->version;
		$dockerPath         = $this->dockyard . '/' . $name;
		$this->setup[$name] = [
			'image'       => 'mysql:' . $this->version,
			'volumes'     => [
				getcwd() . "/$dockerPath:/docker-entrypoint-initdb.d",
			],
			'environment' => [
				'MYSQL_DATABASE'      => $config->get('mysql.name'),
				'MYSQL_ROOT_PASSWORD' => $config->get('mysql.rootPassword'),
				'MYSQL_USER'          => $config->get('mysql.user'),
				'MYSQL_PASSWORD'      => $config->get('mysql.password'),
			],
		];

		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$template = new Template(__DIR__ . '/template/mysql/createdb.sql');

		foreach ($this->configs as $config)
		{
			$template->setVariables(
				[
					'database.name' => $config->get('database.name'),
					'mysql.user'    => $config->get('mysql.user'),
				]
			);
			$template->write("{$this->dockyard}/mysql-{$this->version}/" . $config->get('database.name') . '.sql');
		}
	}
}
