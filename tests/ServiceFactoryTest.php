<?php
/**
 * Part of the Joomla Testing Framework Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Testing;

use Joomla\Testing\ServerConfig;
use Joomla\Testing\Service\Nginx;
use Joomla\Testing\Service\PhpFpm;
use Joomla\Testing\Service\PostgreSql;
use Joomla\Testing\ServiceFactory;

class ServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  ServiceFactory  The object under test
	 */
	protected $factory;

	public function setUp()
	{
		$this->factory = new ServiceFactory();
		$this->factory->setConfiguration(new ServerConfig(__DIR__ . '/fixtures/j3x.xml'));
	}

	public function testServiceFactoryReturnsTheConfiguredWebserver()
	{
		$this->assertInstanceOf(Nginx::class, $this->factory->getWebserver());
	}

	public function testServiceFactoryReturnsTheConfiguredDatabaseServer()
	{
		$this->assertInstanceOf(PostgreSql::class, $this->factory->getDatabaseServer());
	}

	public function testServiceFactoryReturnsTheConfiguredPhpServer()
	{
		$this->assertInstanceOf(PhpFpm::class, $this->factory->getPhpServer());
	}

	public function testServiceFactoryReturnsTheSameInstanceForIdenticalVersions()
	{
		$this->assertSame($this->factory->getWebserver(), $this->factory->getWebserver());
	}

	public function testServiceFactoryReturnsDifferentInstancesForDifferentVersions()
	{
		$webserver1 = $this->factory->getWebserver();

		$this->factory->setConfiguration(new ServerConfig(__DIR__ . '/fixtures/j3y.xml'));
		$webserver2 = $this->factory->getWebserver();

		$this->assertNotSame($webserver1, $webserver2);
	}
}
