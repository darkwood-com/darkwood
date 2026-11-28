<?php

declare(strict_types=1);

namespace Darkwood\IaExceptionBundle\EventSubscriber;

use Darkwood\IaExceptionBundle\Model\ExceptionContext;
use Darkwood\IaExceptionBundle\Service\ExceptionAiAnalyzer;
use Darkwood\IaExceptionBundle\Service\ExceptionContextStore;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Twig\Environment;

use function in_array;

final class IaExceptionSubscriber implements EventSubscriberInterface
{
    /** @param list<int> $onlyStatusCodes */
    public function __construct(
        private readonly ExceptionAiAnalyzer $analyzer,
        private readonly ExceptionContextStore $contextStore,
        private readonly ErrorRendererInterface $renderer,
        private readonly UrlGeneratorInterface $urls,
        private readonly Environment $twig,
        private readonly bool $debug,
        private readonly bool $enabled,
        private readonly array $onlyStatusCodes,
        private readonly bool $async,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', -64]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->debug || !$this->enabled || !$event->isMainRequest()
            || $request->getRequestFormat() !== 'html'
            || $request->getPreferredFormat() !== 'html'
            || $request->attributes->get('_route') === 'darkwood_ia_exception_async') {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        if (!in_array($statusCode, $this->onlyStatusCodes, true)) {
            return;
        }

        try {
            $error = $this->renderer->render($exception);
            $page = $error->getAsString();
            $bodyEnd = strrpos($page, '</body>');
            if ($bodyEnd === false) {
                return;
            }

            $errorId = bin2hex(random_bytes(16));
            $context = ExceptionContext::fromThrowable($exception);
            if ($this->async) {
                $url = $this->urls->generate('darkwood_ia_exception_async', ['error_id' => $errorId]);
                $this->contextStore->store($errorId, $context);
                $panel = $this->twig->render('@DarkwoodIaException/_ai_analysis_action.html.twig', ['url' => $url]);
            } else {
                $analysis = $this->analyzer->analyzeFromContext($context);
                if ($analysis === null) {
                    return;
                }

                $panel = $this->twig->render('@DarkwoodIaException/_ai_analysis_block.html.twig', [
                    'analysis' => $analysis,
                    'error_id' => $errorId,
                ]);
            }

            $response = new Response(substr_replace($page, $panel, $bodyEnd, 0), $error->getStatusCode(), $error->getHeaders());
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Robots-Tag', 'noindex');
            $event->setResponse($response);
        } catch (Throwable) {
            // Leave the exception to Symfony if the optional analysis cannot be presented.
        }
    }
}
