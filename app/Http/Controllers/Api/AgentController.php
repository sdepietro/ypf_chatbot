<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function index(): JsonResponse
    {
        $agents = Agent::orderBy('name')
            ->get()
            ->map(fn($agent) => [
                'id' => $agent->id,
                'name' => $agent->name,
                'description' => $agent->description,
                'system_prompt' => $agent->system_prompt,
                'is_active' => $agent->is_active,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ]);

        return response()->json([
            'status' => true,
            'data' => $agents,
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $agent = Agent::create($request->validated());

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'description' => $agent->description,
                'system_prompt' => $agent->system_prompt,
                'is_active' => $agent->is_active,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ],
            'message' => 'Agente creado exitosamente',
            'errors' => [],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Agente no encontrado',
                'errors' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'description' => $agent->description,
                'system_prompt' => $agent->system_prompt,
                'is_active' => $agent->is_active,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ],
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function update(UpdateAgentRequest $request, int $id): JsonResponse
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Agente no encontrado',
                'errors' => [],
            ], 404);
        }

        $agent->update($request->validated());

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'description' => $agent->description,
                'system_prompt' => $agent->system_prompt,
                'is_active' => $agent->is_active,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ],
            'message' => 'Agente actualizado exitosamente',
            'errors' => [],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Agente no encontrado',
                'errors' => [],
            ], 404);
        }

        // Check if agent has chats
        if ($agent->chats()->exists()) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'No se puede eliminar un agente con chats asociados',
                'errors' => [],
            ], 400);
        }

        $agent->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Agente eliminado exitosamente',
            'errors' => [],
        ]);
    }
}
