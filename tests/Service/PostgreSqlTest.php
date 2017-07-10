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

class PostgreSqlTest extends ServiceTestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration((new ServerConfig)->loadFromFile(__DIR__ . '/../fixtures/j3x.xml'));
		$this->service = $serverFactory->getDatabaseServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'postgresql-latest' => [
				'image'       => 'postgres:latest',
				'volumes'     => [
					getcwd() . '/dockyard/postgresql-latest:/docker-entrypoint-initdb.d',
				],
				'environment' => [
					'POSTGRES_DB'       => 'joomla_test',
					'POSTGRES_USER'     => 'sqladmin',
					'POSTGRES_PASSWORD' => 'sqladmin',
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
			'dockyard/postgresql-latest/joomla3.sql',
			[
				'CREATE DATABASE "joomla3" OWNER "sqladmin"',
			]
		);
	}
}
