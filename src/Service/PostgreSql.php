<?php
/**
 * Part of the Joomla Testing Framework Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Testing\Service;

/**
 * Class PostgreSql
 *
 * @package  Joomla\Testing
 * @since    __DEPLOY_VERSION__
 */
class PostgreSql extends AbstractService
{
	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		throw new \RuntimeException('Method not implemented');
	}
}
