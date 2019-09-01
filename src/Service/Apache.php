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
 * Class Apache
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Apache extends PhpBase
{
	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$config = $this->configs[0];

		$name               = 'apache-' . $this->version;
		$dockerPath         = $this->dockyard . '/docker/' . $name;
		$this->setup[$name] = [
			'build'       => 'docker/' . $name,
			'volumes'     => [
				getcwd() . "/$dockerPath/conf:/etc/apache2/sites-enabled",
				getcwd() . "/$dockerPath/html:/var/www/html",
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
			],
			'links'       => [],
			'environment' => [
				'VIRTUAL_HOST' => [],
			],
		];

		$driver                                              = Map::getType($config->get('database.driver'));
		$version                                             = $config->getVersion('database');
		$this->setup[$name]['links'][]                       = "$driver-$version";
		$this->setup[$name]['environment']['VIRTUAL_HOST'][] = $config->getDomain();
		$this->setup[$name]['networks'][] 				     = $this->network;

		$this->setup[$name]['links']                       = array_unique($this->setup[$name]['links']);
		$this->setup[$name]['environment']['VIRTUAL_HOST'] = implode(',', $this->setup[$name]['environment']['VIRTUAL_HOST']);

//		return array_merge(
//			$this->setup,
//			parent::getSetup()
//		);
		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$dockerPath = $this->dockyard . '/docker/apache-' . $this->version;

		$this->createDockerfile($dockerPath, __DIR__ . '/docker/apache');
		$this->createVhosts($dockerPath);

//		parent::prepare();
	}

	/**
	 * @param $dockerPath
	 */
	protected function createVhosts($dockerPath)
	{
		$vhostTemplate = new Template(__DIR__ . '/template/apache/vhost.conf');

		foreach ($this->configs as $config)
		{
			$domain = $config->getDomain();
			$vhostTemplate->setVariables(
				[
					'domain' => $domain,
				]
			);
			$vhostTemplate->write("$dockerPath/conf/$domain.conf");
		}
	}

}
