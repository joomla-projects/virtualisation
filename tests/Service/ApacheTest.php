<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation\Service;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class ApacheTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration((new ServerConfig)->loadFromFile(__DIR__ . '/../fixtures/j25.xml'));
		$this->service = $serverFactory->getWebserver();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'apache-5.4' => [
				'build'       => 'docker/apache-5.4',
				'volumes'     => [
					getcwd() . '/dockyard/docker/apache-5.4/conf:/etc/apache2/sites-enabled',
					getcwd() . '/dockyard/docker/apache-5.4/html:/var/www/html',
					getcwd() . '/vendor:/usr/local/lib/php/vendor',
				],
				'links'       => [
					'mysql-latest',
				],
				'environment' => [
					'VIRTUAL_HOST' => 'j25-mysqli.dev',
				],
				'networks' => [
					'joomla',
				],
			],
		];

		$result = $this->service->getSetup();

		$this->assertEquals($expected, $result);
	}

	public function testFilesystemIsSetUp()
	{
		$this->service->prepare();

		$this->assertFileContains(
			'dockyard/docker/apache-5.4/Dockerfile',
			[
				'php:5.4-apache',
				'pecl install xdebug-2.4.1',
			]
		);

		$this->assertFileContains(
			'dockyard/docker/apache-5.4/conf/j25-mysqli.dev.conf',
			[
				'ServerName      j25-mysqli.dev',
				'DocumentRoot    /var/www/html/j25-mysqli.dev',
			]
		);
	}
}
