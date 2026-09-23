<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class UploaderService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private string $uploadDir,
    ) {
    }

    public function upload(UploadedFile $file, string $directory): string
    {
        $targetDir = $this->uploadDir . '/' . $directory;

        // On ne fait pas confiance à l'extension envoyée par le client : elle est
        // devinée à partir du type MIME réel du fichier. Un fichier PHP renommé en
        // .jpg n'obtiendra donc jamais une extension .php ici.
        $extension = $file->guessExtension() ?? 'bin';

        $count = count(glob($targetDir . '/image-*')) + 1;
        $filename = 'image-' . $count . '.' . $extension;

        $file->move($targetDir, $filename);

        return '/uploads/' . $directory . '/' . $filename;
    }
}
