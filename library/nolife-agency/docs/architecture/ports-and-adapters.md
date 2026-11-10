# Ports and Adapters

Core boundaries:

- `App\Integration\Ai\AiAgentClient`
- `App\Integration\Flow\FlowWorkflowClient`
- `App\Integration\Navi\NaviTraceClient`
- `App\Integration\Media\MediaGenerationClient`

Null adapters keep local runs deterministic and testable without external services.

Concrete adapters are swappable by Symfony service aliases.
