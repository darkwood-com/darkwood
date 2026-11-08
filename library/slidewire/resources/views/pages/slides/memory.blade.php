<x-darkwood.deck transition="fade">
@php
$flowCode = <<<'PHP'
yield static function (array $state) use (&$calculations): array {
    ++$calculations;
    return $state + ['total' => array_sum($state['values'])];
};
// Extraits du job d’addition et du lancement du pipeline :
$flow(new Ip(['values' => [4, 7, 9]]));
$flow->await();
PHP;
@endphp
<x-darkwood.cover class="memory-slide" transition="fade" id="memory-01" title="Mémoire : ce qui rend la suite possible" subtitle="Du logiciel à l’IA, des jeux vidéo à la musique et à l’image : ce que nous conservons pour continuer à créer." date="08-11-2026" background="black" />
<x-darkwood.content class="memory-slide" transition="fade" id="memory-02" title="Que faut-il conserver ?" overline="Mémoire" label="02 / 22 · Darkwood">
<p class="memory-question">Pour que quelque chose puisse continuer.</p><div class="memory-chain"><span>Reprendre</span><span aria-hidden="true">→</span><span>Comprendre</span><span aria-hidden="true">→</span><span>Transformer</span></div><p>Une information utile, à la bonne échelle et pour la bonne durée.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-03" title="Retrouver une décision" overline="Darkwood" label="03 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Identifiant</h2><p>Une adresse stable malgré les changements de titre.</p></div><div><h2>Provenance</h2><p>Une date, une origine et un statut compréhensibles.</p></div><div><h2>Versions</h2><p>Le chemin qui explique un choix et ses corrections.</p></div></div>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-04" title="MEM-001 : garder de quoi reprendre" overline="Darkwood" label="04 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Version 1 · 10 janvier</h2><p>Conserver uniquement le rendu audio.</p></div><div><h2>Version 2 · 12 janvier</h2><p>Garder aussi notes, tempo et paramètres pour créer une variante.</p></div></div><p class="memory-note">Décision fictive de l’atelier : les raisons du changement restent lisibles.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-05" title="Le contexte des personnes et des agents" overline="Darkwood" label="05 / 22 · Darkwood">
<div class="memory-chain"><span>Question</span><span aria-hidden="true">→</span><span>Référence validée</span><span aria-hidden="true">→</span><span>Action</span></div><p>Un résumé doit permettre de revenir au détail.</p><p class="memory-note">Une information retrouvée ne vaut ni validation ni autorisation. Aucune mémoire universelle des six services n’est annoncée.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-06" title="Un tableau qui s’enrichit" overline="Flow" label="06 / 22 · Darkwood">
<div class="memory-chain"><div><h2>Additionner</h2><p>4 + 7 + 9</p><p class="memory-result">total : 20</p></div><span aria-hidden="true">→</span><div><h2>Moyenne</h2><p>20 ÷ 3</p><p class="memory-result">6,67</p></div><span aria-hidden="true">→</span><div><h2>Reste</h2><p>100 − 20</p><p class="memory-result">80</p></div></div><p>Le total accompagne l’état. Il est calculé une seule fois.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-07" title="Le véritable exemple PHP" overline="Flow" label="07 / 22 · Darkwood">
<x-darkwood.code :source="$flowCode" language="php" title="Extrait de examples/flow-memory.php" /><p class="memory-note">FlowFactory assemble les jobs · darkwood/flow 8.1.6<br>PHP exécuté pendant le build ; pipeline complet dans Memory.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-08" title="Trois durées de mémoire" overline="Flow" label="08 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Temporaire</h2><p>L’état existe pendant une évaluation.</p></div><div><h2>Réutilisé</h2><p>Deux lectures partagent un calcul dans cette évaluation.</p></div><div><h2>Conservé</h2><p>Le résultat survit grâce à un stockage explicitement choisi.</p></div></div><p class="memory-note">Flow transmet l’état ; il ne fournit ici ni sauvegarde durable ni reprise après panne. L’atelier navigateur est une illustration JavaScript.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-09" title="La méthode enregistrée" overline="Uniflow" label="09 / 22 · Darkwood">
<div class="memory-chain"><span>Collecter<br>12, 8, 5</span><span aria-hidden="true">→</span><span>Additionner<br>25</span><span aria-hidden="true">→</span><span>Produire<br>le bilan</span></div><p>« Préparer un bilan » · définition atelier-bilan · version 1</p><p class="memory-note">La recette reste la même à chaque nouveau lancement.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-10" title="Une exécution peut s’arrêter" overline="Uniflow" label="10 / 22 · Darkwood">
<ol class="memory-timeline"><li>Collecte terminée : 3 valeurs</li><li>Addition terminée : total 25 — pause</li><li>À la reprise : produire le bilan de 25 points</li></ol><p>Le curseur indique l’étape suivante. Le résultat intermédiaire permet de poursuivre.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-11" title="La trace et le point de reprise" overline="Uniflow" label="11 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Dans Uniflow</h2><p>Des programmes structurés sont enregistrés avec description, classement et visibilité.</p></div><div><h2>Dans Memory</h2><p>Une expérience locale conserve un total et un curseur. La reprise ne rejoue pas les étapes terminées.</p></div></div><p class="memory-note">Cette démonstration ne prouve pas l’existence d’un historique exhaustif ou d’une reprise durable dans Uniflow en production.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-12" title="Aftertrace : apprendre la rive" overline="Darkwaar" label="12 / 22 · Darkwood">
<div class="memory-game"><img src="/images/memory/aftertrace-failure.png" alt="Capture réelle d’Aftertrace : une fissure interrompt la tentative" width="648" height="1152"><div><h2>Explorer les mains vides</h2><p>Une grille cachée de 4 × 4 cases.</p><p>Un signal à trouver, des pas limités, une sortie à atteindre.</p><p>Une fissure arrête la tentative et laisse une découverte.</p></div></div>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-13" title="Rewind : le monde revient" overline="Darkwaar" label="13 / 22 · Darkwood">
<div class="memory-game"><img src="/images/memory/aftertrace-route.png" alt="Capture réelle d’Aftertrace : la route reconnue garde les cases sûres et la fissure" width="648" height="1152"><div><h2>La carte reste connue</h2><p>La position et les pas sont réinitialisés.</p><p>Les cases découvertes restent marquées.</p><p>Le joueur peut préparer le prochain trajet.</p></div></div>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-14" title="Carry : utiliser ce que l’on sait" overline="Darkwaar" label="14 / 22 · Darkwood">
<div class="memory-game"><img src="/images/memory/aftertrace-delivery.png" alt="Capture réelle d’Aftertrace : la braise atteint la sortie" width="648" height="1152"><div><h2>Repartir avec une braise</h2><p>Après la reconnaissance, refaire le trajet appris.</p><p>Trois rives : chaque nouveau lieu efface la carte.</p><p class="memory-note">Forget efface les découvertes courantes. Recharger le jeu efface la mémoire de partie.</p></div></div>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-15" title="Huit pas pour garder une idée" overline="DJMatyo" label="15 / 22 · Darkwood">
<div class="memory-score" role="img" aria-label="Motif réel de l’atelier : notes 4, silence, 3, silence, 2, 3, 1, silence"><div><span>1</span><p class="memory-note-on">4</p></div><div><span>2</span><p class="memory-silent">—</p></div><div><span>3</span><p class="memory-note-on">3</p></div><div><span>4</span><p class="memory-silent">—</p></div><div><span>5</span><p class="memory-note-on">2</p></div><div><span>6</span><p class="memory-note-on">3</p></div><div><span>7</span><p class="memory-note-on">1</p></div><div><span>8</span><p class="memory-silent">—</p></div></div><p>Notes, silences, tempo : un motif que l’on peut réécouter et modifier.</p><p class="memory-note">Synthèse originale du navigateur, sans sample tiers. Un atelier de mémoire musicale, pas un morceau attribué à DJMatyo.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-16" title="Le même motif, un autre départ" overline="DJMatyo" label="16 / 22 · Darkwood">
<p>La variation de l’atelier décale la séquence d’un pas.</p><div class="memory-sequence"><span>Initial</span><code>4 · — · 3 · — · 2 · 3 · 1 · —</code><span>Variation</span><code>— · 4 · — · 3 · — · 2 · 3 · 1</code></div><p>Comparer ce qui reste reconnaissable quand la place des événements change.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-17" title="Conserver une structure musicale" overline="DJMatyo" label="17 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Rejouer</h2><p>Le fichier JSON conserve notes et tempo, sans enregistrement audio.</p></div><div><h2>Transformer</h2><p>La version gardée devient le point de comparaison d’une variante.</p></div></div><p class="memory-note">L’identité Happy Hardcore de DJMatyo ouvre une réflexion sur répétition, énergie et reconnaissance. L’atelier ne documente pas ses outils de studio.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-18" title="Ce que reçoit le prochain plan" overline="Heroclip" label="18 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Avec rappel</h2><p>Nora · ambre · lanterne<br>→ Nora · ambre · lanterne</p></div><div><h2>Sans rappel</h2><p>Nora · ambre · lanterne<br>→ Voyageur · bleu · boussole</p></div></div><p class="memory-note">Démonstration Memory : storyboard déterministe, six plans maximum, sans génération vidéo.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-19" title="Une sélection de storyboards" overline="Heroclip" label="19 / 22 · Darkwood">
<div class="memory-chain"><span>Jusqu’à 3<br>générations réussies</span><span aria-hidden="true">→</span><span>Storyboards<br>sélectionnés</span><span aria-hidden="true">→</span><span>Préparation<br>de la suite</span></div><p>Le mécanisme de continuation est présent dans le code d’Heroclip.</p><p class="memory-note">Certains champs sont raccourcis. Une dernière image peut servir de référence lorsqu’elle est disponible, selon la configuration.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-20" title="Vérifier la continuité" overline="Heroclip" label="20 / 22 · Darkwood">
<div class="memory-columns"><div><h2>Repères transmis</h2><p>Personnage, palette, accessoire, action accomplie.</p></div><div><h2>Résultat à relire</h2><p>Un nom ne garantit pas un visage. Une palette ne garantit pas un raccord.</p></div></div><p>Une génération réussie techniquement peut encore demander une correction artistique.</p><p class="memory-note">La filiation est conservée ; l’archivage durable des médias reste un sujet distinct.</p>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-21" title="Six mémoires, six portées" overline="Mémoire" label="21 / 22 · Darkwood">
<div class="memory-summary"><p>Darkwood <span>les raisons d’une décision</span></p><p>Flow <span>l’état d’un calcul</span></p><p>Uniflow <span>la méthode et son exécution</span></p><p>Darkwaar <span>la connaissance du joueur</span></p><p>DJMatyo <span>un motif transformable</span></p><p>Heroclip <span>les repères d’un récit</span></p></div>
</x-darkwood.content>
<x-darkwood.content class="memory-slide" transition="fade" id="memory-22" title="Ce qui rend la suite possible" overline="Mémoire" label="22 / 22 · Darkwood">
<p class="memory-question">La mémoire rend la suite possible lorsqu’elle conserve ce dont nous avons besoin pour reprendre, comprendre et transformer.</p><p><a href="https://memory.content.darkwood.com/" target="_blank" rel="noopener noreferrer">Explorer les six démonstrations →</a></p><p class="memory-note">Un même principe, des réalisations et des durées différentes.</p>
</x-darkwood.content>
</x-darkwood.deck>
