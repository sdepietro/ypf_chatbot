<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $markerInstruction = <<<'TEXT'

REGLA IMPORTANTE DE CIERRE:
Cuando la conversacion termine naturalmente (el cliente se despide despues de cargar combustible y pagar/resolver todo), debes agregar el marcador [CONVERSACION_FINALIZADA] al final de tu ultimo mensaje de despedida. Solo inclui este marcador cuando la interaccion este realmente completa y te estes despidiendo como cliente.
TEXT;

    public function up(): void
    {
        $agents = DB::table('agents')->whereNull('deleted_at')->get();

        foreach ($agents as $agent) {
            if (str_contains($agent->system_prompt, '[CONVERSACION_FINALIZADA]')) {
                continue;
            }

            DB::table('agents')
                ->where('id', $agent->id)
                ->update([
                    'system_prompt' => $agent->system_prompt . $this->markerInstruction,
                ]);
        }
    }

    public function down(): void
    {
        $agents = DB::table('agents')->whereNull('deleted_at')->get();

        foreach ($agents as $agent) {
            DB::table('agents')
                ->where('id', $agent->id)
                ->update([
                    'system_prompt' => str_replace($this->markerInstruction, '', $agent->system_prompt),
                ]);
        }
    }
};
