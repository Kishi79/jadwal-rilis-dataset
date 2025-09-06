<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SatuDataApiService
{
    protected $baseUrl = 'https://satudata-api.garutkab.go.id/api';
    protected $timeout = 30;

    /**
     * Get all datasets from API with caching
     */
    public function getDatasets()
    {
        try {
            // Cache selama 1 jam
            return Cache::remember('satudata_datasets', 3600, function () {
                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl . '/datasets');

                if ($response->successful()) {
                    $data = $response->json();
                    // Pastikan data ada dan dalam format array
                    if (isset($data['data'])) {
                        return $data['data'];
                    } elseif (is_array($data)) {
                        return $data;
                    }
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching datasets from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all OPDs from API with caching
     */
    public function getOpds()
    {
        try {
            // Cache selama 1 jam
            return Cache::remember('satudata_opds', 3600, function () {
                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl . '/opds');

                if ($response->successful()) {
                    $data = $response->json();
                    // Pastikan data ada dan dalam format array
                    if (isset($data['data'])) {
                        return $data['data'];
                    } elseif (is_array($data)) {
                        return $data;
                    }
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching OPDs from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single dataset by ID
     */
    public function getDatasetById($id)
    {
        try {
            $cacheKey = 'satudata_dataset_' . $id;

            return Cache::remember($cacheKey, 1800, function () use ($id) {
                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl . '/datasets/' . $id);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching dataset by ID from API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get single OPD by ID
     */
    public function getOpdById($id)
    {
        try {
            $cacheKey = 'satudata_opd_' . $id;

            return Cache::remember($cacheKey, 1800, function () use ($id) {
                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl . '/opds/' . $id);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching OPD by ID from API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear all cached data
     */
    public function clearCache()
    {
        Cache::forget('satudata_datasets');
        Cache::forget('satudata_opds');
        // Clear individual dataset/opd caches if needed
    }

    /**
     * Get list sektoral (hardcoded atau dari datasets)
     */
    public function getSektoralList()
    {
        // Bisa diambil dari dataset yang ada atau hardcode
        return [
            'Pendidikan',
            'Kesehatan',
            'Ekonomi',
            'Pertanian',
            'Pariwisata',
            'Infrastruktur',
            'Sosial',
            'Pemerintahan',
            'Lingkungan',
            'Teknologi Informasi'
        ];
    }
}
