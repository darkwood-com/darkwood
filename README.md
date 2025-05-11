# Composio PHP MCP Server

## What do we do?

- Create a Composio MCP server with https://github.com/php-mcp/server

```bash
composer create-project symfony/skeleton
composer require php-mcp/server
```

- Add tools: Gmail and Google Calendar

Get your api key on https://composio.dev

Add actions :
```bash
make nix
php bin/console app:generate-tools \
    --entityId default \
    --action GMAIL_FETCH_EMAILS \
    --action GMAIL_FETCH_MESSAGE_BY_MESSAGE_ID \
    --action GMAIL_FETCH_MESSAGE_BY_THREAD_ID \
    --action GMAIL_SEND_EMAIL \
    --action GOOGLECALENDAR_CREATE_EVENT \
    --action GOOGLECALENDAR_FIND_EVENT \
    --action GOOGLECALENDAR_FIND_FREE_SLOTS \
    --action GOOGLECALENDAR_GET_CALENDAR \
    --action GOOGLECALENDAR_GET_CURRENT_DATE_TIME \
    --action GOOGLECALENDAR_LIST_CALENDARS \
    --action GOOGLECALENDAR_QUICK_ADD
```

Composio PHP Sdk is inspired from the official Composio Javascript SDK : `https://github.com/ComposioHQ/composio-js`

Debug MCP server

```bash
npx @modelcontextprotocol/inspector node build/index.js
```

- Create an agent with neuron AI for Uniflow PHP Client

More info https://www.neuron-ai.dev

- Execute the workflow with Uniflow

flows :

- object :

env.OPENAI_API_KEY=
REDACTED

- text :
variable : prompt
```
I would like to respond to important emails.

Please find important unread emails in my inbox and summarize them here (leave out details, because people are watching).

Then, find a free slot this week (May 6 2025) in my calendar that would be ideal to respond to ALL important emails and create a calendar event.
```

- javascript :

```
agent(prompt)
```

## Resources

Composio PHP Sdk is inspired from the official Composio Javascript SDK : `https://github.com/ComposioHQ/composio-js`

