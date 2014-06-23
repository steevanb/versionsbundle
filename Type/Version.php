<?php

namespace kujaff\VersionsBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use \kujaff\VersionsBundle\Entity\Version as VersionEntity;

/**
 * Field base64 encoded value
 */
class Version extends Type
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
        return ($value === null) ? null : $value->asString();
    }

    /**
     * {@inherited}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : new VersionEntity($value);
    }
}