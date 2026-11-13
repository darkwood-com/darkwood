<?php

namespace App\Support;

use App\Models\ExternalPublication;
use App\Models\Presentation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PublicationAssociations
{
    public function __construct(private PublicationProviders $providers) {}

    /** Manual confirmation proves the editorial association, not remote PDF equality. */
    public function associate(Presentation $presentation, string $provider, string $remoteId, string $url, bool $confirmed): ExternalPublication
    {
        if (! $confirmed) {
            throw new InvalidArgumentException('The editorial correspondence must be explicitly confirmed.');
        }
        $this->providers->validateIdentity($provider, $remoteId, $url);

        return DB::transaction(function () use ($presentation, $provider, $remoteId, $url) {
            $existing = $presentation->publications()->where('provider', $provider)->first();
            if ($existing && $existing->remote_id !== $remoteId) {
                throw new InvalidArgumentException('Already associated: refusing to replace a remote identity.');
            }
            if ($existing) {
                $existing->update(['remote_url' => $url, 'remote_slug' => basename(parse_url($url, PHP_URL_PATH))]);

                return $existing;
            }

            return $presentation->publications()->create([
                'provider' => $provider, 'remote_id' => $remoteId, 'remote_url' => $url,
                'remote_slug' => $provider === 'speakerdeck' ? basename(parse_url($url, PHP_URL_PATH)) : explode('/', trim(parse_url($url, PHP_URL_PATH), '/'))[1],
                'status' => 'manual_verification_required', 'source_hash' => null,
            ]);
        });
    }
}
