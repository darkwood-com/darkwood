<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface ResultInterface
{
    public function getContent(): mixed;

    public function getRawResult(): ?RawResultInterface;

    public function setRawResult(RawResultInterface $rawResult): void;

    public function getMetadata(): Metadata;
}
