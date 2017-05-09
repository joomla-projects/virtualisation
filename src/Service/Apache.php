<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Template;

/**
 * Class Apache
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Apache extends PhpBase
{
	public function __construct($version, ServerConfig $config)
	{
		parent::__construct($version, $config);

		// The Apache service uses the PHP version!
		$this->version = $config->getVersion('php');
	}

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'apache-' . $this->version;
		$dockerPath         = 'docker/' . $name;
		$this->setup[$name] = [
			'build'       => $dockerPath,
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

		foreach ($this->configs as $config)
		{
			$driver                                              = Map::getType($config->get('database.driver'));
			$version                                             = $config->getVersion('database');
			$this->setup[$name]['links'][]                       = "$driver-$version";
			$this->setup[$name]['environment']['VIRTUAL_HOST'][] = $config->getDomain();
		}

		$this->setup[$name]['links']                       = array_unique($this->setup[$name]['links']);
		$this->setup[$name]['environment']['VIRTUAL_HOST'] = implode(',', $this->setup[$name]['environment']['VIRTUAL_HOST']);

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
			$vhostTemplate->write("$dockerPath/conf/$domain");
		}
	}

}
