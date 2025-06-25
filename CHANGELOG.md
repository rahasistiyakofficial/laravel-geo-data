# Changelog

## [1.0.0] - 2025-06-25
- Initial release with optimized region, country, phone code, city, and currency data access.

## [1.1.0] - 2025-06-26
- Optimized region, country, phone code, city, and currency data access.

## [2.0.0] - 2025-06-26
### Changes Made:
- Updated **Cities** section to reflect the new methods: `getCities()`, `getCityById()`, and `getCityDropdown()`.
- Added example code to demonstrate how these methods can be used.

## [2.1.0] - 2025-06-27
### Changes Made:
- **Added Chunked Caching** for large city datasets to handle **database cache driver** more effectively.
    - Divided the caching process into smaller chunks to avoid exceeding MySQL’s packet size.
    - Introduced `cacheInChunks()` to store the cities data in smaller parts and retrieve them efficiently.
    - This change ensures that caching large city data in the database cache driver doesn't lead to the "MySQL server has gone away" error.
- **Performance Improvement**: Optimized city data retrieval by caching in chunks and merging data from multiple chunks.
- **Database Cache Driver Compatibility**: The chunked caching strategy now ensures compatibility with the database cache driver (`CACHE_DRIVER=database`), preventing large cache payload issues.

## [2.2.0] - 2025-06-27
### Changes Made:
- **Optimized Chunked Caching** for large city datasets to handle **database cache driver** more effectively.
- **Duplicate City Removal**: Remove Duplicate Cities from Dataset.

## [2.3.0] - 2025-06-28
### Changes Made:
- **All Data Return Types Array to Collection** Use $data->name insted of $data['name']
