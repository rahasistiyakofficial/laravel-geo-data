<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class City
{
    protected static $data = null;
    protected static $configKey = 'cities';

    /**
     * Get all cities for a given country code.
     *
     * @param string $countryCode
     * @return array
     */
    public static function getCities($countryCode)
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return [];
        }

        $data = static::loadData($countryCode);
        return $data;
    }

    /**
     * Get a specific city by country code and city ID.
     *
     * @param string $countryCode
     * @param int $id
     * @return array|null
     */
    public static function getCityById($countryCode, $id)
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return null;
        }

        $data = static::loadData($countryCode);
        return isset($data[$id]) ? $data[$id] : null;
    }

    /**
     * Get a dropdown list of cities for a given country code.
     *
     * @param string $countryCode
     * @param array $fields
     * @param string $sortBy
     * @return array
     */
    public static function getCityDropdown($countryCode, $fields = ['id', 'name'], $sortBy = 'name')
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return [];
        }

        $data = static::loadData($countryCode);

        // Create a dropdown structure
        $dropdown = array_map(function ($city) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = $city[$field] ?? null;
            }
            return $row;
        }, $data);

        // Sort dropdown by the given field
        if ($sortBy && isset($data[array_key_first($data)][$sortBy])) {
            usort($dropdown, function ($a, $b) use ($sortBy) {
                return strcmp($a[$sortBy] ?? '', $b[$sortBy] ?? '');
            });
        }

        return $dropdown;
    }

    /**
     * Load data for cities from the specific country code file.
     *
     * @param string $countryCode
     * @return array
     */
    protected static function loadData($countryCode)
    {
        $dataFilePath = __DIR__ . '/../data/cities/' . strtoupper($countryCode) . '.php';

        if (!file_exists($dataFilePath)) {
            \Log::warning('Geo data file missing: ' . $dataFilePath);
            return [];
        }

        // Cache the data for the given country
        $cacheKey = 'geo-data.cities.' . strtoupper($countryCode);
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($dataFilePath) {
            return include $dataFilePath;
        });
    }
}
