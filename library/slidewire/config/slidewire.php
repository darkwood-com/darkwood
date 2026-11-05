<?php

declare(strict_types=1);

use Phiki\Theme\Theme;
use WendellAdriel\SlideWire\DTOs\FontConfig;
use WendellAdriel\SlideWire\DTOs\HighlightConfig;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\DTOs\ThemeConfig;
use WendellAdriel\SlideWire\DTOs\ThemeFont;
use WendellAdriel\SlideWire\Enums\FontSource;
use WendellAdriel\SlideWire\Enums\SlideTransition;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;

return [
    'presentation_roots' => [
        resource_path('views/pages/slides'),
    ],

    'slides' => new SlidesConfig(
        theme: 'default',
        showControls: true,
        showProgress: true,
        showFullscreenButton: true,
        keyboard: true,
        touch: true,
        transition: SlideTransition::Slide,
        transitionDuration: 350,
        transitionSpeed: SlideTransitionSpeed::Default,
        autoSlide: 0,
        autoSlidePauseOnInteraction: true,
        highlight: new HighlightConfig(
            enabled: true,
            theme: Theme::CatppuccinMocha,
            font: 'JetBrainsMono',
            fontSize: 'text-base',
        ),
    ),

    'themes' => [
        'default' => new ThemeConfig(
            background: 'bg-gradient-to-br from-slate-900 via-blue-950 to-slate-950 text-slate-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-200', size: 'text-lg'),
        ),
        'black' => new ThemeConfig(
            background: 'bg-slate-900 text-slate-200',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-200', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-300', size: 'text-lg'),
        ),
        'white' => new ThemeConfig(
            background: 'bg-white text-zinc-800',
            highlightTheme: Theme::CatppuccinLatte,
            title: new ThemeFont(font: 'Inter', color: 'text-zinc-800', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-zinc-600', size: 'text-lg'),
        ),
        'aurora' => new ThemeConfig(
            background: 'bg-gradient-to-br from-emerald-950 via-cyan-900 to-slate-950 text-emerald-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-emerald-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-cyan-100', size: 'text-lg'),
        ),
        'sunset' => new ThemeConfig(
            background: 'bg-gradient-to-br from-rose-950 via-orange-900 to-amber-700 text-orange-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-orange-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-amber-100', size: 'text-lg'),
        ),
        'neon' => new ThemeConfig(
            background: 'bg-gradient-to-br from-fuchsia-950 via-violet-900 to-cyan-900 text-fuchsia-50',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Inter', color: 'text-fuchsia-50', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-cyan-100', size: 'text-lg'),
        ),
        'solarized' => new ThemeConfig(
            background: 'bg-yellow-50 text-slate-600',
            highlightTheme: Theme::CatppuccinLatte,
            title: new ThemeFont(font: 'Inter', color: 'text-slate-700', size: 'text-4xl'),
            text: new ThemeFont(font: 'Inter', color: 'text-slate-600', size: 'text-lg'),
        ),
        'darkwood-original' => new ThemeConfig(
            background: 'bg-white text-zinc-950',
            highlightTheme: Theme::CatppuccinMocha,
            title: new ThemeFont(font: 'Jost', color: 'text-zinc-950', size: 'text-4xl'),
            text: new ThemeFont(font: 'Jost', color: 'text-zinc-900', size: 'text-lg'),
        ),
    ],

    'fonts' => [
        'Inter' => new FontConfig(source: FontSource::Google, weights: [400, 600, 700]),
        'JetBrainsMono' => new FontConfig(source: FontSource::Google, weights: [400, 700]),
        'Jost' => new FontConfig(source: FontSource::System),
        'Verdana' => new FontConfig(source: FontSource::System),
    ],
];
