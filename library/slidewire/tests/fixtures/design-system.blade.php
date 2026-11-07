<div class="slidewire-theme-darkwood">
    <x-darkwood.cover title="Darkwood — fond bleu" />
    <x-darkwood.cover background="black" title="Darkwood — fond noir" />
    <x-darkwood.content title="Puces et listes" overline="Fond blanc · HTML et Markdown">
        <x-darkwood.columns>
            <div>
                <ul>
                    <li>HTML level one
                        <ul><li>HTML level two<ul><li>HTML level three</li></ul></li></ul>
                    </li>
                    <li>Une ligne longue pour vérifier l’alignement du texte après un retour à la ligne dans une liste imbriquée.</li>
                </ul>
            </div>
            <div>
                <x-slidewire::markdown>
- Generated level one
    - Generated level two
        - Generated level three
- Second generated item
                </x-slidewire::markdown>
            </div>
        </x-darkwood.columns>
    </x-darkwood.content>
    <div id="dw-spacing-cases">
        <section data-spacing-case="all">
            <x-darkwood.heading overline="Surtitre" title="Titre principal" subtitle="Sous-titre" />
        </section>
        <section data-spacing-case="overline-title">
            <x-darkwood.heading overline="Surtitre" title="Titre principal" />
        </section>
        <section data-spacing-case="title-subtitle">
            <x-darkwood.heading title="Titre principal" subtitle="Sous-titre" />
        </section>
        <section data-spacing-case="title-only">
            <x-darkwood.heading title="Titre principal" />
        </section>
        <section data-spacing-case="two-lines">
            <x-darkwood.heading overline="Surtitre" title="Un titre principal sur deux lignes pour vérifier les espacements" subtitle="Sous-titre" />
        </section>
    </div>
</div>

@php
    $terminalSample = <<<'TERMINAL'
$ php artisan test
PASS  Application ready
Tests: 28 passed
TERMINAL;
    $codeSample = <<<'CODE'
<?php
final class Example
{
    public function run(): string
    {
        return 'Slidewire';
    }
}
CODE;
@endphp
<div id="native-code-cases" style="background:#fff;color:#000;padding:32px">
    <x-darkwood.code-capture :source="$codeSample" language="php" title="example.php" />
    <x-darkwood.terminal-capture :source="$terminalSample" title="Terminal" />
</div>
<div id="native-line-cases" class="sw-content" style="background:#fff;color:#000;padding:32px">
    <svg viewBox="0 0 400 100" width="400" height="100" aria-label="Traits graphiques">
        <line x1="10" y1="20" x2="390" y2="20" stroke="currentColor" />
        <rect x="10" y="40" width="160" height="40" stroke="currentColor" />
        <circle cx="250" cy="60" r="20" stroke="currentColor" />
    </svg>
    <style>#native-line-cases::after {content:"";display:block;width:400px;border-top:var(--dw-border-width) solid var(--dw-blue)}</style>
</div>
