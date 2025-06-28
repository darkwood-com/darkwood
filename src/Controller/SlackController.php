<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlackController
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {}

    #[Route('/slack/callback', name: 'slack_callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');

        $url = 'https://slack.com/api/oauth.v2.access?' . http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        $json = file_get_contents($url);
        $data = json_decode($json, true);

        if ($data['ok'] ?? false) {
            $token = $data['access_token']; // xoxp- token
            return new Response("✅ Your Slack user token: " . $token);
        }

        return new Response("❌ Error: " . ($data['error'] ?? 'Unknown error'));
    }
}
