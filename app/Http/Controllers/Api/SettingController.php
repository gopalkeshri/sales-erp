<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Display a listing of all settings.
     */
    public function index(Request $request): JsonResponse
    {
        $grouped = Setting::getAllGrouped();
        $flat = Setting::getAllKeyValue();

        return response()->json([
            'status' => 'success',
            'data' => [
                'grouped' => $grouped,
                'settings' => $flat,
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'environment' => app()->environment(),
                    'server_time' => now()->toDateTimeString(),
                    'server_timezone' => config('app.timezone', 'UTC'),
                    'database_driver' => config('database.default'),
                ],
            ],
        ]);
    }

    /**
     * Update settings in batch.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->all();

        // Remove token/method fields if passed from form
        unset($data['_token'], $data['_method']);

        if (empty($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No settings payload provided for update.',
            ], 422);
        }

        // Validate and sanitize specific known fields if present
        if (isset($data['company_email']) && !filter_var($data['company_email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided company email address is invalid.',
            ], 422);
        }

        if (isset($data['default_tax_rate']) && (!is_numeric($data['default_tax_rate']) || $data['default_tax_rate'] < 0 || $data['default_tax_rate'] > 100)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Default tax rate must be a percentage between 0 and 100.',
            ], 422);
        }

        if (isset($data['default_commission_rate']) && (!is_numeric($data['default_commission_rate']) || $data['default_commission_rate'] < 0 || $data['default_commission_rate'] > 100)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Default commission rate must be a percentage between 0 and 100.',
            ], 422);
        }

        if (isset($data['low_stock_threshold']) && (!is_numeric($data['low_stock_threshold']) || $data['low_stock_threshold'] < 0)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Low stock threshold must be a non-negative integer.',
            ], 422);
        }

        Setting::setMany($data);

        return response()->json([
            'status' => 'success',
            'message' => 'General settings saved and updated successfully.',
            'data' => Setting::getAllKeyValue(),
        ]);
    }

    /**
     * Reset settings to factory defaults.
     */
    public function reset(): JsonResponse
    {
        Setting::truncate();
        Setting::seedDefaults();

        return response()->json([
            'status' => 'success',
            'message' => 'General settings have been successfully reset to system factory defaults.',
            'data' => Setting::getAllKeyValue(),
        ]);
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): JsonResponse
    {
        Cache::flush();
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
        } catch (\Throwable $e) {
            // Ignore if console commands restricted
        }

        return response()->json([
            'status' => 'success',
            'message' => 'System application caches, view templates, and configuration buffers flushed successfully.',
        ]);
    }
}
