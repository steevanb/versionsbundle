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
			->setName('bundles:install')
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
		$bundle = $this->getContainer()->get('bundle.version')->get($name);
		if ($bundle->isInstalled()) {
			throw new \Exception('Bundle "' . $name . '" is already installed.');
		}

		// before schema update
		if ($bundle->getInstallationPreSchema() == false) {
			$this->_command($output, 'bundles:install:preSchema', array('name' => $name));
		}

		// update database schema
		$this->_command($output, 'doctrine:schema:update', array('--force' => true));

		// after schema update
		if ($bundle->getInstallationPostSchema() == false) {
			$this->_command($output, 'bundles:install:postSchema', array('name' => $name));
		}
	}

}