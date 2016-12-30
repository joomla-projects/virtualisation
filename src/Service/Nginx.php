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
 * Class Nginx
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Nginx extends AbstractService
{
	protected $setup = [];

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$name               = 'nginx-' . $this->version;
		$dockerPath         = 'docker/' . $name;
		$this->setup[$name] = [
			'image'   => 'nginx:' . $this->version,
			'volumes' => [
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
				"$dockerPath/conf:/etc/nginx/conf.d",
			],
			'links'   => [],
		];

		foreach ($this->configs as $config)
		{
			$version                       = $config->getVersion('php');
			$this->setup[$name]['links'][] = "php-$version";

			$domain                          = $config->getDomain();
			$this->setup[$name]['volumes'][] = "docker/$name/html/$domain:/var/www/html/$domain";
		}

		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$name       = 'nginx-' . $this->version;
		$dockerPath = $this->dockyard . '/docker/' . $name;

		$template = new Template(__DIR__ . '/template/nginx');

		foreach ($this->configs as $config)
		{
			$domain = $config->getDomain();
			$template->setVariables(
				[
					'domain'   => $domain,
					'name'     => $name,
					'php.host' => 'php-' . $config->getVersion('php'),
					'php.port' => '9000',
				]
			);
			$template->write("$dockerPath/conf/$domain");
		}
	}
}
