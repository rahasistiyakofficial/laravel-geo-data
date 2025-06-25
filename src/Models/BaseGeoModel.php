<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Illuminate\Support\Collection;

abstract class BaseGeoModel
{
    protected static $data = null;
    protected static $dataFile = '';
    protected static $configKey = '';
    protected static $primaryKey = 'id';
    protected static $dropdownFields = ['id', 'name'];

    /**
     * Load and cache data for the model.
     *
     * @return Collection
     */
    protected static function loadData()
    {
        if (!static::$dataFile || !static::$configKey) {
            throw new InvalidArgumentException('Data file and config key must be defined in child class');
        }

        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return collect([]);
        }

        if (is_null(static::$data)) {
            $cacheKey = 'geo-data.' . static::$configKey;
            static::$data = Cache::remember($cacheKey, now()->addHours(24), function () use ($cacheKey) {
                $filePath = __DIR__ . '/../data/' . static::$dataFile;
                if (!file_exists($filePath)) {
                    \Log::warning('Geo data file missing: ' . $filePath);
                    return [];
                }

                $data = include $filePath;
                if (!is_array($data)) {
                    \Log::error('Geo data file did not return an array for ' . $cacheKey . ': ' . $filePath);
                    return [];
                }

                return $data;
            });

            // Ensure cached data is always a Collection
            static::$data = collect(static::$data);
        }

        return static::$data;
    }

    /**
     * Get all data as a collection.
     *
     * @return Collection
     */
    public static function all()
    {
        return static::loadData()->map(fn($item) => (object) $item);;
    }

    /**
     * Get data formatted for dropdowns.
     *
     * @param array|null $fields
     * @param string|null $sortBy
     * @return Collection
     */
    public static function dropdown($fields = null, $sortBy = 'name')
    {
        $data = static::loadData();
        $fields = $fields ?? static::$dropdownFields;

        // Ensure data is a Collection
        if (!($data instanceof Collection)) {
            \Log::error('Data is not a Collection in dropdown for ' . static::$configKey . ': ' . gettype($data));
            $data = collect($data);
        }

        $result = $data->map(function ($item) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = isset($item[$field]) ? $item[$field] : null;
            }
            return $row;
        })->values();

        if ($sortBy && $data->first() && isset($data->first()[$sortBy])) {
            $result = $result->sortBy($sortBy);
        }

        return $result->map(fn($item) => (object) $item);
    }

    /**
     * Get a single item by ID.
     *
     * @param int $id
     * @return Collection|null
     */
    public static function byId($id)
    {
        $data = static::loadData();
        return isset($data[$id]) ? (object)$data[$id] : null;
    }

    /**
     * Paginate the data.
     *
     * @param int $perPage
     * @param int $page
     * @return Collection
     */
    public static function paginate($perPage = 100, $page = 1)
    {
        $data = static::loadData();
        $offset = ($page - 1) * $perPage;
        return collect(array_slice($data->toArray(), $offset, $perPage))->map(fn($item) => (object) $item);
    }
}
