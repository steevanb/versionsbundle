<?php
namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Installation after schema update
 */
class UninstallCommand extends ContainerAwareCommand
{

	/**
	 * {@inherited}
	 */
	protected function configure()
	{
		$this
			->setName('bundle:uninstall')
			->setDescription('Uninstall a bundle')
			->addArgument('name', InputArgument::REQUIRED)
		;
	}

	/**
	 * {@inherited}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');

		$output->write('[<comment>' . $name . '</comment>] Uninstalling ...');
		$this->getContainer()->get('bundle.installer')->uninstall($name);
		$output->write(' <info>Done</info>', true);
	}

}