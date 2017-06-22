<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation;

use Greencape\PhpVersions;
use Joomla\Virtualisation\Service\Service;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DockerComposeGenerator
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class DockerComposeGenerator
{
//	use \Joomla\Testing\Robo\Tasks\loadTasks;
//	use \Robo\Task\Composer\loadTasks;
	/**
	 * @var  string
	 */
	protected $path;
	private $servers = [];

	public function __construct($path)
	{
		$this->path = $path;
		$this->refreshVersionCache();
	}

	private function refreshVersionCache()
	{
		$versions = new PhpVersions(null, PhpVersions::VERBOSITY_SILENT | PhpVersions::CACHE_DISABLED);
	}

	public function write($filename)
	{
		$xmlFiles = array_diff(scandir($this->path), ['.', '..', 'database.xml', 'default.xml', 'network.xml', 'selenium.xml']);

		$this->getServices($xmlFiles);

		$this->addSelenium($this->path . '/selenium.xml');

		$services = $this->getCombinedSetup($this->servers);

		$network = $this->getNetworkInfo($this->path . '/network.xml');

		$compose = array(
			'version' => '3',
			'networks' => array($network['name'] => array('driver' => $network['driver'])),
			'services' => $services,
		);

		file_put_contents($filename, Yaml::dump($compose, 4, 2));

		return $this->getHostInfo($services);
	}

	public function getNetworkInfo($networkXmlPath)
	{
		$network = [];

		$xml = simplexml_load_file($networkXmlPath);

		foreach ($xml->attributes() as $key => $attribute)
		{
			$network[$key] = (string) $attribute;
		}
		return $network;
	}

	/**
	 * @param Service[] $servers
	 *
	 * @return array
	 */
	protected function getCombinedSetup($servers)
	{
		$fixName  = function ($name)
		{
			return strtolower(str_replace(['-', '.'], ['v', 'p'], $name));
		};
		$contents = [];

		foreach ($servers as $server)
		{
			$server->prepare();

			foreach ($server->getSetup() as $name => $setup)
			{
				if (isset($setup['links']))
				{
					$setup['links'] = array_map($fixName, $setup['links']);
				}

				$contents[$fixName($name)] = $setup;
			}
		}

		return array_filter($contents);
	}

	/**
	 * @param $xmlFiles
	 *
	 * @return Service[]
	 */
	protected function getServices($xmlFiles)
	{
		$this->servers = [];
		$factory       = new ServiceFactory();

		foreach ($xmlFiles as $file)
		{
			$config = new ServerConfig($this->path . '/' . $file);
			$factory->setConfiguration($config);

			$this->registerServer($factory->getProxyServer());
			$this->registerServer($factory->getWebserver());
			$this->registerServer($factory->getDatabaseServer());
			$this->registerServer($factory->getPhpServer());
			$this->registerServer($factory->getApplication());
		}

		return $this->servers;
	}

	protected function addSelenium($seleniumXmlPath){
		$factory	= new ServiceFactory();
		$config 	= new ServerConfig($seleniumXmlPath);

//		$dockerPath = $config->get('host.dockyard') . '/selenium/' . $config->get('selenium.version');
//
//		$taskCMSSetup = $this->taskCMSSetup()
//			->setBaseTestsPath($dockerPath)
//			->setCmsRepository($config->get('weblinks.repoOwner') . '/' . $config->get('weblinks.repoName'))
//			->setCmsPath('extension');
//
//		if (!empty($config->get('weblinks.repoBranch')))
//		{
//			$taskCMSSetup->setCmsBranch($config->get('weblinks.repoBranch'));
//		}
//
//		$taskCMSSetup->cloneCMSRepository()
//			->setupCMSPath()
//			->run()
//			->stopOnFail();
//
//		// Installs composer dependencies prior to tests
//		$this->taskComposerInstall(__DIR__ . '/composer.phar')
//			->option('working-dir', $dockerPath . '/extension/tests')
//			->preferDist()
//			->run();
		$no = $config->get("selenium.no");
		for ($i=0; $i<$no; $i++){
			$config->setSeleniumNo($i);
			$factory->setConfiguration($config);
			$this->registerServer($factory->getSeleniumServer());
		}
	}

	/**
	 * @param $server
	 */
	protected function registerServer($server)
	{
		if (empty($server))
		{
			return;
		}
		$this->servers[spl_object_hash($server)] = $server;
	}

	/**
	 * @param $service
	 *
	 * @return string
	 */
	protected function getHtmlPath($service)
	{
		foreach ($service['volumes'] as $volume)
		{
			$parts = explode('/html', $volume);
			if (count($parts) > 1)
			{
				return $parts[0] . '/html';
			}
		}

		return '';
	}

	/**
	 * @param $services
	 *
	 * @return array
	 */
	protected function getHostInfo($services)
	{
		$info = [];
		foreach ($services as $name => $service)
		{
			if (isset($service['environment']['VIRTUAL_HOST']))
			{
				$path = $this->getHtmlPath($service);

				foreach (explode(',', $service['environment']['VIRTUAL_HOST']) as $virtualHost)
				{
					$info[$virtualHost] = [
						'name'   => $name,
						'url'    => $virtualHost,
						'volume' => $path . '/' . $virtualHost,
					];
				}
			}
		}

		return $info;
	}
}
