<?php

namespace App\Models;

use PXP\Data\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property string $file
 */
class Marker extends Model
{
    protected string $table = 'markers';

    public function position(): ?array
    {
        return self::getExifLocation(path("photos/$this->file"));
    }

    public static function getExifLocation(string $file) {
        $exif = exif_read_data($file, 0, true);

        if (! $exif || ! isset($exif['GPS'])) {
            return null;
        }

        $GPSLatitudeRef = $exif['GPS']['GPSLatitudeRef'];
        $GPSLatitude = $exif['GPS']['GPSLatitude'];
        $GPSLongitudeRef= $exif['GPS']['GPSLongitudeRef'];
        $GPSLongitude = $exif['GPS']['GPSLongitude'];

        if ($GPSLatitudeRef === null || $GPSLongitudeRef === null) {
            return null;
        }
        
        $lat_degrees = count($GPSLatitude) > 0 ? self::gpsToNum($GPSLatitude[0]) : 0;
        $lat_minutes = count($GPSLatitude) > 1 ? self::gpsToNum($GPSLatitude[1]) : 0;
        $lat_seconds = count($GPSLatitude) > 2 ? self::gpsToNum($GPSLatitude[2]) : 0;

        $lon_degrees = count($GPSLongitude) > 0 ? self::gpsToNum($GPSLongitude[0]) : 0;
        $lon_minutes = count($GPSLongitude) > 1 ? self::gpsToNum($GPSLongitude[1]) : 0;
        $lon_seconds = count($GPSLongitude) > 2 ? self::gpsToNum($GPSLongitude[2]) : 0;
        
        $lat_direction = ($GPSLatitudeRef == 'W' || $GPSLatitudeRef == 'S') ? -1 : 1;
        $lon_direction = ($GPSLongitudeRef == 'W' || $GPSLongitudeRef == 'S') ? -1 : 1;
        
        $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
        $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

        return [
            $latitude,
            $longitude,
        ];
    }

    private static function gpsToNum($coordPart){
        $parts = explode('/', $coordPart);

        if(count($parts) <= 0) {
            return 0;
        }

        if(count($parts) == 1) {
            return $parts[0];
        }

        return floatval($parts[0]) / floatval($parts[1]);
    }
}
