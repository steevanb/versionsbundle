<?php

namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install all bundles who needs to be
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
     * {@inherited}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('versions.installer')->installAll($output);
    }
}