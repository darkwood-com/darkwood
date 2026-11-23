<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Entity\Program;
use App\Model\Page;
use App\Repository\FolderRepository;
use App\Repository\ProgramRepository;
use App\Repository\User\ShopUserRepository;
use App\Service\FolderService;
use App\Service\ProgramService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/', name: 'app_shop_')]
class FeedController extends AbstractController
{
    public function __construct(
        private readonly ShopUserRepository $shopUserRepository,
        private readonly ProgramRepository $programRepository,
        private readonly ProgramService $programService,
        private readonly FolderRepository $folderRepository,
        private readonly FolderService $folderService,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    #[Route('/embed/flows/{path}', name: 'flow_embed', methods: ['GET'], requirements: ['path' => '[a-z0-9]+(?:-[a-z0-9]+)*'])]
    public function embed(string $path): Response
    {
        $user = $this->shopUserRepository->findOneByUidOrUsername('public');
        if (!$user instanceof UserInterface) {
            throw $this->createNotFoundException('User not found');
        }

        $program = $this->programRepository->findOneByUserAndPath($user, $path);
        if (!$program instanceof Program || !$program->getPublic()) {
            throw $this->createNotFoundException('Program not found');
        }

        $response = $this->render('shop/feed/embed.html.twig', [
            'page' => new Page(
                page: 'flow-embed',
                title: $program->getName(),
                description: $program->getDescription() ?? '',
            ),
            'program' => $this->programService->getJsonProgram($program),
            'token' => null,
        ]);
        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', "frame-ancestors https://blog.darkwood.com https://darkwood.com");

        return $response;
    }

    #[Route('/feed/{user?me}{path?}', name: 'feed', methods: ['GET', 'POST'], requirements: ['user' => '[a-zA-Z0-9-]+', 'path' => '.*'])]
    public function feed(Request $request, string $user = 'me', ?string $path = null): Response
    {
        $user = $user === 'me' ? $this->getUser() : $this->shopUserRepository->findOneByUidOrUsername($user);

        if (!$user instanceof UserInterface) {
            throw $this->createNotFoundException('User not found');
        }

        $currentUser = $this->getUser();
        $canEdit = $currentUser instanceof UserInterface && $currentUser->getId() === $user->getId();
        $search = $request->request->getString('search');
        if ($request->isMethod('POST')) {
            if (!$canEdit || !$this->isCsrfTokenValid('feed_create', $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }
            if ('' === trim($search)) {
                return $this->redirectToRoute('app_shop_feed');
            }
            $program = $this->programService->createProgram($user, ['name' => $search]);
            $program = $this->programService->getJsonProgram($program);

            return $this->redirectToRoute('app_shop_feed', ['user' => $program['user'], 'path' => $program['path']]);
        }

        $program = $this->programRepository->findOneByUserAndPath($user, $path);
        $folder = null;
        if ($program instanceof Program) {
            if (!$canEdit && !$program->getPublic()) {
                throw $this->createNotFoundException('Program not found');
            }
            $folder = $program->getFolder();
        }

        $navigation = [];
        $navigationFolders = $canEdit ? $this->folderRepository->findByUserAndParent($user, $folder) : [];
        foreach ($navigationFolders as $navigationFolder) {
            $navigation[] = [
                'type' => 'folder',
                'entity' => $this->folderService->getJsonFolder($navigationFolder),
            ];
        }

        $navigationPrograms = $this->programRepository->findByUserAndFolder($user, $folder);
        foreach ($navigationPrograms as $navigationProgram) {
            if (!$canEdit && !$navigationProgram->getPublic()) {
                continue;
            }
            $navigation[] = [
                'type' => 'program',
                'entity' => $this->programService->getJsonProgram($navigationProgram),
            ];
        }

        $token = null;
        if ($canEdit) {
            $token = $this->jwtManager->create($user);
        }

        return $this->render('shop/feed/feed.html.twig', [
            'page' => new Page(
                page: 'feed',
                title: 'Feed',
                description: 'Feed',
            ),
            'folder' => $folder,
            'program' => $program,
            'canEdit' => $canEdit,
            'navigation' => $navigation,
            'token' => $token,
        ]);
    }
}
