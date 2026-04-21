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
        return view('create');
    }

    public function store()
    {
        $title = request('title');

        if (strlen($title) < 2 || strlen($title) > 100) {
            throw new ValidationException('title length must between 1 and 100');
        }

        $author = request('author');

        if (strlen($author) < 2 || strlen($author) > 100) {
            throw new ValidationException('author length must between 1 and 100');
        }

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

        if ($location === null) {
            session([
                'title' => $title,
                'author' => $author,
                'error' => 'Keine Geodaten gefunden. Können über
                    <a href="https://www.thexifer.net">thexifer.net</a>
                    nachträglich eingefügt werden.',
            ]);

            return Redirect::path('/create');
        }

        $filename = time().'-'.uniqid().'.'.pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $full_path = path('photos/'.$filename);

        if (! file_exists(path('photos'))) {
            mkdir(path('photos'));
        }

        if (! move_uploaded_file($_FILES['photo']['tmp_name'], $full_path)) {
            throw new RuntimeException('error moving uploaded image');
        }

        chmod($full_path, 0644); // Owner: rw-, Group: r--, Others: r--

        Marker::create(
            title: $title,
            author: $author,
            file: $filename,
        );

        return Redirect::path('/');
    }
}
