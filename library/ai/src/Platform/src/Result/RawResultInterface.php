<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface RawResultInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array;

    public function getObject(): object;
}
