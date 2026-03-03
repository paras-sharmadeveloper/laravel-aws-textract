<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoService
{
    public function geocode($address, $prefix = 'home')
    {
        if (!$address) return null;

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => config('services.googlemap.api_key'),
        ]);

        if (!$response->successful()) return null;

        $data = $response->json();

        if ($data['status'] !== 'OK') return null;

        $location = $data['results'][0]['geometry']['location'];

        return [
            "{$prefix}_latitude" => $location['lat'],
            "{$prefix}_longitude" => $location['lng'],
        ];
    }
}
