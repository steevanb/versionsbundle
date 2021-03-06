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
            ->setDescription('Uninstall a bundle, use --force to force uninstall although bundle is not installed')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('force')
        ;
    }

    /**
     * {@inherited}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $force = $input->getOption('force');

        $output->write('[<comment>' . $name . '</comment>] Uninstalling ...');
        $this->getContainer()->get('versions.installer')->uninstall($name, $force);
        $output->write(' <info>Done</info>', true);
    }
}
