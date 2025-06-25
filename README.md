````markdown
# Laravel Geo Data

An optimized Laravel package for accessing region, country, phone code, city, and currency data.

## Compatibility

* **PHP**: 7.3+, 8.0+
* **Laravel**: 6.x, 7.x, 8.x, 9.x, 10.x, 11.x, 12.x

## Installation

To install the package, run the following command:

```bash
composer require rahasistiyak/laravel-geo-data
````

The package will be auto-discovered by Laravel.

### Publish the Configuration File (if needed)

If you'd like to publish the configuration file for customization, use:

```bash
php artisan vendor:publish --tag=geo-data-config
```

This will publish the configuration file to `config/geo-data.php`.

## Usage

### Regions

You can interact with the **regions** model as follows:

```php
use RahasIstiyak\GeoData\Models\Region;

$regions = Region::all(); // All regions
$dropdown = Region::dropdown(); // ['id', 'name']
$region = Region::byId(1); // By ID
$page = Region::paginate(10, 1); // Paginated results
```

### Countries

You can interact with the **countries** model as follows:

```php
use RahasIstiyak\GeoData\Models\Country;

$countries = Country::all(); // All countries
$dropdown = Country::dropdown(['id', 'name', 'code']); // Custom fields default if no parameter ['id','name']- if you need more data then send it in parameter
$country = Country::byCode('AF'); // By ISO2/code or ISO3
$country = Country::byId(1); // By ID
$regionCountries = Country::byRegion(3); // By region ID
$page = Country::paginate(10, 1); // Paginated results
```

### Phone Codes

You can interact with the **phone codes** model as follows:

```php
use RahasIstiyak\GeoData\Models\PhoneCode;

$phoneCodes = PhoneCode::all(); // All phone codes
$dropdown = PhoneCode::dropdown(); // ['country_id', 'phonecode']
$phoneCode = PhoneCode::byCountryId(1); // By country ID
$page = PhoneCode::paginate(10, 1); // Paginated results
```

### Cities

You can interact with the **cities** model as follows:

```php
use RahasIstiyak\GeoData\Models\City;

$cities = City::getCities('US'); // All cities in the United States
$dropdown = City::getCityDropdown('US'); // Dropdown ['id', 'name'] for the United States
// If you need more data, send parameters like ['id', 'name', 'latitude']
$dropdownWithAdditionalFields = City::getCityDropdown('US', ['id', 'name', 'latitude']); // Additional fields

$city = City::getCityById('US', 1); // Get city with ID 1 in the United States
```

### Currencies

You can interact with the **currencies** model as follows:

```php
use RahasIstiyak\GeoData\Models\Currency;

$currencies = Currency::all(); // All currencies
$dropdown = Currency::dropdown(); // ['country_id', 'currency', 'currency_name']
$currency = Currency::byCountryId(1); // By country ID
$currency = Currency::byCode('AFN'); // By currency code
$page = Currency::paginate(10, 1); // Paginated results
```

## Configuration

You can edit the configuration in `config/geo-data.php`. The configuration file includes options for enabling/disabling specific data types and setting cache duration.

```php
return [
    'enabled_data' => [
        'regions' => true,
        'countries' => true,
        'phone_codes' => true,
        'cities' => true,
        'currencies' => true,
    ],
    'cache_ttl' => 1440, // Cache duration in minutes
];
```

## Test Usage

To test the functionality, you can add the following route in your `routes/web.php` file:

```php
use RahasIstiyak\GeoData\Models\Country;
use RahasIstiyak\GeoData\Models\City;

Route::get('/test-geo', function () {
    return response()->json([
        'countries' => Country::dropdown(),// you can send parameter for custom fields eg: ['id', 'name', 'code']
        'country_af' => Country::byCode('AF'),
        'country_1' => Country::byId(1),
        'cities_us' => City::getCities('US'), // Cities for the United States
        'city_1_us' => City::getCityById('US', 1), // City with ID 1 for US
       
    ]);
});
```

This will give you a sample response for countries and cities data.

## Why This is Optimized

* **Memory Efficiency**: Caching with `Cache::remember` reduces file I/O and memory usage for repeated calls.
* **Code Reusability**: The `BaseGeoModel` class eliminates duplicate logic across models.
* **Large Dataset Handling**: The `paginate` method allows partial loading of large datasets, like cities, to avoid memory overflow.
* **Error Handling**: The package validates data files and logs warnings for any missing or corrupted files.
* **Flexibility**: Dropdown fields and sorting are customizable to suit different use cases.
* **Performance**: Configuration checks are done once during data loading, and cache TTL is configurable for optimized performance.

---

### **License**

This package is open-source and available under the **MIT License**.

---

### **Contact**

For any questions or support, you can reach us at:
**Email**: [rahasistiyak.official@gmail.com](mailto:rahasistiyak.official@gmail.com)

```

### Changes Made:

- Updated the **Cities** section to include the new functions `getCityDropdown()` with parameters like `['id', 'name', 'latitude']`, and to reflect `getCityById()` and `getCities()`.
- Provided additional usage examples showing how to use the new parameters in the `City::getCityDropdown()` method.
```
