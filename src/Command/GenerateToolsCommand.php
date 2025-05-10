<?php

namespace App\Command;

use App\ComposioSdk\Composio;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate-tools',
    description: 'Generate ComposioMcp Tools',
)]
class GenerateToolsCommand extends Command
{
    public function __construct(private Composio $composio)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('action', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The action(s) to perform. You can specify multiple --action options.')
            ->addOption('entityId', null, InputOption::VALUE_REQUIRED, 'The entity ID to use for the actions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $actions = $input->getOption('action');
        $entityId = $input->getOption('entityId');

        if (empty($actions)) {
            $io->error('At least one --action option is required.');
            return Command::FAILURE;
        }

        if (empty($entityId)) {
            $io->error('The --entityId option is required.');
            return Command::FAILURE;
        }

        $methods = [];
        foreach ($actions as $action) {
            $actionData = $this->composio->actions->get(['actionName' => $action]);
            $methods[] = $this->generateMethodFromAction($actionData[0], $entityId);
        }

        $this->writeMethodsToFile($methods, __DIR__ . '/../ComposioMcpTools.php');
        $io->success('ComposioMcpTools.php has been generated with the latest methods.');

        return Command::SUCCESS;
    }

    private function generateMethodFromAction(array $actionData, string $entityId): string
    {
        $name = $actionData['name'] ?? 'unknownAction';
        $methodName = $this->camelCase($name);
        $description = $actionData['description'] ?? '';
        $parameters = $actionData['parameters']['properties'] ?? [];
        $response = $actionData['response']['properties'] ?? [];

        // Build PHPDoc
        $phpdoc = "/**\n      * {$description}\n";
        $args = [];
        foreach ($parameters as $paramName => $paramData) {
            $type = $this->phpType($paramData['type'] ?? 'mixed', $paramData);
            $desc = $paramData['description'] ?? '';
            $phpdoc .= "      * @param {$type} \${$paramName} {$desc}\n";
            $default = isset($paramData['default']) 
                ? ' = ' . var_export($paramData['default'], true) 
                : ' = ' . $this->phpDefaultType($type);
            $args[] = "{$type} \${$paramName}{$default}";
        }
        $phpdoc .= "      * @return array The response data from the action execution.\n";
        $phpdoc .= "      */";

        // Build method
        $argsString = implode(', ', $args);
        $toolName = strtolower($name);
        $dataArray = [];
        foreach ($parameters as $paramName => $paramData) {
            $dataArray[] = "'{$paramName}' => \${$paramName}";
        }
        $dataString = implode(', ', $dataArray);
        return <<<EOD
    {$phpdoc}
    #[McpTool(name: '{$toolName}')]
    public function {$methodName}({$argsString}): array
    {
        \$data = [{$dataString}];
        return \$this->composioToolSet->execute_action('{$name}', \$data, '{$entityId}');
    }

EOD;
    }

    private function writeMethodsToFile(array $methods, string $filePath): void
    {
        $classHeader = <<<EOD
<?php

namespace App;

use PhpMcp\Server\Attributes\McpTool;
use App\ComposioSdk\ComposioToolSet;

class ComposioMcpTools
{
    private ComposioToolSet \$composioToolSet;

    public function __construct(ComposioToolSet \$composioToolSet)
    {
        \$this->composioToolSet = \$composioToolSet;
    }

EOD;

        $classFooter = "}\n";
        $methodsString = implode("\n", $methods);

        $content = "{$classHeader}\n{$methodsString}{$classFooter}";

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filePath, $content);
    }

    private function camelCase(string $str): string
    {
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return lcfirst($str);
    }

    private function phpType(string $type, array $paramData): string
    {
        // Map JSON schema types to PHP types
        switch ($type) {
            case 'integer': return 'int';
            case 'boolean': return 'bool';
            case 'array':
                $itemsType = $paramData['items']['type'] ?? 'mixed';
                return $itemsType === 'string' ? 'array' : 'array';
            case 'string': return 'string';
            case 'object': return 'array';
            default: return 'mixed';
        }
    }

    private function phpDefaultType(string $type): string
    {
        return match ($type) {
            'int' => '0',
            'float' => '0.0',
            'bool' => 'false',
            'string' => "''",
            'array' => '[]',
            default => 'null'
        };
    }
}
