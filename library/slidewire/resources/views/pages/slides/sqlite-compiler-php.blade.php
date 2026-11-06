<x-darkwood.deck transition="fade">

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="section" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--section',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-section-title dw-title',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead mt-6',
));
@endphp
<x-darkwood.heading variant="section" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Darkwood · <span class="dw-accent">PHP</span> · C · <span class="dw-accent">ARM64</span> · <span class="dw-accent">SQLite</span>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
I Built a C Compiler<br>in PHP<br>That Can Compile SQLite
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
<span class="dw-accent">PHP</span> → C frontend → <span class="dw-accent">ARM64</span> → <span class="dw-accent">SQLite</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="mt-10 dw-flow">
                <div class="dw-node">C</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">PHP</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">.s</div>
                <div class="dw-arrow">→</div>
                <div class="dw-node">native</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The question
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
How much of a C compiler<br>can I implement in <span class="dw-accent">PHP</span><br>before it can compile <span class="dw-accent">SQLite</span>?
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="01 | question" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading mt-8',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The result
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">darkwood</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">

            <div class="mt-4 dw-flow" style="flex-wrap:wrap">
                <div class="dw-node">SQLite 3.46.0</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP compiler</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ARM64</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">native executable</div>
                <div class="dw-arrow">↓</div>
            </div>

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-note mt-6">46/46 regression fixtures</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="02 | result" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What “compiled in PHP” means</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>PHP</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_0'
Lexer
Preprocessor
Parser
AST
Sema
ARM64 Codegen
SW_CODE_3_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel">
                    <h3>macOS</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_3_1'
as
linker
native executable
SW_CODE_3_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Clang does <strong>not</strong> compile <code>sqlite3.c</code>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="03 | boundary" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Deliberately small</p>
            <div class="mt-4 dw-flow">
                <div class="dw-node">source</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">tokens</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">AST</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">typed AST</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ARM64 .s</div>
            </div>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px">
                <div class="dw-chip">No LLVM</div>
                <div class="dw-chip">No SSA</div>
                <div class="dw-chip">No CIR</div>
                <div class="dw-chip">No custom assembler</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="04 | architecture" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>src/Compiler/Driver/Compiler.php</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The driver
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_5_0'
$tokens = $preprocessor->preprocess($fileId);

$parser = new Parser($tokens, $this->diagnostics);
$decls = $parser->parse();

$sema = new Sema($this->diagnostics);
$tast = $sema->analyze($decls);

$codegen = new Codegen($enumConstants);
$assembly = $codegen->generate($tast);
SW_CODE_5_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note mt-4">SOURCE → TOKENS → AST → TYPES → ASM</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="05 | driver" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>SourceLoc.php</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Source locations
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_6_0'
final readonly class SourceLoc
{
    public function __construct(
        public int $fileId,
        public int $offset,
    ) {}
}
SW_CODE_6_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-flow">
                <div class="dw-node">fileId + offset</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">SourceManager</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">line : column</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="06 | source-loc" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>Token.php</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
A token is more than text
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_7_0'
public function __construct(
    public TokenKind $kind,
    public string $spelling,
    public SourceLoc $loc,
    public array $hideSet = [],
) {}
SW_CODE_7_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-question mt-6">Why does a token need a <span class="dw-accent">hideSet</span>?</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="07 | token" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Macro expansion needs memory</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_8_0'
#define A B
#define B A
SW_CODE_8_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:flex-start;gap:8px">
                <div class="dw-node">A</div>
                <div class="dw-arrow">↓ expand</div>
                <div class="dw-node">B  <span class="dw-accent">[hide A]</span></div>
                <div class="dw-arrow">↓ expand</div>
                <div class="dw-node">A  <span class="dw-accent">[hide A,B]</span></div>
                <div class="dw-arrow">↓</div>
                <div class="dw-chip">STOP</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="08 | hide-set" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>MacroTable.php</code> · MacroExpander
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Inside the preprocessor
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_9_0'
if ($token->hideSet !== []
    && in_array($token->spelling, $token->hideSet, true)) {
    $output[] = $token;
    continue;
}

$newHideSet = $this->unionHideSet($token->hideSet, [$macro->name]);
$expanded = $this->expandObjectLike(...);
for ($i = count($expanded) - 1; $i >= 0; --$i) {
    $pendingReversed[] = $expanded[$i];
}
SW_CODE_9_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="09 | expander" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">The preprocessor is a compiler subsystem</p>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px">
                <div class="dw-chip">#define</div>
                <div class="dw-chip">function macros</div>
                <div class="dw-chip">arguments</div>
                <div class="dw-chip">#if / #elif / #else</div>
                <div class="dw-chip">defined()</div>
                <div class="dw-chip"># / ##</div>
                <div class="dw-chip">__VA_ARGS__</div>
                <div class="dw-chip">rescanning</div>
                <div class="dw-chip">hide sets</div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">SQLite never reaches the parser unless this works.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="10 | preproc" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
Parsing C by hand
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
No parser generator
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-flow">
                <div class="dw-node">recursive descent</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Pratt expressions</div>
            </div>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px">
                <div class="dw-chip"><code>FuncDecl</code></div>
                <div class="dw-chip"><code>BinaryExpr</code></div>
                <div class="dw-chip"><code>CallExpr</code></div>
                <div class="dw-chip"><code>CompoundLiteralExpr</code></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="11 | parser" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading mt-8',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
C declarators fight back
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
≠
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">

            <div class="mt-6 dw-split">
                <div class="dw-split-panel">

@endverbatim
@php
$snippet = <<<'SW_CODE_12_0'
int *p[4];
SW_CODE_12_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="dw-note mt-4">array of pointers</p>
                </div>
                <div class="dw-split-panel is-ok">

@endverbatim
@php
$snippet = <<<'SW_CODE_12_1'
int (*p)[4];
SW_CODE_12_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                    <p class="dw-note mt-4">pointer to array</p>
                </div>
            </div>

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="12 | declarators" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>Parser::applyArraySuffix()</code></p>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_0'
if ($type instanceof PointerCType) {
    $to = $type->to;
    $array = new ArrayCType($to, $count);
    return new PointerCType($array);
}
SW_CODE_13_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3><code>int *p[4]</code></h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_1'
Array
 └── Pointer
      └── int
SW_CODE_13_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3><code>int (*p)[4]</code></h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_13_2'
Pointer
 └── Array[4]
      └── int
SW_CODE_13_2;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="13 | applyArraySuffix" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>Ast.php</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
The AST is just PHP
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_14_0'
final class BinaryExpr extends Expr
{
    public function __construct(
        public BinaryOp $op,
        public Expr $left,
        public Expr $right,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {}
}
SW_CODE_14_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-6">Parser builds structure. Sema gives it meaning.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="14 | ast" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
<code>CType.php</code>
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
Representing C types in PHP
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">



@endverbatim
@php
$snippet = <<<'SW_CODE_15_0'
CType
├── IntCType / CharCType / …
├── PointerCType
├── ArrayCType
├── FunctionCType
├── StructCType / UnionCType
└── EnumCType
SW_CODE_15_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">

@endverbatim
@php
$snippet = <<<'SW_CODE_15_1'
$this instanceof EnumCType => true,  // isInteger()
SW_CODE_15_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="15 | ctype" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Array ≠ pointer — except when it is</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_16_0'
int a[3];
int *p = a + 1;
SW_CODE_16_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:flex-start;gap:8px">
                <div class="dw-node">ArrayCType remains</div>
                <div class="dw-arrow">↓ expression context</div>
                <div class="dw-node">pointer-like behavior</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-chip">+ 1 means + sizeof(int)</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="16 | array-ptr" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Semantic analysis</p>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">Scopes</div>
                <div class="dw-chip">Symbols</div>
                <div class="dw-chip">Typedefs</div>
                <div class="dw-chip">Enums</div>
                <div class="dw-chip">Types</div>
                <div class="dw-chip">Conversions</div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_17_0'
$this->enumConstants[$case->name] = $case->value;
SW_CODE_17_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-flow">
                <div class="dw-node"><code>enum { VALUE = 42 };</code></div>
                <div class="dw-arrow">→</div>
                <div class="dw-node"><code>.long 42</code></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="17 | sema" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Global initialization is codegen too</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>C</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_18_0'
static unsigned char table[4] = {
    84, 92, 134, 0
};
SW_CODE_18_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>ARM64</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_18_1'
_table:
.byte 84
.byte 92
.byte 134
.byte 0
SW_CODE_18_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-question mt-6">This once became <code>.quad</code>.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="18 | globals" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">A PHP namespace bug corrupted C memory</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_19_0'
instanceof ArrayCType
SW_CODE_19_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Expected</h3>
                    <p style="margin:0"><code>App\Compiler\Common\ArrayCType</code></p>
                </div>
                <div class="dw-split-panel is-warn">
                    <h3>PHP resolved</h3>
                    <p style="margin:0"><code>App\Compiler\CodeGen\ArrayCType</code></p>
                </div>
            </div>
            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <div class="mt-4 dw-flow" style="flex-wrap:wrap">
                    <div class="dw-chip">wrong class</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">wrong branch</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">.quad</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">runtime fail</div>
                </div>
</div>
            <div class="slidewire-fragment" data-fragment-index="1" data-fragment="data-fragment">
    <p class="dw-takeaway mt-4">It assembled. It linked. It was wrong.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="19 | ArrayCType" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">From AST to ARM64</p>
            <div class="mt-3 dw-split">
                <div class="dw-split-panel">

@endverbatim
@php
$snippet = <<<'SW_CODE_20_0'
int add(int a, int b)
{
    return a + b;
}
SW_CODE_20_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">

@endverbatim
@php
$snippet = <<<'SW_CODE_20_1'
_add:
stp x29, x30, [sp, #-16]!
mov x29, sp
sub sp, sp, #16
str x0, [x29, #-8]
str x1, [x29, #-16]
… ldrsw … add …
mov x0, x9
ldp x29, x30, [sp], #16
ret
SW_CODE_20_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:8px">
                <div class="dw-chip">x0 / x1 args</div>
                <div class="dw-chip">x29 frame · x30 LR</div>
                <div class="dw-chip">x0 return</div>
                <div class="dw-chip">x9–x15 scratch</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="20 | arm64-add" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Stack frame</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_21_0'
high
──────────────
saved x29 / x30
──────────────
params · locals
temps · compounds
──────────────
SP
low
SW_CODE_21_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim


@endverbatim
@php
$snippet = <<<'SW_CODE_21_1'
sub sp, sp, #0  ; FRAME_SIZE_PLACEHOLDER
SW_CODE_21_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <p class="dw-note mt-3">Patched after emission · 16-byte aligned</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="21 | frame" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Pointer arithmetic means scaling</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_22_0'
int a[3];
a + 1
SW_CODE_22_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-6 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>Wrong</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_22_1'
add x?, x?, #1
SW_CODE_22_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Correct</h3>
                    <p style="margin:0">address + 1 × sizeof(int)</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_22_2'
lsl …, #2
SW_CODE_22_2;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-note mt-4"><code>035-array-ptr-arith</code></p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="22 | scale" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Direct vs indirect calls</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>Direct</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_23_0'
bl _function
SW_CODE_23_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Function pointer</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_23_1'
blr xN
SW_CODE_23_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_23_2'
int call(int (*fn)(int), int value)
{
    return fn(value);
}
SW_CODE_23_2;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="23 | calls" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
The ABI is where “valid assembly” stops
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
syntax correct<br><span class="dw-accent">≠</span><br>ABI correct
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-8 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">arguments</div>
                <div class="dw-chip">register lifetime</div>
                <div class="dw-chip">stack alignment</div>
                <div class="dw-chip">variadics</div>
                <div class="dw-chip">structs</div>
                <div class="dw-chip">aggregate returns</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="24 | abi" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Struct returns use registers too</p>
            <div class="mt-4 dw-flow">
                <div class="dw-node">≤16 bytes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-chip">x0 first word</div>
                <div class="dw-chip">x1 second word</div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_25_0'
struct S b(void) { return a(); }
SW_CODE_25_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>Bug</h3>
                    <p style="margin:0">preserve only part of x0/x1</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Fix</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_25_1'
str x0, [...]
str x1, [...]
SW_CODE_25_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-note mt-3"><code>054-struct-return-chain</code> · Loop 48</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="25 | struct-ret" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading-slide mt-2',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes">
<x-slot:overline>@verbatim
SQLite is an integration oracle
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
<span class="dw-accent">SQLite</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">


            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px">
                <div class="dw-chip">Preprocessor</div>
                <div class="dw-chip">Declarators</div>
                <div class="dw-chip">Globals</div>
                <div class="dw-chip">Enums</div>
                <div class="dw-chip">Structs / Unions</div>
                <div class="dw-chip">Function pointers</div>
                <div class="dw-chip">Variadic calls</div>
                <div class="dw-chip">ABI</div>
                <div class="dw-chip">Static data</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="26 | sqlite" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Failure → fixture → fix</p>
            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  A[SQLite / clang mismatch]
  B[reduce]
  C[tiny C fixture]
  D[find subsystem]
  E[fix one behavior]
  F[all fixtures]
  G[SQLite]
  A --&gt; B --&gt; C --&gt; D --&gt; E --&gt; F --&gt; G
  G -.-&gt; A</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="27 | methodology" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Bugs became permanent knowledge</p>
            <div class="mt-6 dw-map">
                <div><strong>010</strong><span>char array global</span></div>
                <div><strong>016</strong><span>variadic spill</span></div>
                <div><strong>034</strong><span>compound literal argument</span></div>
                <div><strong>035</strong><span>array pointer arithmetic</span></div>
                <div><strong>044</strong><span>nested designated initializer</span></div>
                <div><strong>051</strong><span>pointer-to-array</span></div>
                <div><strong>054</strong><span>struct return chain</span></div>
            </div>
            <p class="dw-takeaway mt-6">Every fixture is a bug the compiler should only have once.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="28 | fixtures" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">Two different pipelines</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>COMPILER</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_29_0'
tokens
 ↓ AST
 ↓ Sema
 ↓ ARM64
SW_CODE_29_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
                <div class="dw-split-panel is-ok">
                    <h3>OPERATION</h3>

@endverbatim
@php
$snippet = <<<'SW_CODE_29_1'
compile
 ↓ assemble
 ↓ link
 ↓ run
 ↓ validate
SW_CODE_29_1;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

                </div>
            </div>
            <p class="dw-question mt-6">Where should <span class="dw-accent">Flow</span> live?</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="29 | two-pipelines" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Where Darkwood <span class="dw-accent">Flow</span> fits</p>
            <div class="mt-3 dw-split">
                <div class="dw-split-panel">
                    <h3>Compiler internals</h3>
                    <p style="margin:0">plain PHP</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Operational pipeline</h3>
                    <p style="margin:0">darkwood/flow</p>
                </div>
            </div>

@endverbatim
@php
$snippet = <<<'SW_CODE_30_0'
yield $this->timed('compile_sqlite', $this->compileSqlite);
yield $this->timed('assemble_sqlite', $this->assembleSqlite);
yield $this->timed('compile_harness', $this->compileHarness);
yield $this->timed('assemble_harness', $this->assembleHarness);
yield $this->timed('link', $this->linkSmokeExecutable);
yield $this->timed('run', $this->runSmokeTest);
SW_CODE_30_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="php" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="30 | flow" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">One command, many artifacts</p>

@endverbatim
@php
$snippet = <<<'SW_CODE_31_0'
php bin/console app:compiler-sqlite
SW_CODE_31_0;
$snippetAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.code language="text" :source="$snippet" :pre-attributes="$snippetAttributes" />
@verbatim

            <div class="mt-4 dw-embed" >
                <pre class="mermaid slidewire-diagram" data-slidewire-diagram>flowchart TB
  C[sqlite3.c] --&gt; S[sqlite3.s]
  S --&gt; O[sqlite3.o]
  H[harness.c] --&gt; HS[harness.s]
  HS --&gt; HO[harness.o]
  O --&gt; L[link]
  HO --&gt; L
  L --&gt; E[executable]
  E --&gt; D[darkwood]</pre>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="31 | command" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">The engineering loop</p>
            <div class="mt-4 dw-flow" style="flex-wrap:wrap">
                <div class="dw-node">probe</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">execute</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">compare</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">reduce</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">fix</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">validate</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-chip">KEEP</div>
            </div>
            <p class="dw-takeaway mt-8">AI becomes useful when it cannot escape the executable feedback loop.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="32 | iterate" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">The five-minute wake loop</p>
            <div class="mt-6 dw-map">
                <div><strong>WAKE TICK</strong><span>resume + probe</span></div>
                <div><strong>ENGINEERING LOOP</strong><span>investigated change</span></div>
                <div><strong>KEEP</strong><span>survives validation</span></div>
            </div>
            <p class="dw-note mt-8">Loop 48 = last retained engineering change</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="33 | wake" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$overlineAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'dw-overline sw-overline dw-kicker',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading',
));
$subtitleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-subtitle dw-lead mt-6',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :overline-attributes="$overlineAttributes" :title-attributes="$titleAttributes" :subtitle-attributes="$subtitleAttributes">
<x-slot:overline>@verbatim
Eventually
@endverbatim
</x-slot:overline>
<x-slot:title>@verbatim
No mismatch.
@endverbatim
</x-slot:title>
<x-slot:subtitle>@verbatim
46/46<br>SQLite → <span class="dw-accent">darkwood</span>
@endverbatim
</x-slot:subtitle>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">



            <div class="slidewire-fragment" data-fragment-index="0" data-fragment="data-fragment">
    <p class="dw-takeaway mt-8">The correct decision was to stop changing the compiler.</p>
</div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="34 | stop" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">What this proves</p>
            <div class="mt-6 dw-map">
                <div><strong>PHP</strong><span>can host a non-trivial compiler</span></div>
                <div><strong>Direct path</strong><span>frontend → ARM64 can go surprisingly far</span></div>
                <div><strong>SQLite</strong><span>forces subsystems to become honest</span></div>
                <div><strong>Feedback</strong><span>executable evidence beats plausible code</span></div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="35 | proves" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">What it does NOT prove</p>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px">
                <div class="dw-chip">Not full C compliance</div>
                <div class="dw-chip">Not a Clang replacement</div>
                <div class="dw-chip">Not a production compiler</div>
                <div class="dw-chip">Not “AI removes validation”</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="36 | limits" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-canvas--untitled',
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <p class="dw-kicker">The Darkwood takeaway</p>
            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:center;gap:12px">
                <div class="dw-node">Compiler algorithms → plain PHP</div>
                <div class="dw-node">Operational pipelines → Darkwood Flow</div>
                <div class="dw-node">Correctness → executable evidence</div>
            </div>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="37 | takeaway" />
</x-darkwood.slide>

@php
$canvasAttributes = new \Illuminate\View\ComponentAttributeBag(array (
));
@endphp
<x-darkwood.slide variant="content" class="slidewire-slide slidewire-transition-slide dw-slide" :canvas-attributes="$canvasAttributes" data-transition="slide">
@php
$headingAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-header sw-header--content sw-content-header',
));
$titleAttributes = new \Illuminate\View\ComponentAttributeBag(array (
  'class' => 'sw-heading dw-heading mt-8',
));
@endphp
<x-darkwood.heading variant="content" :header-attributes="$headingAttributes" :title-attributes="$titleAttributes">
<x-slot:title>@verbatim
<span class="dw-accent">darkwood</span>
@endverbatim
</x-slot:title>
</x-darkwood.heading>
<x-darkwood.body>
@verbatim
<section class="dw-wrap">
            <div class="dw-flow" style="flex-wrap:wrap">
                <div class="dw-node">SQLite</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ARM64</div>
                <div class="dw-arrow">↓</div>
            </div>

            <p class="dw-note mt-8">It assembled. It linked. It queried SQLite.</p>
        </section>

@endverbatim
</x-darkwood.body>
<x-darkwood.footer label="" />
</x-darkwood.slide>
</x-darkwood.deck>
