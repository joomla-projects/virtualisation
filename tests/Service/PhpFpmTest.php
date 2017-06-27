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

class PhpFpmTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration((new ServerConfig)->loadFromFile(__DIR__ . '/../fixtures/j3x.xml'));
		$this->service = $serverFactory->getPhpServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'php-7.1' => [
				'build'   => 'docker/php-7.1',
				'volumes' => [
					getcwd() . '/vendor:/usr/local/lib/php/vendor',
					getcwd() . '/dockyard/docker/nginx-1.9/html/j3-postgresql-19.dev:/var/www/html/j3-postgresql-19.dev',
				],
				'links'   => [
					'postgresql-latest',
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
			'dockyard/docker/php-7.1/Dockerfile',
			[
				'php:7.1-fpm',
				'pecl install xdebug-2.5.3',
			]
		);
	}
}
