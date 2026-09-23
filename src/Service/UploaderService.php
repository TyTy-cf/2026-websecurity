<?php

namespace App\Service;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

readonly class UploaderService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private string $uploadDir,
    ) {
    }

    public function upload(UploadedFile $file, string $directory): string
    {
        $extension = $file->guessExtension();

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            throw new InvalidArgumentException('Extension invalide : ' . $extension);
        }
        $targetDir = $this->uploadDir . '/' . $directory;

        $filename = Uuid::v4() . '.' . $extension;

        $file->move($targetDir, $filename);

        return '/uploads/' . $directory . '/' . $filename;
    }
}
