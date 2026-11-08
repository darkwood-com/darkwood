<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class MemoryExamples
{
    public const array DEMOS = [
        'darkwood' => ['Darkwood', 'La trace d’une décision', 'Retrouver le pourquoi, la source et les révisions d’un choix.', 'Connaissances'],
        'flow' => ['Flow', 'Trois durées de mémoire', 'Observer ce qui disparaît, se réutilise et reste disponible.', 'Exécution'],
        'uniflow' => ['Uniflow', 'La recette et son histoire', 'Mettre en pause un processus, puis reprendre la même exécution.', 'Processus'],
        'darkwaar' => ['Darkwaar', 'La chambre des retours', 'Le monde recommence. Ce que vous avez appris reste.', 'Jeu'],
        'djmatyo' => ['DJMatyo', 'Un motif, plusieurs vies', 'Composer huit pas, les écouter et retrouver son motif.', 'Musique'],
        'heroclip' => ['Heroclip', 'D’un plan à l’autre', 'Transmettre les repères d’un personnage à la scène suivante.', 'Récit'],
    ];

    public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDir) {}

    public function flowLibrary(): array
    {
        $file = $this->projectDir.'/examples/flow-memory.php';
        return ['result' => require $file, 'source' => file_get_contents($file)];
    }

    public function decision(string $id): ?array
    {
        $decisions = json_decode(file_get_contents($this->projectDir.'/data/decisions.json'), true, flags: JSON_THROW_ON_ERROR);
        return $decisions[$id] ?? null;
    }

    public function workflow(): array
    {
        return json_decode(file_get_contents($this->projectDir.'/data/workflow.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function calculation(int $n): array
    {
        $calls = 0;
        $compute = static function () use ($n, &$calls): int {
            ++$calls;
            return array_sum(range(1, $n));
        };
        $temporary = $compute();
        $memo = [];
        $memoCalls = 0;
        $cached = static function () use (&$memo, &$memoCalls, $n): int {
            if (!isset($memo[$n])) {
                ++$memoCalls;
                $memo[$n] = array_sum(range(1, $n));
            }
            return $memo[$n];
        };
        return ['n' => $n, 'temporary' => $temporary, 'calls' => $calls, 'first' => $cached(), 'second' => $cached(), 'memoCalls' => &$memoCalls];
    }

    public function act(string $slug, string $action, array $input, SessionInterface $session): void
    {
        if ('flow' === $slug) {
            if ('save' === $action) {
                $n = $input['n'];
                $session->set('flow_result', ['n' => $n, 'result' => array_sum(range(1, $n))]);
            } elseif ('clear' === $action) {
                $session->remove('flow_result');
            }
        } elseif ('uniflow' === $slug) {
            $runs = $session->get('runs', []);
            if ('clear' === $action) {
                $session->remove('runs');
                return;
            }
            if ('start' === $action) {
                $definition = $this->workflow();
                $total = array_sum($definition['input']);
                $runs[] = ['id' => bin2hex(random_bytes(4)), 'version' => $definition['version'], 'cursor' => 2, 'status' => 'En pause', 'events' => ['Collecter : '.count($definition['input']).' valeurs ['.implode(', ', $definition['input']).']', 'Additionner : total '.$total], 'total' => $total];
                $runs = array_slice($runs, -12);
            } elseif ('resume' === $action && [] !== $runs) {
                $last = array_key_last($runs);
                if (2 === $runs[$last]['cursor']) {
                    $runs[$last]['events'][] = 'Produire le bilan : '.$runs[$last]['total'].' points';
                    $runs[$last]['cursor'] = 3;
                    $runs[$last]['status'] = 'Terminée';
                }
            }
            $session->set('runs', $runs);
        } elseif ('darkwaar' === $slug) {
            $game = $session->get('game', ['knowledge' => false, 'phase' => 'room', 'loops' => 0]);
            if ('observe' === $action) {
                $game['knowledge'] = true;
                $game['phase'] = 'inscription';
            } elseif ('rewind' === $action) {
                $game['phase'] = 'room';
                ++$game['loops'];
            } elseif ('forget' === $action) {
                $game = ['knowledge' => false, 'phase' => 'room', 'loops' => 0];
            } elseif (in_array($action, ['moon', 'star', 'sun'], true)) {
                $game['phase'] = 'moon' === $action && $game['knowledge'] ? 'won' : 'closed';
            }
            $session->set('game', $game);
        } elseif ('heroclip' === $slug) {
            if ('clear' === $action) {
                $session->remove('scenes');
                return;
            }
            $scenes = $session->get('scenes', []);
            if (count($scenes) >= 6) {
                return;
            }
            $previous = $scenes ? $scenes[array_key_last($scenes)] : null;
            $continuity = $input['memory'];
            $scenes[] = [
                'character' => $previous && $continuity ? $previous['character'] : ($previous ? 'Voyageur' : $input['character']),
                'palette' => $previous && $continuity ? $previous['palette'] : ($previous ? 'bleu' : $input['palette']),
                'prop' => $previous && $continuity ? $previous['prop'] : ($previous ? 'boussole' : 'lanterne'),
                'place' => ['La porte', 'Le pont', 'La forêt', 'La tour', 'Le sommet', 'Le retour'][count($scenes)],
                'parent' => count($scenes),
                'recalled' => null !== $previous && $continuity,
            ];
            $session->set('scenes', $scenes);
        }
    }
}
