<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationHealth;

class IntegrationController extends Controller
{
    public function index(IntegrationHealth $health)
    {
        $integrations = collect(IntegrationHealth::INTEGRATIONS)->map(fn($meta, $key) => $meta + [
            'key' => $key,
            'config' => $health->config($key),
            'last' => $health->last($key),
        ]);

        return view('admin.integrations', compact('integrations'));
    }

    public function test(IntegrationHealth $health, string $integration)
    {
        abort_unless(array_key_exists($integration, IntegrationHealth::INTEGRATIONS), 404);

        return response()->json($health->run($integration));
    }
}
