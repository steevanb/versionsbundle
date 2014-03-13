<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installation after schema update
 */
class ListCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:list')
			->setDescription('List all versionned bundles')
		;
	}

	/**
	 * {@inherited}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<comment>Bundle</comment>                    <comment>Files</comment>      <comment>Installed</comment>  <comment>Installation</comment>         <comment>Last update</comment>');

		$bundles = $this->getContainer()->get('bundle.version')->getVersionnedBundles();
		foreach ($bundles as $bundleVersion) {
			$name = sprintf('%-25s', $bundleVersion->getName());
			$version = sprintf('%-10s', $bundleVersion->getVersion()->asString());
			if ($bundleVersion->isInstalled()) {
				$installedVersion = sprintf('%-10s', $bundleVersion->getInstalledVersion()->asString());
				if ($bundleVersion->needUpdate()) {
					$installedVersion = '<error>' . $installedVersion . '</error>';
				} else {
					$installedVersion = '<info>' . $installedVersion . '</info>';
				}
				$installationDate = $bundleVersion->getInstallationDate()->format('Y-m-d H:i:s');
				$updateDate = ($bundleVersion->getUpdateDate() == null) ? null : $bundleVersion->getUpdateDate()->format('Y-m-d H:i:s');
			} else {
				$installedVersion = '<error>' . sprintf('%-10s', 'none') . '</error>';
				$installationDate = sprintf('%-19s', null);
				$updateDate = sprintf('%-19s', null);
			}
			$output->writeln($name . ' ' . $version . ' ' . $installedVersion . ' ' . $installationDate . '  ' . $updateDate);
		}
	}

}