<?php

namespace RahasIstiyak\GeoData\Tests;

use Orchestra\Testbench\TestCase;
use RahasIstiyak\GeoData\GeoDataServiceProvider;
use RahasIstiyak\GeoData\Models\Region;
use RahasIstiyak\GeoData\Models\Country;
use RahasIstiyak\GeoData\Models\PhoneCode;
use RahasIstiyak\GeoData\Models\City;
use RahasIstiyak\GeoData\Models\Currency;

class GeoDataTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GeoDataServiceProvider::class];
    }

    public function testRegionsData()
    {
        $this->assertNotEmpty(Region::all());
        $this->assertNotEmpty(Region::dropdown());
        $this->assertNotNull(Region::byId(1));
        $this->assertNotEmpty(Region::paginate(10));
    }

    public function testCountriesData()
    {
        $this->assertNotEmpty(Country::all());
        $this->assertNotEmpty(Country::dropdown(['id', 'name', 'iso2']));
        $this->assertNotNull(Country::byCode('AF'));
        $this->assertNotNull(Country::byId(1));
        $this->assertNotEmpty(Country::byRegion(3));
        $this->assertNotEmpty(Country::paginate(10));
    }

    public function testPhoneCodesData()
    {
        $this->assertNotEmpty(PhoneCode::all());
        $this->assertNotEmpty(PhoneCode::dropdown());
        $this->assertNotNull(PhoneCode::byCountryId(1));
        $this->assertNotEmpty(PhoneCode::paginate(10));
    }

    public function testCitiesData()
    {
        $this->assertNotEmpty(City::all());
        $this->assertNotEmpty(City::dropdown());
        $this->assertNotNull(City::byId(1));
        $this->assertNotEmpty(City::byCountryId(1));
        $this->assertNotEmpty(City::byCountryCode('AF'));
        $this->assertNotEmpty(City::paginate(10));
    }

    public function testCurrenciesData()
    {
        $this->assertNotEmpty(Currency::all());
        $this->assertNotEmpty(Currency::dropdown());
        $this->assertNotNull(Currency::byCountryId(1));
        $this->assertNotNull(Currency::byCode('AFN'));
        $this->assertNotEmpty(Currency::paginate(10));
    }
}