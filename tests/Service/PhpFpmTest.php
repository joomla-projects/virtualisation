<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class PhpFpmTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration(new ServerConfig(__DIR__ . '/../fixtures/j25.xml'));
		$this->service = $serverFactory->getPhpServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'php-5.2' => [
				'build'   => 'tests/tmp/docker/php-5.2',
				'volumes' => [
					getcwd() . '/vendor:/usr/local/lib/php/vendor',
					'tests/tmp/docker/apache-5.2/html/j25-mysqli.dev:/var/www/html/j25-mysqli.dev',
				],
				'links'   => [
					'mysql-latest',
				],
			],
		];

		$result = $this->service->getSetup();

		$this->assertEquals($expected, $result);
	}

	public function testFilesystemIsSetUp()
	{
		$this->service->prepare();

		$filename = 'tests/tmp/docker/php-5.2/Dockerfile';
		$this->assertFileExists($filename);

		$content = file_get_contents($filename);
		$this->assertContains('FROM php:5.2-fpm', $content);
		$this->assertContains('ENV XDEBUG_VERSION 2.2.7', $content);
	}
}
