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
 * Class Selenium
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Selenium extends AbstractService
{
	public function __construct(ServerConfig $config)
	{
		parent::__construct($config->getDomain(), $config);
	}

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		return [];
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$dockerPath = $this->dockyard . '/selenium/' . $this->version;

		`cp -R "tests" "$dockerPath/tests"`;

		$template = new Template(__DIR__ . '/template/selenium/behat.yml');
		$template->setVariables(
			[
				'domain' => $this->version,
			]
		);
		$template->write("$dockerPath/behat.yml");
	}
}
