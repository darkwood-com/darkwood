<x-darkwood.deck transition="fade">
<x-darkwood.cover title="Darkwood MCP Experience" subtitle="One ecosystem. Multiple capabilities. One AI experience." overline="Welcome to Darkwood · MATYO91 · Your guide" date="19-11-2026" />
<x-darkwood.content title="Discover the ecosystem" overline="Darkwood MCP Experience" :top="false" label="02 | explore → use → inspect">
<x-darkwood.columns :count="3">
<div class="sw-column"><h3>Mario</h3><p>Generate original geometry</p></div>
<div class="sw-column"><h3>MCP Apps</h3><p>Publishing prototype · simulated</p></div>
<div class="sw-column"><h3>Composio MCP</h3><p>Connectors · historical experiment</p></div>
<div class="sw-column"><h3>Recherche Entreprise</h3><p>Company search · custom MCP</p></div>
<div class="sw-column"><h3>Flowvox</h3><p>Voice workflows · qualification needed</p></div>
<div class="sw-column"><h3>Connect Flow</h3><p>Orchestration · experiment</p></div>
</x-darkwood.columns>
<p><a href="https://ai-mcp-symfony-hackathon-content.darkwood.com/#selection">Code, articles and maturity of each card</a></p>
</x-darkwood.content>
<x-darkwood.content title="Ask for a capability" overline="Darkwood MCP Experience" :top="false" label="03 | explore → use → inspect">
<p class="sw-lead">“Create a small original forest level.”</p>
<div class="sw-process"><div class="sw-process-step">Your request</div><div class="sw-process-arrow"><x-darkwood.arrow /></div><div class="sw-process-step">Symfony AI</div><div class="sw-process-arrow"><x-darkwood.arrow /></div><div class="sw-process-step">MCP tool</div></div>
</x-darkwood.content>
<x-darkwood.content title="Choose a real path" overline="Darkwood MCP Experience" :top="false" label="04 | explore → use → inspect">
<x-darkwood.columns :count="2"><div class="sw-column"><h3>Mario</h3><p><code>generate_original_level</code></p><p>A brief → checked geometry</p></div><div class="sw-column"><h3>DarkwoodGame</h3><p><code>get_darkwood_state</code></p><p>Local API → read-only observation</p></div></x-darkwood.columns>
<p>Separate demos. No automatic hand-off between cards.</p>
</x-darkwood.content>
<x-darkwood.content title="Watch the tool work" overline="Recorded execution · 18 November 2026" :top="false" label="05 | explore → use → inspect">
@php
$command = "php bin/console app:mario:design \\\n  --prompt='Create a small original forest level' \\\n  --approve-export";
@endphp
<x-darkwood.terminal :source="$command" title="An original brief, then an approved local export" />
<p>Real Symfony AI → official SDK client → MCP server.</p>
</x-darkwood.content>
<x-darkwood.content title="Observe another capability" overline="Darkwood MCP Experience" :top="false" label="06 | explore → use → inspect">
@php
$state = '{"user":6,"state":"main","mode":null,"display":"web"}';
@endphp
<x-darkwood.code :source="$state" language="json" title="get_darkwood_state" />
<p>A read-only Symfony AI agent interprets these exact fields.</p>
<p>Recorded local API · 19 November · synthetic player</p>
</x-darkwood.content>
<x-darkwood.content title="Inspect the result" overline="Darkwood MCP Experience" :top="false" label="07 | explore → use → inspect">
<img src="{{ asset('darkwood/hackathon/level-preview.png') }}" alt="Forest Clearing - Beginner: actual generated geometry, not a playable game" style="max-width:100%;max-height:300px;object-fit:contain" />
<p>48 × 15 tiles · JSON + evidence + approved preview</p>
<p>Checked geometry is not proof of playability.</p>
</x-darkwood.content>
<x-darkwood.content title="Darkwood MCP Experience" overline="Darkwood MCP Experience" :top="false" label="08 | explore → use → inspect">
<p class="sw-lead">One ecosystem. Multiple capabilities. One AI experience.</p>
<p>Public code · Symfony AI + official PHP MCP SDK</p>
<p><a href="https://ai-mcp-symfony-hackathon-content.darkwood.com/">ai-mcp-symfony-hackathon-content.darkwood.com</a></p>
<p>Prepared for the hackathon. Not yet submitted.</p>
</x-darkwood.content>
</x-darkwood.deck>
