<?php

namespace Awaresoft\Sonata\PageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * AwaresoftSonataPageBundle class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class AwaresoftSonataPageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SonataPageBundle';
    }
}