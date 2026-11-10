<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\DepthResult;
use App\Service\DepthAnythingService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class DepthController extends AbstractController
{
    public function __construct(
        private readonly DepthAnythingService $depthAnything,
        private readonly string $inferenceDir,
    ) {
    }

    #[Route('/', name: 'depth_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('depth/index.html.twig');
    }

    #[Route('/infer/sample/{sample}', name: 'depth_infer_sample', requirements: ['sample' => 'mountains|street|canyon'], methods: ['POST'])]
    public function inferSample(string $sample, Request $request): Response
    {
        $path = $this->getParameter('kernel.project_dir').'/public/samples/'.$sample.'.jpg';
        if (!is_file($path)) {
            throw $this->createNotFoundException();
        }

        $upload = new UploadedFile($path, $sample.'.jpg', 'image/jpeg', null, true);
        $variant = (string) $request->request->get('model', 'q4_k');

        try {
            $result = $this->depthAnything->process($upload, $variant);
        } catch (RuntimeException $e) {
            $this->addFlash('error', $this->publicErrorMessage($e->getMessage()));

            return $this->redirectToRoute('depth_index');
        }

        return $this->render('depth/result.html.twig', [
            'result' => $result,
        ]);
    }

    #[Route('/infer', name: 'depth_infer', methods: ['POST'])]
    public function infer(Request $request): Response
    {
        $upload = $request->files->get('image');
        if (!$upload instanceof UploadedFile) {
            $this->addFlash('error', 'Please choose an image.');

            return $this->redirectToRoute('depth_index');
        }

        $variant = (string) $request->request->get('model', 'q4_k');

        try {
            $result = $this->depthAnything->process($upload, $variant);
        } catch (RuntimeException $e) {
            $this->addFlash('error', $this->publicErrorMessage($e->getMessage()));

            return $this->redirectToRoute('depth_index');
        }

        return $this->render('depth/result.html.twig', [
            'result' => $result,
        ]);
    }

    #[Route('/result/{id}/asset/{name}', name: 'depth_asset', requirements: ['name' => 'input\.(jpg|png|webp)|depth\.png|confidence\.png|scene\.glb|metadata\.json'], methods: ['GET'])]
    public function asset(string $id, string $name): Response
    {
        $path = rtrim($this->inferenceDir, '/').'/'.$id.'/'.$name;
        if (!is_file($path)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($path);
        if (str_ends_with($name, '.glb')) {
            $response->headers->set('Content-Type', 'model/gltf-binary');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $name);
        } elseif (str_ends_with($name, '.json')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    #[Route('/result/{id}/download/glb', name: 'depth_download_glb', methods: ['GET'])]
    public function downloadGlb(string $id): Response
    {
        $path = rtrim($this->inferenceDir, '/').'/'.$id.'/scene.glb';
        if (!is_file($path)) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, 'depth-scene.glb');
    }

    private function publicErrorMessage(string $message): string
    {
        if (str_contains($message, 'PHP FFI is disabled')) {
            return 'Server configuration error (FFI disabled).';
        }
        if (str_contains($message, 'Model file not found')) {
            return 'Depth model is not configured on this server.';
        }
        if (str_contains($message, 'Native library not found')) {
            return 'Native depth library is not available.';
        }
        if (str_contains($message, 'Invalid upload')) {
            return 'Please choose a valid image file.';
        }
        if (str_contains($message, 'Only JPEG, PNG, and WebP')) {
            return 'Only JPEG, PNG, and WebP images are supported.';
        }
        if (str_contains($message, 'too large')) {
            return $message;
        }

        if (str_starts_with($message, 'Unable to') || str_starts_with($message, 'Depth inference')) {
            return 'Unable to process this image. Try another file or a sample image.';
        }

        return 'Processing failed. Try another image or check server configuration.';
    }
}
