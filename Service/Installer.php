<?php

namespace kujaff\VersionsBundle\Service;

use kujaff\VersionsBundle\Entity\Version;
use kujaff\VersionsBundle\Entity\BundleVersion;
use kujaff\VersionsBundle\Exception\StructureException;
use kujaff\VersionsBundle\Exception\InstallStateException;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\DependencyInjection\ContainerAware;

class Installer extends ContainerAware
{
    /**
     * Return tagged services
     *
     * @param string $bundle
     * @param string $tag
     * @param $implements Fully qualified interface name must be implemented
     * @return array
     * @throws StructureException
     */
    private function getService($bundle, $tag, $implements)
    {
        $fileName = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'services.bundle.' . $tag . '.php';
        if (file_exists($fileName) === false) {
            throw new StructureException('Unable to find service tagged "bundle."' . $tag . '.');
        }

        $serviceIds = require($fileName);
        foreach ($serviceIds as $id) {
            $service = $this->container->get($id);
            if (!$service instanceof $implements) {
                throw new StructureException('Service "' . $id . '" must implements ' . $implements . '.');
            }
            if ($service->getBundleName() == $bundle) {
                return $service;
            }
        }
        return false;
    }

    /**
     * Get bundle version, assert a version si defined
     *
     * @param string $bundle
     * @return BundleVersion
     * @throws StructureException
     */
    private function getBundleVersion($bundle)
    {
        $return = $this->container->get('versions.bundle')->getVersion($bundle);
        if ($return->getVersion() === null) {
            throw new StructureException('Bundle "' . $bundle . '" main class must extends kujaff\VersionsBundle\Model\VersionnedBundle.');
        }
        return $return;
    }

    /**
     * Install
     *
     * @param string $bundle
     * @param boolean $force Force installation
     * @param OutputInterface $output
     * @return Version
     * @throws StructureException
     * @throws InstallStateException
     */
    public function install($bundle, $force = false, $output = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        if ($force === false) {
            $bundleVersion = $this->getBundleVersion($bundle);

            // already installed
            if ($bundleVersion->isInstalled()) {
                throw new InstallStateException('Bundle "' . $bundle . '" is already installed.');
            }
        }

        if ($output instanceof OutputInterface) {
            $output->write('[<comment>' . $bundle . '</comment>] Installing ... ');
        }

        $service = $this->getService($bundle, 'install', 'kujaff\\VersionsBundle\\Model\\Install');
        // bundle has a service to do stuff
        if ($service !== false) {
            $installedVersion = $service->install();
            if (!$installedVersion instanceof Version) {
                throw new StructureException('Service "' . get_class($service) . '" install method must return an instance of kujaff\VersionsBundle\Entity\Version.');
            }
            if ($output instanceof OutputInterface) {
                $output->writeln('<info>' . $installedVersion->asString() . '</info> installed.');
            }

            if ($force) {
                $bundleVersion = $this->getBundleVersion($bundle);
            }
            $bundleVersion->setInstalledVersion($installedVersion);
            $bundleVersion->setInstallationDate(new \DateTime());
            $manager->persist($bundleVersion);
            $manager->flush();
            return $installedVersion;
        }

        if ($output instanceof OutputInterface) {
            $output->writeln('<info>' . $installedVersion->asString() . '</info> installed.');
        }

        // no install service for this bundle, assume we installed the latest version
        if ($force) {
            $bundleVersion = $this->getBundleVersion($bundle);
        }
        $bundleVersion->setInstalledVersion($bundleVersion->getVersion());
        $bundleVersion->setInstallationDate(new \DateTime());
        $manager->persist($bundleVersion);
        $manager->flush();
        return $bundleVersion->getInstalledVersion();
    }

    /**
     * Install all bundle in required order
     *
     * @param OutputInterface $output
     */
    public function installAll($output = null)
    {
        foreach ($this->container->getParameter('versions.installOrder') as $bundle => $options) {
            try {
                $bundleVersion = $this->getBundleVersion($bundle);
            } catch (\Exception $e) {
                if ($bundle != 'VersionsBundle') {
                    throw $e;
                }
                $bundleVersion = new BundleVersion('VersionsBundle');
            }
            if ($bundleVersion->isInstalled() === false) {
                $this->install($bundle, $options['force'], $output);
            }
        }

        foreach ($this->container->get('versions.bundle')->getBundles() as $bundle) {
            $bundleVersion = $this->getBundleVersion($bundle->getName());
            if ($bundleVersion->isInstalled() === false) {
                $this->install($bundle->getName(), false, $output);
            }
        }
    }

    /**
     * Update a bundle
     *
     * @param string $bundle Bundle name
     * @param Version $version Version to install, null to latest
     * @param OutputInterface $output
     * @throws InstallStateException
     */
    public function update($bundle, $version = null, $output = null)
    {
        $bundleVersion = $this->getBundleVersion($bundle);
        if ($bundleVersion->isInstalled() === false) {
            throw new InstallStateException('Bundle "' . $bundle . '" is not installed.');
        }

        $version = ($version instanceof Version) ? $version : $bundleVersion->getVersion();

        // already up to date
        if ($this->container->get('versions.version')->compare($bundleVersion->getInstalledVersion(), $version) >= 0) {
            return $bundleVersion->getInstalledVersion();
        }

        if ($output instanceof OutputInterface) {
            $output->write('[<comment>' . $bundle . '</comment>] Updating from ' . $bundleVersion->getInstalledVersion()->asString() . ' to ' . $version->asString() . ' ... ');
        }

        $service = $this->getService($bundle, 'update', 'kujaff\\VersionsBundle\\Model\\Update');
        // an update service has be found
        if ($service !== false) {
            $installedVersion = $service->update($bundleVersion, $version);

            // no update service, assume we have updated bundle to the latest version
        } else {
            $installedVersion = $version;
        }

        if ($output instanceof OutputInterface) {
            $output->writeln('<info>' . $installedVersion->asString() . '</info> installed.');
        }

        $bundleVersion->setInstalledVersion($installedVersion);
        $bundleVersion->setUpdateDate(new \DateTime());
        $this->container->get('doctrine')->getManager()->flush();

        return $installedVersion;
    }

    /**
     * Update all bundles
     *
     * @param OutputInterface $output
     */
    public function updateAll($output = null)
    {
        foreach ($this->container->getParameter('versions.updateOrder') as $order) {
            $this->update($order['bundle'], new Version($order['version']), $output);
        }

        foreach ($this->container->get('versions.bundle')->getBundles() as $bundle) {
            $this->update($bundle->getName(), null, $output);
        }
    }

    /**
     * Uninstall
     *
     * @param string $bundle
     * @param boolean $force Force uninstall, although it's not installed
     * @throws InstallStateException
     */
    public function uninstall($bundle, $force = false)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $bundleVersion = $this->getBundleVersion($bundle);
        if ($force === false && $bundleVersion->isInstalled() === false) {
            throw new InstallStateException('Bundle "' . $bundle . '" is not installed.');
        }

        $service = $this->getService($bundle, 'uninstall', 'kujaff\\VersionsBundle\\Model\\Uninstall');
        if ($service !== false) {
            $service->uninstall();
        }

        $manager->remove($bundleVersion);
        $manager->flush();
    }
}
