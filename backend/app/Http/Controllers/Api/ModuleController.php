<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LessonSummaryResource;
use App\Http\Resources\ModuleResource;
use App\Models\Module;
use Illuminate\Http\JsonResponse;

class ModuleController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $modules = Module::with('lessons')->orderBy('order')->get();

        return $this->success(ModuleResource::collection($modules), 'Modules retrieved');
    }

    public function show(Module $module): JsonResponse
    {
        $module->load('lessons');

        return $this->success(new ModuleResource($module), 'Module retrieved');
    }

    public function lessons(Module $module): JsonResponse
    {
        return $this->success(LessonSummaryResource::collection($module->lessons), 'Module lessons retrieved');
    }
}
