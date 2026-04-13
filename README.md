# connect-flow

`connect-flow` is a Symfony 8 application exploring a connector-oriented execution model centered on:

- `LambdaExpression` as the domain language
- `LambdaCapability` as the execution-facing capability
- `LanguageCapability` as a local LLM overlay (`text -> lambda`)
- `darkwood/flow` as the runtime
- Composio as one backend for primitive/tool execution

This repository is currently a pragmatic MVP / phase-2 prototype. It is intentionally small and focused on a single vertical slice.

## Current architecture

The current runtime path is:

`natural language -> LanguageCapability -> LambdaExpression -> LambdaFlowRuntime -> Flow -> Composio primitives`

Key points:

- Lambda expressions are first-class domain objects.
- Flow executes the expression through `Flow\Job\LambdaJob`.
- `darkwood/navi` is kept in the runtime shell, not as the center of the domain.
- Composio is an infrastructure adapter behind `PrimitiveInvokerInterface`.
- Ollama + Mistral are used locally for GDPR-friendly text-to-lambda generation.

## Requirements

- PHP `>= 8.5`
- Composer
- Ollama running locally for `LanguageCapability`
- A Composio API key if you want to execute real primitives

## Install

```bash
composer install
cp .env .env.local
```

Then adjust the environment in `.env.local`.

## Important environment variables

```dotenv
COMPOSIO_API_KEY=
COMPOSIO_BASE_URL=https://backend.composio.dev/api
COMPOSIO_PRIMITIVES=send_email
COMPOSIO_PRIMITIVE_MAP={}
COMPOSIO_USER_ID=

OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=ministral-3:8b
```

Notes:

- `COMPOSIO_PRIMITIVES` lists the primitives injected into lambda evaluation as leading curried arguments.
- `COMPOSIO_PRIMITIVE_MAP` maps primitive names to Composio tool slugs.
- For handwritten or model-generated lambda expressions, prefer alphanumeric identifiers such as `sendemail` or `fetchemails`.
- The runtime normalizes configured primitive names before handing the expression to `Flow\Job\LambdaJob`.

## Lambda syntax accepted by the runtime

The runtime uses `Flow\Job\LambdaJob`, which accepts a small lambda syntax:

- Unicode `λ` for abstraction
- alphanumeric identifiers only
- `.`, `(`, `)`, and spaces
- application by adjacency or grouping

Good examples:

```text
λx.x
λsendemail.λpayload.sendemail(payload)
λfetchdata.λquery.fetchdata(query)
```

Avoid:

- underscores or hyphens in identifiers inside the lambda source
- JSON, arrays, object literals, or markdown
- inline literals such as `(λx.x)(5)` for generated expressions

## Console commands

Translate natural language to lambda:

```bash
php bin/console connect-flow:language:to-lambda "fetch my latest emails"
```

Run a lambda expression through Flow:

```bash
php bin/console connect-flow:lambda:run 'λx.x' --args='[5]'
```

Run a primitive-backed expression:

```bash
php bin/console connect-flow:lambda:run 'λsendemail.λpayload.sendemail(payload)' \
  --args='[{"to":"team@example.com","subject":"Hello"}]' \
  --user='your-composio-user-id'
```

## Examples

The repository includes small runnable examples:

```bash
php examples/simple_lambda.php
php examples/lambda_tool.php
php examples/text_to_lambda.php
```

They are useful to understand the shape of the runtime without booting the whole Symfony app.

## Tests

Run the test suite with:

```bash
php bin/phpunit
```

Or via Composer:

```bash
composer test
```

## Project structure

```text
src/
  Application/    Small orchestration services around evaluation
  Command/        Symfony CLI entrypoints
  Domain/         Lambda, capabilities, execution context, primitive contracts
  Infrastructure/ AI, Composio, Flow, ExpressionLanguage adapters
examples/         Small standalone scripts
tests/            Unit and integration-level tests for the MVP slice
```

## Status

This project is not a full multi-provider orchestration platform yet.

Out of scope for the current phase:

- multi-provider abstraction
- plugin systems
- advanced Flow orchestration
- Church/Lambda research-grade modeling
- distributed agents
- non-local LLM orchestration

The goal right now is a credible, executable foundation around:

`Lambda -> Capability -> Execution`
