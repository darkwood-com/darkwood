<?php

namespace App\Console\Commands;

use App\Models\Presentation;
use App\Support\CanonicalRevision;
use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublicationReportCommand extends Command
{
    protected $signature = 'publications:report {--missing} {--output= : Write report to a file} {--html : Render the full mapping table as HTML}';

    protected $description = 'List all canonical identities, provider mappings and current validated PDF hashes';

    public function handle(CanonicalRevision $revision): int
    {
        $current = $revision->hash();
        app(PublicPublicationManifest::class)->snapshot(); // Validate every public remote identity before rendering.
        $metadata = File::exists(resource_path('data/presentation-metadata.json')) ? json_decode(File::get(resource_path('data/presentation-metadata.json')), true) : [];
        $preflight = File::exists(storage_path('app/design-system/pdf-readiness.json')) ? json_decode(File::get(storage_path('app/design-system/pdf-readiness.json')), true) : [];
        $rows = [];
        foreach (Presentation::with('publications')->orderBy('source_slug')->get() as $deck) {
            $exports = [];
            $root = storage_path('app/publications/'.$deck->id);
            foreach (glob($root.'/*/pdf-validation.json') ?: [] as $file) {
                $manifest = json_decode(File::get(dirname($file).'/manifest.json'), true);
                $validation = json_decode(File::get($file), true);
                $pdf = dirname($file).'/presentation.pdf';
                if (($manifest['revision'] ?? null) === $current && is_file($pdf) && hash_file('sha256', $pdf) === ($validation['sha256'] ?? null)) {
                    $exports[] = array_intersect_key($validation, array_flip(['pages', 'canonical_slides', 'sha256', 'width_pt', 'height_pt']));
                }
            }
            $providers = [];
            foreach (['speakerdeck', 'slideshare'] as $provider) {
                $publication = $deck->publications->firstWhere('provider', $provider);
                $public = $publication?->remote_id ? array_intersect_key($publication->toArray(), array_flip(PublicPublicationManifest::FIELDS)) : [];
                $status = in_array($publication?->status, ['pending', 'not_published', 'published', 'up_to_date', 'update_required', 'update_available', 'manual_action_required', 'manual_verification_required', 'failed', 'published_cache_pending', 'imported_public_manifest'], true) ? $publication->status : 'pending';
                $providers[$provider] = $public + ['status' => $status, 'remote_id' => null, 'remote_url' => null, 'last_error' => $publication?->last_error ? 'publication_action_required' : null];
                $hashes = array_column($exports, 'sha256');
                $providers[$provider]['computed_status'] = $publication?->remote_id && $hashes
                    ? (in_array($publication->source_hash, $hashes, true) ? 'up_to_date' : 'update_required')
                    : $status;
            }
            if ($this->option('missing') && collect($providers)->every(fn ($p) => ! empty($p['remote_id']))) {
                continue;
            }
            $rows[] = ['slide_id' => $deck->id, 'slug' => $deck->source_slug, 'title' => $deck->title,
                'slide_count' => $deck->slide_count, 'route' => '/slides/'.$deck->source_slug,
                'metadata' => $metadata[$deck->id] ?? null,
                'source_layout_warnings' => array_map(fn ($row) => ['slide' => $row['slide']], $preflight[$deck->source_slug]['blocked'] ?? []),
                'pdf_preflight' => $exports ? [] : array_map(fn ($row) => ['slide' => $row['slide']], $preflight[$deck->source_slug]['blocked'] ?? []),
                'validated_current_exports' => $exports, 'providers' => $providers];
        }
        $json = json_encode(['revision' => $current, 'presentations' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $output = $this->option('html') ? $this->html($rows) : $json;
        if ($this->option('output')) {
            File::put($this->option('output'), $output);
        }
        $this->line($output);

        return self::SUCCESS;
    }

    private function html(array $rows): string
    {
        $escape = fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<!doctype html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Publications Slidewire</title><style>body{font:15px system-ui;margin:32px;color:#171717;background:white}h1{font-size:24px}table{border-collapse:collapse;width:100%;margin:24px 0}th,td{text-align:left;vertical-align:top;padding:12px;border-bottom:1px solid #ddd}th{position:sticky;top:0;background:white}code{font-size:12px;overflow-wrap:anywhere}a{color:#007bff}small{display:block;margin-top:8px}details{margin-top:8px}</style><h1>Publications Slidewire</h1><p>Source : registre Laravel réel, '.count($rows).' présentations. Rapport généré le '.$escape(now()->toIso8601String()).'. Les champs vides restent inconnus. Les blocages PDF et provider ne constituent pas des publications réussies.</p><table><thead><tr><th>Présentation</th><th>ID Slidewire</th><th>Speaker Deck</th><th>ID distant</th><th>SlideShare</th><th>ID distant</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr><td><strong>'.$escape($row['metadata']['cover_title'] ?? $row['title']).'</strong><small>'.$escape($row['slug']).' · '.$row['slide_count'].' slides · '.$escape($row['metadata']['author'] ?? 'Auteur non renseigné').'</small><small>Route : '.$escape($row['route']).'</small>';
            if ($row['validated_current_exports']) {
                $html .= '<small>'.$row['validated_current_exports'][0]['pages'].' pages PDF validées</small>';
            }
            if ($row['pdf_preflight']) {
                $html .= '<small>PDF à reprendre : slides '.$escape(implode(', ', array_column($row['pdf_preflight'], 'slide'))).'</small>';
            }
            $html .= '</td><td><code>'.$escape($row['slide_id']).'</code></td>';
            foreach ($row['providers'] as $provider) {
                $html .= '<td>'.$escape($provider['computed_status']);
                if ($provider['remote_url']) {
                    $html .= '<small><a href="'.$escape($provider['remote_url']).'">Publication publique</a></small>';
                }
                if ($provider['last_error'] ?? null) {
                    $html .= '<small>'.$escape($provider['last_error']).'</small>';
                }
                if ($provider['source_hash'] ?? null) {
                    $html .= '<details><summary>SHA-256 publié</summary><code>'.$escape($provider['source_hash']).'</code></details>';
                }
                $html .= '</td><td><code>'.$escape($provider['remote_id'] ?? '—').'</code></td>';
            }
            $html .= '</tr>';
        }

        return $html.'</tbody></table></html>';
    }
}
