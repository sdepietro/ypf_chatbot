# CLAUDE.md

Este archivo guía a Claude Code (claude.ai/code) para trabajar en este repositorio.

## Proyecto: Ypf Chat Station

**Ypf Chat Station** es un proyecto Laravel (API + Frontend Blade) cuyo objetivo es construir el **core inicial** para que un *playero de estación de servicio* practique conversaciones con un bot.

- El bot **asume un rol** (por ejemplo: cliente apurado, cliente enojado, cliente indeciso, etc.)
- El playero (humano) debe **responder correctamente** para completar la conversación.
- Por ahora el dominio funcional es mínimo: **conversaciones (chats)** y **mensajes**.

### Alcance actual (MVP Core)

- ✅ API para crear chats, listar chats, enviar mensajes y obtener historial.
- ✅ Frontend en Blade que consume esa API y ofrece una UI similar a ChatGPT:
    - Lista de conversaciones a la izquierda
    - Mensajes del chat en el centro
    - Input abajo para enviar
- ✅ Persistencia en base de datos:
    - `chats` (sala / conversación)
    - `messages` (mensajes del humano o del bot)

### integraciónes externas

- **OpenAI (ChatGPT)** para generar respuestas del bot.

---

## Rutas del proyecto (Host / Docker)

**En tu máquina (host):**

- `/home/sergio/Develop/workspace/www/ypf/chat_station`

**Dentro del contenedor Docker:**

- `/var/www/html/ypf/chat_station`

---

## Forma correcta de trabajar con archivos

**IMPORTANTE** al editar código en este proyecto:

- **SIEMPRE editar archivos directamente** en el host usando el filesystem (Edit/Write tools).
- **NO editar archivos desde Docker** (por ejemplo, evitar `docker exec ... sed ...` para modificar código).
- **Usar Docker SOLO para**:
    - Ejecutar comandos de Laravel (artisan)
    - Instalar dependencias (composer)
    - Ejecutar tests
    - Verificar sintaxis / correr la app

**¿Por qué?**  
Los archivos del host están montados dentro del contenedor. Editar desde el host evita problemas de permisos y mantiene un workflow limpio.

**Workflow recomendado**

1. Editás en host (fuera de Docker)
2. Corrés comandos en Docker (artisan/composer/tests)
3. Si hace falta, limpiás cachés

---

## Comandos de desarrollo (Docker-first para ejecutar)

> Nota: los ejemplos asumen un contenedor `php`. Si tu servicio se llama distinto, reemplazalo.

### Laravel (artisan)

```bash
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan --version"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan route:list"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan migrate"
```

### Cache / Config

```bash
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan cache:clear"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan config:clear"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan route:clear"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan view:clear"
```

### Composer

```bash
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && composer install"
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && composer dump-autoload"
```

### Tests

```bash
docker exec -i php bash -c "cd /var/www/html/ypf/chat_station && php artisan test"
```

### Frontend
Por ahora se usaran sono boostrap y blade, no es necesario npm.

---

## Arquitectura (MVP)

### Capas

- **API (JSON)**: endpoints para chats y mensajes
- **Frontend (Blade)**: consume la API y renderiza UI “tipo ChatGPT”
- **Service Layer**: lógica de negocio para:
    - Orquestar conversación
    - Llamar a OpenAI
    - Persistir mensajes
- **Persistencia**: MySQL (o equivalente) con tablas `chats` y `messages`

### Entidades actuales

#### Chat (Sala)

- Identifica una conversación.
- Cada chat tiene un **ID único** (autoincrement o UUID; elegir uno y sostenerlo).
- Debe poder listarse y abrirse desde el sidebar del frontend.

Campos sugeridos (orientativo):

- `id`
- `title` (opcional: ej “Cliente apurado - 20/01”)
- `status` (opcional: active, finished)
- `created_at`, `updated_at`

#### Message (Mensaje)

- Pertenece a un `chat_id`.
- Puede ser del **humano** o del **bot**.
- Ordenado por tiempo de creación.

Campos sugeridos (orientativo):

- `id`
- `chat_id`
- `role` (human | bot | system)
- `content` (texto)
- `meta` (json opcional: tokens, modelo, debugging)
- `created_at`, `updated_at`

---

## Integración OpenAI (ChatGPT)

### Responsabilidad

- Dado un chat y su historial, generar la próxima respuesta del bot.

### Reglas del proyecto

- No hay pagos ni subscripciones: **mantener simple**.
- Toda integración con OpenAI debe estar encapsulada en un **servicio** (ej: `OpenAIChatService`).
- Loguear errores y respuesta fallida de forma clara (sin exponer secrets).

### Configuración sugerida (.env)

- `OPENAI_API_KEY=...`
- `OPENAI_MODEL=...` (ej: gpt-4o-mini o el que uses)
- `OPENAI_TEMPERATURE=...` (opcional)

> NO hardcodear API keys en el repo.

---

## Contratos de API (convención)

Para mantener consistencia, usar este formato estándar:

- Respuesta exitosa:
  ```json
  {
  "status": true,
  "data": {},
  "message": "OK",
  "errors": []
  }
  ```

- Respuesta con error:
  ```json
  {
  "status": false,
  "data": null,
  "message": "Validation error",
  "errors": {
  "field": ["..."]
  }
  }
  ```

---

## Endpoints mínimos esperados (MVP)

> Nombres orientativos; lo importante es que estén claros y consistentes.

### Chats

- `GET /api/chats`  
  Lista chats para el sidebar.

- `POST /api/chats`  
  Crea una nueva sala (chat) y devuelve el chat creado.

- `GET /api/chats/{chat}`  
  Devuelve info del chat (y opcionalmente metadata).

### Mensajes

- `GET /api/chats/{chat}/messages`  
  Devuelve historial de mensajes.

- `POST /api/chats/{chat}/messages`  
  Envía un mensaje del humano y genera (o encola) respuesta del bot.

Flujo típico de `POST /messages`:

1. Persistir mensaje humano
2. Construir prompt/historial
3. Llamar OpenAI
4. Persistir mensaje del bot
5. Devolver ambos (o el estado final)

---

## Frontend Blade (UI tipo ChatGPT)

### Reglas

- Sidebar izquierdo: lista de chats (ordenado por `updated_at desc`)
- Centro: mensajes del chat seleccionado
- Input abajo: enviar mensaje
- UX básica:
    - Enter para enviar, Shift+Enter para salto de línea (si lo implementás)
    - Scroll al último mensaje al enviar/recibir
    - Estado “generando respuesta” (loader)

### Convenciones

- El frontend debe consumir la API (no mezclar lógica pesada en Blade).
- Blade debe ser fino: render + llamadas JS (fetch/axios) a endpoints.

---

## Base de datos

- Base de datos: MySQL (o equivalente según tu docker-compose).
- Migraciones deben crear como mínimo:
    - `chats`
    - `messages` con FK a `chats`

### Soft deletes

- Por ahora: **no es obligatorio** usar soft deletes.
- Si se implementa, sostenerlo consistentemente en toda query del listado.

---

## Logging y errores

- Usar el logger de Laravel (`Log::info`, `Log::error`) para:
    - Errores de OpenAI
    - Timeouts o respuestas inválidas
    - Problemas al persistir mensajes
- No loguear secrets ni headers sensibles.

---

## Convenciones de código

- Controllers livianos, servicios con lógica.
- Validación en FormRequest cuando aplique.
- Nombres consistentes:
    - `Chat`, `Message`
    - `ChatController`, `MessageController`
    - `ChatService`, `OpenAIChatService`

---

## Notas importantes

- Este repo es **un core inicial**, priorizar:
    - claridad
    - simplicidad
    - facilidad de extender (roles, escenarios, scoring, etc.)
- Si algo no está explícitamente pedido para el MVP, **no inventar complejidad**.

---
