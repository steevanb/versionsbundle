<?php

namespace kujaff\VersionsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update all bundles
 */
class UpdateAllCommand extends ContainerAwareCommand
{

    /**
     * {@inherited}
     */
    protected function configure()
    {
        $this
            ->setName('bundle:update:all')
            ->setDescription('Update all versionned bundles who are not already updated')
        ;
    }

    /**
     * {@inherited}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('versions.installer')->updateAll($output);
    }
}