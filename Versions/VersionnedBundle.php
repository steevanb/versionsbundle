<?php
namespace kujaff\VersionsBundle\Versions;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Extends this class instead of Bundle SF2 class to add getVersion
 */
class VersionnedBundle extends Bundle
{
	/**
	 * Version
	 *
	 * @var Version
	 */
	protected $version;

	/**
	 * Indicate if bundle needs to be installed or if it can be used without installation
	 *
	 * @var boolean
	 */
	protected $needInstallation = true;

	/**
	 * Indicate if bundle needs to be up to date or if it can be used without being up to date
	 *
	 * @var boolean
	 */
	protected $needUpToDate = true;

	/**
	 * Boot the bundle
	 *
	 * @throws Exception
	 */
	public function boot()
	{
		parent::boot();

		if ($this->needInstallation || $this->needUpToDate) {
			// if you have a better option to know if we are in console or in normal application ...
			if (isset($GLOBALS['argv']) && is_array($GLOBALS['argv']) && $GLOBALS['argv'][0] == 'app/console') {
				return null;
			}
			$bundleVersion = $this->container->get('bundle.version')->get($this->getName());
			if ($this->needInstallation && $bundleVersion->isInstalled() == false) {
				throw new Exception('Bundle "' . $this->getName() . '" needs to be installed. Exec "php app/console bundle:install ' . $this->getName() . '".');
			}
			if ($this->needUpToDate && $bundleVersion->needUpdate()) {
				throw new Exception('Bundle "' . $this->getName() . '" needs to be updated. Exec "php app/console bundle:update ' . $this->getName() . '".');
			}
		}
	}

	/**
	 * Return version
	 *
	 * @return Version
	 */
	public function getVersion()
	{
		return $this->version;
	}

}