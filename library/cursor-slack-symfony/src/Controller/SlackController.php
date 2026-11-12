<?php

namespace App\Controller;

use Flow\FlowFactory;
use Flow\Ip;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlackController
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
        private string $slackToken,
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

    #[Route('/write-notification', name: 'slack_write_notification')]
    public function writeNotification(): Response
    {
        $flow = (new FlowFactory())->create(static function() {
            yield function($token) {
                $url = 'https://slack.com/api/conversations.list';

                $params = [
                    'types' => 'public_channel,private_channel,mpim,im', // to include all types
                    'limit' => 1000,
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $token,
                ]);

                $response = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($response, true);

                $channel = array_filter($data['channels'], function($item) {
                    return ($item['name'] ?? '') === 'social';
                });
                $channel = array_shift($channel);

                $channelId = $channel['id'];

                return [$token, $channelId];
            };
            yield function($data) {
                [$token, $channelId] = $data;
                $response = file_get_contents('https://slack.com/api/users.list', false, stream_context_create([
                    'http' => [
                        'header' => "Authorization: Bearer $token\r\n"
                    ]
                ]));

                $data = json_decode($response, true);

                $member = array_filter($data['members'], function($item) {
                    return ($item['name'] ?? '') === 'cursor';
                });
                $member = array_shift($member);
                return [$token, $channelId, $member];
            };
            yield function($data) {
                [$token, $channelId, $member] = $data;
                $message = sprintf('<@%s> write an hello world in php', $member['id']);

                $payload = json_encode([
                    'channel' => $channelId,
                    'text' => $message,
                ]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/chat.postMessage');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                ]);

                $response = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($response, true);

                if ($data['ok']) {
                    echo "✅ Message sent to channel {$channelId}\n";
                } else {
                    echo "❌ Error: " . $data['error'] . "\n";
                }
            };
        });
        $ip = new Ip($this->slackToken);
        $flow($ip);
        $flow->await();

        return new JsonResponse('ok');
    }
}
