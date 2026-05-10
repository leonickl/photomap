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

    public function create()
    {
        $pos = request('pos');

        return view('create', [
            'coords' => $pos === null
                ? [null, null]
                : explode(', ', substr($pos, 7, strlen($pos) - 8)),
        ]);
    }

    private function validateTitle(string $title): void
    {
        if (strlen($title) < 2 || strlen($title) > 100) {
            throw new ValidationException('title length must between 1 and 100');
        }
    }

    private function validateAuthor(string $author): void
    {
        if (strlen($author) < 2 || strlen($author) > 100) {
            throw new ValidationException('author length must between 1 and 100');
        }
    }

    private function saveImage(string $imageData, string $extension): string
    {
        $filename = time().'-'.uniqid().'.'.$extension;
        $full_path = path('photos/'.$filename);

        if (! file_exists(path('photos'))) {
            mkdir(path('photos'));
        }

        if (file_put_contents($full_path, $imageData) === false) {
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
        $title = request('title');
        $author = request('author');

        $this->validateTitle($title);
        $this->validateAuthor($author);

        $imageData = request('image_data');

        if (empty($imageData)) {
            throw new ValidationException('no image data provided');
        }

        if (!preg_match('/^data:image\/(jpeg|jpg);base64,/', $imageData)) {
            throw new ValidationException('invalid image format');
        }

        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $decodedImage = base64_decode($imageData);

        if ($decodedImage === false) {
            throw new ValidationException('failed to decode image');
        }

        if (strlen($decodedImage) > 10_000_000) {
            throw new ValidationException('image size must be 10 MB or smaller');
        }

        $lat = request()->float('lat');
        $lon = request()->float('lon');

        $location = null;
        if ($lat !== 0.0 || $lon !== 0.0) {
            $location = [$lat, $lon, true];
        }

        if ($location === null) {
            session([
                'title' => $title,
                'author' => $author,
                'error' => 'Keine GPS-Koordinaten verfügbar. Bitte erlaube den Standortzugriff oder wähle die Position manuell.',
            ]);

            return Redirect::path('/camera');
        }

        $filename = $this->saveImage($decodedImage, 'jpg');

        Marker::create(
            title: $title,
            author: $author,
            file: $filename,
            lat: $location[0],
            lon: $location[1],
        );

        return Redirect::path('/');
    }

    public function store()
    {
        $title = request('title');
        $author = request('author');

        $this->validateTitle($title);
        $this->validateAuthor($author);

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
            session([
                'title' => $title,
                'author' => $author,
                'error' => 'Keine Geodaten gefunden. Wähle bitte zuerst die Koordinaten
                    auf der <a href="/">Karte</a> aus.',
            ]);

            return Redirect::path('/create');
        }

        if ($location === false) {
            session([
                'title' => $title,
                'author' => $author,
                'error' => 'Sieht so aus, als wären die Geodaten beim Upload vom Handy gelöscht worden.
                    Wähle bitte zuerst die Koordinaten auf der <a href="/">Karte</a> aus.',
            ]);

            return Redirect::path('/create');
        }

        $filename = $this->saveUploadedFile($_FILES['photo'], $extension);

        Marker::create(
            title: $title,
            author: $author,
            file: $filename,
            lat: count($location) === 3 ? $location[0] : null,
            lon: count($location) === 3 ? $location[1] : null,
        );

        return Redirect::path('/');
    }

    private function saveUploadedFile(array $file, string $extension): string
    {
        $filename = time().'-'.uniqid().'.'.$extension;
        $full_path = path('photos/'.$filename);

        if (! file_exists(path('photos'))) {
            mkdir(path('photos'));
        }

        if (! move_uploaded_file($file['tmp_name'], $full_path)) {
            throw new RuntimeException('error moving uploaded image');
        }

        chmod($full_path, 0644);

        return $filename;
    }
}
