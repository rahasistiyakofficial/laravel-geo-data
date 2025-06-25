<?php

namespace RahasIstiyak\GeoData\Models;

class Currency extends BaseGeoModel
{
    protected static $dataFile = 'currencies.php';
    protected static $configKey = 'currencies';
    protected static $dropdownFields = ['country_id', 'currency', 'currency_name'];

    public static function byCountryId($countryId)
    {
        $data = static::loadData();
        return $data[$countryId] ?? null;
    }

    public static function byCode($code)
    {
        return array_values(array_filter(static::loadData(), function ($currency) use ($code) {
                return $currency['currency'] === $code;
            }))[0] ?? null;
    }
}