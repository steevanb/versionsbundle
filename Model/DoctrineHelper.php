<?php

namespace kujaff\VersionsBundle\Model;

/**
 * Helps your using Doctrine
 * Just needs a property ContainerInterface $container
 */
trait DoctrineHelper
{
    /**
     * Execute a DQL query (only for SELECT, UPDATE or DELETE)
     *
     * @param string $dql
     * @param array $parameters
     * @return mixed
     */
    protected function _executeDQL($dql, array $parameters = array())
    {
        $em = $this->container->get('doctrine')->getManager();
        $query = $em->createQuery($dql);
        foreach ($parameters as $name => $value) {
            $query->setParameter($name, $value);
        }
        return $query->getResult();
    }

    /**
     * Execute raw SQL
     *
     * @param string $sql
     * @param array $parameters
     * @return \Doctrine\DBAL\Statement
     */
    protected function _executeSQL($sql, array $parameters = array())
    {
        $em = $this->container->get('doctrine')->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        foreach ($parameters as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Drop tables if exists
     *
     * @param array $tables
     */
    protected function _dropTables(array $tables)
    {
        foreach ($tables as $table) {
            $this->_executeSQL('DROP TABLE IF EXISTS ' . $table);
        }
    }
}
