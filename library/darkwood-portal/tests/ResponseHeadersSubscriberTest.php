<?php

declare(strict_types=1);

namespace App\Tests;

use App\EventSubscriber\ResponseHeadersSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ResponseHeadersSubscriberTest extends TestCase
{
    public function testCrossOriginIsolationIsEnabledForMarkedBlogArticle(): void
    {
        $response = $this->invokeSubscriber(
            'blog_article',
            '<iframe data-cross-origin-isolated src="https://apps.darkwood.com/game"></iframe>',
        );

        self::assertSame('same-origin', $response->headers->get('Cross-Origin-Opener-Policy'));
        self::assertSame('credentialless', $response->headers->get('Cross-Origin-Embedder-Policy'));
        self::assertSame(
            'cross-origin-isolated=(self "https://apps.darkwood.com")',
            $response->headers->get('Permissions-Policy'),
        );
    }

    public function testCrossOriginIsolationIsNotEnabledWithoutMarker(): void
    {
        $response = $this->invokeSubscriber('blog_article', '<p>Regular article</p>');

        self::assertFalse($response->headers->has('Cross-Origin-Opener-Policy'));
        self::assertFalse($response->headers->has('Cross-Origin-Embedder-Policy'));
        self::assertFalse($response->headers->has('Permissions-Policy'));
    }

    public function testCrossOriginIsolationIsNotEnabledOutsideBlogArticle(): void
    {
        $response = $this->invokeSubscriber(
            'darkwood_home',
            '<iframe data-cross-origin-isolated src="https://apps.darkwood.com/game"></iframe>',
        );

        self::assertFalse($response->headers->has('Cross-Origin-Opener-Policy'));
        self::assertFalse($response->headers->has('Cross-Origin-Embedder-Policy'));
        self::assertFalse($response->headers->has('Permissions-Policy'));
    }

    private function invokeSubscriber(string $route, string $content): Response
    {
        $request = Request::create('/');
        $request->attributes->set('_route', $route);
        $response = new Response($content);
        $event = new ResponseEvent(
            self::createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        (new ResponseHeadersSubscriber())($event);

        return $response;
    }
}
