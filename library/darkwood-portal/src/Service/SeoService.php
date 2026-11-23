<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\App;
use App\Entity\Article;
use App\Entity\PageTranslation;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class SeoService.
 *
 * Object manager of site.
 */
class SeoService
{
    public function __construct(
        protected CacheInterface $appCache,
        protected RouterInterface $router,
        protected UploaderHelper $uploaderHelper,
        protected PageService $pageService,
    ) {}

    public function getSeo($context, $force = false)
    {
        /** @var PageTranslation $pageTranslation */
        $pageTranslation = $context['page'];
        $cacheId = 'seo-' . $pageTranslation->getId();
        if (isset($context['article']) && $context['article'] instanceof Article) {
            $articleTranslation = $context['article']->getOneTranslation($pageTranslation->getLocale());
            $cacheId .= '-article_' . $articleTranslation->getId();
        }

        return $this->appCache->get($cacheId, function (ItemInterface $item) use ($context) {
            $item->expiresAfter(43200); // 12 hours
            /** @var PageTranslation $pageTranslation */
            $pageTranslation = $context['page'];
            $canonicalUrl = $this->pageService->getUrl($pageTranslation, RouterInterface::ABSOLUTE_URL) ?? '';
            $data = [
                'title' => $pageTranslation->getSeoTitle() ?? $pageTranslation->getTitle(),
                'description' => $pageTranslation->getSeoDescription() ?? $pageTranslation->getDescription(),
                'keywords' => $pageTranslation->getSeoKeywords(),
                'facebook' => [
                    'title' => $pageTranslation->getOgTitle() ?? $pageTranslation->getTitle(),
                    'description' => $pageTranslation->getOgDescription() ?? $pageTranslation->getDescription(),
                    'type' => $pageTranslation->getOgType() ?? 'article',
                    'url' => $canonicalUrl,
                    'site_name' => $pageTranslation->getPage()->getSite()->getName(),
                    'src' => $this->uploaderHelper->asset($pageTranslation, 'ogImage') ?? $this->uploaderHelper->asset($pageTranslation, 'image'),
                ],
                'twitter' => [
                    'card' => $pageTranslation->getTwitterCard() ?? 'summary',
                    'title' => $pageTranslation->getTwitterTitle() ?? $pageTranslation->getTitle(),
                    'description' => $pageTranslation->getTwitterDescription() ?? $pageTranslation->getDescription(),
                    'site' => $pageTranslation->getTwitterSite(),
                    'src' => $this->uploaderHelper->asset($pageTranslation, 'twitterImage') ?? $this->uploaderHelper->asset($pageTranslation, 'image'),
                ],
            ];
            $page = $pageTranslation->getPage();
            if ($page instanceof App) {
                $data = array_replace_recursive($data, ['facebook' => ['src' => $this->uploaderHelper->asset($page, 'banner')], 'twitter' => ['src' => $this->uploaderHelper->asset($page, 'banner')]]);
            }

            if (isset($context['article']) && $context['article'] instanceof Article) {
                $articleTranslation = $context['article']->getOneTranslation($pageTranslation->getLocale());
                $articleUrl = $this->pageService->getUrl($articleTranslation, RouterInterface::ABSOLUTE_URL) ?? '';
                $articleDescription = $this->getArticleDescription((string) $articleTranslation->getContent());
                $data = array_replace_recursive($data, [
                    'title' => $articleTranslation->getTitle(),
                    'description' => $articleDescription,
                    'facebook' => [
                        'src' => $this->uploaderHelper->asset($articleTranslation, 'image'),
                        'title' => $articleTranslation->getTitle(),
                        'description' => $articleDescription,
                        'url' => $articleUrl,
                    ],
                    'twitter' => [
                        'src' => $this->uploaderHelper->asset($articleTranslation, 'image'),
                        'title' => $articleTranslation->getTitle(),
                        'description' => $articleDescription,
                    ],
                ]);
            }

            return $data;
        }, $force ? INF : null);
    }

    private function getArticleDescription(string $content): string
    {
        $content = preg_replace('/```.*?```/s', '', $content) ?? $content;
        foreach (preg_split('/\R{2,}/', $content) ?: [] as $paragraph) {
            $paragraph = trim($paragraph);
            if ('' === $paragraph || str_starts_with($paragraph, '#') || str_starts_with($paragraph, ':::') || str_starts_with($paragraph, '![')) {
                continue;
            }

            $paragraph = preg_replace('/\[([^]]+)]\([^)]+\)/', '$1', $paragraph) ?? $paragraph;
            $paragraph = strip_tags($paragraph);
            $paragraph = preg_replace('/[*_`~]+/', '', $paragraph) ?? $paragraph;
            $paragraph = preg_replace('/\s+/', ' ', $paragraph) ?? $paragraph;
            $paragraph = trim($paragraph);
            if ('' === $paragraph) {
                continue;
            }

            return mb_strlen($paragraph) > 240 ? mb_substr($paragraph, 0, 237) . '…' : $paragraph;
        }

        return '';
    }
}
