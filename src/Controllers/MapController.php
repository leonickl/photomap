<?php

namespace App\Controllers;

use App\Models\Marker;
use PXP\Exceptions\ValidationException;
use PXP\Http\Controllers\Controller;
use PXP\Http\Response\Redirect;
use RuntimeException;

class MapController extends Controller
{
    public function index()
    {
        return view('charte', ['markers' => Marker::all()], layout: 'map-layout');
    }

    public function upload()
    {
        $pos = request('pos');

        return view('create', [
            'coords' => $pos === null
                ? [null, null]
                : explode(', ', substr($pos, 7, strlen($pos) - 8)),
        ]);
    }

    private function saveFile(callable $store, string $extension): string
    {
        $filename = time().'-'.uniqid().'.'.$extension;
        $full_path = path('photos/'.$filename);

        if (! file_exists(path('photos'))) {
            mkdir(path('photos'));
        }

        if ($store($full_path) === false) {
            throw new RuntimeException('error saving image');
        }

        chmod($full_path, 0644);

        return $filename;
    }

    public function camera()
    {
        return view('camera');
    }

    public function storeFromCamera()
    {
        return $this->store(function() {
            $imageData = request('image_data');

            if (empty($imageData)) {
                throw new ValidationException('no image data provided');
            }

            if (!preg_match('/^data:image\/(jpeg|jpg);base64,/', $imageData)) {
                throw new ValidationException('invalid image format');
            }

            $decodedImage = base64_decode(substr($imageData, strpos($imageData, ',') + 1));

            if ($decodedImage === false) {
                throw new ValidationException('failed to decode image');
            }

            if (strlen($decodedImage) > 10_000_000) {
                throw new ValidationException('image size must be 10 MB or smaller');
            }

            $lat = request()->float('lat');
            $lon = request()->float('lon');

            if ($lat === 0.0 && $lon === 0.0) {
                return (object)[
                    'error' => 'Keine GPS-Koordinaten verfügbar. Bitte erlaube den
                        Standortzugriff oder wähle die Position manuell.',
                    'back' => 'camera',
                ];
            }

            $location = [$lat, $lon, true];

            $filename = $this->saveFile(
                fn(string $full_path) => file_put_contents($full_path, $decodedImage),
                'jpg',
            );

            return (object)compact('location', 'filename');
        });
    }

    public function storeFromUpload()
    {
        return $this->store(function () {
            if (@$_FILES['photo']['size'] === 0) {
                throw new ValidationException('no photo given');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg'])) {
                throw new ValidationException('file extension must be jpg or jpeg');
            }

            if (! in_array($mime_type, ['image/jpeg'])) {
                throw new ValidationException('file type must be image/jpeg');
            }

            if ($_FILES['photo']['size'] > 10_000_000) {
                throw new ValidationException('file size must be 10 MB or smaller');
            }

            $location = Marker::getExifLocation($_FILES['photo']['tmp_name']);

            if ($location === null || $location === false) {
                $lat = request()->float('lat');
                $lon = request()->float('lon');

                if ($lat !== 0.0 || $lon !== 0.0) {
                    $location = [$lat, $lon, true]; // third element signals manual coordinates
                }
            }

            if ($location === null) {
                return (object)[
                    'error' => 'Keine Geodaten gefunden. Wähle bitte zuerst die Koordinaten
                        auf der <a href="/">Karte</a> aus.',
                    'back' => 'create',
                ];
            }

            if ($location === false) {
                return (object)[
                    'error' => 'Sieht so aus, als wären die Geodaten beim Upload vom
                        Handy gelöscht worden. Wähle bitte zuerst die Koordinaten
                        auf der <a href="/">Karte</a> aus.',
                    'back' => 'create',
                ];
            }

            $filename = $this->saveFile(
                fn(string $full_path) => move_uploaded_file($_FILES['photo']['tmp_name'], $full_path),
                $extension,
            );

            return (object)compact('location', 'filename');
        });
    }

    private function store(callable $processImage)
    {
        $title = request('title');
        $author = request('author');

        if (strlen($title) < 2 || strlen($title) > 100) {
            throw new ValidationException('title length must between 1 and 100');
        }

        if (strlen($author) < 2 || strlen($author) > 100) {
            throw new ValidationException('author length must between 1 and 100');
        }

        $image = $processImage();

        if (isset($image->error)) {
            session([
                'title' => $title,
                'author' => $author,
                'error' => $image->error,
            ]);

            return Redirect::path("/$image->back");
        }

        Marker::create(
            title: $title,
            author: $author,
            file: $image->filename,
            lat: @$image->location[2] ? $image->location[0] : null,
            lon: @$image->location[2] ? $image->location[1] : null,
        );

        return Redirect::path('/');
    }
}
