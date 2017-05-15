<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Greencape\PhpVersions;
use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Template;

/**
 * Class PhpBase
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
abstract class PhpBase extends AbstractService
{
	protected $setup = [];

	/**
	 * PhpBase constructor.
	 *
	 * @param              $version
	 * @param ServerConfig $config
	 */
	public function __construct($version, ServerConfig $config)
	{
		parent::__construct($version, $config);

		// The Apache service uses the PHP version!
		$this->version = $config->getVersion('php');
	}

	/**
	 * @param $dockerPath
	 * @param $template
	 */
	protected function createDockerfile($dockerPath, $template)
	{
		$phpVersions = new PhpVersions();
		$phpInfo     = $phpVersions->getInfo($this->version);

		$dockerTemplate = new Template($template);
		$dockerTemplate->setVariables(
			[
				'php.version'    => preg_replace('~^(\d+\.\d+).*$~', '\\1', $phpInfo['version']),
				'php.major'      => preg_replace('~^(\d+).*$~', '\\1', $phpInfo['version']),
				'xdebug.version' => $phpInfo['xdebug']['version'],
			]
		);
		$dockerTemplate->write($dockerPath);
	}
}
