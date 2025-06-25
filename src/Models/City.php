<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class City
{
    protected static $configKey = 'cities';

    /**
     * Get all cities for a given country code as a collection of objects.
     *
     * @param string $countryCode
     * @return Collection
     */
    public static function getCities($countryCode)
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return collect([]);
        }

        return collect(static::loadData($countryCode))->flatten(1)->map(fn($item) => (object) $item);
    }

    /**
     * Get a specific city by country code and city ID as an object.
     *
     * @param string $countryCode
     * @param int $id
     * @return Collection|null
     */
    public static function getCityById($countryCode, $id)
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return null;
        }

        $data = collect(static::loadData($countryCode, true))->map(fn($item) => (object) $item);
        return isset($data[$id]) ? (object)$data[$id] : null;
    }

    /**
     * Get a dropdown list of cities for a given country code as a collection of objects.
     *
     * @param string $countryCode
     * @param array $fields
     * @param string $sortBy
     * @return Collection
     */
    public static function getCityDropdown($countryCode, $fields = ['id', 'name'], $sortBy = 'name')
    {
        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return collect([]);
        }

        $data = collect(static::loadData($countryCode));

        $dropdown = $data->flatMap(function ($chunk) use ($fields) {
            return array_map(function ($city) use ($fields) {
                return (object) array_intersect_key($city, array_flip($fields));
            }, $chunk);
        })->values();

        if ($sortBy) {
            $dropdown = $dropdown->sortBy($sortBy);
        }

        return $dropdown;
    }

    /**
     * Load and cache data for cities from the specific country code file.
     *
     * @param string $countryCode
     * @param bool $indexById
     * @return array
     */
    protected static function loadData($countryCode, $indexById = false)
    {
        $dataFilePath = __DIR__ . '/../data/cities/' . strtoupper($countryCode) . '.php';

        if (!file_exists($dataFilePath)) {
            \Log::warning('Geo data file missing: ' . $dataFilePath);
            return [];
        }

        $data = static::cacheInChunks($dataFilePath);

        if ($indexById) {
            $indexed = [];
            foreach ($data as $chunk) {
                foreach ($chunk as $city) {
                    if (isset($city['id'])) {
                        $indexed[$city['id']] = $city;
                    }
                }
            }
            return $indexed;
        }

        return $data;
    }

    /**
     * Cache large city data in smaller chunks.
     *
     * @param string $dataFilePath
     * @return array
     */
    protected static function cacheInChunks($dataFilePath)
    {
        $cities = include $dataFilePath;

        if (!is_array($cities)) {
            \Log::error('Cities data is not an array: ' . print_r($cities, true));
            return [];
        }

        $chunkSize = 100;
        $chunkedData = array_chunk($cities, $chunkSize);

        $allCacheKeys = [];
        foreach ($chunkedData as $index => $chunk) {
            if (!is_array($chunk)) {
                \Log::error('Invalid chunk detected: ' . print_r($chunk, true));
                continue;
            }

            $chunkCacheKey = 'geo-data.cities.chunk.' . md5($dataFilePath . $index);

            Cache::remember($chunkCacheKey, now()->addHours(12), function () use ($chunk) {
                return $chunk;
            });

            $allCacheKeys[] = $chunkCacheKey;
        }

        $allCities = [];
        foreach ($allCacheKeys as $chunkCacheKey) {
            $chunkData = Cache::get($chunkCacheKey);

            if (is_array($chunkData)) {
                $allCities[] = $chunkData;
            } else {
                \Log::error('Invalid data found in cache for key: ' . $chunkCacheKey);
            }
        }

        return $allCities;
    }
}
