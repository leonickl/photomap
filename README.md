# Photo-Map

A simple web app where users can upload and view geotagged pictures on a map.
Currently only available in German.

## Installation and Setup

- Install PHP 8.5 with exif-extension and composer
- Clone this repository using `git`
- Install dependencies with `composer install`
- Create and migrate database with `./run migrate`
- Start the webserver with `./run server`

## Usage

The main view is a [https://leafletjs.com/](Leaflet) map where pin markers are shown for all
available photos. They can be clicked at to view the pictures. You can also show them in new tab.
With the plus icon in the top-right corner, you can upload a new picture to the map. Please
specify title and author. The coordinates should be read from the image automatically. If not,
the app will tell you and you must click on the correct position on the map and thereby manually select
the coordinates for the image. If you want, you can also insert them in the form fields.

## Updating

- Update the code with `git pull`
- Migrate the database with `./run migrate`
