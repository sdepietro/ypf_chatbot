<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConfigRequest;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\Config;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $configs = Config::orderBy('tag')
            ->get()
            ->map(fn($config) => [
                'id' => $config->id,
                'tag' => $config->tag,
                'value' => $config->value,
                'description' => $config->description,
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
            ]);

        return response()->json([
            'status' => true,
            'data' => $configs,
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function store(StoreConfigRequest $request): JsonResponse
    {
        $config = Config::create($request->validated());

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $config->id,
                'tag' => $config->tag,
                'value' => $config->value,
                'description' => $config->description,
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
            ],
            'message' => 'Configuracion creada exitosamente',
            'errors' => [],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $config = Config::find($id);

        if (!$config) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Configuracion no encontrada',
                'errors' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $config->id,
                'tag' => $config->tag,
                'value' => $config->value,
                'description' => $config->description,
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
            ],
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function update(UpdateConfigRequest $request, int $id): JsonResponse
    {
        $config = Config::find($id);

        if (!$config) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Configuracion no encontrada',
                'errors' => [],
            ], 404);
        }

        $config->update($request->validated());

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $config->id,
                'tag' => $config->tag,
                'value' => $config->value,
                'description' => $config->description,
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
            ],
            'message' => 'Configuracion actualizada exitosamente',
            'errors' => [],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $config = Config::find($id);

        if (!$config) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Configuracion no encontrada',
                'errors' => [],
            ], 404);
        }

        $config->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Configuracion eliminada exitosamente',
            'errors' => [],
        ]);
    }
}
