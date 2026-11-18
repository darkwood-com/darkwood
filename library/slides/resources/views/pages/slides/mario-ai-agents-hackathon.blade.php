<x-darkwood.deck transition="fade">
<x-darkwood.cover title="Original level designer" subtitle="Symfony AI + the official PHP MCP SDK" overline="Darkwood · Hackathon 2026" date="18-11-2026" />
<x-darkwood.content title="A real tool call" overline="Agent → MCP → checked JSON" :top="false" label="02 | execution">
@php
$command = "php bin/console app:mario:design \
  --prompt='An original beginner forest level' \
  --approve-export";
@endphp
<x-darkwood.terminal :source="$command" title="Existing Darkwood Symfony application" />
<p>The agent interprets the brief. The SDK client calls the real server.</p>
</x-darkwood.content>
<x-darkwood.content title="Inspect the result" overline="Actual generated geometry" :top="false" label="03 | evidence">
<img src="{{ asset('darkwood/hackathon/level-preview.png') }}" alt="Geometry preview from an actual Symfony AI and MCP execution" style="max-width:100%;max-height:380px;object-fit:contain" />
<p>Checked coordinates, structured JSON and execution evidence.</p>
</x-darkwood.content>
<x-darkwood.content title="Reproduce it" overline="Public source · MIT · Nix" :top="false" label="04 | limits">
<ul><li>Symfony AI 1.0.0-BETA1 + mcp/sdk 0.10.0</li><li>No hidden fixture or model-side publishing</li><li>Geometry is not proof of playability</li></ul>
<p><a href="https://github.com/darkwood-com/darkwood/tree/main/library/content-mario-orquestration">Public code and quickstart</a></p>
</x-darkwood.content>
</x-darkwood.deck>
