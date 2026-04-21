<?php

namespace App\Controllers;

use RuntimeException;

/**
 * A Controller able to serve static file like css sheets
 * that are provided by the framework. To serve your own
 * files, just copy this controller and adjust it.
 */
class AssetController
{
    /**
     * Add this to your route definitions to serve css files:
     * Route::get('/css/{file}')->do(AssetController::class, 'css')
     */
    public function css(string $file)
    {
        if (! preg_match('/^[a-zA-Z-]+$/', $file)) {
            throw new RuntimeException("Invalid CSS path '$file'");
        }

        header('Content-Type: text/css');

        return file_get_contents(path("assets/css/$file.css"));
    }
}
