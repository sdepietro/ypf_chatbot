# Medicion de Consumo y Costos en Tiempo Real

Este documento explica como el sistema mide los costos de uso de la API de OpenAI y como se muestran en tiempo real en la interfaz de usuario.

---

## Arquitectura General

El flujo de medicion de costos involucra tres capas:

```
Frontend (Blade/JS) ←→ API (Controllers) ←→ Services ←→ OpenAI API
                                              ↓
                                         Base de Datos
```

---

## 1. Obtencion de Datos de Consumo desde OpenAI

### OpenAIService (`app/Services/OpenAIService.php`)

Cuando se hace una llamada a la API de OpenAI, la respuesta incluye informacion de uso de tokens:

```php
$response = $this->client->chat()->create([
    'model' => $this->model,
    'messages' => $formattedMessages,
    'temperature' => $this->temperature,
]);

// OpenAI devuelve estos datos en la respuesta:
$promptTokens = $response->usage->promptTokens;       // Tokens de entrada (prompt)
$completionTokens = $response->usage->completionTokens; // Tokens de salida (respuesta)
```

### Tipos de Tokens

| Tipo | Descripcion |
|------|-------------|
| **Prompt Tokens** | Cantidad de tokens en el mensaje de entrada (incluye historial + system prompt) |
| **Completion Tokens** | Cantidad de tokens en la respuesta generada por el modelo |
| **Total Tokens** | Suma de prompt + completion tokens |

---

## 2. Calculo del Costo

### Tabla de Precios por Modelo

El servicio mantiene una tabla de precios por millon de tokens (precios aproximados 2024):

```php
protected array $pricing = [
    'gpt-4o'         => ['input' => 2.50,  'output' => 10.00],
    'gpt-4o-mini'    => ['input' => 0.15,  'output' => 0.60],
    'gpt-4-turbo'    => ['input' => 10.00, 'output' => 30.00],
    'gpt-4'          => ['input' => 30.00, 'output' => 60.00],
    'gpt-3.5-turbo'  => ['input' => 0.50,  'output' => 1.50],
];
```

### Formula de Calculo

```php
protected function calculateCost(int $promptTokens, int $completionTokens): float
{
    $modelPricing = $this->pricing[$this->model] ?? $this->pricing['gpt-4o-mini'];

    // Costo = (tokens / 1,000,000) * precio_por_millon
    $inputCost = ($promptTokens / 1_000_000) * $modelPricing['input'];
    $outputCost = ($completionTokens / 1_000_000) * $modelPricing['output'];

    return round($inputCost + $outputCost, 6);
}
```

**Ejemplo con gpt-4o-mini:**
- 500 prompt tokens: (500 / 1,000,000) * $0.15 = $0.000075
- 150 completion tokens: (150 / 1,000,000) * $0.60 = $0.000090
- **Costo total: $0.000165**

---

## 3. Persistencia de Datos

### A Nivel de Mensaje (`messages` table)

Cada mensaje del bot guarda su consumo individual:

```php
// ChatService.php - Al guardar respuesta del bot:
$botMessage = Message::create([
    'chat_id' => $chat->id,
    'role' => 'bot',
    'content' => $response['content'],
    'prompt_tokens' => $response['prompt_tokens'],      // Tokens de entrada
    'completion_tokens' => $response['completion_tokens'], // Tokens de salida
    'cost' => $response['cost'],                        // Costo calculado
    'meta' => [
        'model' => $response['model'],
        'total_tokens' => $response['total_tokens'],
    ],
]);
```

**Campos en tabla `messages`:**
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `prompt_tokens` | integer | Tokens de entrada usados |
| `completion_tokens` | integer | Tokens de salida generados |
| `cost` | decimal(10,6) | Costo en USD de este mensaje |

### A Nivel de Chat (`chats` table)

Cada chat acumula el total de todos sus mensajes:

```php
// ChatService.php - Despues de guardar el mensaje:
$chat->addTokensAndCost(
    $response['total_tokens'],
    $response['cost']
);

// Chat.php - Metodo que incrementa los acumulados:
public function addTokensAndCost(int $tokens, float $cost): void
{
    $this->increment('total_tokens', $tokens);  // Suma atomica
    $this->increment('total_cost', $cost);      // Suma atomica
}
```

**Campos en tabla `chats`:**
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `total_tokens` | integer | Total acumulado de tokens usados |
| `total_cost` | decimal(10,6) | Costo total acumulado en USD |

---

## 4. Exposicion via API

### Respuesta de POST `/api/chats/{id}/messages`

Cuando se envia un mensaje, la API devuelve los datos de consumo:

```json
{
    "status": true,
    "data": {
        "human_message": {
            "id": 123,
            "role": "human",
            "content": "Hola, necesito cargar nafta"
        },
        "bot_message": {
            "id": 124,
            "role": "bot",
            "content": "Buen dia! Claro, que tipo de combustible...",
            "prompt_tokens": 450,
            "completion_tokens": 89,
            "cost": "0.000125"
        },
        "usage": {
            "prompt_tokens": 450,
            "completion_tokens": 89,
            "total_tokens": 539,
            "cost": 0.000125
        }
    }
}
```

### Respuesta de GET `/api/chats/{id}`

Devuelve los totales acumulados del chat:

```json
{
    "status": true,
    "data": {
        "id": 15,
        "title": "Cliente apurado - 20/01 15:30",
        "total_tokens": 2547,
        "total_cost": "0.001234",
        "agent": {
            "name": "Cliente Apurado"
        }
    }
}
```

---

## 5. Visualizacion en Tiempo Real (Frontend)

### Header del Chat (`resources/views/chat/index.blade.php`)

El header muestra los totales actuales:

```html
<div class="chat-stats">
    <div>Tokens: <span id="chatTokens">0</span></div>
    <div>Costo: $<span id="chatCost">0.000000</span></div>
</div>
```

### Carga Inicial del Chat

Cuando se selecciona un chat, se cargan los totales desde la API:

```javascript
async function selectChat(chatId) {
    const chatResponse = await apiFetch(`/api/chats/${chatId}`);
    const chatData = await chatResponse.json();

    if (chatData.status) {
        document.getElementById('chatTokens').textContent = chatData.data.total_tokens;
        document.getElementById('chatCost').textContent = parseFloat(chatData.data.total_cost).toFixed(6);
    }
}
```

### Actualizacion en Tiempo Real

Despues de enviar cada mensaje, se actualizan los contadores sumando el nuevo consumo:

```javascript
async function sendMessage() {
    const response = await apiFetch(`/api/chats/${currentChatId}/messages`, {
        method: 'POST',
        body: JSON.stringify({ content })
    });

    const data = await response.json();

    if (data.status && data.data.usage) {
        // Obtener valores actuales
        const currentTokens = parseInt(document.getElementById('chatTokens').textContent);
        const currentCost = parseFloat(document.getElementById('chatCost').textContent);

        // Sumar el nuevo consumo
        document.getElementById('chatTokens').textContent =
            currentTokens + data.data.usage.total_tokens;
        document.getElementById('chatCost').textContent =
            (currentCost + data.data.usage.cost).toFixed(6);
    }
}
```

---

## 6. Diagrama de Flujo Completo

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO DE MEDICION DE COSTOS                       │
└─────────────────────────────────────────────────────────────────────────────┘

1. Usuario envia mensaje
   │
   ▼
2. Frontend → POST /api/chats/{id}/messages
   │
   ▼
3. MessageController → ChatService.sendMessage()
   │
   ▼
4. ChatService → OpenAIService.chat()
   │
   ▼
5. OpenAIService → API OpenAI (chat completions)
   │
   ▼
6. OpenAI responde con:
   ├── content: "Respuesta del bot..."
   ├── usage.promptTokens: 450
   └── usage.completionTokens: 89
   │
   ▼
7. OpenAIService.calculateCost()
   │  └── Calcula: (450/1M)*0.15 + (89/1M)*0.60 = $0.000125
   │
   ▼
8. ChatService guarda en DB:
   ├── Message (con prompt_tokens, completion_tokens, cost)
   └── Chat.addTokensAndCost() (incrementa totales)
   │
   ▼
9. API devuelve respuesta con campo 'usage'
   │
   ▼
10. Frontend actualiza contadores en tiempo real
    ├── chatTokens: currentTokens + 539
    └── chatCost: currentCost + 0.000125
```

---

## 7. Consideraciones Importantes

### Precision del Costo

- Los costos se almacenan con 6 decimales (`decimal(10,6)`)
- Esto es necesario porque los costos por mensaje son muy pequenos (fracciones de centavo)

### Actualizacion de Precios

Si OpenAI cambia sus precios, se debe actualizar la tabla `$pricing` en `OpenAIService.php`:

```php
protected array $pricing = [
    'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
    // Actualizar segun https://openai.com/pricing
];
```

### Modelo por Defecto

Si el modelo configurado no tiene precio definido, se usa el precio de `gpt-4o-mini` como fallback:

```php
$modelPricing = $this->pricing[$this->model] ?? $this->pricing['gpt-4o-mini'];
```

### Consistencia de Datos

Los incrementos en `Chat` usan `increment()` de Eloquent, que es atomico y evita race conditions:

```php
$this->increment('total_tokens', $tokens);
$this->increment('total_cost', $cost);
```

---

## 8. Ejemplo Practico

**Escenario:** Usuario envia 5 mensajes en una conversacion

| Msg # | Prompt Tokens | Completion Tokens | Costo Individual | Total Acumulado |
|-------|---------------|-------------------|------------------|-----------------|
| 1 | 120 | 45 | $0.000045 | $0.000045 |
| 2 | 285 | 62 | $0.000080 | $0.000125 |
| 3 | 467 | 78 | $0.000117 | $0.000242 |
| 4 | 665 | 95 | $0.000157 | $0.000399 |
| 5 | 880 | 110 | $0.000198 | $0.000597 |

**Nota:** Los prompt tokens aumentan porque incluyen todo el historial previo.

El usuario ve en la UI: `Tokens: 2807 | Costo: $0.000597`
