<?php
/**
 * Part of the Joomla Testing Framework Test Suite
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Testing;

use Joomla\Testing\ServerConfig;

class ServerConfigTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  ServerConfig  The object under test
	 */
	protected $config;

	public function setUp()
	{
		$this->config = new ServerConfig(__DIR__ . '/fixtures/j3x.xml');
	}

	public function testConfigurationIsRead()
	{
		$this->assertEquals('postgresql', $this->config->get('database.driver'));
	}

	public function testDefaultIsIncorporated()
	{
		$this->assertEquals(1, $this->config->get('debug.system'));
	}

	public function testDatabaseCredentialsAreRead()
	{
		$this->assertEquals('sqladmin', $this->config->get('mysql.user'));
	}
}
