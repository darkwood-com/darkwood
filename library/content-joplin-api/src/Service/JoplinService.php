<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_key_exists;

class JoplinService
{
    private string $token;

    public function __construct(
        private HttpClientInterface $joplinClient,
        string $joplinToken
    ) {
        $this->token = $joplinToken;
    }

    /**
     * Request authorization from Joplin.
     *
     * @return string The auth token to be used for checking authorization status
     *
     * @throws Exception If the authorization request fails
     */
    public function requestAuthorization(): string
    {
        $response = $this->joplinClient->request('POST', '/auth');
        $data = $response->toArray();

        if (!isset($data['auth_token'])) {
            throw new Exception('Failed to get auth token from Joplin');
        }

        return $data['auth_token'];
    }

    /**
     * Check the status of an authorization request.
     *
     * @param string $authToken The auth token received from requestAuthorization()
     *
     * @return array{status: string, token?: string} The authorization status and token if accepted
     *
     * @throws Exception If the check request fails
     */
    public function checkAuthorization(string $authToken): array
    {
        $response = $this->joplinClient->request('GET', '/auth/check', [
            'query' => ['auth_token' => $authToken],
        ]);

        return $response->toArray();
    }

    /**
     * Wait for user authorization with a timeout.
     *
     * @param string $authToken            The auth token received from requestAuthorization()
     * @param int    $timeoutSeconds       Maximum time to wait in seconds
     * @param int    $checkIntervalSeconds How often to check the status in seconds
     *
     * @return string The API token if authorized
     *
     * @throws Exception If authorization is rejected or times out
     */
    public function waitForAuthorization(string $authToken, int $timeoutSeconds = 60, int $checkIntervalSeconds = 2): string
    {
        $startTime = time();

        while (time() - $startTime < $timeoutSeconds) {
            $result = $this->checkAuthorization($authToken);

            if ($result['status'] === 'accepted' && isset($result['token'])) {
                return $result['token'];
            }

            if ($result['status'] === 'rejected') {
                throw new Exception('Authorization was rejected by the user');
            }

            sleep($checkIntervalSeconds);
        }

        throw new Exception('Authorization request timed out');
    }

    public function ping(): bool
    {
        try {
            $response = $this->joplinClient->request('GET', '/ping', [
                'query' => ['token' => $this->token],
            ]);

            return $response->getContent() === 'JoplinClipperServer';
        } catch (Exception $e) {
            return false;
        }
    }

    public function createNote(string $title, string $body, ?string $parentId = null): array
    {
        $data = [
            'title' => $title,
            'body' => $body,
        ];

        if ($parentId) {
            $data['parent_id'] = $parentId;
        }

        $response = $this->joplinClient->request('POST', '/notes', [
            'query' => ['token' => $this->token],
            'json' => $data,
        ]);

        return $response->toArray();
    }

    public function getNote(string $noteId): array
    {
        $response = $this->joplinClient->request('GET', "/notes/{$noteId}", [
            'query' => ['token' => $this->token],
        ]);

        return $response->toArray();
    }

    /**
     * Update the content of a note by its ID.
     *
     * @return array The updated note data
     */
    public function updateNote(string $noteId, string $content): array
    {
        $response = $this->joplinClient->request('PUT', "/notes/{$noteId}", [
            'query' => ['token' => $this->token],
            'json' => [
                'body' => $content,
            ],
        ]);

        return $response->toArray();
    }

    public function getFolders(): array
    {
        $response = $this->joplinClient->request('GET', '/folders', [
            'query' => ['token' => $this->token],
        ]);

        return $response->toArray();
    }

    /**
     * Parse all markdown blocks like :::lang-FR ... :::, :::note ... :::, etc.
     *
     * @return array<int, array{type: string, content: string}>
     */
    public function getNoteBlocks(string $markdown): array
    {
        $pattern = '/:::(\w+(?:-\w+)*)\n([\s\S]*?)\n:::/m';
        preg_match_all($pattern, $markdown, $matches, PREG_SET_ORDER);

        $blocks = [];
        foreach ($matches as $match) {
            $type = $match[1]; // e.g., lang-FR, note, warning
            $content = trim($match[2]);
            $blocks[] = [
                'type' => $type,
                'content' => $content,
            ];
        }

        return $blocks;
    }

    /**
     * Replace the first block of each given type with new content in the markdown.
     */
    public function updateNoteBlocks(string $markdown, array $blockTypes, array $newContents): string
    {
        foreach ($blockTypes as $i => $blockType) {
            if (!array_key_exists($i, $newContents)) {
                continue;
            }
            $pattern = '/:::' . preg_quote($blockType, '/') . '\n([\s\S]*?)\n:::/m';
            $replacement = ":::{$blockType}\n{$newContents[$i]}\n:::";
            $markdown = preg_replace($pattern, $replacement, $markdown, 1);
        }

        return $markdown;
    }
}
