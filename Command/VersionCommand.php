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
		$bundleVersion = $this->getContainer()->get('bundle.version')->get($name);

		// install
		$output->writeln('Bundle <comment>' . $name . '</comment> informations :');
		$filesVersion = ($bundleVersion->getVersion() == null) ? '<error>unknow</error> (bundle must extends kujaff\VersionsBundle\Versions\VersionnedBundle)' : $bundleVersion->getVersion()->get();
		$output->writeln('Files version : ' . $filesVersion);
		if ($bundleVersion->getInstalledVersion() == null) {
			$output->writeln('Installed version : <error>not installed</error>');
		} else {
			if ($bundleVersion->needUpdate()) {
				$output->writeln('Installed version : <error>' . $bundleVersion->getInstalledVersion()->get() . '</error> (run php \'app/console bundle:update ' . $name . '\' to update to ' . $bundleVersion->getVersion()->get() . ')');
			} else {
				$output->writeln('Installed version : <info>' . $bundleVersion->getInstalledVersion()->get() . '</info>');
			}
			$output->writeln('Installation date : ' . $bundleVersion->getInstallationDate()->format('Y-m-d H:i:s'));
			$updateDate = ($bundleVersion->getUpdateDate() == null) ? 'none' : $bundleVersion->getInstallationDate()->format('Y-m-d H:i:s');
			$output->writeln('Last update date : ' . $updateDate);
		}
	}

}