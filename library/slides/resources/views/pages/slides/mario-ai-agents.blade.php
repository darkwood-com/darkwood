<x-darkwood.deck transition="fade">
<x-darkwood.cover title="Symfony AI × MCP" subtitle="Un brief, un outil réel, une géométrie vérifiée" overline="Darkwood · Hackathon 2026" date="18-11-2026" />

<x-darkwood.content title="Le problème" overline="Conception de niveaux" :top="false" label="02 | problème">
<p>Un texte convaincant ne garantit pas un niveau exploitable.</p>
<ul><li>Interpréter le besoin du concepteur</li><li>Contrôler les coordonnées produites</li><li>Faire approuver l’export local</li></ul>
</x-darkwood.content>

<x-darkwood.content title="Un artefact vérifiable" overline="La solution retenue" :top="false" label="03 | solution">
<p>Une seule card existante : Mario Orchestration.</p>
<ul><li>Un brief original, un appel d’outil</li><li>Du JSON et une vue de sa géométrie</li><li>Aucun fichier écrit sans approbation</li></ul>
</x-darkwood.content>

<x-darkwood.content title="Le parcours exécuté" overline="Architecture réelle" :top="false" label="04 | architecture">
@php
$pipeline = "Brief utilisateur
  → Symfony AI : level_designer
  → Toolbox : client MCP officiel
  → Serveur MCP : level_generator
  → Validation PHP → JSON + aperçu approuvé";
@endphp
<x-darkwood.code :source="$pipeline" language="text" title="Même application Symfony, serveur stdio existant" />
</x-darkwood.content>

<x-darkwood.content title="Le rôle de Symfony AI" overline="Agent · Platform · Toolbox" :top="false" label="05 | Symfony AI">
<ul><li>Interpréter le brief et appeler l’outil</li><li>Générer le niveau puis expliquer le résultat</li><li>Agent et Platform 1.0.0-BETA1, modèle gpt-5-mini</li></ul>
<p>Les règles géométriques restent en PHP.</p>
</x-darkwood.content>

<x-darkwood.content title="Le rôle de MCP" overline="SDK PHP officiel 0.10.0" :top="false" label="06 | MCP">
<ul><li>Client et serveur du SDK officiel</li><li>Découverte par tools/list, appel par tools/call</li><li>Un schéma SDK préservé : generate_original_level</li></ul>
<p>Un adaptateur Toolbox relie les API existantes. Aucun protocole maison.</p>
</x-darkwood.content>

<x-darkwood.content title="Exécuter puis approuver" overline="Démonstration" :top="false" label="07 | exécution">
@php
$command = "php bin/console app:mario:design \
  --prompt='Un petit niveau de forêt original' \
  --approve-export";
@endphp
<x-darkwood.terminal :source="$command" title="Depuis library/content-mario-orquestration" />
<p>Une nouvelle sortie locale : level.json, evidence.json et preview.html.</p>
</x-darkwood.content>

<x-darkwood.content title="Le résultat observé" overline="Essai réel du 18 novembre 2026" :top="false" label="08 | résultat">
<img src="{{ asset('darkwood/hackathon/level-preview.png') }}" alt="Aperçu de la géométrie du niveau Forest Clearing - Beginner réellement généré" style="max-width:100%;max-height:340px;object-fit:contain" />
<p>48 × 15 cases · 4 pièces de terrain · 3 objets · 47,6 s sur cet essai.</p>
<p>Cette mesure isolée n’est pas un benchmark.</p>
</x-darkwood.content>

<x-darkwood.content title="Ce que la preuve établit" overline="Reproduction et limites" :top="false" label="09 | conclusion">
<ul><li>Agent → outil MCP → résultat réel vérifié</li><li>Installation publique sous Nix, clé personnelle</li><li>La géométrie ne prouve pas la jouabilité</li></ul>
<p><a href="https://ai-mcp-symfony-hackathon-content.darkwood.com/">Atelier, code et instructions de reproduction</a></p>
<p>Candidature en préparation. Aucune soumission ou sélection annoncée.</p>
</x-darkwood.content>
</x-darkwood.deck>
