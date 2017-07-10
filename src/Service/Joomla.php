<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Alchemy\Zippy\Zippy;
use Joomla\Virtualisation\ServerConfig;
use Joomla\Virtualisation\Template;

/**
 * Class Joomla
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class Joomla extends AbstractService
{
	protected $setup = [];

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$name     = 'joomla-' . $this->version;
		$htdocs   = $this->dockyard . '/' . $name;
		$template = new Template($this->prepareSource($htdocs));

//		$userData = substr($name, 0, 8);
//		if ($name == "joomla-staging")
//		{
			$userData = "joomla-3";
//		}

		foreach ($this->configs as $config)
		{
			$domain      = $config->getDomain();
			$destination = $this->getDockerPath($config) . "/html/$domain";
			$this->clearDirectory($destination);

			$template->setVariables([]);
			$template->write($destination);

			$this->appendSql('joomla.sql', $destination, $config);

			$sampleData = $config->get('joomla.sampleData');

			if (!empty($sampleData))
			{
				$this->appendSql('sample_' . $sampleData . '.sql', $destination, $config);
			}

			$this->appendUserSql($userData, $config);

			$this->updateConfiguration($config, $destination);
			$this->removeDirectory($destination . '/installation');
		}

		$this->removeDirectory($htdocs);
	}

	/**
	 * @param $htdocs
	 *
	 * @return string
	 */
	protected function prepareSource($htdocs)
	{
		$versionFile = $this->dockyard . '/joomla-versions.json';
		$cachePath   = __DIR__ . '/cache';
		$this->createDirectory($cachePath);

		$this->getVersions($versionFile);
		$tarball = $this->getTarball($this->version, $versionFile, $cachePath);

		$this->clearDirectory($htdocs);
		$this->untar($tarball, $htdocs);

		$dirs = glob("$htdocs/*", GLOB_ONLYDIR);
		$sourcePath = reset($dirs);

		$this->removeDirectory($sourcePath . '/.github');

		return $sourcePath;
	}

	/**
	 * @param $directory
	 */
	protected function createDirectory($directory)
	{
		if (!file_exists($directory))
		{
			mkdir($directory, 0777, true);
		}
	}

	/**
	 * @param $versionFile
	 */
	protected function getVersions($versionFile)
	{
		if (file_exists($versionFile) && filemtime($versionFile) > strtotime('-7 days'))
		{
			return;
		}

		// GreenCape first, so entries get overwritten if provided by Joomla
		$repos = array(
			'greencape/joomla-legacy',
			'joomla/joomla-cms',
		);

		$versions = array();

		foreach ($repos as $repo)
		{
			$command = "git ls-remote https://github.com/{$repo}.git | grep -E 'refs/(tags|heads)' | grep -v '{}'";
			$result  = shell_exec($command);
			$refs    = explode(PHP_EOL, $result);
			$pattern = '/^[0-9a-f]+\s+refs\/(heads|tags)\/([a-z0-9\.\-_]+)$/im';

			foreach ($refs as $ref)
			{
				if (preg_match($pattern, $ref, $match))
				{
					if ($match[1] == 'tags')
					{
						if (!preg_match('/^\d+\.\d+\.\d+$/m', $match[2]))
						{
							continue;
						}
						$parts = explode('.', $match[2]);
						$this->checkAlias($versions, $parts[0], $match[2]);
						$this->checkAlias($versions, $parts[0] . '.' . $parts[1], $match[2]);
						$this->checkAlias($versions, 'latest', $match[2]);
					}
					$versions[$match[1]][$match[2]] = $repo;
				}
			}
		}

		// Special case: 1.6 and 1.7 belong to 2.x, so the latest 1.5 is the latest 1
		$versions['alias']['1'] = $versions['alias']['1.5'];

		file_put_contents($versionFile, json_encode($versions, JSON_PRETTY_PRINT));
	}

	/**
	 * @param $versions
	 * @param $alias
	 * @param $version
	 *
	 * @return void
	 */
	protected function checkAlias(&$versions, $alias, $version)
	{
		if (!isset($versions['alias'][$alias]) || version_compare($versions['alias'][$alias], $version, '<'))
		{
			$versions['alias'][$alias] = $version;
		}
	}

	/**
	 * @param $version
	 * @param $versionFile
	 * @param $cachePath
	 *
	 * @return null|string
	 */
	protected function getTarball($version, $versionFile, $cachePath)
	{
		$versions  = json_decode(file_get_contents($versionFile), true);
		$requested = $version;

		// Resolve alias
		if (isset($versions['alias'][$version]))
		{
			$version = $versions['alias'][$version];
		}

		$tarball = $cachePath . '/' . $version . '.tar.gz';

		if (file_exists($tarball) && !isset($versions['heads'][$version]))
		{
			return $tarball;
		}

		if (isset($versions['heads'][$version]))
		{
			// It's a branch, so get it from the original repo
			$url = 'http://github.com/joomla/joomla-cms/tarball/' . $version;
		}
		elseif (isset($versions['tags'][$version]))
		{
			$url = 'https://github.com/' . $versions['tags'][$version] . '/archive/' . $version . '.tar.gz';
		}
		else
		{
			throw new \RuntimeException("$requested: Version is unknown");
		}

		$bytes = file_put_contents($tarball, fopen($url, 'r'));

		if ($bytes === false || $bytes == 0)
		{
			throw new \RuntimeException("$requested: Failed to download $url");
		}

		return $tarball;
	}

	/**
	 * @param $directory
	 */
	protected function clearDirectory($directory)
	{
		$this->removeDirectory($directory);
		$this->createDirectory($directory);
	}

	/**
	 * @param $directory
	 */
	protected function removeDirectory($directory)
	{
		if (!file_exists($directory))
		{
			return;
		}

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path)
		{
			$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
		}

		rmdir($directory);
	}

	protected function untar($archive, $destination)
	{
		static $archiveManager = null;

		if (is_null($archiveManager))
		{
			$archiveManager = Zippy::load();
		}

		$archiveManager->open($archive)->extract($destination);
	}

	/**
	 * @param   ServerConfig $config
	 *
	 * @return string
	 */
	protected function getDockerPath($config)
	{
		$path    = $config->get('host.dockyard') . '/docker';
		$server  = $config->get('server.type');
		$version = $server == 'apache' ? $config->getVersion('php') . '-' . $config->getVersion('joomla') : $config->getVersion('server');

		return "$path/$server-$version";
	}

	/**
	 * @param string       $filename
	 * @param string       $destination
	 * @param ServerConfig $config
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function appendSql($filename, $destination, $config)
	{
		$databaseEngine   = Map::getType($config->get('database.driver'));
		$databaseVersion  = $config->getVersion($databaseEngine);
		$databaseName     = $config->get('database.name');
		$databaseDir      = $this->dockyard . '/' . $databaseEngine . '-' . $databaseVersion;
		$installationData = $destination . '/installation/sql/' . $databaseEngine . '/' . $filename;

		if (!file_exists($installationData))
		{
			throw new \Exception("Joomla! $this->version does not provide '$filename' for $databaseEngine.");
		}

		$sql = str_replace('#__', $config->get('database.prefix'), file_get_contents($installationData));
		if ($databaseEngine == 'postgresql')
		{
			// Fix escaping
			$sql = str_replace("\\'", "''", $sql);
			file_put_contents("$databaseDir/$databaseName.dump", $sql, FILE_APPEND);

			$databaseUser = $config->get('postgresql.user');
			$script = "psql -v ON_ERROR_STOP=1 --username=\"$databaseUser\" --dbname=\"$databaseName\" --file=\"/docker-entrypoint-initdb.d/$databaseName.dump\"\n";

			file_put_contents("$databaseDir/10-$databaseName.sh", $script);

			return;
		}

		file_put_contents("$databaseDir/$databaseName.sql", $sql, FILE_APPEND);
	}

	/**
	 * @param string       $filename
	 * @param ServerConfig $config
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function appendUserSql($filename, $config)
	{
		$databaseEngine   = Map::getType($config->get('database.driver'));
		$databaseName     = $config->get('database.name');
		$databaseVersion  = $config->getVersion($databaseEngine);
		$databaseDir      = $this->dockyard . '/' . $databaseEngine . '-' . $databaseVersion;
		$installationData = __DIR__ . '/template/' . $databaseEngine. '/' . $filename . '.sql';

		if (!file_exists($installationData))
		{
			throw new \Exception("There is no user data provided for '$filename' for $databaseEngine.");
		}

		$sql = str_replace('#__', $config->get('database.prefix'), file_get_contents($installationData));
		if ($databaseEngine == 'postgresql')
		{
			// Fix escaping
			$sql = str_replace("\\'", "''", $sql);
			file_put_contents("$databaseDir/$databaseName.dump", $sql, FILE_APPEND);

			$databaseUser = $config->get('postgresql.user');
			$script = "psql -v ON_ERROR_STOP=1 --username=\"$databaseUser\" --dbname=\"$databaseName\" --file=\"/docker-entrypoint-initdb.d/$databaseName.dump\"\n";

			file_put_contents("$databaseDir/10-$databaseName.sh", $script);

			return;
		}

		file_put_contents("$databaseDir/$databaseName.sql", $sql, FILE_APPEND);
	}

	/**
	 * @param ServerConfig $config
	 * @param string       $destination
	 */
	private function updateConfiguration($config, $destination)
	{
		$domain               = $config->getDomain();
		$version              = $this->version;
		$databaseDriver       = $config->get('database.driver');
		$databaseEngine       = Map::getType($databaseDriver);
		$prettyServerName     = ucfirst($config->get('server.type'));
		$prettyDatabaseDriver = ucfirst(str_replace('sql', 'SQL', str_replace('my', 'My', $databaseDriver)));

		$configFile = $this->findConfigFile($destination);

		$replacements = [
			// Site settings
			'sitename'        => "Joomla! $version/$prettyServerName/$prettyDatabaseDriver",

			// Database settings
			'dbtype'          => $databaseDriver,
			'host'            => $this->fixName($databaseEngine . '-' . $config->getVersion($databaseEngine)),
			'user'            => $config->get($databaseEngine . '.user'),
			'password'        => $config->get($databaseEngine . '.password'),
			'db'              => $config->get('database.name'),
			'dbprefix'        => $config->get('database.prefix'),

			// Server settings
			'error_reporting' => E_ALL & ~E_STRICT & ~E_DEPRECATED,

			// Locale settings
			'offset'          => $config->get('server.offset'),

			// Session settings
			'lifetime'        => $config->get('session.lifetime'),
			'session_handler' => $config->get('session.handler'),

			// Mail settings
			'mailer'          => "smtp",
			'mailfrom'        => "admin@$domain",
			'fromname'        => "Joomla! $version/$prettyServerName/$prettyDatabaseDriver",
			'sendmail'        => "/usr/bin/env catchmail",
			'smtpauth'        => 0,
			'smtpuser'        => '',
			'smtppass'        => '',
			'smtphost'        => "mail:1025",
			'smtpsecure'      => "none",

			// Cache settings
			'caching'         => $config->get('cache.enabled'),
			'cachetime'       => $config->get('cache.time'),
			'cache_handler'   => $config->get('cache.handler'),

			// Debug settings
			'debug'           => $config->get('debug.system'),
			'debug_db'        => $config->get('debug.system'),
			'debug_lang'      => $config->get('debug.language'),

			// Meta settings
			'MetaDesc'        => $config->get('meta.description'),
			'MetaKeys'        => $config->get('meta.keywords'),
			'MetaTitle'       => $config->get('meta.showTitle'),
			'MetaAuthor'      => $config->get('meta.showAuthor'),
			'MetaVersion'     => $config->get('meta.showVersion'),

			// SEO settings
			'sef'             => $config->get('sef.enabled'),
			'sef_rewrite'     => $config->get('sef.rewrite'),
			'sef_suffix'      => $config->get('sef.suffix'),
			'unicodeslugs'    => $config->get('sef.unicode'),

			// Feed settings
			'feed_limit'      => $config->get('feeds.limit'),
			'feed_email'      => $config->get('feeds.email'),
		];

		$replacements = $this->prepareReplacements($replacements);

		file_put_contents(
			$destination . '/configuration.php',
			preg_replace(array_keys($replacements), array_values($replacements), file_get_contents($configFile))
		);
	}

	/**
	 * @param $destination
	 *
	 * @return string
	 */
	private function findConfigFile($destination)
	{
		$candidates = [
			$destination . '/installation/configuration.php-dist',
			$destination . '/configuration.php-dist',
			$destination . '/configuration.php',
		];

		foreach ($candidates as $configFile)
		{
			if (file_exists($configFile))
			{
				return $configFile;
			}
		}

		throw new \Exception('No configuration file found!');
	}

	/**
	 * @param $rawReplacements
	 *
	 * @return array
	 */
	private function prepareReplacements($rawReplacements)
	{
		$replacements = [];
		foreach ($rawReplacements as $key => $value)
		{
			$replacements['~(\$' . $key . '\s*=\s*)(.*?);(?:\s*//\s*(.*))?~'] = '\1\'' . $value . '\'; // \3 was: \2';
		}

		return $replacements;
	}
}
