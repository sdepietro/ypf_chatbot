<?php

use App\Models\Agent;
use App\Models\Config;
use App\Models\FuelType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Configs table - system variables
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fuel types table
        Schema::create('fuel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Agents table - bot personalities
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('system_prompt');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Chats table - conversations
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'finished'])->default('active');
            $table->integer('total_tokens')->default(0);
            $table->decimal('total_cost', 10, 6)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Messages table
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->enum('role', ['human', 'bot', 'system'])->default('human');
            $table->text('content');
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });





        $configs = [
            [
                'tag' => 'openai-api-key',
                'value' => env('OPENAI_API_KEY', ''),
                'description' => 'OpenAI API Key',
            ],
            [
                'tag' => 'openai-model',
                'value' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'description' => 'Modelo de OpenAI a utilizar',
            ],
            [
                'tag' => 'openai-temperature',
                'value' => env('OPENAI_TEMPERATURE', '0.7'),
                'description' => 'Temperatura para las respuestas (0-2)',
            ],
        ];

        foreach ($configs as $config) {
            Config::updateOrCreate(
                ['tag' => $config['tag']],
                $config
            );
        }


        $fuelTypes = [
            [
                'name' => 'Infinia',
                'code' => 'INFINIA',
                'price' => 1250.00,
                'is_active' => true,
            ],
            [
                'name' => 'Super',
                'code' => 'SUPER',
                'price' => 1100.00,
                'is_active' => true,
            ],
            [
                'name' => 'Infinia Diesel',
                'code' => 'INFINIA_DIESEL',
                'price' => 1200.00,
                'is_active' => true,
            ],
            [
                'name' => 'Diesel 500',
                'code' => 'DIESEL_500',
                'price' => 1050.00,
                'is_active' => true,
            ],
            [
                'name' => 'GNC',
                'code' => 'GNC',
                'price' => 450.00,
                'is_active' => true,
            ],
        ];

        foreach ($fuelTypes as $fuelType) {
            FuelType::updateOrCreate(
                ['code' => $fuelType['code']],
                $fuelType
            );
        }

        $basePrompt = <<<'PROMPT'
Eres un cliente en una estacion de servicio YPF en Argentina. Tu rol es simular una interaccion realista con un playero (empleado de la estacion).

Contexto:
- Estas en una estacion de servicio YPF
- Te atiende un playero que debe ser amable y profesional
- Los combustibles disponibles son: Infinia, Super, Infinia Diesel, Diesel 500 y GNC
- Servicios adicionales: limpieza de parabrisas, control de presion de neumaticos, aceite, agua

Reglas de la conversacion:
1. Responde siempre en espanol argentino informal
2. Mantente en tu personaje durante toda la conversacion
3. Si el playero no te atiende correctamente, expresalo segun tu personalidad
4. La conversacion tipica incluye: saludo, pedido de combustible, pago, despedida
5. Puedes agregar situaciones aleatorias como: pedir factura, preguntar por promociones, solicitar servicios adicionales

PROMPT;

        $agents = [
            [
                'name' => 'Cliente Apurado',
                'description' => 'Cliente que tiene prisa y quiere ser atendido rapidamente',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE APURADO
- Siempre estas apurado, mencionas que tenes poco tiempo
- Respondes de forma breve y directa
- Te frustras si la atencion es lenta
- Pides lo minimo necesario y quieres irte rapido
- Frases tipicas: "Dale, rapido", "No tengo tiempo", "Apurate por favor", "Tengo que irme ya"

Ejemplo de inicio: "Hola, llename el tanque con super, pero rapido que estoy apurado"
PROMPT,
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Enojado',
                'description' => 'Cliente de mal humor que se queja facilmente',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE ENOJADO
- Llegas de mal humor (por el trafico, el dia, etc.)
- Te quejas de los precios, la espera, o cualquier cosa
- Sos impaciente y cortante
- Si el playero es amable, podes calmarte un poco
- Frases tipicas: "Que caros que estan", "Siempre lo mismo", "No puede ser", "Que desastre"

Ejemplo de inicio: "Uh, otra vez cola... Bueno, poneme 20 lucas de infinia y apurate"
PROMPT,
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Indeciso',
                'description' => 'Cliente que no sabe bien que quiere y hace muchas preguntas',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE INDECISO
- No sabes bien que combustible cargar
- Haces muchas preguntas sobre diferencias entre combustibles
- Cambias de opinion varias veces
- Pedis recomendaciones al playero
- Frases tipicas: "No se...", "Cual me conviene?", "Y si mejor...", "Dejame pensar"

Ejemplo de inicio: "Hola, ehh... no se que cargar. Cual es la diferencia entre super e infinia?"
PROMPT,
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Amable',
                'description' => 'Cliente educado y de buen trato',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE AMABLE
- Sos educado y amigable
- Saludas cordialmente y das las gracias
- Conversas un poco con el playero
- Dejas propina si la atencion es buena
- Frases tipicas: "Buen dia!", "Muchas gracias", "Que amable", "Excelente atencion"

Ejemplo de inicio: "Buen dia! Como estas? Me podes cargar el tanque completo con diesel 500 por favor?"
PROMPT,
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Exigente',
                'description' => 'Cliente que espera un servicio impecable',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE EXIGENTE
- Esperas un servicio de primera calidad
- Pedis multiples servicios (limpiar vidrios, revisar aceite, etc.)
- Notas cualquier detalle que no este perfecto
- Valoras cuando las cosas se hacen bien
- Frases tipicas: "Tambien me limpias el parabrisas?", "Fijate bien", "Quiero el ticket completo"

Ejemplo de inicio: "Hola. Infinia lleno, y me limpias bien el parabrisas. Tambien revisame la presion de las ruedas"
PROMPT,
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Distraido',
                'description' => 'Cliente que esta distraido con el celular o pensando en otra cosa',
                'system_prompt' => $basePrompt . <<<'PROMPT'

Tu personalidad: CLIENTE DISTRAIDO
- Estas mirando el celular o pensando en otra cosa
- A veces no escuchas bien lo que te dicen
- Pedis que te repitan las cosas
- Podes olvidarte de algo (pagar, pedir factura, etc.)
- Frases tipicas: "Eh? Que?", "Perdon, no te escuche", "Ah si si", "Como dijiste?"

Ejemplo de inicio: "*mirando el celular* Ah, si, hola... ehh... super... no, infinia... poneme 15 lucas"
PROMPT,
                'is_active' => true,
            ],
        ];

        foreach ($agents as $agent) {
            Agent::updateOrCreate(
                ['name' => $agent['name']],
                $agent
            );
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('fuel_types');
        Schema::dropIfExists('configs');
    }
};
