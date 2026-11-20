# Thumbnail MCP Tool

A Replicate-based tool for generating thumbnail images via the MCP protocol.

## Setup

1. Make sure you have a Replicate API key
2. Set the environment variable `REPLICATE_API_KEY` with your key
3. Install dependencies:
   ```
   composer install
   ```

## Testing the Tool

### Method 1: Using the Mock Test

This method uses a mocked platform to test the tool structure without making real API calls:

```bash
php test_thumbnail.php
```

### Method 2: Using the Real API

This method makes actual API calls to Replicate:

```bash
REPLICATE_API_KEY=your_api_key php test_real.php
```

### Method 3: Using the Symfony Command

This is the most realistic test as it uses the Symfony DI container:

```bash
# Make sure you have the API key in .env.local
bin/console app:test-thumbnail "Your Name"
```

## MCP clients

The STDIO entry point is `php bin/console mcp:server`. For Streamable HTTP use
`/mcp` with a client that supports that transport. Clients using the legacy
SSE handshake at `/sse` and `/messages/{id}` must switch to Streamable
HTTP or STDIO. The HTTP allow-list defaults to localhost. Configure specific
hostnames in `mcp.servers.thumbnail_generator.http.allowed_hosts` when hosting
the server remotely.

Both tools accept a required string `prompt`.

- `generate_thumbnail_image` returns the original and styled image URLs.
- `generate_ghibli_image` returns the styled image URL.
