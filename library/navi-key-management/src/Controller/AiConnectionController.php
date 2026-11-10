<?php

declare(strict_types=1);

namespace App\Controller;

use App\AiCostTable;
use App\CredentialProtector;
use App\Entity\AiConnection;
use App\Form\AiConnectionInput;
use App\KeyBackendInfo;
use App\Navi\TestAiConnectionAction;
use Doctrine\ORM\EntityManagerInterface;
use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AiConnectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CredentialProtector $credentials,
        private KeyBackendInfo $backend,
        private WorkflowRunner $workflows,
        private HttpClientInterface $httpClient,
        private AiCostTable $costTable,
    ) {
    }

    #[Route('/', name: 'connection_index', methods: ['GET'])]
    public function index(): Response
    {
        $connections = $this->entityManager->getRepository(AiConnection::class)->findBy([], ['id' => 'ASC']);

        return $this->render('connection/index.html.twig', [
            'connections' => $connections,
            'backend' => $this->backend->name(),
        ]);
    }

    #[Route('/connections/new', name: 'connection_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new AiConnection(), ['Default', 'create']);
    }

    #[Route('/connections/{id}/edit', name: 'connection_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AiConnection $connection): Response
    {
        return $this->handleForm($request, $connection, ['Default']);
    }

    #[Route('/connections/{id}/test', name: 'connection_test', methods: ['POST'])]
    public function test(Request $request, AiConnection $connection): Response
    {
        $this->validateCsrf('test_connection', $request);

        $apiKey = $this->credentials->decrypt($connection);

        try {
            $result = $this->workflows->run(
                Context::fromArray([
                    'connection_id' => $connection->getId(),
                    'provider' => $connection->getProvider(),
                    'model' => $connection->getModel(),
                ]),
                [
                    new TestAiConnectionAction(
                        $apiKey,
                        $connection->getProvider(),
                        $connection->getModel(),
                        $connection->getEndpoint(),
                        $this->httpClient,
                        $this->costTable,
                    ),
                ],
            );
        } finally {
            $apiKey = str_repeat("\0", \strlen($apiKey));
            unset($apiKey);
        }

        $context = $result->state()->context()->toArray();

        return $this->render('connection/test.html.twig', [
            'connection' => $connection,
            'backend' => $this->backend->name(),
            'ok' => (bool) ($context['test_ok'] ?? false),
            'reply' => $context['test_reply'] ?? null,
            'error' => $context['test_error'] ?? null,
            'tokens' => $context['tokens'] ?? [],
            'cost' => $context['cost'] ?? null,
        ]);
    }

    /**
     * @param list<string> $validationGroups
     */
    private function handleForm(Request $request, AiConnection $connection, array $validationGroups): Response
    {
        $input = new AiConnectionInput();
        if ($connection->getId() !== null) {
            $input->label = $connection->getLabel();
            $input->provider = $connection->getProvider();
            $input->model = $connection->getModel();
            $input->endpoint = $connection->getEndpoint();
        }

        $form = $this->createForm(AiConnectionInput::class, $input, [
            'validation_groups' => $validationGroups,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $connection->setLabel($input->label);
            $connection->setProvider($input->provider);
            $connection->setModel($input->model);
            $connection->setEndpoint($input->endpoint);

            if (\is_string($input->apiKey) && $input->apiKey !== '') {
                $this->credentials->persist($connection, $this->credentials->encrypt($input->apiKey));
            }

            $this->entityManager->persist($connection);
            $this->entityManager->flush();

            return $this->redirectToRoute('connection_index');
        }

        return $this->render('connection/form.html.twig', [
            'form' => $form,
            'connection' => $connection,
            'backend' => $this->backend->name(),
            'is_new' => $connection->getId() === null,
        ]);
    }

    private function validateCsrf(string $id, Request $request): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
