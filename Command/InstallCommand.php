<?php

namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

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
            ->addOption('force')
        ;
    }

    /**
     * {@inherited}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $this->getContainer()->get('versions.installer')->install($name, $input->getOption('force'), $output);
    }
}
