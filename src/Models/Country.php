<?php

namespace RahasIstiyak\GeoData\Models;

class Country extends BaseGeoModel
{
    protected static $dataFile = 'countries.php';
    protected static $configKey = 'countries';

    public static function byCode($code)
    {
        $data = static::loadData();
        foreach ($data as $country) {
            if ($country['code'] === $code || $country['iso3'] === $code) {
                return $country;
            }
        }
        return null;
    }

    public static function byRegion($regionId)
    {
        return array_filter(static::loadData(), function ($country) use ($regionId) {
            return $country['region_id'] === $regionId;
        });
    }
}