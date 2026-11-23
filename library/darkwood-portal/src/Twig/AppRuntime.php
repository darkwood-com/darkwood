<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Article;
use App\Entity\PageTranslation;
use App\Service\PageService;
use App\Service\SeoService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SeoService $seoService,
        private readonly PageService $pageService,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    /**
     * Plain-text teaser without CommonMark (lists/RSS descriptions).
     */
    public function articleExcerpt(?string $content, int $length = 260): string
    {
        if (null === $content || '' === $content) {
            return '';
        }

        $text = preg_replace('/```[\s\S]*?```/', ' ', $content) ?? $content;
        $text = strip_tags($text);
        $text = preg_replace('/[#>*_`\[\]()!|~\-]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(0, $length - 3))) . '...';
    }

    /**
     * Cap markdown sources so pathological bodies cannot monopolize PHP workers.
     */
    public function limitMarkdownSource(?string $content, int $maxChars = AppExtension::MARKDOWN_MAX_CHARS): string
    {
        if (null === $content || '' === $content) {
            return '';
        }

        if (mb_strlen($content) <= $maxChars) {
            return $content;
        }

        return mb_substr($content, 0, $maxChars) . "\n\n…";
    }

    /**
     * @return array<string, mixed>
     */
    public function seoData(?PageTranslation $pageTranslation, ?Article $article = null): array
    {
        $empty = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'facebook' => ['site_name' => '', 'title' => '', 'description' => '', 'url' => '', 'type' => 'website', 'src' => ''],
            'twitter' => ['card' => 'summary', 'site' => '', 'title' => '', 'description' => '', 'src' => ''],
        ];

        if (!$pageTranslation instanceof PageTranslation) {
            return $empty;
        }

        $context = ['page' => $pageTranslation];
        if ($article instanceof Article) {
            $context['article'] = $article;
        }

        $seo = $this->seoService->getSeo($context);
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->attributes->get('_route') === 'blog_section' && $request->attributes->get('section') === 'auto') {
            $seo['title'] = 'Auto – ' . $seo['title'];
            $seo['facebook']['title'] = $seo['twitter']['title'] = $seo['title'];
            $seo['facebook']['url'] = $this->urlGenerator->generate('blog_section', ['_locale' => $request->getLocale(), 'section' => 'auto'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $seo;
    }

    /**
     * @return array{canonical: null|array{url: string, locale: string}, pageLinks: array<string, string>}
     */
    public function hreflangData(string $ref, mixed $entity = null): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return ['canonical' => null, 'pageLinks' => []];
        }

        if ($request->attributes->get('_route') === 'blog_section' && $request->attributes->get('section') === 'auto') {
            $pageLinks = [];
            foreach (array_keys($this->pageService->getPageLinks($ref, $entity, $request->getHost())) as $locale) {
                $pageLinks[$locale] = $this->urlGenerator->generate('blog_section', ['_locale' => $locale, 'section' => 'auto'], UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $locale = $request->getLocale();

            $canonical = $this->urlGenerator->generate('blog_section', ['_locale' => $locale, 'section' => 'auto'], UrlGeneratorInterface::ABSOLUTE_URL);

            return ['canonical' => ['locale' => $locale, 'url' => $canonical], 'pageLinks' => $pageLinks];
        }

        return [
            'canonical' => $this->pageService->getPageCanonical($ref, $entity, $request->getHost(), $request->getLocale()),
            'pageLinks' => $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale(), UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function pageLinks(string $ref, mixed $entity = null): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return [];
        }

        if ($request->attributes->get('_route') === 'blog_section' && $request->attributes->get('section') === 'auto') {
            return array_diff_key($this->hreflangData($ref, $entity)['pageLinks'], [$request->getLocale() => true]);
        }

        return $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale());
    }

    /**
     * @return list<array{label: string, link: string}>
     */
    public function appMenuLinks(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return [];
        }

        $apps = $this->pageService->findActives($request->getLocale(), 'app');
        $appLinks = [];
        foreach ($apps as $app) {
            $translation = $app->getOneTranslation();
            $appLinks[] = [
                'label' => (string) $translation->getTitle(),
                'link' => (string) $this->pageService->getUrl($translation),
            ];
        }

        return $appLinks;
    }
}
