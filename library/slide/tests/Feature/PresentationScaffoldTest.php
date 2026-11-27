<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class PresentationScaffoldTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/native-scaffold-'.Str::uuid());
        config(['slidewire.presentation_roots' => [$this->directory]]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_default_command_generates_native_slides_and_escapes_plain_text_titles(): void
    {
        $title = '{{ 1 + 1 }} @endphp 🐘 <em>titre</em>';
        $this->artisan('make:slidewire', ['name' => 'example', '--title' => $title])->assertSuccessful();
        $compiler = app(PresentationCompiler::class);
        $compiled = $compiler->compile('example');
        $slides = $compiler->flattenSlides($compiled['slides']);
        $this->assertSame('darkwood', $compiled['deck_meta']['theme']);
        $this->assertCount(2, $slides);
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$slides[0]->html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $this->assertSame($title, trim($dom->getElementsByTagName('h1')->item(0)->textContent));
        $this->assertStringContainsString('class="sw-footer"', $slides[0]->html);
        $this->assertStringContainsString('class="sw-body"', $slides[1]->html);
        $this->assertStringNotContainsString('bg-slate', File::get($this->directory.'/example.blade.php'));
    }

    public function test_markdown_and_multi_file_options_still_use_the_native_layout(): void
    {
        $this->artisan('make:slidewire', ['name' => 'example', '--md' => true, '--multi' => true])->assertSuccessful();
        $this->assertFileExists($this->directory.'/example/deck.blade.php');
        $this->assertSame([], File::glob($this->directory.'/example/*.md'));
        $compiler = app(PresentationCompiler::class);
        $compiled = $compiler->compile('example');
        $slides = $compiler->flattenSlides($compiled['slides']);
        $this->assertSame('darkwood', $compiled['deck_meta']['theme']);
        $this->assertCount(2, $slides);
        $this->assertStringContainsString('<ul>', $slides[1]->html);
        $this->assertStringContainsString('class="sw-footer"', $slides[1]->html);
    }

    public function test_scaffolding_does_not_overwrite_sources_or_change_canonical_identities_implicitly(): void
    {
        $registry = hash_file('sha256', resource_path('presentations.json'));
        $this->artisan('make:slidewire', ['name' => 'example'])->assertSuccessful();
        $before = File::get($this->directory.'/example.blade.php');
        $this->artisan('make:slidewire', ['name' => 'example'])->assertFailed();
        $this->assertSame($before, File::get($this->directory.'/example.blade.php'));
        $this->artisan('make:slidewire', ['name' => '../outside'])->assertFailed();
        $this->artisan('make:slidewire', ['name' => 'example', '--force' => true, '--title' => 'Nouveau titre'])->assertSuccessful();
        $this->assertSame($registry, hash_file('sha256', resource_path('presentations.json')));
    }
}
