<?php

declare(strict_types=1);

namespace App\Authorizers;

use App\Contracts\OwnableInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class AuthorizeUser extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->reflClass->implementsInterface(OwnableInterface::class)) {
            return '';
        }

        return $targetTableAlias . '.user_id = ' . $this->getParameter('user_id');
    }
}