<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Installation after schema update
 */
class VersionCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:version')
			->setDescription('Informations about a bundle')
			->addArgument('name', InputArgument::REQUIRED)
		;
	}

	/**
	 * {@inherited}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');
		$bundleVersion = $this->getContainer()->get('bundle.version')->getBundleVersion($name);

		// informations
		$output->writeln('<comment>' . $name . '</comment> informations :');

		// unversionned bundle
		if ($bundleVersion->isVersionned() == false) {
			$output->writeln('Bundle <error>not versionned</error>.');
			$output->writeln('Unable to get informations, install, update or uninstall it.');
			$output->writeln('To create a versionned bundle, see <info>' . realpath(__DIR__ . '/../README.md') . '</info>');

			// versionned bundle
		} else {
			$output->writeln('Files version : ' . $bundleVersion->getVersion()->asString());
			if ($bundleVersion->getInstalledVersion() == null) {
				$output->writeln('Installed version : <error>not installed</error>');
			} else {
				if ($bundleVersion->needUpdate()) {
					$output->writeln('Installed version : <error>' . $bundleVersion->getInstalledVersion()->asString() . '</error> (run php \'app/console bundle:update ' . $name . '\' to update to ' . $bundleVersion->getVersion()->asString() . ')');
				} else {
					$output->writeln('Installed version : <info>' . $bundleVersion->getInstalledVersion()->asString() . '</info>');
				}
				$output->writeln('Installation date : ' . $bundleVersion->getInstallationDate()->format('Y-m-d H:i:s'));
				$updateDate = ($bundleVersion->getUpdateDate() == null) ? 'none' : $bundleVersion->getInstallationDate()->format('Y-m-d H:i:s');
				$output->writeln('Last update date : ' . $updateDate);
			}
		}
	}

}