<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Installation after schema update
 */
class UpdateCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:update')
			->setDescription('Update a bundle')
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

		if ($bundleVersion->isInstalled() == false) {
			throw new \Exception('Bundle "' . $name . '" is not installed.');
		}

		$output->write('[<comment>' . $name . '</comment>] Updating from ' . $bundleVersion->getInstalledVersion()->get() . ' to ' . $bundleVersion->getVersion()->get() . ' ... ');
		$newVersion = $this->getContainer()->get('bundle.installer')->update($name);
		$output->writeln('<info>' . $newVersion->get() . '</info> installed.');
	}

}