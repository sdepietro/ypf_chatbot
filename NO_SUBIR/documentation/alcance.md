# YPF Chat Station - Alcance del Proyecto

## Descripción General

**YPF Chat Station** es una aplicación web para entrenar playeros de estaciones de servicio YPF mediante conversaciones simuladas con un bot que adopta diferentes personalidades de clientes.

El bot siempre tiene como objetivo cargar combustible (X pesos o X litros de un tipo específico), y el playero (humano) debe atenderlo correctamente según la personalidad asignada.

---

## Stack Tecnológico

| Componente | Tecnología |
|------------|------------|
| Backend | Laravel 12 + PHP 8.2+ |
| Base de datos | MySQL (Docker) |
| Frontend | Blade + Bootstrap 5 + FontAwesome |
| IA | OpenAI ChatGPT API |
| Entorno | Docker |

---

## Funcionalidades Principales

### 1. Sistema de Chat (UI tipo ChatGPT)
- Sidebar izquierdo con lista de conversaciones
- Panel central con mensajes del chat
- Input inferior para enviar mensajes
- Estados: "escribiendo...", scroll automático

### 2. ABM de Agentes (Personalidades)
- Crear, editar, eliminar personalidades
- Cada agente tiene: nombre + system prompt
- Activar/desactivar agentes

### 3. Integración OpenAI
- Llamadas a ChatGPT API
- Tracking de tokens usados
- Cálculo de costo por conversación
- Configuración dinámica del modelo

### 4. Autenticación Simple
- Sin sistema de usuarios
- Contraseña maestra única: `YPF2026WOOPI`

---

## Arquitectura de Base de Datos

### Diagrama de Entidades

```
┌──────────────┐     ┌──────────────┐
│   configs    │     │  fuel_types  │
├──────────────┤     ├──────────────┤
│ id           │     │ id           │
│ tag (unique) │     │ name         │
│ value        │     │ code (unique)│
│ type         │     │ price_per_l  │
│ name         │     │ is_active    │
│ description  │     │ sort_order   │
│ is_public    │     │ timestamps   │
│ timestamps   │     │ deleted_at   │
│ deleted_at   │     └──────────────┘
└──────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   agents     │     │    chats     │     │   messages   │
├──────────────┤     ├──────────────┤     ├──────────────┤
│ id           │◄────│ agent_id (FK)│◄────│ chat_id (FK) │
│ name         │     │ id           │     │ id           │
│ system_prompt│     │ title        │     │ role         │
│ is_active    │     │ status       │     │ content      │
│ usage_count  │     │ total_cost   │     │ meta (json)  │
│ timestamps   │     │ total_tokens │     │ timestamps   │
│ deleted_at   │     │ meta (json)  │     │ deleted_at   │
└──────────────┘     │ timestamps   │     └──────────────┘
                     │ deleted_at   │
                     └──────────────┘
```

### Detalle de Tablas

#### `configs`
Variables de configuración del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| tag | varchar(100) | Identificador único (ej: `openai-api-key`) |
| value | text | Valor de la configuración |
| type | enum | `string`, `integer`, `float`, `boolean`, `json`, `text` |
| name | varchar(150) | Nombre legible |
| description | text | Descripción para admins |
| is_public | boolean | Si se expone al frontend |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Configuraciones iniciales:**
- `openai-api-key` - API key de OpenAI
- `openai-model` - Modelo a usar (gpt-4o-mini)
- `openai-max-tokens` - Límite de tokens (500)
- `openai-temperature` - Creatividad (0.7)

---

#### `fuel_types`
Tipos de combustible disponibles.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| name | varchar(100) | Nombre (ej: Infinia Diesel) |
| code | varchar(20) | Código único (ej: infinia_diesel) |
| price_per_liter | decimal(10,2) | Precio por litro |
| is_active | boolean | Si está disponible |
| sort_order | int | Orden de display |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Combustibles iniciales:**
- Infinia Diesel ($1250/l)
- Diesel 500 ($1100/l)
- Infinia ($1350/l)
- Super ($1150/l)
- GNC ($450/m³)

---

#### `agents`
Personalidades del bot.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| name | varchar(150) | Nombre descriptivo |
| system_prompt | text | Prompt de sistema para ChatGPT |
| is_active | boolean | Si puede ser seleccionado |
| usage_count | int | Contador de veces usado |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Agentes iniciales:**
1. **Cliente Apurado** - Breve, impaciente, mira el reloj
2. **Cliente Indeciso** - Pregunta mucho, cambia de opinión
3. **Cliente Enojado** - Mal humor inicial, se calma con buen trato
4. **Cliente Amable** - Cordial, conversador, agradecido
5. **Cliente Primerizo** - Primera vez cargando, necesita guía

---

#### `chats`
Conversaciones/salas.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| agent_id | bigint | FK → agents |
| title | varchar(200) | Título (autogenerado del 1er mensaje) |
| status | enum | `active`, `finished`, `archived` |
| total_cost | decimal(12,6) | Costo acumulado USD |
| total_tokens | int | Tokens totales usados |
| meta | json | Metadata adicional |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

#### `messages`
Mensajes de cada conversación.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| chat_id | bigint | FK → chats |
| role | enum | `human`, `bot`, `system` |
| content | text | Contenido del mensaje |
| meta | json | Tokens, modelo, costo, tiempo |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Estructura del campo `meta` (mensajes del bot):**
```json
{
    "model": "gpt-4o-mini",
    "prompt_tokens": 150,
    "completion_tokens": 80,
    "total_tokens": 230,
    "cost_usd": 0.000345,
    "response_time_ms": 1250
}
```

---

## API REST

### Autenticación
- Web: Session con contraseña maestra
- API: Header `X-Auth-Password` o session

### Endpoints

#### Chats
```
GET    /api/chats              Lista chats (sidebar)
POST   /api/chats              Crear chat (asigna agent aleatorio)
GET    /api/chats/{id}         Detalle de chat
DELETE /api/chats/{id}         Eliminar chat (soft delete)
```

#### Mensajes
```
GET    /api/chats/{id}/messages     Historial de mensajes
POST   /api/chats/{id}/messages     Enviar mensaje + obtener respuesta
```

#### Agentes
```
GET    /api/agents             Lista agentes
POST   /api/agents             Crear agente
GET    /api/agents/{id}        Detalle agente
PUT    /api/agents/{id}        Actualizar agente
DELETE /api/agents/{id}        Eliminar agente
```

#### Configs
```
GET    /api/configs/public     Configs públicas
```

### Formato de Respuesta

**Éxito:**
```json
{
    "status": true,
    "data": { ... },
    "message": "OK",
    "errors": []
}
```

**Error:**
```json
{
    "status": false,
    "data": null,
    "message": "Error description",
    "errors": { "field": ["..."] }
}
```

---

## Rutas Web

```
GET  /login          Formulario de login
POST /login          Procesar login
POST /logout         Cerrar sesión

GET  /               Redirect a /chat
GET  /chat           Lista de chats + chat activo
GET  /chat/{id}      Chat específico

GET  /agents         ABM de agentes
```

---

## Estructura de Archivos

```
app/
├── Helpers/
│   └── Configs.php              # wGetConfigs() existente
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── ChatController.php
│   │   │   ├── MessageController.php
│   │   │   ├── AgentController.php
│   │   │   └── ConfigController.php
│   │   └── Web/
│   │       ├── AuthController.php
│   │       ├── ChatPageController.php
│   │       └── AgentPageController.php
│   ├── Middleware/
│   │   └── MasterPasswordAuth.php
│   └── Requests/
│       ├── SendMessageRequest.php
│       ├── StoreAgentRequest.php
│       └── UpdateAgentRequest.php
├── Models/
│   ├── Config.php
│   ├── FuelType.php
│   ├── Agent.php
│   ├── Chat.php
│   └── Message.php
└── Services/
    ├── OpenAIService.php        # Reemplaza ChatGptService
    └── ChatService.php          # Lógica de conversaciones

database/
├── migrations/
│   └── 0002_01_01_000002_create_all_tables.php  # TODAS las tablas
└── seeders/
    ├── ConfigSeeder.php
    ├── FuelTypeSeeder.php
    ├── AgentSeeder.php
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   └── app.blade.php            # Bootstrap 5 + FontAwesome
├── auth/
│   └── login.blade.php
├── chat/
│   └── index.blade.php          # UI tipo ChatGPT
└── agents/
    └── index.blade.php          # ABM Agentes

routes/
├── api.php                      # CREAR
└── web.php                      # MODIFICAR
```

---

## Tracking de Costos (OpenAI)

### Precios por 1K tokens (actualizar según OpenAI)

| Modelo | Input | Output |
|--------|-------|--------|
| gpt-4o-mini | $0.00015 | $0.0006 |
| gpt-4o | $0.005 | $0.015 |
| gpt-3.5-turbo | $0.0005 | $0.0015 |

### Cálculo
```
costo = (prompt_tokens / 1000 * precio_input) + (completion_tokens / 1000 * precio_output)
```

### Almacenamiento
- Cada mensaje del bot guarda tokens y costo en `meta`
- Cada chat acumula `total_cost` y `total_tokens`

---

## Variables de Entorno (.env)

```env
# Autenticación
MASTER_PASSWORD=YPF2026WOOPI

# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7

# Base de datos (ya configurado)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=chat_stations
DB_USERNAME=root
DB_PASSWORD=root
```

---

## Flujo de Conversación

```
1. Usuario crea nuevo chat
   └── Sistema selecciona Agent aleatorio
   └── Se crea registro en `chats`
   └── Se incrementa `usage_count` del agent

2. Usuario envía mensaje
   └── Se guarda en `messages` (role: human)
   └── Se genera título si es primer mensaje

3. Sistema procesa mensaje
   └── Construye historial de conversación
   └── Llama a OpenAI con system_prompt del agent
   └── Calcula tokens y costo

4. Sistema guarda respuesta
   └── Se guarda en `messages` (role: bot, meta con costos)
   └── Se actualiza `total_cost` y `total_tokens` del chat

5. Frontend muestra respuesta
   └── Muestra mensaje del bot
   └── Actualiza display de costo total
```

---

## Comandos de Desarrollo

```bash
# Migraciones
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan migrate"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan migrate:fresh"

# Seeders
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan db:seed"

# Limpiar caché
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear"

# Instalar OpenAI
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && composer require openai-php/client"

# Listar rutas
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan route:list"
```

---

## Criterios de Verificación

1. **Login funciona** con contraseña `YPF2026WOOPI`
2. **Crear chat** asigna agent aleatorio
3. **Enviar mensaje** genera respuesta del bot
4. **Costos se trackean** en cada mensaje y chat
5. **ABM Agentes** permite CRUD completo
6. **UI tipo ChatGPT** con sidebar, mensajes y input
7. **Responsive** funciona en móvil

---

## Notas de Implementación

- **Todas las migraciones** van en un solo archivo
- **Todos los modelos** tienen soft deletes
- **Sin sistema de usuarios** - solo contraseña maestra
- **Frontend consume API** - Blade es solo render + JS/fetch
- **Mantener simple** - no over-engineering
