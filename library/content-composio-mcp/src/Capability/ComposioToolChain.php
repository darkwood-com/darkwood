<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Capability;

use App\ComposioSdk\Composio;
use Symfony\AI\McpSdk\Capability\Tool\CollectionInterface;
use Symfony\AI\McpSdk\Capability\Tool\IdentifierInterface;
use Symfony\AI\McpSdk\Capability\Tool\MetadataInterface;
use Symfony\AI\McpSdk\Capability\Tool\ToolCall;
use Symfony\AI\McpSdk\Capability\Tool\ToolCallResult;
use Symfony\AI\McpSdk\Capability\Tool\ToolExecutorInterface;
use Symfony\AI\McpSdk\Exception\InvalidCursorException;
use Symfony\AI\McpSdk\Exception\ToolExecutionException;
use Symfony\AI\McpSdk\Exception\ToolNotFoundException;
use App\ComposioSdk\ComposioToolSet;
use Exception;
use Symfony\AI\Agent\Toolbox\ToolFactoryInterface;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * A collection of tools. All tools need to implement IdentifierInterface.
 */
class ComposioToolChain implements ToolExecutorInterface, CollectionInterface, ToolFactoryInterface
{
    public function __construct(
        private Composio $composio,
        private string $entityId,
        private string $actions,
    ) {
    }

    public function getMetadata(int $count, ?string $lastIdentifier = null): iterable
    {
        $actions = $this->getActions();
        $found = null === $lastIdentifier;
        $result = [];
        
        foreach ($actions as $action) {
            $item = $this->generateItem($action);

            if (false === $found) {
                $found = $item->getName() === $lastIdentifier;
                continue;
            }

            $result[] = $item;
            if (count($result) >= $count) {
                break;
            }
        }

        if (!$found && null !== $lastIdentifier) {
            throw new InvalidCursorException($lastIdentifier);
        }
        
        return $result;
    }

    public function call(ToolCall $input): ToolCallResult
    {
        $actions = $this->getActions();
        foreach ($actions as $action) {
            $item = $this->generateItem($action);
            if ($item instanceof ToolExecutorInterface && $input->name === $item->getName()) {
                try {
                    return $item->call($input);
                } catch (\Throwable $e) {
                    throw new ToolExecutionException($input, $e);
                }
            }
        }

        throw new ToolNotFoundException($input);
    }

    /**
     * @return iterable<Tool>
     *
     * @throws ToolException if the metadata for the given reference is not found
     */
    public function getTool(string $reference): iterable
    {
        $actions = $this->getActions();
        $actions = array_filter($actions, function($action) use ($reference) {
            return $action === $reference;
        });
            
        $tools = [];
        
        $slugger = new AsciiSlugger();
        foreach ($actions as $action) {
            $item = $this->generateItem($action);
            $name = $item->getName();
            
            $tool = new Tool(
                new ExecutionReference(self::class, 'execute'),
                $slugger->slug($name),
                $item->getDescription(),
                $item->getInputSchema(),
            );
            $tools[] = $tool;
        }
        
        return $tools;
    }

    public function getActions(): array {
        return explode(',', $this->actions);
    }

    public function execute()
    {
        $name = func_get_args()[0];

        $actions = array_map(function($action) {
            return $this->generateItem($action);
        }, $this->getActions());
        $items = [];
        foreach($actions as $item) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($item->getName())->toString();

            if($slug === $name) {
                $items[] = $item;
            }
        };

        $item = $items[0];
        return $item->call(new ToolCall(uniqid(), $name, []));
    }

    private function generateItem(string $action) {
        // Get action data from Composio
        $actionData = $this->composio->actions->get(['actionName' => $action]);
        if (!$actionData) {
            throw new \RuntimeException("Action '{$action}' not found");
        }

        $name = $actionData['name'] ?? 'unknownAction';
        $description = $actionData['description'] ?? '';
        $parameters = $actionData['input_parameters']['properties'] ?? [];
        $composioToolSet = new ComposioToolSet($this->composio->http, $this->composio->apiKey, $this->composio->baseUrl, null, $this->entityId);

        return new class($action, $name, $description, $parameters, $this->composio, $composioToolSet, $this->entityId) implements MetadataInterface, ToolExecutorInterface {
            public function __construct(
                private string $action,
                private string $name,
                private string $description,
                private array $parameters,
                private Composio $composio,
                private ComposioToolSet $composioToolSet,
                private string $entityId
            ) {}

            public function getName(): string
            {
                return strtolower($this->name);
            }

            public function getDescription(): string
            {
                return $this->description;
            }

            public function getInputSchema(): array
            {
                $properties = [];
                $required = [];

                foreach ($this->parameters as $paramName => $paramData) {
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

            public function call(ToolCall $input): ToolCallResult
            {
                // Extract parameters from the tool call
                $params = $input->arguments ?? [];
                
                // Execute the action using Composio
                $result = $this->composioToolSet->execute_action($this->action, $params, $this->entityId);
                
                return new ToolCallResult(json_encode($result));
            }
        };
    }
}
