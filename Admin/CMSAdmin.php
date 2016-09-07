<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Awaresoft\Sonata\PageBundle\Entity\Page;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Doctrine\ORM\EntityManager;
use Gedmo\Sluggable\Util\Urlizer;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\PageBundle\Admin\PageAdmin as BasePageAdmin;
use Knp\Menu\ItemInterface as MenuItemInterface;

/**
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CMSAdmin extends BasePageAdmin
{
    protected $baseRouteName = 'admin_awaresoft_cms';
    protected $baseRoutePattern = 'awaresoft/cms';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->add('compose', '{id}/compose', [
            'id' => null,
        ]);
        $collection->add('compose_container_show', 'compose/container/{id}', [
            'id' => null,
        ]);
        $collection->add('tree', 'tree');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere($query->expr()->eq($query->getRootAliases()[0] . '.inCms', ':inCms'));
        $query->setParameter('inCms', '1');

        return $query;
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        if (!$this->getSubject()->isDynamic()) {
            $errorElement->with('title')
                ->assertNotBlank()
                ->end()
                ->with('metaDescription')
                ->assertNotBlank()
                ->end();
        }


        if (!$this->isHomepage()) {
            $errorElement->with('slug')
                ->assertNotBlank()
                ->end();
        }
    }

    /**
     * @inheritdoc
     */
    public function prePersist($object)
    {
        parent::prePersist($object);

        $object->setInCms(true);
    }

    /**
     * @inheritdoc
     */
    public function preValidate($object)
    {
        parent::preValidate($object);

        if (!$object->getSlug() && $object->isCms()) {
            $object->setSlug(Urlizer::urlize($object->getName()));
        }
    }

    /**
     * Check condidtion if current page is mainpage
     *
     * @return bool
     */
    protected function isHomepage()
    {
        if (!$this->getSubject() || !$this->getSubject()->getId()) {
            return false;
        }

        if ($this->getSubject()->getParent() !== null) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->with($this->trans('admin.admin.form.group.main'))
            ->add('name')
            ->add('url')
            ->add('redirectUrl')
            ->add('site')
            ->add('enabled')
            ->add('hidden')
            ->end();

        $showMapper->with($this->trans('admin.admin.form.group.seo'))
            ->add('slug')
            ->add('title')
            ->add('description')
            ->end();

        $showMapper->with($this->trans('admin.admin.form.group.content'))
            ->add('content', 'html')
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name')
            ->add('url', 'url')
            ->add('redirectUrl', 'url')
            ->add('site')
            ->add('enabled', null, ['editable' => true])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name')
            ->add('url')
            ->add('redirectUrl')
            ->add('site')
            ->add('slug')
            ->add('title')
            ->add('enabled');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->em = $this->getEntityManager();
        $site = $this->getSubject()->getSite() ? $this->getSubject()->getSite() : null;

        $nameDisabled = $this->isHomepage() ? true : false;
        $requiredSlug = $this->getSubject() && $this->getSubject()->getId() ? true : false;

        // define group zoning
        $formMapper->with($this->trans('admin.admin.form.group.main'), ['class' => 'col-md-6'])
            ->end()
            ->with($this->trans('admin.admin.form.group.seo'), ['class' => 'col-md-6'])
            ->end()
            ->with($this->trans('admin.admin.form.group.content'))
            ->end()
            ->with($this->trans('admin.admin.form.group.advanced'), ['class' => 'col-md-6'])
            ->end();

        $formMapper->with($this->trans('admin.admin.form.group.main'))
            ->add('name', null, [
                'disabled' => $nameDisabled,
            ])
            ->add('enabled', null, ['required' => false])
            ->end();

        if ($this->getSubject() && $this->getSubject()->getDecorate()) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('templateCode', 'sonata_page_template', ['required' => true])
                ->end();
        }

        if ($this->isGranted('SUPER_ADMIN')) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('decorate', null, [
                    'required' => false,
                ])
                ->end();
        }

        if ($this->hasSubject() && !$this->getSubject()->getId()) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('site', null, ['required' => true, 'read_only' => true])
                ->end();
        }

        if (!$this->isHomepage()) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('parent', 'entity', [
                    'class' => 'AwaresoftSonataPageBundle:Page',
                    'query_builder' => function (PageRepository $pr) use ($site) {
                        return $pr->findCmsPages($site);
                    },
                ], [
                    'admin_code' => 'awaresoft.page.admin.cms',
                ])
                ->end();
        }

        $formMapper->with($this->trans('admin.admin.form.group.main'))
            ->add('position', 'integer')
            ->end();


        if (!$this->getSubject() || !$this->getSubject()->getId() || !$this->getSubject()->isHybrid()) {
            if (!$this->isHomepage()) {
                $formMapper->with($this->trans('admin.admin.form.group.seo'))
                    ->add('slug', 'text', [
                        'required' => $requiredSlug,
                        'disabled' => true,
                    ])
                    ->end();
            }
        }

        if (!$this->getSubject() || (!$this->getSubject()->isInternal() && !$this->getSubject()->isError())) {
            $formMapper->with($this->trans('admin.admin.form.group.seo'))
                ->add('url', 'text', ['attr' => ['readonly' => 'readonly'], 'required' => false])
                ->end();
        }

        if (!$this->getSubject()->isDynamic()) {
            $formMapper->with($this->trans('admin.admin.form.group.seo'), ['collapsed' => true])
                ->add('title', 'text', [
                    'max_length' => AwaresoftAbstractAdmin::SEO_TITLE_MAX_LENGTH,
                    'required' => true,
                ])
                ->add('metaDescription', 'textarea', [
                    'max_length' => AwaresoftAbstractAdmin::SEO_DESCRIPTION_MAX_LENGTH,
                ])
                ->end();
        }

        $formMapper->with($this->trans('admin.admin.form.group.advanced'))
            ->add('redirectUrl', 'text', [
                'required' => false,
            ])
            ->add('hidden', null, ['required' => false])
            ->end();

        $formMapper->with($this->trans('admin.admin.form.group.content'), ['collapsed' => true])
            ->add('content', 'sonata_formatter_type', [
                'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
                'format_field' => 'contentFormatter',
                'source_field' => 'rawContent',
                'source_field_options' => [
                    'horizontal_input_wrapper_class' => $this->getConfigurationPool()
                        ->getOption('form_type') == 'horizontal' ? 'col-lg-12' : '',
                    'attr' => [
                        'class' => $this->getConfigurationPool()
                            ->getOption('form_type') == 'horizontal' ? 'span10 col-sm-10 col-md-10' : '',
                        'rows' => 20,
                    ],
                ],
                'ckeditor_context' => 'default',
                'target_field' => 'content',
                'listener' => true,
                'required' => false,
            ])
            ->end();

        $formMapper->setHelps([
            'name' => $this->trans('page.admin.help.name'),
            'slug' => $this->trans('page.admin.help.slug'),
            'url' => $this->trans('page.admin.help.url'),
            'redirectUrl' => $this->trans('page.admin.help.redirect_url'),
            'enabled' => $this->trans('page.admin.help.enabled'),
            'hidden' => $this->trans('page.admin.help.hidden'),
            'title' => $this->trans('admin.admin.help.meta_title'),
            'metaDescription' => $this->trans('admin.admin.help.meta_description'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild($this->trans('page.admin.sidemenu.edit_page'), [
            'uri' => $admin->getRouteGenerator()->generate('admin_awaresoft_cms_edit', ['id' => $id]),
        ]);

        $menu->addChild($this->trans('page.admin.sidemenu.compose_page'), [
            'uri' => $this->getRouteGenerator()->generate('admin_awaresoft_cms_compose', ['id' => $id]),
        ]);


        if (!$this->getSubject()->isInternal()) {
            if (!$this->getSubject()->isHybrid()) {
                try {
                    $menu->addChild($this->trans('page.admin.sidemenu.view_page'), [
                        'uri' => $this->getRouteGenerator()->generate($this->getSubject()->getRouteName(), ['path' => $this->getSubject()->getUrl()]),
                        'linkAttributes' => ['target' => '_blank'],
                    ]);
                } catch (\Exception $e) {
                    // avoid crashing the admin if the route is not setup correctly
//                throw $e;
                }
            } else {
                try {
                    $menu->addChild($this->trans('page.admin.sidemenu.view_page'), [
                        'uri' => $this->getRouteGenerator()->generate($this->getSubject()->getRouteName()),
                        'linkAttributes' => ['target' => '_blank'],
                    ]);
                } catch (\Exception $e) {
                    // avoid crashing the admin if the route is not setup correctly
//                throw $e;
                }
            }
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
    }


    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $this->em->getRepository('AwaresoftSonataPageBundle:Page');
    }

}
