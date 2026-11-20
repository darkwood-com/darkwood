<?php

declare(strict_types=1);

namespace App\Capability;

use App\ComposioSdk\Composio;
use Mcp\Capability\Registry\Loader\LoaderInterface;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Request\CallToolRequest;
use Mcp\Schema\Tool as McpTool;
use Mcp\Server\RequestContext;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionException;
use Symfony\AI\Agent\Toolbox\Exception\ToolNotFoundException;
use Symfony\AI\Agent\Toolbox\ToolboxInterface;
use Symfony\AI\Agent\Toolbox\ToolResult;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;

final class ComposioToolChain implements ToolboxInterface, LoaderInterface
{
    private ?array $tools = null;

    public function __construct(
        private readonly Composio $composio,
        private readonly string $entityId,
        private readonly string $actions,
    ) {
    }

    public function load(RegistryInterface $registry): void
    {
        foreach ($this->actionsByToolName() as $name => $action) {
            $registry->registerTool(
                McpTool::fromArray([
                    'name' => $name,
                    'description' => $action['description'],
                    'inputSchema' => $action['schema'],
                ]),
                function (RequestContext $context) use ($action): string {
                    $request = $context->getRequest();
                    if (!$request instanceof CallToolRequest) {
                        throw new \LogicException('Expected an MCP tool call.');
                    }

                    return $this->run($action['id'], $request->arguments);
                },
            );
        }
    }

    public function getTools(): array
    {
        $tools = [];
        foreach ($this->actionsByToolName() as $name => $action) {
            $tools[] = new Tool(new ExecutionReference(self::class, 'execute'), $name, $action['description'], $action['schema']);
        }

        return $tools;
    }

    public function execute(ToolCall $toolCall): ToolResult
    {
        $action = $this->actionsByToolName()[$toolCall->getName()] ?? null;
        if (null === $action) {
            throw ToolNotFoundException::notFoundForToolCall($toolCall);
        }

        try {
            return new ToolResult($toolCall, $this->run($action['id'], $toolCall->getArguments()));
        } catch (\Throwable $error) {
            throw ToolExecutionException::executionFailed($toolCall, $error);
        }
    }

    private function actionsByToolName(): array
    {
        if (null !== $this->tools) {
            return $this->tools;
        }

        $tools = [];
        foreach (explode(',', $this->actions) as $id) {
            $id = trim($id);
            if ('' === $id) {
                continue;
            }

            $data = $this->composio->actions->get(['actionName' => $id]);
            if (null === $data) {
                throw new \RuntimeException(sprintf('Unknown Composio action "%s".', $id));
            }

            $tools[strtolower($data['name'] ?? 'unknownAction')] = [
                'id' => $id,
                'description' => $data['description'] ?? '',
                'schema' => self::schema($data['input_parameters']['properties'] ?? []),
            ];
        }

        return $this->tools = $tools;
    }

    private function run(string $id, array $arguments): string
    {
        return json_encode(
            $this->composio->getEntity($this->entityId)->execute($id, $arguments),
            JSON_THROW_ON_ERROR,
        );
    }

    private static function schema(array $parameters): array
    {
        $properties = [];
        $required = [];

        foreach ($parameters as $paramName => $paramData) {
            $property = [
                'type' => $paramData['type'] ?? 'string',
                'description' => $paramData['description'] ?? '',
            ];

            if (isset($paramData['default'])) {
                $property['default'] = $paramData['default'];
            }

            if (isset($paramData['examples'])) {
                $property['examples'] = $paramData['examples'];
            }

            if (isset($paramData['nullable']) && $paramData['nullable']) {
                $property['type'] = [$property['type'], 'null'];
            }

            if (isset($paramData['items'])) {
                $property['items'] = $paramData['items'];
            }

            if (isset($paramData['minimum'])) {
                $property['minimum'] = $paramData['minimum'];
            }
            if (isset($paramData['maximum'])) {
                $property['maximum'] = $paramData['maximum'];
            }

            $properties[$paramName] = $property;

            if (!isset($paramData['nullable']) || !$paramData['nullable']) {
                if (!isset($paramData['default'])) {
                    $required[] = $paramName;
                }
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if (!empty($required)) {
            $schema['required'] = $required;
        }

        return $schema;
    }
}
