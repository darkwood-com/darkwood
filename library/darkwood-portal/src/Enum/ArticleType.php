<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleType: string
{
    case Manual = 'manual';
    case Auto = 'auto';
    case Watch = 'watch';
    case Release = 'release';
    case Creator = 'creator';

    public function blogListRouteName(): string
    {
        return match ($this) {
            self::Manual => 'blog_home',
                        self::Auto => 'blog_auto',
            self::Watch => 'blog_watch',
            self::Release => 'blog_release',
            self::Creator => 'blog_creator',
        };
    }
}
