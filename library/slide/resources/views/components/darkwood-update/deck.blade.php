@php
    $english = $language === 'en';
    $media = 'images/darkwood-update/';
    $workshop = 'https://ai-mcp-symfony-hackathon-content.darkwood.com/';
    $entry = 'https://symfony.com/hackathon/projects/darkwood-mcp-experience';
    $flow = 'https://flow.darkwood.com/docs/getting-started/driver/';
    $titles = $english ? [
        'A week of releases', 'Darkwood MCP Experience', 'From brief to approved export',
        'Six paths through the workshop', 'PHP 8.6 from day one', 'Partial application in Flow',
        'PollDriver listens for I/O', 'One design system, four Portal themes',
        'Five games from Darkwaar', 'Six clips and six sets',
        'Explore Darkwood. Support Darkwood MCP Experience.',
    ] : [
        'Une semaine de réalisations', 'Darkwood MCP Experience', 'Du brief à l’export approuvé',
        'Six parcours dans l’atelier', 'PHP 8.6 dès le premier jour', 'Les fonctions partielles dans Flow',
        'PollDriver attend les événements d’E/S', 'Un design commun, quatre thèmes sur Portal',
        'Cinq jeux sur Darkwaar', 'Six clips et six sets',
        'Explorez Darkwood. Soutenez Darkwood MCP Experience.',
    ];
    $week = $english ? [
        ['Hackathon', 'Darkwood MCP Experience submitted'],
        ['PHP 8.6', 'Adopted for development'],
        ['Darkwood Flow', 'Partial application and native polling'],
        ['Darkwood Design', 'Shared visual conventions'],
        ['Creative releases', '17 games, clips and sets'],
        ['Navi and Uniflow', 'Shared tooling and UI consolidation'],
    ] : [
        ['Hackathon', 'Candidature Darkwood MCP Experience soumise'],
        ['PHP 8.6', 'Adopté pour le développement'],
        ['Darkwood Flow', 'Fonctions partielles et polling natif'],
        ['Darkwood Design', 'Des repères visuels communs'],
        ['Publications créatives', '17 jeux, clips et sets'],
        ['Navi et Uniflow', 'Outillage mutualisé et interface consolidée'],
    ];
    $cards = [
        ['Mario Orchestration', $english ? 'Verified execution' : 'Exécution vérifiée'],
        ['MCP Apps', $english ? 'Prototype · simulated publishing' : 'Prototype · publication simulée'],
        ['Composio MCP', $english ? 'Earlier connector experiment' : 'Expérience historique de connexion'],
        ['Recherche Entreprise MCP', $english ? 'Live API · custom MCP' : 'API réelle · MCP personnalisé'],
        ['Connect Flow', $english ? 'Workflow composition MVP' : 'MVP de composition de workflows'],
        ['Flowvox', $english ? 'Voice pipeline · end-to-end checks pending' : 'Pipeline vocal · validation de bout en bout à poursuivre'],
    ];
    $steps = $english
        ? ['Brief', 'Symfony AI agent', 'MCP client', 'MCP server', 'Generation agent', 'PHP validation', 'Approved export']
        : ['Brief', 'Agent Symfony AI', 'Client MCP', 'Serveur MCP', 'Agent de génération', 'Validation PHP', 'Export approuvé'];
    $games = ['moth-ferry' => 'Moth Ferry', 'stillweight' => 'Stillweight', 'hindmost' => 'Hindmost', 'shoalpass' => 'Shoalpass', 'hoopwend' => 'Hoopwend'];
    $clips = ['Écart', 'Marée de lumière', 'Le jardin des heures', 'Le pli et l’envol', 'La spirale d’or', 'L’harmonographe'];
    $sets = ['Trajectoire — Second Mix', 'Lisière électrique', 'Verre lent', 'Sillage doré', 'Marée de néon', 'Brume de quai'];
    $destinations = [
        [$english ? 'Symfony submission' : 'Candidature Symfony', $entry],
        [$english ? 'MCP workshop' : 'Atelier MCP', $workshop],
        [$english ? 'Darkwood catalogue' : 'Catalogue Darkwood', 'https://page.darkwood.com/'],
        [$english ? 'Flow documentation' : 'Documentation Flow', $flow],
        ['Darkwaar', 'https://darkwaar.com/games'],
        ['HeroClip', 'https://heroclip.darkwood.com/clips'],
        ['DJ Matyo', 'https://djmatyo91.darkwood.com/sets'],
    ];
    $code = <<<'PHP'
    yield shiftNumber(?, 1);
    yield scaleNumber(factor: 2, number: ?);
    yield sprintf('Result: %02d', ?);
    PHP;
@endphp
<x-darkwood.deck transition="fade" lang="{{ $language }}">
    <x-darkwood.cover class="update-slide update-cover" id="update-cover" title="Darkwood Update"
        subtitle="Symfony AI · MCP · PHP 8.6" author="Darkwood" background="black"
        :date="$english ? '16–22 November 2026' : '16–22 novembre 2026'">
        <link rel="stylesheet" href="{{ asset($media.'deck.css') }}?v=2">
        <script defer src="{{ asset($media.'theme.js') }}?v=1"></script>

        <details class="update-theme" lang="{{ $language }}">
            <summary aria-label="{{ $english ? 'Change slide theme' : 'Changer le thème des slides' }}">
                @foreach (['light', 'dark', 'sepia'] as $theme)
                    <img data-theme-icon="{{ $theme }}" src="{{ asset($media.$theme.'.svg') }}" alt="" width="22" height="22">
                @endforeach
            </summary>
            <fieldset>
                <legend>{{ $english ? 'Slide theme' : 'Thème des slides' }}</legend>
                @foreach (['light' => 'Light', 'dark' => 'Dark', 'sepia' => 'Sepia'] as $theme => $label)
                    <label><input type="radio" name="update-theme" value="{{ $theme }}" @checked($theme === 'light')>{{ $label }}</label>
                @endforeach
            </fieldset>
        </details>
        <img class="update-poster" src="{{ asset($media.'experience-poster.jpg') }}" alt="">
    </x-darkwood.cover>

    <x-darkwood.content class="update-slide" id="update-week" :title="$titles[0]" :page="2" :pages="12" label="Darkwood">
        <dl class="update-grid">
            @foreach ($week as [$topic, $description])
                <div><dt>{{ $topic }}</dt><dd>{{ $description }}</dd></div>
            @endforeach
        </dl>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-hackathon" :title="$titles[1]" :page="3" :pages="12" label="Darkwood">
        <p class="update-statement" lang="en">One ecosystem. Multiple capabilities. One AI experience.</p>
        <p>{{ $english ? 'Submitted to the Symfony AI & MCP Hackathon on 19 November 2026.' : 'Candidature soumise au Symfony AI & MCP Hackathon le 19 novembre 2026.' }}</p>
        <p><a href="{{ $entry }}">{{ $english ? 'View the official entry' : 'Voir la candidature officielle' }}</a></p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-execution" :title="$titles[2]" :page="4" :pages="12" label="Darkwood">
        <ol class="update-pipeline">
            @foreach ($steps as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
        <a class="update-trace" href="{{ $workshop.'hackathon/mcp-proof.json' }}">
            <img src="{{ asset($media.'mcp-trace.png') }}" alt="{{ $english ? 'Execution log: the official SDK calls generate_original_level and returns valid geometry' : 'Trace d’exécution : le SDK officiel appelle generate_original_level et renvoie une géométrie valide' }}">
        </a>
        <figure class="update-level">
            <img src="{{ asset('darkwood/hackathon/level-preview.png') }}" alt="{{ $english ? 'Generated forest-level geometry, with platforms and objects' : 'Géométrie générée du niveau forestier, avec plateformes et objets' }}">
            <figcaption>48 × 15 · {{ $english ? 'Geometry verified in PHP. Playability remains unverified.' : 'Géométrie vérifiée en PHP. Jouabilité à évaluer.' }} <a href="{{ $workshop }}">{{ $english ? 'Execution evidence' : 'Preuves d’exécution' }}</a></figcaption>
        </figure>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-workshop" :title="$titles[3]" :page="5" :pages="12" label="Darkwood">
        <dl class="update-grid">
            @foreach ($cards as [$name, $status])
                <div><dt>{{ $name }}</dt><dd>{{ $status }}</dd></div>
            @endforeach
        </dl>
        <p class="update-note">{{ $english ? 'The workshop documents these projects independently.' : 'L’atelier documente ces projets indépendamment.' }}</p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-php" :title="$titles[4]" :page="6" :pages="12" label="Darkwood">
        <p class="update-statement">{{ $english ? '19 November 2026' : '19 novembre 2026' }}</p>
        <p>{{ $english ? 'PHP 8.6 became Darkwood’s development baseline on release day.' : 'PHP 8.6 est devenu la référence de développement de Darkwood le jour de sa sortie.' }}</p>
        <p>Navi · Nix 2.36 · Symfony 8.1</p>
        <p class="update-note">{{ $english ? 'Navi supplies a reproducible environment. FrankenPHP in production still runs PHP 8.5.' : 'Navi fournit un environnement reproductible. FrankenPHP en production tourne encore sous PHP 8.5.' }}</p>
        <p><a href="https://www.php.net/releases/8.6/en.php">{{ $english ? 'PHP 8.6 release notes' : 'Annonce officielle de PHP 8.6' }}</a></p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-pfa" :title="$titles[5]" :page="7" :pages="12" label="Darkwood">
        <p>{{ $english ? 'The question mark leaves an argument for the next value in the Flow pipeline.' : 'Le point d’interrogation réserve un argument pour la prochaine valeur du pipeline Flow.' }}</p>
        <x-darkwood.code :source="$code" language="php" />
        <p class="update-result">3 <span>→ +1 → ×2 →</span> <strong>08</strong></p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-poll" :title="$titles[6]" :page="8" :pages="12" label="Darkwood">
        <p class="update-statement"><code>Io&#92;Poll</code></p>
        <ul class="update-list">
            <li>{{ $english ? 'Waits for streams to become readable or writable' : 'Attend qu’un flux soit prêt en lecture ou en écriture' }}</li>
            <li>{{ $english ? 'Times out using' : 'Gère les délais avec' }} <code>Time&#92;Duration</code></li>
            <li>{{ $english ? 'Reports structured stream errors' : 'Remonte des erreurs de flux structurées' }}</li>
        </ul>
        <p class="update-note">{{ $english ? 'FiberDriver remains the default.' : 'FiberDriver reste le pilote par défaut.' }} <a href="{{ $flow }}">{{ $english ? 'Driver documentation' : 'Documentation des pilotes' }}</a></p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-design" :title="$titles[7]" :page="9" :pages="12" label="Darkwood">
        <ul class="update-palettes">
            @foreach (['original' => 'Original', 'light' => 'Light', 'dark' => 'Dark', 'sepia' => 'Sepia'] as $theme => $label)
                <li data-palette="{{ $theme }}">
                    <img src="{{ asset($media.$theme.'.svg') }}" alt=""><strong>{{ $label }}</strong>
                </li>
            @endforeach
        </ul>
        <p>{{ $english ? 'Jost, shared colour tokens, familiar navigation and accessible controls.' : 'Jost, tokens de couleur, navigation cohérente et contrôles accessibles.' }}</p>
        <p class="update-note">{{ $english ? 'Portal preserves each site’s own styling through Original and remembers the theme per site.' : 'Portal conserve l’habillage de chaque site via Original et mémorise le thème par site.' }} <a href="https://github.com/darkwood-com/darkwood/tree/main/library/darkwood-design">Darkwood Design</a></p>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-games" :title="$titles[8]" :page="10" :pages="12" label="Darkwood">
        <ul class="update-games">
            @foreach ($games as $slug => $title)
                <li><a href="https://darkwaar.com/games/{{ $slug }}"><img src="{{ asset($media.$slug.'.jpg') }}" alt=""><strong>{{ $title }}</strong></a></li>
            @endforeach
        </ul>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-media" :title="$titles[9]" :page="11" :pages="12" label="Darkwood">
        <div class="update-media">
            <section>
                <a href="https://heroclip.darkwood.com/clips"><img src="{{ asset($media.'harmonographe.jpg') }}" alt="{{ $english ? 'L’harmonographe cover' : 'Couverture de L’harmonographe' }}"><h2>HeroClip</h2></a>
                <ul>@foreach ($clips as $title)<li>{{ $title }}</li>@endforeach</ul>
            </section>
            <section>
                <a href="https://djmatyo91.darkwood.com/sets"><img src="{{ asset($media.'brume-de-quai.svg') }}" alt="{{ $english ? 'Brume de quai cover' : 'Couverture de Brume de quai' }}"><h2>DJ Matyo</h2></a>
                <ul>@foreach ($sets as $title)<li>{{ $title }}</li>@endforeach</ul>
            </section>
        </div>
    </x-darkwood.content>

    <x-darkwood.content class="update-slide" id="update-support" :title="$titles[10]" :page="12" :pages="12" label="Darkwood">
        <p>{{ $english ? 'Discover the submission, watch the demo and share it. Feedback on the project is welcome.' : 'Découvrez la candidature, regardez la démo et partagez le projet. Vos retours sont les bienvenus.' }}</p>
        <ul class="update-links">
            @foreach ($destinations as [$label, $href])
                <li><a href="{{ $href }}">{{ $label }}</a></li>
            @endforeach
        </ul>
    </x-darkwood.content>
</x-darkwood.deck>
