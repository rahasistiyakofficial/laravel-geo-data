<?php

namespace RahasIstiyak\GeoData\Models;

class City extends BaseGeoModel
{
    protected static $dataFile = 'cities.php';
    protected static $configKey = 'cities';

    public static function byCountryId($countryId)
    {
        return array_filter(static::loadData(), function ($city) use ($countryId) {
            return $city['country_id'] === $countryId;
        });
    }

    public static function byCountryCode($countryCode)
    {
        return array_filter(static::loadData(), function ($city) use ($countryCode) {
            return $city['country_code'] === $countryCode;
        });
    }
}