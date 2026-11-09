<?php

namespace App\Command;

use Flow\Driver\FiberDriver;
use Flow\ExceptionInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-thispersondoesnotexist',
    description: 'Fetch fictional person images from thispersondoesnotexist.com',
)]
class FetchThisPersonDoesNotExistCommand extends Command
{
    private const string FETCH_URL = 'https://thispersondoesnotexist.com';
    private const string USER_AGENT = 'Flow POC 1.0';
    private const string OUTPUT_DIR = 'var/thispersondoesnotexist';
    private const int DELAY_MIN_SECONDS = 1;
    private const int DELAY_MAX_SECONDS = 3;

    private SymfonyStyle $io;

    private string $outputDir;

    protected function configure(): void
    {
        $this
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Output filename (only valid when --count=1)')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Number of images to download', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $count = (int) $input->getOption('count');
        $file = $input->getOption('file');

        if ($count < 1) {
            $this->io->error('Option --count must be at least 1.');

            return Command::FAILURE;
        }

        if ($file !== null && $count > 1) {
            $this->io->error('Option --file can only be used when --count=1.');

            return Command::FAILURE;
        }

        $this->outputDir = \dirname(__DIR__, 2).'/'.self::OUTPUT_DIR;
        if (!is_dir($this->outputDir) && !mkdir($this->outputDir, 0777, true) && !is_dir($this->outputDir)) {
            $this->io->error(sprintf('Unable to create output directory: %s', $this->outputDir));

            return Command::FAILURE;
        }

        $this->io->title('Fetching fictional person images');
        $this->io->text(sprintf('Source: %s', self::FETCH_URL));
        $this->io->text(sprintf('Output directory: %s', $this->outputDir));
        if ($count > 1) {
            $this->io->text(sprintf(
                'Rate limit: random %d–%ds delay per download (fibers run in parallel, max %d at once)',
                self::DELAY_MIN_SECONDS,
                self::DELAY_MAX_SECONDS,
                $count,
            ));
        }
        $this->io->newLine();

        $driver = new FiberDriver();
        $savedFiles = [];

        $flow = (new FlowFactory())->create(function () use ($count, $file, &$savedFiles, $driver) {
            yield [
                fn (int $index) => $this->fetchImage($index, $driver),
                fn (ExceptionInterface $exception) => throw new \RuntimeException($exception->getMessage()),
            ];
            yield function (string $image) use ($file, &$savedFiles): string {
                $outputPath = $this->saveAndReport($image, $file);
                $savedFiles[] = $outputPath;

                return $outputPath;
            };
        }, ['driver' => $driver]);

        for ($index = 1; $index <= $count; ++$index) {
            $flow(new Ip($index));
        }

        try {
            $flow->await();
        } catch (\RuntimeException $exception) {
            $this->io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $this->io->section('Summary');
        $this->io->success(sprintf(
            'Downloaded %d image(s) to %s',
            \count($savedFiles),
            $this->outputDir,
        ));
        $this->io->listing($savedFiles);

        return Command::SUCCESS;
    }

    /**
     * Opens a remote HTTP stream and reads the full body.
     *
     * Preferred for Flow experimentation: the resource handle is the primitive
     * that can later be wired to stream_select(), non-blocking mode, or fibers.
     */
    private function fetchImage(int $index, FiberDriver $driver): string
    {
        $delay = random_int(self::DELAY_MIN_SECONDS, self::DELAY_MAX_SECONDS);
        $this->io->note(sprintf('#%d: suspending fiber for %ds before download...', $index, $delay));
        $driver->delay($delay);

        $startedAt = microtime(true);
        $this->io->text(sprintf('#%d: downloading (other fibers may run while this one waits)...', $index));

        $context = $this->createHttpContext();
        $stream = fopen(self::FETCH_URL, 'r', false, $context);

        if ($stream === false) {
            throw new \RuntimeException('Failed to open remote stream.');
        }

        try {
            $responseHeaders = function_exists('http_get_last_response_headers')
                ? (http_get_last_response_headers() ?: [])
                : ($http_response_header ?? []);

            $this->assertSuccessfulHttpResponse($responseHeaders);

            $contentType = $this->extractContentType($responseHeaders);
            $this->assertImageContentType($contentType);

            $content = stream_get_contents($stream);
            if ($content === false) {
                throw new \RuntimeException('Failed to read remote stream.');
            }

            $this->io->text(sprintf(
                '#%d: fetch complete in %.2fs',
                $index,
                microtime(true) - $startedAt,
            ));

            return $content;
        } finally {
            fclose($stream);
        }
    }

    private function saveAndReport(string $image, ?string $fileOverride): string
    {
        $checksum = $this->computeChecksum($image);
        $filename = $this->generateFilename($checksum, $fileOverride);
        $outputPath = $this->saveFile($filename, $image);
        $this->report($outputPath, $checksum, \strlen($image));

        return $outputPath;
    }

    private function createHttpContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: '.self::USER_AGENT."\r\n",
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);
    }

    private function computeChecksum(string $binary): string
    {
        return md5($binary);
    }

    private function generateFilename(string $checksum, ?string $override): string
    {
        return $override ?? $checksum.'.jpeg';
    }

    private function saveFile(string $filename, string $binary): string
    {
        $outputPath = $this->outputDir.'/'.$filename;
        $bytesWritten = file_put_contents($outputPath, $binary);

        if ($bytesWritten === false) {
            throw new \RuntimeException(sprintf('Failed to write image to: %s', $outputPath));
        }

        return $outputPath;
    }

    private function report(string $outputPath, string $checksum, int $bytesWritten): void
    {
        $this->io->success('Image downloaded');
        $this->io->listing([
            sprintf('Checksum: %s', $checksum),
            sprintf('Output file: %s', $outputPath),
            sprintf('Bytes written: %d', $bytesWritten),
        ]);
    }

    /**
     * @param list<string> $headers
     */
    private function assertSuccessfulHttpResponse(array $headers): void
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d+)/', $header, $matches)) {
                $statusCode = (int) $matches[1];
                if ($statusCode < 200 || $statusCode >= 300) {
                    throw new \RuntimeException(sprintf(
                        'HTTP request failed with status code %d.',
                        $statusCode,
                    ));
                }

                return;
            }
        }
    }

    /**
     * @param list<string> $headers
     */
    private function extractContentType(array $headers): string
    {
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                return trim(substr($header, \strlen('Content-Type:')));
            }
        }

        return '';
    }

    private function assertImageContentType(string $contentType): void
    {
        if ($contentType === '') {
            return;
        }

        if (!str_contains(strtolower($contentType), 'image')) {
            throw new \RuntimeException(sprintf(
                'Unexpected content type: %s (expected an image).',
                $contentType,
            ));
        }
    }
}
