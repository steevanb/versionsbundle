<?php
namespace kujaff\VersionsBundle\Versions;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Field base64 encoded value
 */
class DoctrineType extends Type
{
	const VERSION = 'version';

	/**
	 * Return type name
	 *
	 * @return type
	 */
	public function getName()
	{
		return self::VERSION;
	}

	/**
	 * {@inherited}
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		if (!array_key_exists('length', $fieldDeclaration)) {
			$fieldDeclaration['length'] = 10;
		}
		return 'VARCHAR(' . intval($fieldDeclaration['length']) . ')';
	}

	/**
	 * {@inherited}
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return ($value === null) ? null : $value->get();
	}

	/**
	 * {@inherited}
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return ($value === null) ? null : new Version($value);
	}

}