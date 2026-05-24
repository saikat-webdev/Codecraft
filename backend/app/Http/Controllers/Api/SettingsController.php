<?php

namespace App\Http\Controllers\Api;

use App\Services\AppSettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends BaseApiController
{
    public function index(AppSettingsService $settings): JsonResponse
    {
        return $this->success($settings->getPublicSettings(), 'Feature settings retrieved');
    }
}
