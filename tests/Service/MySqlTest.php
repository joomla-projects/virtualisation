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

class MySqlTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration((new ServerConfig)->loadFromFile(__DIR__ . '/../fixtures/j25.xml'));
		$this->service = $serverFactory->getDatabaseServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'mysql-latest' => [
				'image'       => 'mysql:latest',
				'volumes'     => [
					getcwd() . '/dockyard/mysql-latest:/docker-entrypoint-initdb.d',
				],
				'environment' => [
					'MYSQL_DATABASE'      => 'joomla_test',
					'MYSQL_ROOT_PASSWORD' => 'root',
					'MYSQL_USER'          => 'sqladmin',
					'MYSQL_PASSWORD'      => 'sqladmin',
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
			'dockyard/mysql-latest/joomla25.sql',
			[
				'CREATE DATABASE IF NOT EXISTS `joomla25`',
			]
		);
	}
}
