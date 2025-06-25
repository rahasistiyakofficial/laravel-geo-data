<?php

namespace RahasIstiyak\GeoData\Models;

class Country extends BaseGeoModel
{
    protected static $dataFile = 'countries.php';
    protected static $configKey = 'countries';

    public static function byCode($code)
    {
        return static::loadData()
            ->mapInto(\Illuminate\Support\Fluent::class) // More readable than casting to (object)
            ->first(function ($country) use ($code) {
                return $country->code === $code || $country->iso3 === $code;
            }, null)?->tap(function ($country) {
                $country->translations = json_decode($country->translations, true);
            });
    }


    public static function byRegion($regionId)
    {
        return static::loadData()
            ->map(fn($item) => (object) $item)
            ->filter(fn($country) => $country->region_id === $regionId)
            ->values();
    }
}
