<?php

namespace Awaresoft\Sonata\PageBundle\Command;

use Awaresoft\Sonata\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UndecorateCommand
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class UndecorateCommand extends ContainerAwareCommand
{
    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('awaresoft:page:undecorate')
            ->setDescription('Undecorate page in all sites by route name')
            ->addArgument('route', InputArgument::REQUIRED, 'route of page');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $pageManager = $this->getContainer()->get('sonata.page.manager.page');

        /**
         * @var Page[] $pages
         */
        $pages = $pageManager->findBy([
            'routeName' => $input->getArgument('route'),
        ]);

        foreach ($pages as $page) {
            if (!$page->getDecorate()) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(sprintf(
                        'Page: %s, site: %s is undecorate.',
                        $page->getRouteName(),
                        $page->getSite()->getName()
                    ));
                }

                continue;
            }

            $page->setDecorate(false);

            $logger->info(sprintf(
                'Undecorated page: %s, site: %s',
                $page->getRouteName(),
                $page->getSite()->getName()
            ), [
                'page' => $page,
            ]);

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(sprintf(
                    'Undecorated page: %s, site: %s',
                    $page->getRouteName(),
                    $page->getSite()->getName()
                ));
            }
        }

        $em->flush();
    }
}
