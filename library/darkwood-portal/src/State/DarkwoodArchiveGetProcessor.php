<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DarkwoodArchiveIdInput;
use App\Service\DarkwoodMcpForwarderService;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class DarkwoodArchiveGetProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarderService $forwarder,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $id = '';
        if ($data instanceof DarkwoodArchiveIdInput) {
            $id = $data->id;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $id);
        if (false === $date || $date->format('Y-m-d') !== $id) {
            throw new BadRequestHttpException('Archive id must be an actual YYYY-MM-DD date.');
        }

        return $this->forwarder->forward('api_darkwood_archive_get', 'GET', null, ['id' => $id]);
    }
}
