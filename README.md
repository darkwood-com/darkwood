# Joplin API Integration

A Symfony-based PHP application that provides seamless integration with Joplin's Web Clipper API. This tool allows you to create tasks and notes in Joplin directly from the command line, with support for customizable templates and project types.

## Features

- **Joplin Authentication**: Secure OAuth-like authorization flow with Joplin desktop application
- **Task Creation**: Create structured tasks with predefined templates
- **Template System**: Built-in templates for meetings, calls, and follow-ups
- **Project Types**: Support for different project structures (Skeleton, MCP Bundle, etc.)
- **Command Line Interface**: Easy-to-use Symfony console commands
- **Real-time Authorization**: Interactive authorization process with timeout handling

## Prerequisites

- PHP 8.2 or higher
- Composer
- Joplin desktop application with Web Clipper enabled
- Symfony CLI (optional, for development)

## Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd joplin-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your Joplin configuration
   ```

4. **Set up Joplin Web Clipper**
   - Open Joplin desktop application
   - Go to Tools > Web Clipper
   - Enable the Web Clipper
   - Note the port number (default: 41184)

## Configuration

### Environment Variables

Create a `.env` file with the following variables:

```env
# Joplin Web Clipper settings
JOPLIN_HOST=http://localhost:41184
JOPLIN_TOKEN=your_auth_token_here
```

### Joplin Authorization

Before using the API, you need to authorize the application:

```bash
# Run the authorization command
php bin/console app:joplin:authorize
```

This will:
1. Request authorization from Joplin
2. Show a prompt in your Joplin desktop app
3. Wait for you to accept the request
4. Provide the token to add to your `.env` file

## Usage

### Available Commands

#### 1. Joplin Authorization
```bash
php bin/console app:joplin:authorize
```
Authorizes the application to access your Joplin instance.

#### 2. Create Task
```bash
php bin/console app:joplin:create-task [title] [template] [options]
```

**Arguments:**
- `title`: Task title (optional, will prompt if not provided)
- `template`: Template type (empty, meeting, call, follow-up)

**Options:**
- `--folder, -f`: Folder ID where to create the task
- `--emoji, -emo`: Emoji to use for the task
- `--project-type, -p`: Type of project to create

**Examples:**
```bash
# Create a meeting task
php bin/console app:joplin:create-task "Team Standup" meeting

# Create a task with custom options
php bin/console app:joplin:create-task "Review PR" empty --emoji "🔍" --project-type skeleton

# Interactive mode
php bin/console app:joplin:create-task
```

### Task Templates

The application includes several predefined templates:

- **Empty**: Basic task without predefined structure
- **Meeting**: Structured template with date, location, attendees, agenda, and action items
- **Call**: Template for phone/video calls with notes and action items
- **Follow-up**: Template for follow-up tasks with due dates and context

### Project Types

Supported project types:

- **None**: Standard Joplin note
- **Skeleton**: Symfony skeleton project structure
- **MCP**: Symfony project with php-llm/mcp-bundle integration

## Development

### Project Structure

```
src/
├── Command/           # Console commands
│   ├── JoplinAuthorizeCommand.php
│   └── JoplinCreateTaskCommand.php
├── Enum/             # Enums for templates and project types
│   ├── ProjectType.php
│   └── TaskTemplate.php
├── Service/          # Core business logic
│   └── JoplinService.php
└── Kernel.php        # Application kernel
```

### Adding New Templates

To add a new task template:

1. Add a new case to `TaskTemplate` enum
2. Implement the `getBody()` method with your template structure
3. The template will automatically be available in the CLI

### Adding New Project Types

To add a new project type:

1. Add a new case to `ProjectType` enum
2. Implement the `getDescription()` method
3. Handle the new type in your task creation logic

### Testing

```bash
# Run PHPUnit tests
composer test

# Run PHPStan for static analysis
composer phpstan

# Run PHP CS Fixer
composer php-cs-fixer
```

## Troubleshooting

### Common Issues

1. **"Could not connect to Joplin"**
   - Ensure Joplin desktop app is running
   - Check that Web Clipper is enabled
   - Verify the port number in your configuration

2. **Authorization timeout**
   - Check Joplin desktop app for authorization prompts
   - Ensure you accept the authorization request within 60 seconds

3. **Invalid token errors**
   - Re-run the authorization command
   - Update your `.env` file with the new token

### Debug Mode

Enable debug mode for more detailed error information:

```bash
APP_ENV=dev php bin/console app:joplin:create-task
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is licensed under the [MIT](LICENSE.md) license.

## Support

For issues and questions:
- Check the troubleshooting section above
- Review Joplin's Web Clipper documentation
- Open an issue on the project repository

## Related Links

- [Joplin Official Website](https://joplinapp.org/)
- [Joplin Web Clipper Documentation](https://joplinapp.org/clipper/)
- [Symfony Console Component](https://symfony.com/doc/current/components/console.html)
