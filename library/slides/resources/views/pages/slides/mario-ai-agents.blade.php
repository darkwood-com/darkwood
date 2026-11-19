<x-darkwood.deck transition="fade">
<x-darkwood.cover title="Darkwood MCP Experience" subtitle="One ecosystem. Multiple capabilities. One AI experience." overline="Bienvenue dans Darkwood · MATYO91 · Votre guide" date="19-11-2026" />
<x-darkwood.content title="Explorer l’écosystème" overline="Darkwood MCP Experience" :top="false" label="02 | explore → use → inspect">
<x-darkwood.columns :count="3">
<div class="sw-column"><h3>Mario</h3><p>Générer une géométrie originale</p></div>
<div class="sw-column"><h3>MCP Apps</h3><p>Prototype de publication · simulé</p></div>
<div class="sw-column"><h3>Composio MCP</h3><p>Connecteurs · expérience historique</p></div>
<div class="sw-column"><h3>Recherche Entreprise</h3><p>Recherche société · MCP personnalisé</p></div>
<div class="sw-column"><h3>Flowvox</h3><p>Workflows vocaux · à qualifier</p></div>
<div class="sw-column"><h3>Connect Flow</h3><p>Orchestration · expérimentation</p></div>
</x-darkwood.columns>
<p><a href="https://ai-mcp-symfony-hackathon-content.darkwood.com/#selection">Code, articles et maturité de chaque card</a></p>
</x-darkwood.content>
<x-darkwood.content title="Demander une capacité" overline="Darkwood MCP Experience" :top="false" label="03 | explore → use → inspect">
<p class="sw-lead">« Créer un petit niveau de forêt original. »</p>
<div class="sw-process"><div class="sw-process-step">Votre demande</div><div class="sw-process-arrow"><x-darkwood.arrow /></div><div class="sw-process-step">Symfony AI</div><div class="sw-process-arrow"><x-darkwood.arrow /></div><div class="sw-process-step">Outil MCP</div></div>
</x-darkwood.content>
<x-darkwood.content title="Choisir un parcours réel" overline="Darkwood MCP Experience" :top="false" label="04 | explore → use → inspect">
<x-darkwood.columns :count="2"><div class="sw-column"><h3>Mario</h3><p><code>generate_original_level</code></p><p>Un brief → une géométrie vérifiée</p></div><div class="sw-column"><h3>DarkwoodGame</h3><p><code>get_darkwood_state</code></p><p>API locale → observation en lecture seule</p></div></x-darkwood.columns>
<p>Démos indépendantes. Aucun enchaînement automatique entre cards.</p>
</x-darkwood.content>
<x-darkwood.content title="Voir l’outil travailler" overline="Exécution enregistrée · 18 novembre 2026" :top="false" label="05 | explore → use → inspect">
@php
$command = "php bin/console app:mario:design \\\n  --prompt='Create a small original forest level' \\\n  --approve-export";
@endphp
<x-darkwood.terminal :source="$command" title="Un brief original, puis un export local approuvé" />
<p>Symfony AI réel → client SDK officiel → serveur MCP.</p>
</x-darkwood.content>
<x-darkwood.content title="Observer une autre capacité" overline="Darkwood MCP Experience" :top="false" label="06 | explore → use → inspect">
@php
$state = '{"user":6,"state":"main","mode":null,"display":"web"}';
@endphp
<x-darkwood.code :source="$state" language="json" title="get_darkwood_state" />
<p>Un agent Symfony AI en lecture seule explique ces champs exacts.</p>
<p>API locale enregistrée · 19 novembre · joueur synthétique</p>
</x-darkwood.content>
<x-darkwood.content title="Inspecter le résultat" overline="Darkwood MCP Experience" :top="false" label="07 | explore → use → inspect">
<img src="{{ asset('darkwood/hackathon/level-preview.png') }}" alt="Forest Clearing - Beginner: actual generated geometry, not a playable game" style="max-width:100%;max-height:300px;object-fit:contain" />
<p>48 × 15 cases · JSON + preuve + aperçu approuvé</p>
<p>La géométrie vérifiée ne prouve pas la jouabilité.</p>
</x-darkwood.content>
<x-darkwood.content title="Darkwood MCP Experience" overline="Darkwood MCP Experience" :top="false" label="08 | explore → use → inspect">
<p class="sw-lead">One ecosystem. Multiple capabilities. One AI experience.</p>
<p>Code public · Symfony AI + SDK PHP MCP officiel</p>
<p><a href="https://ai-mcp-symfony-hackathon-content.darkwood.com/">ai-mcp-symfony-hackathon-content.darkwood.com</a></p>
<p>Candidature préparée, non soumise.</p>
</x-darkwood.content>
</x-darkwood.deck>
