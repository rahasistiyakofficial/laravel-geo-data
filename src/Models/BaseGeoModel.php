<?php

namespace RahasIstiyak\GeoData\Models;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

abstract class BaseGeoModel
{
    protected static $data = null;
    protected static $dataFile = '';
    protected static $configKey = '';
    protected static $primaryKey = 'id';
    protected static $dropdownFields = ['id', 'name'];

    protected static function loadData()
    {
        if (!static::$dataFile || !static::$configKey) {
            throw new InvalidArgumentException('Data file and config key must be defined in child class');
        }

        if (!config('geo-data.enabled_data.' . static::$configKey, true)) {
            return [];
        }

        if (is_null(static::$data)) {
            $cacheKey = 'geo-data.' . static::$configKey;
            static::$data = Cache::remember($cacheKey, now()->addHours(24), function () {
                $filePath = __DIR__ . '/../data/' . static::$dataFile;
                if (!file_exists($filePath)) {
                    \Log::warning('Geo data file missing: ' . $filePath);
                    return [];
                }
                return include $filePath;
            });
        }

        return static::$data ?: [];
    }

    public static function all()
    {
        return static::loadData();
    }

    public static function dropdown($fields = null, $sortBy = 'name')
    {
        $data = static::loadData();
        $fields = isset($fields) ? $fields : static::$dropdownFields;

        $result = array_map(function ($item) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = isset($item[$field]) ? $item[$field] : null;
            }
            return $row;
        }, $data);

        if ($sortBy && isset($data[array_key_first($data)][$sortBy])) {
            usort($result, function ($a, $b) use ($sortBy) {
                return strcmp(isset($a[$sortBy]) ? $a[$sortBy] : '', isset($b[$sortBy]) ? $b[$sortBy] : '');
            });
        }

        return $result;
    }

    public static function byId($id)
    {
        $data = static::loadData();
        return isset($data[$id]) ? $data[$id] : null;
    }

    public static function paginate($perPage = 100, $page = 1)
    {
        $data = static::loadData();
        $offset = ($page - 1) * $perPage;
        return array_slice($data, $offset, $perPage);
    }
}