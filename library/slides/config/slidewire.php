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
        theme: 'darkwood',
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
            theme: Theme::DarkPlus,
            font: 'JetBrains Mono',
            fontSize: 'text-base',
        ),
    ),

    'themes' => [
        'darkwood' => new ThemeConfig(
            background: 'bg-white text-black',
            highlightTheme: Theme::DarkPlus,
            title: new ThemeFont(font: 'Jost', color: 'text-black', size: 'text-4xl'),
            text: new ThemeFont(font: 'Jost', color: 'text-black', size: 'text-lg'),
        ),
    ],

    'fonts' => [
        'JetBrains Mono' => new FontConfig(source: FontSource::System),
        'Jost' => new FontConfig(source: FontSource::System),
        'Verdana' => new FontConfig(source: FontSource::System),
    ],
];
