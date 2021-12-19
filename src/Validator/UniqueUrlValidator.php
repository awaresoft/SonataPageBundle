<?php

namespace Awaresoft\Sonata\PageBundle\Validator;

use Sonata\PageBundle\Validator\UniqueUrlValidator as BaseUniqueUrlValidator;
use Symfony\Component\Validator\Constraint;

class UniqueUrlValidator extends BaseUniqueUrlValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value->isHybrid() || $value->isDynamic()) {
            return;
        }
    }
}
