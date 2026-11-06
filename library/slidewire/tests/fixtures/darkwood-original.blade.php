<div class="slidewire-theme-darkwood-original">
    <x-darkwood.cover title="Darkwood — fond bleu" />
    <x-darkwood.cover background="black" title="Darkwood — fond noir" />
    <x-darkwood.content title="Puces historiques" overline="Fond blanc · HTML et Markdown">
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
