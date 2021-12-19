<?php

namespace Awaresoft\Sonata\PageBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Gedmo\Sluggable\Util\Urlizer;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Validator\ErrorElement;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Sonata\PageBundle\Admin\PageAdmin as BasePageAdmin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CmsAdmin extends BasePageAdmin
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
            ->add('url', TextType::class)
            ->add('redirectUrl', TextType::class)
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
                'help' => $this->trans('page.admin.help.name')
            ])
            ->add('enabled', null, [
                'required' => false,
                'help' => $this->trans('page.admin.help.enabled')
            ])
            ->end();

        if ($this->getSubject() && $this->getSubject()->getDecorate()) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('templateCode', TemplateChoiceType::class, ['required' => true])
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
                ->add('site', null, ['required' => true, 'attr' => ['readonly' => true]])
                ->end();
        }

        if (!$this->isHomepage()) {
            $formMapper->with($this->trans('admin.admin.form.group.main'))
                ->add('parent', EntityType::class, [
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
            ->add('position', IntegerType::class)
            ->end();


        if (!$this->getSubject() || !$this->getSubject()->getId() || !$this->getSubject()->isHybrid()) {
            if (!$this->isHomepage()) {
                $formMapper->with($this->trans('admin.admin.form.group.seo'))
                    ->add('slug', TextType::class, [
                        'required' => $requiredSlug,
                        'help' => $this->trans('page.admin.help.slug')
                    ])
                    ->end();
            }
        }

        if (!$this->getSubject() || (!$this->getSubject()->isInternal() && !$this->getSubject()->isError())) {
            $formMapper->with($this->trans('admin.admin.form.group.seo'))
                ->add('url', TextType::class, [
                    'attr' => ['readonly' => 'readonly'],
                    'required' => false,
                    'help' => $this->trans('page.admin.help.url')
                ])
                ->end();
        }

        if (!$this->getSubject()->isDynamic()) {
            $formMapper->with($this->trans('admin.admin.form.group.seo'), ['collapsed' => true])
                ->add('title', TextType::class, [
                    'attr' => [
                        'max_length' => AwaresoftAbstractAdmin::SEO_TITLE_MAX_LENGTH,
                    ],
                    'required' => true,
                    'help' => $this->trans('admin.admin.help.meta_title')
                ])
                ->add('metaDescription', TextareaType::class, [
                    'attr' => [
                        'max_length' => AwaresoftAbstractAdmin::SEO_DESCRIPTION_MAX_LENGTH,
                    ],
                    'help' => $this->trans('admin.admin.help.meta_description')
                ])
                ->end();
        }

        $formMapper->with($this->trans('admin.admin.form.group.advanced'))
            ->add('redirectUrl', TextType::class, [
                'required' => false,
                'help' => $this->trans('page.admin.help.redirect_url')
            ])
            ->add('hidden', null, [
                'required' => false,
                'help' => $this->trans('page.admin.help.hidden')
            ])
            ->end();

        $formMapper->with($this->trans('admin.admin.form.group.content'), ['collapsed' => true])
            ->add('content', FormatterType::class, [
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
    }

    /**
     * {@inheritdoc}
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        /**
         * @var $subject PageInterface
         */
        $subject = $this->getSubject();

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('page.admin.sidemenu.edit_page'),
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild(
            $this->trans('page.admin.sidemenu.compose_page'),
            $this->generateMenuUrl('compose', ['id' => $id])
        );

        if (!$subject->isInternal()) {
            if (!$subject->isHybrid()) {
                try {
                    $menu->addChild($this->trans('page.admin.sidemenu.view_page'), [
                        'uri' => $this->getRouteGenerator()->generate($this->getSubject()->getRouteName(), [
                            'path' => $this->getSubject()->getUrl()
                        ]),
                        'linkAttributes' => ['target' => '_blank'],
                    ]);
                } catch (\Exception $e) {
                    // avoid crashing the admin if the route is not setup correctly
                    // throw $e;
                }
            } else {
                try {
                    $menu->addChild($this->trans('page.admin.sidemenu.view_page'), [
                        'uri' => $this->getRouteGenerator()->generate($this->getSubject()->getRouteName()),
                        'linkAttributes' => ['target' => '_blank'],
                    ]);
                } catch (\Exception $e) {
                    // avoid crashing the admin if the route is not setup correctly
                    // throw $e;
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
     * @return PageRepository|EntityRepository
     */
    protected function getPageRepository()
    {
        return $this->em->getRepository('AwaresoftSonataPageBundle:Page');
    }
}
