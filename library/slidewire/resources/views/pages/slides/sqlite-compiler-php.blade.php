{{-- I Built a C Compiler in PHP That Can Compile SQLite | /slides/sqlite-compiler-php --}}
<x-slidewire::deck theme="black" transition="fade" transition-speed="default" show-progress="true" show-controls="true" show-fullscreen-button="true">

    @php
        $php = '<span class="dw-accent">PHP</span>';
        $flow = '<span class="dw-accent">Flow</span>';
        $arm = '<span class="dw-accent">ARM64</span>';
        $sqlite = '<span class="dw-accent">SQLite</span>';
        $darkwood = '<span class="dw-accent">darkwood</span>';
    @endphp

    {{-- 1 · COVER --}}
    {{-- @notes Open with the result implied by the title. PHP → C frontend → ARM64 → SQLite. No provenance talk. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Darkwood · {!! $php !!} · C · {!! $arm !!} · {!! $sqlite !!}</p>
            <h1 class="dw-title">I Built a C Compiler<br>in PHP<br>That Can Compile SQLite</h1>
            <p class="dw-lead mt-6">{!! $php !!} → C frontend → {!! $arm !!} → {!! $sqlite !!}</p>
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
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

    {{-- 2 · THE QUESTION --}}
    {{-- @notes Pause on the question. The talk is how far a direct PHP compiler can go. ~20s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The question</p>
            <h2 class="dw-heading" style="font-size:clamp(1.6rem,3.6vw,2.6rem);">How much of a C compiler<br>can I implement in {!! $php !!}<br>before it can compile {!! $sqlite !!}?</h2>
        </section>
        <footer class="dw-footer"><span>01 / question</span></footer>
    </x-slidewire::slide>

    {{-- 3 · THE RESULT --}}
    {{-- @notes Show the acceptance path. darkwood is the smoke stdout. 46/46 fixtures as state, not a metrics dashboard. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The result</p>
            <div class="mt-4 dw-flow" style="flex-wrap:wrap;">
                <div class="dw-node">SQLite 3.46.0</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP compiler</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ARM64</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">native executable</div>
                <div class="dw-arrow">↓</div>
            </div>
            <h2 class="dw-heading mt-8" style="font-size:clamp(2.4rem,6vw,4rem);">{!! $darkwood !!}</h2>
            <x-slidewire::fragment :index="0">
                <p class="dw-note mt-6">46/46 regression fixtures</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>02 / result</span></footer>
    </x-slidewire::slide>

    {{-- 4 · WHAT COMPILED IN PHP MEANS --}}
    {{-- @notes Critical boundary: Clang does not compile sqlite3.c. PHP emits .s; as + linker finish. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">What “compiled in PHP” means</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>PHP</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">Lexer
Preprocessor
Parser
AST
Sema
ARM64 Codegen</pre>
                </div>
                <div class="dw-split-panel">
                    <h3>macOS</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">as
linker
native executable</pre>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-6">Clang does <strong>not</strong> compile <code>sqlite3.c</code>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>03 / boundary</span></footer>
    </x-slidewire::slide>

    {{-- 5 · SMALL ARCHITECTURE --}}
    {{-- @notes Deliberately no LLVM/SSA/CIR/custom assembler. Direct path. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
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
            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px;">
                <div class="dw-chip">No LLVM</div>
                <div class="dw-chip">No SSA</div>
                <div class="dw-chip">No CIR</div>
                <div class="dw-chip">No custom assembler</div>
            </div>
        </section>
        <footer class="dw-footer"><span>04 / architecture</span></footer>
    </x-slidewire::slide>

    {{-- 6 · DRIVER --}}
    {{-- @notes Real Compiler.php pipeline. Annotate SOURCE→TOKENS→AST→TYPES→ASM. ~50s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>src/Compiler/Driver/Compiler.php</code></p>
            <h2 class="dw-heading-slide">The driver</h2>
            <pre class="mt-4 dw-code" style="font-size:13px;">$tokens = $preprocessor->preprocess($fileId);

$parser = new Parser($tokens, $this->diagnostics);
$decls = $parser->parse();

$sema = new Sema($this->diagnostics);
$tast = $sema->analyze($decls);

$codegen = new Codegen($enumConstants);
$assembly = $codegen->generate($tast);</pre>
            <p class="dw-note mt-4">SOURCE → TOKENS → AST → TYPES → ASM</p>
        </section>
        <footer class="dw-footer"><span>05 / driver</span></footer>
    </x-slidewire::slide>

    {{-- 7 · SOURCE LOC --}}
    {{-- @notes Locations are offsets; SourceManager maps to line:col later. Survives whole frontend. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>SourceLoc.php</code></p>
            <h2 class="dw-heading-slide">Source locations</h2>
            <pre class="mt-4 dw-code" style="font-size:15px;">final readonly class SourceLoc
{
    public function __construct(
        public int $fileId,
        public int $offset,
    ) {}
}</pre>
            <div class="mt-6 dw-flow">
                <div class="dw-node">fileId + offset</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">SourceManager</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">line : column</div>
            </div>
        </section>
        <footer class="dw-footer"><span>06 / source-loc</span></footer>
    </x-slidewire::slide>

    {{-- 8 · TOKEN --}}
    {{-- @notes Highlight hideSet — set up macro story. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>Token.php</code></p>
            <h2 class="dw-heading-slide">A token is more than text</h2>
            <pre class="mt-4 dw-code" style="font-size:15px;">public function __construct(
    public TokenKind $kind,
    public string $spelling,
    public SourceLoc $loc,
    public array $hideSet = [],
) {}</pre>
            <x-slidewire::fragment :index="0">
                <p class="dw-question mt-6">Why does a token need a <span class="dw-accent">hideSet</span>?</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>07 / token</span></footer>
    </x-slidewire::slide>

    {{-- 9 · MACROS RECURSIVE --}}
    {{-- @notes Blue-painting intuition without drowning in C99. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Macro expansion needs memory</p>
            <pre class="mt-4 dw-code" style="font-size:18px;">#define A B
#define B A</pre>
            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:flex-start;gap:8px;">
                <div class="dw-node">A</div>
                <div class="dw-arrow">↓ expand</div>
                <div class="dw-node">B &nbsp;<span class="dw-accent">[hide A]</span></div>
                <div class="dw-arrow">↓ expand</div>
                <div class="dw-node">A &nbsp;<span class="dw-accent">[hide A,B]</span></div>
                <div class="dw-arrow">↓</div>
                <div class="dw-chip">STOP</div>
            </div>
        </section>
        <footer class="dw-footer"><span>08 / hide-set</span></footer>
    </x-slidewire::slide>

    {{-- 10 · MACROEXPANDER --}}
    {{-- @notes Real MacroExpander excerpt. Rescan via pending stack. ~50s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>MacroTable.php</code> · MacroExpander</p>
            <h2 class="dw-heading-slide">Inside the preprocessor</h2>
            <pre class="mt-4 dw-code" style="font-size:14px;">if ($token->hideSet !== []
    && in_array($token->spelling, $token->hideSet, true)) {
    $output[] = $token;
    continue;
}

$newHideSet = $this->unionHideSet($token->hideSet, [$macro->name]);
$expanded = $this->expandObjectLike(...);
for ($i = count($expanded) - 1; $i >= 0; --$i) {
    $pendingReversed[] = $expanded[$i];
}</pre>
        </section>
        <footer class="dw-footer"><span>09 / expander</span></footer>
    </x-slidewire::slide>

    {{-- 11 · PREPROCESSOR SUBSYSTEM --}}
    {{-- @notes Checklist: this is not regex. SQLite never reaches parser unless it works. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The preprocessor is a compiler subsystem</p>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:10px;">
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
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-8">SQLite never reaches the parser unless this works.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>10 / preproc</span></footer>
    </x-slidewire::slide>

    {{-- 12 · PARSER --}}
    {{-- @notes Hand-written recursive descent + Pratt. AST shapes. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Parsing C by hand</p>
            <h2 class="dw-heading-slide">No parser generator</h2>
            <div class="mt-6 dw-flow">
                <div class="dw-node">recursive descent</div>
                <div class="dw-arrow">+</div>
                <div class="dw-node">Pratt expressions</div>
            </div>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:10px;">
                <div class="dw-chip"><code>FuncDecl</code></div>
                <div class="dw-chip"><code>BinaryExpr</code></div>
                <div class="dw-chip"><code>CallExpr</code></div>
                <div class="dw-chip"><code>CompoundLiteralExpr</code></div>
            </div>
        </section>
        <footer class="dw-footer"><span>11 / parser</span></footer>
    </x-slidewire::slide>

    {{-- 13 · DECLARATORS --}}
    {{-- @notes Strong visual: array of pointers ≠ pointer to array. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">C declarators fight back</p>
            <div class="mt-6 dw-split">
                <div class="dw-split-panel">
                    <pre class="dw-code" style="font-size:20px;margin:0;">int *p[4];</pre>
                    <p class="dw-note mt-4">array of pointers</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <pre class="dw-code" style="font-size:20px;margin:0;">int (*p)[4];</pre>
                    <p class="dw-note mt-4">pointer to array</p>
                </div>
            </div>
            <h2 class="dw-heading mt-8" style="font-size:clamp(1.4rem,3vw,2rem);">≠</h2>
        </section>
        <footer class="dw-footer"><span>12 / declarators</span></footer>
    </x-slidewire::slide>

    {{-- 14 · APPLY ARRAY SUFFIX --}}
    {{-- @notes applyArraySuffix binds [N] to pointee when type is PointerCType. Fixture 051. ~60s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>Parser::applyArraySuffix()</code></p>
            <pre class="mt-3 dw-code" style="font-size:13px;">if ($type instanceof PointerCType) {
    $to = $type->to;
    $array = new ArrayCType($to, $count);
    return new PointerCType($array);
}</pre>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3><code>int *p[4]</code></h3>
                    <pre class="dw-code" style="font-size:12px;margin:0;">Array
 └── Pointer
      └── int</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3><code>int (*p)[4]</code></h3>
                    <pre class="dw-code" style="font-size:12px;margin:0;">Pointer
 └── Array[4]
      └── int</pre>
                </div>
            </div>
        </section>
        <footer class="dw-footer"><span>13 / applyArraySuffix</span></footer>
    </x-slidewire::slide>

    {{-- 15 · AST --}}
    {{-- @notes Parser builds structure; Sema fills resolvedType. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>Ast.php</code></p>
            <h2 class="dw-heading-slide">The AST is just PHP</h2>
            <pre class="mt-4 dw-code" style="font-size:13px;">final class BinaryExpr extends Expr
{
    public function __construct(
        public BinaryOp $op,
        public Expr $left,
        public Expr $right,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {}
}</pre>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-6">Parser builds structure. Sema gives it meaning.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>14 / ast</span></footer>
    </x-slidewire::slide>

    {{-- 16 · CTYPE --}}
    {{-- @notes Type tree + instanceof dispatch. EnumCType is integer. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker"><code>CType.php</code></p>
            <h2 class="dw-heading-slide">Representing C types in PHP</h2>
            <pre class="mt-3 dw-code" style="font-size:13px;">CType
├── IntCType / CharCType / …
├── PointerCType
├── ArrayCType
├── FunctionCType
├── StructCType / UnionCType
└── EnumCType</pre>
            <x-slidewire::fragment :index="0">
                <pre class="mt-4 dw-code" style="font-size:13px;">$this instanceof EnumCType => true,  // isInteger()</pre>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>15 / ctype</span></footer>
    </x-slidewire::slide>

    {{-- 17 · ARRAY VS POINTER --}}
    {{-- @notes Sets up pointer arithmetic bug. Array type remains ArrayCType; expression behaves pointer-like. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Array ≠ pointer — except when it is</p>
            <pre class="mt-4 dw-code" style="font-size:18px;">int a[3];
int *p = a + 1;</pre>
            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:flex-start;gap:8px;">
                <div class="dw-node">ArrayCType remains</div>
                <div class="dw-arrow">↓ expression context</div>
                <div class="dw-node">pointer-like behavior</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-chip">+ 1 means + sizeof(int)</div>
            </div>
        </section>
        <footer class="dw-footer"><span>16 / array-ptr</span></footer>
    </x-slidewire::slide>

    {{-- 18 · SEMA --}}
    {{-- @notes Scopes/symbols/enums. VALUE → .long 42. ~50s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Semantic analysis</p>
            <div class="mt-4 dw-grid dw-grid-3" style="gap:8px;">
                <div class="dw-chip">Scopes</div>
                <div class="dw-chip">Symbols</div>
                <div class="dw-chip">Typedefs</div>
                <div class="dw-chip">Enums</div>
                <div class="dw-chip">Types</div>
                <div class="dw-chip">Conversions</div>
            </div>
            <pre class="mt-4 dw-code" style="font-size:14px;">$this->enumConstants[$case->name] = $case->value;</pre>
            <div class="mt-4 dw-flow">
                <div class="dw-node"><code>enum { VALUE = 42 };</code></div>
                <div class="dw-arrow">→</div>
                <div class="dw-node"><code>.long 42</code></div>
            </div>
        </section>
        <footer class="dw-footer"><span>17 / sema</span></footer>
    </x-slidewire::slide>

    {{-- 19 · GLOBAL INIT --}}
    {{-- @notes Live .byte emission. Transition to ArrayCType bug. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Global initialization is codegen too</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>C</h3>
                    <pre class="dw-code" style="font-size:13px;margin:0;">static unsigned char table[4] = {
    84, 92, 134, 0
};</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>ARM64</h3>
                    <pre class="dw-code" style="font-size:13px;margin:0;">_table:
.byte 84
.byte 92
.byte 134
.byte 0</pre>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <p class="dw-question mt-6">This once became <code>.quad</code>.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>18 / globals</span></footer>
    </x-slidewire::slide>

    {{-- 20 · ARRAYCTYPE BUG --}}
    {{-- @notes Memorable slide. PHP namespace → wrong C layout. Assembled, linked, wrong. ~75s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">A PHP namespace bug corrupted C memory</p>
            <pre class="mt-3 dw-code" style="font-size:16px;">instanceof ArrayCType</pre>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-ok">
                    <h3>Expected</h3>
                    <p style="margin:0;font-size:14px;"><code>App\Compiler\Common\ArrayCType</code></p>
                </div>
                <div class="dw-split-panel is-warn">
                    <h3>PHP resolved</h3>
                    <p style="margin:0;font-size:14px;"><code>App\Compiler\CodeGen\ArrayCType</code></p>
                </div>
            </div>
            <x-slidewire::fragment :index="0">
                <div class="mt-4 dw-flow" style="flex-wrap:wrap;font-size:14px;">
                    <div class="dw-chip">wrong class</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">wrong branch</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">.quad</div>
                    <div class="dw-arrow">↓</div>
                    <div class="dw-chip">runtime fail</div>
                </div>
            </x-slidewire::fragment>
            <x-slidewire::fragment :index="1">
                <p class="dw-takeaway mt-4">It assembled. It linked. It was wrong.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>19 / ArrayCType</span></footer>
    </x-slidewire::slide>

    {{-- 21 · ADD → ARM64 --}}
    {{-- @notes Real generated add. Annotate regs. ~50s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">From AST to ARM64</p>
            <div class="mt-3 dw-split">
                <div class="dw-split-panel">
                    <pre class="dw-code" style="font-size:13px;margin:0;">int add(int a, int b)
{
    return a + b;
}</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <pre class="dw-code" style="font-size:11px;margin:0;">_add:
stp x29, x30, [sp, #-16]!
mov x29, sp
sub sp, sp, #16
str x0, [x29, #-8]
str x1, [x29, #-16]
… ldrsw … add …
mov x0, x9
ldp x29, x30, [sp], #16
ret</pre>
                </div>
            </div>
            <div class="mt-4 dw-grid dw-grid-2" style="gap:8px;">
                <div class="dw-chip">x0 / x1 args</div>
                <div class="dw-chip">x29 frame · x30 LR</div>
                <div class="dw-chip">x0 return</div>
                <div class="dw-chip">x9–x15 scratch</div>
            </div>
        </section>
        <footer class="dw-footer"><span>20 / arm64-add</span></footer>
    </x-slidewire::slide>

    {{-- 22 · STACK FRAME --}}
    {{-- @notes Placeholder then patch. Alignment. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Stack frame</p>
            <pre class="mt-3 dw-code" style="font-size:13px;">high
──────────────
saved x29 / x30
──────────────
params · locals
temps · compounds
──────────────
SP
low</pre>
            <pre class="mt-4 dw-code" style="font-size:14px;">sub sp, sp, #0  ; FRAME_SIZE_PLACEHOLDER</pre>
            <p class="dw-note mt-3">Patched after emission · 16-byte aligned</p>
        </section>
        <footer class="dw-footer"><span>21 / frame</span></footer>
    </x-slidewire::slide>

    {{-- 23 · POINTER ARITH --}}
    {{-- @notes Wrong byte add vs sizeof scale. Fixture 035. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Pointer arithmetic means scaling</p>
            <pre class="mt-4 dw-code" style="font-size:18px;">int a[3];
a + 1</pre>
            <div class="mt-6 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>Wrong</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">add x?, x?, #1</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Correct</h3>
                    <p style="margin:0;">address + 1 × sizeof(int)</p>
                    <pre class="dw-code" style="font-size:13px;margin-top:8px;">lsl …, #2</pre>
                </div>
            </div>
            <p class="dw-note mt-4"><code>035-array-ptr-arith</code></p>
        </section>
        <footer class="dw-footer"><span>22 / scale</span></footer>
    </x-slidewire::slide>

    {{-- 24 · CALLS --}}
    {{-- @notes bl vs blr. Function pointers matter for SQLite callbacks. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Direct vs indirect calls</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>Direct</h3>
                    <pre class="dw-code" style="font-size:16px;margin:0;">bl _function</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Function pointer</h3>
                    <pre class="dw-code" style="font-size:16px;margin:0;">blr xN</pre>
                </div>
            </div>
            <pre class="mt-6 dw-code" style="font-size:14px;">int call(int (*fn)(int), int value)
{
    return fn(value);
}</pre>
        </section>
        <footer class="dw-footer"><span>23 / calls</span></footer>
    </x-slidewire::slide>

    {{-- 25 · ABI --}}
    {{-- @notes Transition: valid asm ≠ ABI correct. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The ABI is where “valid assembly” stops</p>
            <h2 class="dw-heading-slide">syntax correct<br><span class="dw-accent">≠</span><br>ABI correct</h2>
            <div class="mt-8 dw-grid dw-grid-3" style="gap:8px;">
                <div class="dw-chip">arguments</div>
                <div class="dw-chip">register lifetime</div>
                <div class="dw-chip">stack alignment</div>
                <div class="dw-chip">variadics</div>
                <div class="dw-chip">structs</div>
                <div class="dw-chip">aggregate returns</div>
            </div>
        </section>
        <footer class="dw-footer"><span>24 / abi</span></footer>
    </x-slidewire::slide>

    {{-- 26 · STRUCT RETURNS --}}
    {{-- @notes Loop 48 / 054. Preserve x0 and x1. ~55s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Struct returns use registers too</p>
            <div class="mt-4 dw-flow">
                <div class="dw-node">≤16 bytes</div>
                <div class="dw-arrow">→</div>
                <div class="dw-chip">x0 first word</div>
                <div class="dw-chip">x1 second word</div>
            </div>
            <pre class="mt-4 dw-code" style="font-size:15px;">struct S b(void) { return a(); }</pre>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel is-warn">
                    <h3>Bug</h3>
                    <p style="margin:0;">preserve only part of x0/x1</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Fix</h3>
                    <pre class="dw-code" style="font-size:13px;margin:0;">str x0, [...]
str x1, [...]</pre>
                </div>
            </div>
            <p class="dw-note mt-3"><code>054-struct-return-chain</code> · Loop 48</p>
        </section>
        <footer class="dw-footer"><span>25 / struct-ret</span></footer>
    </x-slidewire::slide>

    {{-- 27 · WHY SQLITE --}}
    {{-- @notes Integration oracle — subsystems must interact. No perf numbers. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">SQLite is an integration oracle</p>
            <h2 class="dw-heading-slide mt-2" style="text-align:center;">{!! $sqlite !!}</h2>
            <div class="mt-6 dw-grid dw-grid-3" style="gap:8px;">
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
        <footer class="dw-footer"><span>26 / sqlite</span></footer>
    </x-slidewire::slide>

    {{-- 28 · FIXTURE LOOP --}}
    {{-- @notes Methodology bridge into iteration. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Failure → fixture → fix</p>
            <div class="mt-4 dw-embed" style="height:min(380px,48vh)">
                <x-slidewire::diagram>
flowchart TB
  A[SQLite / clang mismatch]
  B[reduce]
  C[tiny C fixture]
  D[find subsystem]
  E[fix one behavior]
  F[all fixtures]
  G[SQLite]
  A --> B --> C --> D --> E --> F --> G
  G -.-> A
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>27 / methodology</span></footer>
    </x-slidewire::slide>

    {{-- 29 · FIXTURES --}}
    {{-- @notes Selected fixture names as permanent knowledge. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
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
        <footer class="dw-footer"><span>28 / fixtures</span></footer>
    </x-slidewire::slide>

    {{-- 30 · TWO PIPELINES --}}
    {{-- @notes Where should Flow live? Left algorithm, right operation. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Two different pipelines</p>
            <div class="mt-4 dw-split">
                <div class="dw-split-panel">
                    <h3>COMPILER</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">tokens
 ↓ AST
 ↓ Sema
 ↓ ARM64</pre>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>OPERATION</h3>
                    <pre class="dw-code" style="font-size:14px;margin:0;">compile
 ↓ assemble
 ↓ link
 ↓ run
 ↓ validate</pre>
                </div>
            </div>
            <p class="dw-question mt-6">Where should {!! $flow !!} live?</p>
        </section>
        <footer class="dw-footer"><span>29 / two-pipelines</span></footer>
    </x-slidewire::slide>

    {{-- 31 · FLOW FITS --}}
    {{-- @notes Real SqliteValidationFlow yields. darkwood/flow v8.1.5. ~55s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">Where Darkwood {!! $flow !!} fits</p>
            <div class="mt-3 dw-split">
                <div class="dw-split-panel">
                    <h3>Compiler internals</h3>
                    <p style="margin:0;">plain PHP</p>
                </div>
                <div class="dw-split-panel is-ok">
                    <h3>Operational pipeline</h3>
                    <p style="margin:0;">darkwood/flow</p>
                </div>
            </div>
            <pre class="mt-4 dw-code" style="font-size:12px;">yield $this->timed('compile_sqlite', $this->compileSqlite);
yield $this->timed('assemble_sqlite', $this->assembleSqlite);
yield $this->timed('compile_harness', $this->compileHarness);
yield $this->timed('assemble_harness', $this->assembleHarness);
yield $this->timed('link', $this->linkSmokeExecutable);
yield $this->timed('run', $this->runSmokeTest);</pre>
        </section>
        <footer class="dw-footer"><span>30 / flow</span></footer>
    </x-slidewire::slide>

    {{-- 32 · ONE COMMAND --}}
    {{-- @notes Expand app:compiler-sqlite into artifacts. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap dw-wrap--top">
            <p class="dw-kicker">One command, many artifacts</p>
            <pre class="mt-3 dw-code" style="font-size:14px;">php bin/console app:compiler-sqlite</pre>
            <div class="mt-4 dw-embed" style="height:min(360px,46vh)">
                <x-slidewire::diagram>
flowchart TB
  C[sqlite3.c] --> S[sqlite3.s]
  S --> O[sqlite3.o]
  H[harness.c] --> HS[harness.s]
  HS --> HO[harness.o]
  O --> L[link]
  HO --> L
  L --> E[executable]
  E --> D[darkwood]
                </x-slidewire::diagram>
            </div>
        </section>
        <footer class="dw-footer"><span>31 / command</span></footer>
    </x-slidewire::slide>

    {{-- 33 · ENGINEERING LOOP --}}
    {{-- @notes Not “AI wrote a compiler”. Executable feedback. ~40s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The engineering loop</p>
            <div class="mt-4 dw-flow" style="flex-wrap:wrap;">
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
        <footer class="dw-footer"><span>32 / iterate</span></footer>
    </x-slidewire::slide>

    {{-- 34 · WAKE LOOP --}}
    {{-- @notes Tick ≠ engineering loop ≠ KEEP. Loop 48 last retained. ~45s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The five-minute wake loop</p>
            <div class="mt-6 dw-map">
                <div><strong>WAKE TICK</strong><span>resume + probe</span></div>
                <div><strong>ENGINEERING LOOP</strong><span>investigated change</span></div>
                <div><strong>KEEP</strong><span>survives validation</span></div>
            </div>
            <p class="dw-note mt-8">Loop 48 = last retained engineering change</p>
        </section>
        <footer class="dw-footer"><span>33 / wake</span></footer>
    </x-slidewire::slide>

    {{-- 35 · NOTHING CHANGED --}}
    {{-- @notes Natural ending. No mismatch → stop changing. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">Eventually</p>
            <h2 class="dw-heading" style="font-size:clamp(2.2rem,5vw,3.5rem);">No mismatch.</h2>
            <p class="dw-lead mt-6">46/46<br>SQLite → {!! $darkwood !!}</p>
            <x-slidewire::fragment :index="0">
                <p class="dw-takeaway mt-8">The correct decision was to stop changing the compiler.</p>
            </x-slidewire::fragment>
        </section>
        <footer class="dw-footer"><span>34 / stop</span></footer>
    </x-slidewire::slide>

    {{-- 36 · PROVES --}}
    {{-- @notes Restrained claims. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What this proves</p>
            <div class="mt-6 dw-map">
                <div><strong>PHP</strong><span>can host a non-trivial compiler</span></div>
                <div><strong>Direct path</strong><span>frontend → ARM64 can go surprisingly far</span></div>
                <div><strong>SQLite</strong><span>forces subsystems to become honest</span></div>
                <div><strong>Feedback</strong><span>executable evidence beats plausible code</span></div>
            </div>
        </section>
        <footer class="dw-footer"><span>35 / proves</span></footer>
    </x-slidewire::slide>

    {{-- 37 · DOES NOT PROVE --}}
    {{-- @notes Limits. Clean visual. ~30s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">What it does NOT prove</p>
            <div class="mt-8 dw-grid dw-grid-2" style="gap:12px;">
                <div class="dw-chip">Not full C compliance</div>
                <div class="dw-chip">Not a Clang replacement</div>
                <div class="dw-chip">Not a production compiler</div>
                <div class="dw-chip">Not “AI removes validation”</div>
            </div>
        </section>
        <footer class="dw-footer"><span>36 / limits</span></footer>
    </x-slidewire::slide>

    {{-- 38 · TAKEAWAY --}}
    {{-- @notes Darkwood architectural takeaway. ~35s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <p class="dw-kicker">The Darkwood takeaway</p>
            <div class="mt-6 dw-flow" style="flex-direction:column;align-items:center;gap:12px;">
                <div class="dw-node">Compiler algorithms → plain PHP</div>
                <div class="dw-node">Operational pipelines → Darkwood Flow</div>
                <div class="dw-node">Correctness → executable evidence</div>
            </div>
        </section>
        <footer class="dw-footer"><span>37 / takeaway</span></footer>
    </x-slidewire::slide>

    {{-- 39 · FINAL --}}
    {{-- @notes Close on darkwood. Optional line. ~20s. --}}
    <x-slidewire::slide class="dw-slide">
        <section class="dw-wrap">
            <div class="dw-flow" style="flex-wrap:wrap;">
                <div class="dw-node">SQLite</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">PHP</div>
                <div class="dw-arrow">↓</div>
                <div class="dw-node">ARM64</div>
                <div class="dw-arrow">↓</div>
            </div>
            <h2 class="dw-heading mt-8" style="font-size:clamp(2.4rem,6vw,4rem);">{!! $darkwood !!}</h2>
            <p class="dw-note mt-8">It assembled. It linked. It queried SQLite.</p>
        </section>
        <footer class="dw-footer"><span>@matyo91</span><img src="/darkwood/logos/dw512x512-light.png" alt="Darkwood"></footer>
    </x-slidewire::slide>

</x-slidewire::deck>
