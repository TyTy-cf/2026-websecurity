<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;

final class ImageController extends AbstractController
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png'];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private readonly string $uploadDir,
    ) {
    }

    #[Route(
        '/images/{directory}/{filename}',
        name: 'app_image_show',
        requirements: [
            'directory' => '[a-z_]+',
            'filename' => '[\w.-]+',
        ],
        methods: ['GET'],
    )]
    public function show(string $directory, string $filename): BinaryFileResponse
    {
        if (!preg_match('/\.(jpg|jpeg|png)$/', $filename)) {
            throw new NotFoundHttpException();
        }

        $baseDir = realpath($this->uploadDir);
        $path = realpath($this->uploadDir . '/' . $directory . '/' . $filename);

        if (false === $baseDir || false === $path || !str_starts_with($path, $baseDir . \DIRECTORY_SEPARATOR) || !is_file($path)) {
            throw new NotFoundHttpException();
        }

        $mimeType = MimeTypes::getDefault()->guessMimeType($path);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);

        return $response;
    }
}
