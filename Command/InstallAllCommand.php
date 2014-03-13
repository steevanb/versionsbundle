<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Installation after schema update
 */
class InstallAllCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:install:all')
			->setDescription('Install all versionned bundles who are not already installed')
		;
	}

	/**
	 * Run another console command
	 *
	 * @param OutputInterface $output
	 * @param string $command
	 * @param array $params
	 */
	private function _command(OutputInterface $output, $command, $params = array())
	{
		$command = $this->getApplication()->find($command);
		$input = new ArrayInput(array_merge(array($command), $params));
		$command->run($input, $output);
	}

	/**
	 * {@inherited}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$bundles = $this->getContainer()->get('bundle.version')->getVersionnedBundles();
		foreach ($bundles as $bundleVersion) {
			if ($bundleVersion->isInstalled() == false) {
				$this->_command($output, 'bundle:install', array('name' => $bundleVersion->getName()));
			}
		}
	}

}