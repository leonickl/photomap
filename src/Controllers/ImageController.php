<?php

namespace App\Controllers;

use RuntimeException;

class ImageController
{
    public function img(string $file)
    {
        if (! preg_match('/^[a-zA-Z0-9-\.]+$/', $file)) {
            throw new RuntimeException("Invalid image path '$file'");
        }

        $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), path("photos/$file"));

        if (! in_array($mime_type, ['image/jpeg'])) {
            throw new RuntimeException("file type must be image/jpeg");
        }

        header("Content-Type: $mime_type");

        return file_get_contents(path("photos/$file"));
    }
}
