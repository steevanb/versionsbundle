<?php

namespace kujaff\VersionsBundle\Model;

use kujaff\VersionsBundle\Entity\BundleVersion;
use Symfony\Component\Finder\Finder;
use kujaff\VersionsBundle\Entity\Version;
use kujaff\VersionsBundle\Entity\Patch;

/**
 * Search for patchs in Patch
 * CurrentClass.php
 *     - Patch
 *         - Version_X_Y_Z
 *             - PatchYYYYmmDDhhIIss.php
 *         - Current
 *             - PatchYYYYmmDDhhIIss.php
 */
trait UpdateByPatchs
{

    /**
     * Sort dirs with version in name
     *
     * @param string $versionA
     * @param string  $versionB
     * @return int
     */
    private function compareDirVersion(&$versionA, &$versionB)
    {
        return $this->container->get('versions.version')->compare($versionA, $versionB);
    }

    /**
     * Find patch files in good order
     *
     * @param string $dir
     * @return array
     */
    protected function findPatchsFiles($dir)
    {
        if (is_dir($dir) === false) {
            return array();
        }

        $finderFiles = new Finder();
        $finderFiles->files();
        $finderFiles->in($dir);
        $finderFiles->name('Patch*.php');
        $finderFiles->sortByName();
        // we want first patch in first, but sortByName sort first patch in last
        $return = array();
        foreach ($finderFiles as $file) {
            $return = array_merge($return, array($file));
        }
        return $return;
    }

    /**
     * Call update
     *
     * @param string $className Fully qualified class name
     * @param BundleVersion $bundleVersion
     */
    protected function callUpdate($className, BundleVersion $bundleVersion)
    {
        $update = new $className();
        $update->update($bundleVersion);
    }

    /**
     * Try to patch all older versions, in Patch\Version_X_Y_Z dirs
     *
     * @param BundleVersion $bundleVersion
     * @return Version
     */
    protected function patchOldVersions(BundleVersion $bundleVersion)
    {
        $reflection = new \ReflectionClass(get_called_class());
        $fileInfos = new \SplFileInfo($reflection->getFileName());
        $patchPath = $fileInfos->getPath() . DIRECTORY_SEPARATOR . 'Patch';

        // directory doesn't exists, no patch to call
        if (is_dir($patchPath) === false) {
            return $bundleVersion->getInstalledVersion();
        }

        // dirs like Update\Version_X_Y_Z
        $finderVersions = new Finder();
        $finderVersions->directories()->in($patchPath)->name('Version_*')->sortByName();
        $dirsVersions = array();
        foreach ($finderVersions as $dir) {
            $dirsVersions[] = str_replace('_', '.', substr($dir->getFilename(), 8));
        }
        usort($dirsVersions, array($this, 'compareDirVersion'));

        // now that we have dirs in right order, let's find patch files
        $return = $bundleVersion->getInstalledVersion();
        foreach ($dirsVersions as $dir) {
            $files = $this->findPatchsFiles($patchPath . DIRECTORY_SEPARATOR . 'Version_' . str_replace('.', '_', $dir));
            foreach ($files as $file) {
                $className = $reflection->getNamespaceName() . '\\Patch\\Version_' . str_replace('.', '_', $dir) . '\\' . $file->getBasename('.' . $file->getExtension());
                $this->callUpdate($className, $bundleVersion);
            }

            $return = new Version($dir);
        }

        return $return;
    }

    /**
     * Try to patch current versions, in Patch\Current dir
     *
     * @param BundleVersion $bundleVersion
     * @return Version
     */
    protected function patchCurrentVersion(BundleVersion $bundleVersion)
    {
        $reflection = new \ReflectionClass(get_called_class());
        $fileInfos = new \SplFileInfo($reflection->getFileName());
        $manager = $this->container->get('doctrine')->getManager();
        $patchPath = $fileInfos->getPath() . DIRECTORY_SEPARATOR . 'Patch' . DIRECTORY_SEPARATOR . 'Current';
        $files = $this->findPatchsFiles($patchPath);

        foreach ($files as $file) {
            $className = $reflection->getNamespaceName() . '\\Patch\\Current\\' . $file->getBasename('.' . $file->getExtension());
            $this->callUpdate($className, $bundleVersion);

            // insert this patch into Patch, to know that we have already called it
            $patch = new Patch();
            $patch->setBundle($bundleVersion->getName());
            $patch->setDate(new \DateTime());
            $manager->persist($patch);
            $manager->flush();
        }
    }

    /**
     * Update bundle
     *
     * @param BundleVersion $bundleVersion
     * @return Version
     */
    public function update(BundleVersion $bundleVersion)
    {
        $return = $this->patchOldVersions($bundleVersion);
        $this->patchCurrentVersion($bundleVersion);

        return $return;
    }
}
