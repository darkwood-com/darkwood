<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darkwood\AI\Platform\Bridge\Jev;

use Darkwood\AI\Platform\Capability;
use Darkwood\AI\Platform\Model;
use Darkwood\AI\Platform\ModelCatalog\ModelCatalog as BaseModelCatalog;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class ModelCatalog extends BaseModelCatalog
{
    /**
     * @param array<string, Model> $additionalModels
     */
    public function __construct(array $additionalModels = [])
    {
        $capabilities = [Capability::INPUT_TEXT];

        parent::__construct(array_merge([
            'jev-latest' => new Jev('jev-latest', $capabilities),
            'jev-preview' => new Jev('jev-preview', $capabilities),
            'jev-1.13.0' => new Jev('jev-1.13.0', $capabilities),
        ], $additionalModels));
    }
}
