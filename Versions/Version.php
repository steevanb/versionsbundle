<?php
namespace kujaff\VersionsBundle\Versions;

/**
 * Information about a version
 */
class Version
{
	/**
	 * @var int
	 */
	private $major;

	/**
	 * @var int
	 */
	private $minor;

	/**
	 * @var int
	 */
	private $patch;

	/**
	 * @var \DateTime
	 */
	private $date;

	/**
	 * Constructor
	 *
	 * @param string $version Full version (ex : 1.2.3)
	 * @param \DateTime $date
	 */
	public function __construct($version = null, \DateTime $date = null)
	{
		if ($version != null) {
			$this->set($version);
		}
		if ($date != null) {
			$this->setDate($date);
		}
	}

	/**
	 * Define version
	 *
	 * @param string $version Full version (ex : 1.2.3)
	 * @throws Exception
	 */
	public function set($version)
	{
		$parts = explode('.', $version);
		if (count($parts) != 3) {
			throw new Exception('Version "' . $version . '" must be like x.y.z.');
		}
		foreach ($parts as $part) {
			if ($part != (string) intval($part)) {
				throw new Exception('Version "' . $version . '" must be like x.y.z, with x, y and z are only numerics.');
			}
		}
		$this->major = $parts[0];
		$this->minor = $parts[1];
		$this->patch = $parts[2];
	}

	/**
	 * Get full version (ex : 1.2.3)
	 *
	 * @return type
	 */
	public function asString()
	{
		return $this->getMajor() . '.' . $this->getMinor() . '.' . $this->getPatch();
	}

	/**
	 * Return major part of the version
	 *
	 * @return int
	 */
	public function getMajor()
	{
		return $this->major;
	}

	/**
	 * Return minor part of the version
	 *
	 * @return string
	 */
	public function getMinor()
	{
		return $this->minor;
	}

	/**
	 * Return patch part of the version
	 *
	 * @return int
	 */
	public function getPatch()
	{
		return $this->patch;
	}

	/**
	 * Define date
	 *
	 * @param \DateTime $date
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;
	}

	/**
	 * Get date
	 *
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

}