<?php

namespace RahasIstiyak\GeoData\Models;

class PhoneCode extends BaseGeoModel
{
    protected static $dataFile = 'phone_codes.php';
    protected static $configKey = 'phone_codes';
    protected static $dropdownFields = ['country_id', 'phonecode'];

    public static function byCountryId($countryId)
    {
        $data = static::loadData();
        return isset($data[$countryId]) ? $data[$countryId] : null;
    }
}