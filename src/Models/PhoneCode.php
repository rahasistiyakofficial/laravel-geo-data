<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Collection;

class PhoneCode extends BaseGeoModel
{
    protected static $dataFile = 'phone_codes.php';
    protected static $configKey = 'phone_codes';
    protected static $dropdownFields = ['country_id', 'phonecode'];

    /**
     * Get phone code by country ID.
     *
     * @param int $countryId
     * @return Collection|null
     */
    public static function byCountryId($countryId)
    {
        $data = static::loadData();
        return isset($data[$countryId]) ? (object)$data[$countryId] : null;
    }
}
