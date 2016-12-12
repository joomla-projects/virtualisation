<?php
/**
 * Part of the Joomla Virtualisation Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Virtualisation;

use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Service\MySql;
use Joomla\Virtualisation\Service\PostgreSql;
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class PostgreSqlTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration(new ServerConfig(__DIR__ . '/../fixtures/j3x.xml'));
		$this->service = $serverFactory->getDatabaseServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'pgsql-latest' => [
				'image' => 'postgres:latest',
				'volumes' => [
					'tests/tmp/pgsql-latest:/docker-entrypoint-initdb.d',
				],
				'environment' => [
					'POSTGRESQL_DB' => 'joomla_test',
					'POSTGRESQL_USER' => 'sqladmin',
					'POSTGRESQL_PASSWORD' => 'sqladmin',
				],
			],
		];

		$result = $this->service->getSetup();

		$this->assertEquals($expected, $result);
	}

	public function testFilesystemIsSetUp()
	{
		$this->service->prepare();
		/*
		 * @todo Check files
		 */
	}
}
