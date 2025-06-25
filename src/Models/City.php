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
                // Ensure $city is an array before accessing its keys
                if (is_array($city) && isset($city['id']) && $city['id'] == $id) {
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

        // Ensure data is in the expected format
        if (!is_array($cities)) {
            \Log::error("Cities data is not an array: " . print_r($cities, true));
            return [];
        }

        $chunkSize = 50; // Set chunk size to 50 cities
        $chunkedData = array_chunk($cities, $chunkSize);

        // Cache each chunk separately to avoid large single cache items
        $allCacheKeys = [];
        foreach ($chunkedData as $index => $chunk) {
            // Ensure each chunk is an array
            if (!is_array($chunk)) {
                \Log::error("Invalid chunk detected: " . print_r($chunk, true));
                continue;
            }

            $chunkCacheKey = $dataFilePath . '.chunk.' . $index;

            // Cache the chunk using Cache::remember with 12 hours duration
            Cache::remember($chunkCacheKey, now()->addHours(12), function () use ($chunk) {
                return $chunk;
            });

            // Add the chunk cache key to the list
            $allCacheKeys[] = $chunkCacheKey;
        }

        // Efficiently combine chunks from the cache
        $allCities = [];
        foreach ($allCacheKeys as $chunkCacheKey) {
            $chunkData = Cache::get($chunkCacheKey);

            // Validate if the cached data is an array
            if (is_array($chunkData)) {
                $allCities = array_merge($allCities, $chunkData); // Combine chunks from cache
            } else {
                \Log::error("Invalid data found in cache for key: " . $chunkCacheKey);
            }
        }


        return $allCities;
    }
}
