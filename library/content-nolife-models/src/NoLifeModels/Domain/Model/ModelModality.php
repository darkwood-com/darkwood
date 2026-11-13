<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

enum ModelModality: string
{
    case Text = 'text';
    case Image = 'image';
    case Audio = 'audio';
    case Video = 'video';
}
