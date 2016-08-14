<?php

namespace Awaresoft\Sonata\PageBundle\Route;

use Sonata\PageBundle\Route\RoutePageGenerator as BaseRoutePageGenerator;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * This is the page generator service from existing routes (extended)
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class RoutePageGenerator extends BaseRoutePageGenerator
{
    /**
     * Updates site page routes with all routes available in Symfony router service
     *
     * @param SiteInterface $site A page bundle site instance
     * @param OutputInterface $output A Symfony console output
     *
     * @return void
     */
    public function update(SiteInterface $site, OutputInterface $output = null)
    {
        $message = sprintf(" > <info>Updating core routes for site</info> : <comment>%s - %s</comment>", $site->getName(), $site->getUrl());

        $this->writeln($output, array(
            str_repeat('=', strlen($message)),
            "",
            $message,
            "",
            str_repeat('=', strlen($message)),
        ));

        $knowRoutes = array();

        $root = $this->pageManager->getPageByUrl($site, '/');

        // no root url for the given website, create one
        if (!$root) {
            // first search for a route
            foreach ($this->router->getRouteCollection()->all() as $name => $route) {
                if ($route->getPath() === "/") {
                    $requirements = $route->getRequirements();
                    $name = trim($name);
                    $root = $this->pageManager->create(array(
                        'routeName' => $name,
                        'name' => $name,
                        'url' => $route->getPath(),
                        'site' => $site,
                        'requestMethod' => isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT',
                        'slug' => '/',
                    ));
                }
            }
            $root = ($root) ? $root : $this->pageManager->create(array(
                'routeName' => PageInterface::PAGE_ROUTE_CMS_NAME,
                'name' => 'Homepage',
                'url' => '/',
                'site' => $site,
                'requestMethod' => isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT',
                'slug' => '/',
            ));

            $this->pageManager->save($root);
        }

        // Iterate over declared routes from the routing mechanism
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            $name = trim($name);

            $knowRoutes[] = $name;

            $page = $this->pageManager->findOneBy(array(
                'routeName' => $name,
                'site' => $site->getId()
            ));

            $routeHostRegex = $route->compile()->getHostRegex();

            if (
                !$this->decoratorStrategy->isRouteNameDecorable($name)
                || !$this->decoratorStrategy->isRouteUriDecorable($route->getPath())
                || null !== $routeHostRegex
                && !preg_match($routeHostRegex, $site->getHost())
            ) {
                if ($page) {
                    $page->setEnabled(false);

                    $this->writeln($output, sprintf('  <error>DISABLE</error> <error>% -50s</error> %s', $name, $route->getPath()));
                } else {
                    continue;
                }
            }

            $update = true;

            if (!$page) {
                $update = false;

                $requirements = $route->getRequirements();

                $page = $this->pageManager->create(array(
                    'routeName' => $name,
                    'name' => $name,
                    'url' => $route->getPath(),
                    'site' => $site,
                    'requestMethod' => isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT',
                ));
            }

            if (!$page->getParent() && $page->getId() != $root->getId()) {
                $page->setParent($root);
            }

            $page->setSlug($route->getPath());
            $page->setUrl($route->getPath());
            $page->setRequestMethod(isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT');

            $this->pageManager->save($page);

            $this->writeln($output, sprintf('  <info>%s</info> % -50s %s', $update ? 'UPDATE ' : 'CREATE ', $name, $route->getPath()));
        }

        // Iterate over error pages
        foreach ($this->exceptionListener->getHttpErrorCodes() as $name) {
            $name = trim($name);

            $knowRoutes[] = $name;

            $page = $this->pageManager->findOneBy(array(
                'routeName' => $name,
                'site' => $site->getId()
            ));

            if (!$page) {
                $params = array(
                    'routeName' => $name,
                    'name' => $name,
                    'decorate' => false,
                    'site' => $site,
                );

                $page = $this->pageManager->create($params);

                $this->writeln($output, sprintf('  <info>%s</info> % -50s %s', 'CREATE ', $name, ''));
            }

            // an internal page or an error page should not have any parent (no direct access)
            $page->setParent(null);
            $this->pageManager->save($page);
        }

        $has = false;

        foreach ($this->pageManager->getHybridPages($site) as $page) {
            if (!$page->isHybrid() || $page->isInternal()) {
                continue;
            }

            if (!in_array($page->getRouteName(), $knowRoutes)) {
                if (!$has) {
                    $has = true;

                    $this->writeln($output, array('', 'Some hybrid pages does not exist anymore', str_repeat('-', 80)));
                }

                $this->writeln($output, sprintf('  <error>ERROR</error>   %s', $page->getRouteName()));
            }
        }

        if ($has) {
            $this->writeln($output, <<<MSG
<error>
  *WARNING* : Pages has been updated however some pages do not exist anymore.
              You must remove them manually.
</error>
MSG
            );
        }
    }
}
