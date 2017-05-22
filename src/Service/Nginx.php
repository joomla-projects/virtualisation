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
		$dockerPath         = $this->dockyard . '/docker/' . $name;
		$this->setup[$name] = [
			'image'       => 'nginx:' . $this->version,
			'volumes'     => [
				getcwd() . '/vendor:/usr/local/lib/php/vendor',
				getcwd() . "/$dockerPath/conf:/etc/nginx/conf.d",
			],
			'links'       => [],
			'environment' => [
				'VIRTUAL_HOST' => [],
			],
		];

		foreach ($this->configs as $config)
		{
			$version                       = $config->getVersion('php');
			$this->setup[$name]['links'][] = $this->fixName("php-$version");

			$domain                                              = $config->getDomain();
			$this->setup[$name]['volumes'][]                     = getcwd() . "/$dockerPath/html/$domain:/var/www/html/$domain";
			$this->setup[$name]['environment']['VIRTUAL_HOST'][] = $domain;
		}

		$this->setup[$name]['links'] = array_unique($this->setup[$name]['links']);
		$this->setup[$name]['environment']['VIRTUAL_HOST'] = implode(',', $this->setup[$name]['environment']['VIRTUAL_HOST']);

		return array_merge(
			$this->setup,
			parent::getSetup()
		);
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$dockerPath = $this->dockyard . '/docker/' . 'nginx-' . $this->version;

		$this->createVhosts($dockerPath);

		parent::prepare();
	}

	/**
	 * @param $dockerPath
	 */
	protected function createVhosts($dockerPath)
	{
		$template = new Template(__DIR__ . '/template/nginx/vhost.conf');

		foreach ($this->configs as $config)
		{
			$domain = $config->getDomain();
			$template->setVariables(
				[
					'domain'   => $domain,
					'name'     => 'nginx-' . $this->version,
					'php.host' => $this->fixName('php-' . $config->getVersion('php')),
					'php.port' => '9000',
				]
			);
			$template->write("$dockerPath/conf/$domain.conf");
		}
	}
}
