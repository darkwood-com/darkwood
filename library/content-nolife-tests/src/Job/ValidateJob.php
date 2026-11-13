<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\ExcerptDraft;
use App\Model\ExcerptResult;
use Flow\JobInterface;

/**
 * @implements JobInterface<ExcerptDraft, ExcerptResult>
 */
final class ValidateJob implements JobInterface
{
    public function __construct(private int $minLength = 20) {}

    public function __invoke($data): ExcerptResult
    {
        assert($data instanceof ExcerptDraft);

        $excerpt = trim($data->excerpt);
        if ($excerpt === '' || mb_strlen($excerpt) < $this->minLength) {
            throw new \RuntimeException(sprintf(
                'Excerpt for "%s" is too short (min %d characters).',
                $data->title,
                $this->minLength,
            ));
        }

        if (trim($data->title) === '') {
            throw new \RuntimeException('Excerpt title must not be empty.');
        }

        return new ExcerptResult($data->uri, $data->title, $excerpt);
    }
}
