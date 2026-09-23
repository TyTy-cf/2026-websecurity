<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class UploaderService
{
    /**
     * Type MIME reel (sniffe sur le contenu) => extension de confiance.
     * On ne se sert JAMAIS de l'extension ni du type declares par le client :
     * les deux sont controles par l'attaquant.
     */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];

    /** Qualite de re-encodage JPEG (0-100). */
    private const JPEG_QUALITY = 90;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private string $uploadDir,
    ) {
    }

    public function upload(UploadedFile $file, string $directory): string
    {
        // 1. getMimeType() lit le contenu reel du fichier (finfo), pas l'en-tete
        //    Content-Type envoye par le navigateur : rejet rapide de tout ce qui
        //    n'est pas une image autorisee.
        $mimeType = $file->getMimeType();

        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            throw new FileException('Type de fichier non autorise : seules les images JPG, JPEG et PNG sont acceptees.');
        }

        $extension = self::ALLOWED_TYPES[$mimeType];

        // 2. Re-decodage complet de l'image en memoire. C'est la parade contre les
        //    fichiers "polyglottes" (image valide + code PHP cache en EXIF, en
        //    commentaire ou ajoute a la fin) : seuls les pixels survivent, toute
        //    charge utile est eliminee. Une image illisible echoue ici.
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($file->getPathname()),
            'image/png'  => @imagecreatefrompng($file->getPathname()),
        };

        if (!$image instanceof \GdImage) {
            throw new FileException('Image illisible ou corrompue.');
        }

        $targetDir = $this->uploadDir . '/' . $directory;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            imagedestroy($image);
            throw new FileException(sprintf('Impossible de creer le repertoire "%s".', $targetDir));
        }

        // Nom unique et neutre : uniqid() garantit l'unicite (pas de collision ni
        // d'ecrasement), et l'encodage hexa du nom d'origine le rend inoffensif
        // (aucun caractere de traversee de chemin, d'octet nul, etc.) tout en
        // gardant une trace lisible du fichier d'origine.
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = uniqid() . '-' . bin2hex($originalName) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        // 3. Re-encodage propre vers le disque.
        if ('image/png' === $mimeType) {
            // Preserve la transparence du PNG.
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $saved = match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $targetPath, self::JPEG_QUALITY),
            'image/png'  => imagepng($image, $targetPath),
        };

        imagedestroy($image);

        if (!$saved) {
            throw new FileException('Echec de l\'enregistrement de l\'image.');
        }

        return '/uploads/' . $directory . '/' . $filename;
    }
}
