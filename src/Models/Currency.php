<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Collection;

class Currency extends BaseGeoModel
{
    protected static $dataFile = 'currencies.php';
    protected static $configKey = 'currencies';
    protected static $dropdownFields = ['country_id', 'currency', 'currency_name'];

    /**
     * Get currency by country ID.
     *
     * @param int $countryId
     * @return Collection|null
     */
    public static function byCountryId($countryId)
    {
        $data = static::loadData();
        return isset($data[$countryId]) ? (object)$data[$countryId] : null;
    }

    /**
     * Get currency by currency code.
     *
     * @param string $code
     * @return Collection|null
     */
    public static function byCode($code)
    {
        $data = static::loadData();
        return $data->firstWhere('currency', $code) ? (object)$data->firstWhere('currency', $code) : null;
    }
}
