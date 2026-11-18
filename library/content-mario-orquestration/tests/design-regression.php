<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Mario\Application\Command\DesignLevelCommand;
use App\Mario\Infrastructure\AI\McpLevelTool;
use App\Mario\Infrastructure\Export\LevelPreviewExporter;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$tool = new McpLevelTool($root, fixture: true);
$temp = sys_get_temp_dir().'/darkwood-design-test-'.bin2hex(random_bytes(8));
try {
    check(['generate_original_level'] === $tool->discover(), 'SDK discovery failed.');
    $toolbox = new Toolbox([$tool]);
    check('generate_original_level' === $toolbox->getTools()[0]->getName(), 'Symfony AI tool registration failed.');
    $result = $toolbox->execute(new ToolCall('fixture-call', 'generate_original_level', ['prompt' => 'Original forest level']));
    $content = $result->getResult();
    check('fixture' === $content['mode'], 'Fixture must never be labelled real AI.');
    check(1 === count($tool->calls()), 'SDK call was not recorded.');
    try {
        $tool('Second level');
        throw new LogicException('Repeated generation accepted.');
    } catch (RuntimeException $error) {
        check(str_contains($error->getMessage(), 'One generation'), 'Wrong repeated-call failure.');
    }
    $invalidTool = new McpLevelTool($root, fixture: true);
    try {
        $invalidTool('  ');
        throw new LogicException('Whitespace prompt was accepted.');
    } catch (RuntimeException $error) {
        check(str_contains($error->getMessage(), 'MCP tool error: Invalid prompt'), 'Tool failure was hidden as a generic protocol error.');
    } finally {
        $invalidTool->disconnect();
    }
    $level = $content['level'];
    $level['name'] = '<script>alert("x")</script>';
    $exporter = new LevelPreviewExporter();
    $directory = $exporter->write($level, ['mode' => 'fixture', 'summary' => '<script>injection</script>'], $temp);
    $second = $exporter->write($content['level'], ['mode' => 'fixture'], $temp);
    check($directory !== $second, 'Exports overwrite each other.');
    $html = file_get_contents($directory.'/preview.html');
    check(!str_contains($html, '<script>'), 'Preview permits script injection.');
    check(str_contains($html, 'not a playable game'), 'Preview hides its scope.');
    check(is_file($directory.'/level.json') && is_file($directory.'/evidence.json'), 'Artifacts missing.');
    $invalid = $level;
    $invalid['terrain'][0]['w'] = 999;
    try {
        $exporter->write($invalid, [], $temp);
        throw new LogicException('Invalid geometry was exported.');
    } catch (InvalidArgumentException) {
    }
    unset($_ENV['OPENAI_API_KEY'], $_SERVER['OPENAI_API_KEY']);
    putenv('OPENAI_API_KEY');
    $command = new CommandTester(new DesignLevelCommand($root));
    $command->execute(['--prompt' => '']);
    check(Command::INVALID === $command->getStatusCode(), 'Empty design accepted.');
    $command->execute(['--prompt' => 'Original level']);
    check(Command::FAILURE === $command->getStatusCode(), 'Missing key silently accepted.');
    echo "PASS official SDK discovery/call via Symfony Toolbox, explicit fixture label, single call, safe unique preview, invalid geometry, empty prompt, missing-key failure\n";
} finally {
    $tool->disconnect();
    if (is_dir($temp)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($temp);
    }
}
