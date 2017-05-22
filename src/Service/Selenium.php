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
use Symfony\Component\Yaml\Yaml;

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

		$testDir = 'tests';
		$this->injectTests($testDir, $dockerPath . '/tests');

		$behat = array_merge_recursive(
			$this->getOriginalBehatConfiguration($testDir),
			$this->getAdditionalBehatConfiguration()
		);
		file_put_contents("$dockerPath/tests/behat.yml", Yaml::dump($behat, 4,2));
	}

	/**
	 * Inject the tests
	 *
	 * @param $path
	 * @param $dockerPath
	 *
	 * @return Template
	 */
	protected function injectTests($path, $dockerPath)
	{
		$template = new Template($path);
		$template->write("$dockerPath");
	}

	/**
	 * @param $testDir
	 *
	 * @return array|mixed
	 */
	protected function getOriginalBehatConfiguration($testDir)
	{
		$original = [];
		if (file_exists($testDir . '/behat.yml'))
		{
			$original = Yaml::parse(file_get_contents($testDir . '/behat.yml'));
		}

		return $original;
	}

	/**
	 * @return bool|mixed|string
	 */
	protected function getAdditionalBehatConfiguration()
	{
		$additions = file_get_contents(__DIR__ . '/template/selenium/behat.yml');
		$additions = str_replace('${domain}', $this->version, $additions);
		$additions = Yaml::parse($additions);

		return $additions;
	}
}
