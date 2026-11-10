<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\User\Model\UserOAuth as BaseUserOAuth;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_user_oauth', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'oauth_provider_identifier', columns: ['provider', 'identifier']),
])]
class UserOAuth extends BaseUserOAuth {}
