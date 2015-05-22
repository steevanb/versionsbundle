<?php

namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Update a bundle
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
        $this->getContainer()->get('versions.installer')->update($name, null, $output);
    }
}
