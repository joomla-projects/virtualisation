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
use Joomla\Virtualisation\Service\Service;
use Joomla\Virtualisation\ServiceFactory;

class MySqlTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  Service  The object under test
	 */
	protected $service;

	public function setUp()
	{
		$serverFactory = new ServiceFactory();
		$serverFactory->setConfiguration(new ServerConfig(__DIR__ . '/../fixtures/j25.xml'));
		$this->service = $serverFactory->getDatabaseServer();
	}

	public function testServiceSetupIsGeneratedAsAnArraySuitableForDockerCompose()
	{
		$expected = [
			'mysql-latest' => [
				'image'       => 'greencape/mariadb:latest',
				'volumes'     => [
					'tests/tmp/mysql-latest:/import.d',
				],
				'environment' => [
					'MYSQL_DATABASE'      => 'joomla_test',
					'MYSQL_ROOT_PASSWORD' => 'root',
					'MYSQL_USER'          => 'sqladmin',
					'MYSQL_PASSWORD'      => 'sqladmin',
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
