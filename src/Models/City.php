<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class City
{
    protected static $configKey = 'cities';

    /**
     * Get all cities for a given country code.
     *
     * @param string $countryCode
     * @return array
     */
    public static function getCities($countryCode)
    {
        // Return empty if data is not enabled in config
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return [];
        }

        // Load data from cache or file for the given country code
        return static::loadData($countryCode);
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

        // Loop through and find the city with the matching ID
        foreach ($data as $chunk) {
            foreach ($chunk as $city) {
                if ($city['id'] == $id) {
                    return $city;
                }
            }
        }

        return null;
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

        // Efficiently extract and map data fields
        $dropdown = array_merge([], ...array_map(function ($city) use ($fields) {
            return array_intersect_key($city, array_flip($fields));
        }, array_merge(...$data)));

        // Sort by specified field (e.g. name)
        if ($sortBy) {
            usort($dropdown, function ($a, $b) use ($sortBy) {
                return strcmp($a[$sortBy] ?? '', $b[$sortBy] ?? '');
            });
        }

        return $dropdown;
    }

    /**
     * Load and cache data for cities from the specific country code file.
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

        // Cache the data in chunks for large datasets to prevent MySQL packet size issues
        $cacheKey = 'geo-data.cities.' . strtoupper($countryCode);

        // Return the cached data from database cache driver
        return static::cacheInChunks($dataFilePath);
    }

    /**
     * Cache large city data in smaller chunks.
     *
     * @param string $dataFilePath
     * @return array
     */
    protected static function cacheInChunks($dataFilePath)
    {
        // Include the data file (returns an array of cities)
        $cities = include $dataFilePath;

        $chunkSize = 50; // Set chunk size to 50 cities
        $chunkedData = array_chunk($cities, $chunkSize);

        // Cache each chunk separately to avoid large single cache items
        $allCacheKeys = [];
        foreach ($chunkedData as $index => $chunk) {
            $chunkCacheKey = $dataFilePath . '.chunk.' . $index;
            Cache::put($chunkCacheKey, $chunk, now()->addHours(12)); // Cache each chunk for 12 hours
            $allCacheKeys[] = $chunkCacheKey;
        }

        // Efficiently combine chunks
        $allCities = [];
        foreach ($allCacheKeys as $chunkCacheKey) {
            $allCities = array_merge($allCities, Cache::get($chunkCacheKey)); // Combine chunks from cache
        }

        return $allCities;
    }
}
