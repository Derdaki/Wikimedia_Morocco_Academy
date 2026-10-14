<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class UploadService
{
    /** @var array<string, string> */
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
    ];

    /**
     * @param array<string, mixed> $file
     * @return array{path:string,mime:string,size:int,original_name:string}
     */
    public static function store(array $file, string $directory = 'media', bool $imagesOnly = false): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Aucun fichier fourni.');
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Le téléversement a échoué.');
        }

        $maxMb = (int) Env::get('UPLOAD_MAX_MB', '4');
        $maxBytes = max(1, $maxMb) * 1024 * 1024;
        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > $maxBytes) {
            throw new RuntimeException("Le fichier doit peser moins de {$maxMb} Mo.");
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp) || preg_match('/^[a-z-]+$/', $directory) !== 1) throw new RuntimeException('Téléversement invalide.');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp) ?: '';
        finfo_close($finfo);

        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Type de fichier non autorisé.');
        }

        if ($imagesOnly && !str_starts_with($mime, 'image/')) throw new RuntimeException('Une image est requise.');
        $originalExtension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $expected = self::ALLOWED[$mime];
        if (!in_array($originalExtension, $expected === 'jpg' ? ['jpg', 'jpeg'] : [$expected], true)) throw new RuntimeException('Extension de fichier invalide.');
        if (str_starts_with($mime, 'image/')) {
            $dimensions = @getimagesize($tmp);
            if (!$dimensions || $dimensions[0] * $dimensions[1] > 40000000) throw new RuntimeException('Image invalide ou trop grande.');
        }

        $extension = self::ALLOWED[$mime];
        $datePath = date('Y/m');
        $targetDirectory = UPLOAD_PATH . '/' . trim($directory, '/') . '/' . $datePath;

        if (!is_dir($targetDirectory) && mkdir($targetDirectory, 0755, true) === false) {
            throw new RuntimeException('Impossible de créer le dossier de téléversement.');
        }

        $filename = bin2hex(random_bytes(18)) . '.' . $extension;
        $target = $targetDirectory . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Impossible de stocker le fichier.');
        }

        return [
            'path' => '/uploads/' . trim($directory, '/') . '/' . $datePath . '/' . $filename,
            'mime' => $mime,
            'size' => $size,
            'original_name' => mb_substr(basename((string) ($file['name'] ?? $filename)), 0, 255),
        ];
    }
}
