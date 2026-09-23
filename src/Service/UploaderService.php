<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class UploaderService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private string   $uploadDir,
    )
    {
    }

    public function upload(UploadedFile $file, string $directory): string
    {
        $targetDir = $this->uploadDir . '/' . $directory;

        $filename = sprintf(
            'image-%s.%s',
            bin2hex(random_bytes(16)),
            $file->guessExtension(),
        );

        $file->move($targetDir, $filename);

        return '/uploads/' . $directory . '/' . $filename;
    }
}
