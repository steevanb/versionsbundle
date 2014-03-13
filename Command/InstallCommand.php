<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Installation after schema update
 */
class InstallCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:install')
			->setDescription('Install a bundle')
			->addArgument('name', InputArgument::REQUIRED)
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
		$name = $input->getArgument('name');
		$bundleVersion = $this->getContainer()->get('bundle.version')->getBundleVersion($name);

		// install
		$output->write('[<comment>' . $name . '</comment>] Installing ... ');
		$installedVersion = $this->getContainer()->get('bundle.installer')->install($name);
		$output->writeln('<info>' . $installedVersion->get() . '</info> installed.');

		// update
		if ($installedVersion->get() != $bundleVersion->getVersion()->get()) {
			$this->_command($output, 'bundle:update', array('name' => $name));
		}
	}

}