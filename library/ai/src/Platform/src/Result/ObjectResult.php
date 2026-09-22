<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class ObjectResult implements ResultInterface
{
    private ?RawResultInterface $rawResult = null;

    private readonly Metadata $metadata;

    /**
     * @param object|array<string, mixed> $content
     */
    public function __construct(
        private readonly object|array $content,
    ) {
        $this->metadata = new Metadata();
    }

    /**
     * @return object|array<string, mixed>
     */
    public function getContent(): object|array
    {
        return $this->content;
    }

    public function getRawResult(): ?RawResultInterface
    {
        return $this->rawResult;
    }

    public function setRawResult(RawResultInterface $rawResult): void
    {
        $this->rawResult = $rawResult;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}
