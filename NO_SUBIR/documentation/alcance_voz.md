# YPF Chat Station - Alcance: Feature de Voz

## Objetivo

Agregar la capacidad de **conversación por voz** al sistema de chat existente. El comportamiento esperado es:

- Si el humano envía **texto** → el bot responde con **texto** (comportamiento actual).
- Si el humano envía **audio** → el bot responde con **audio**.

Esto permite al playero practicar conversaciones habladas con el bot, simulando una interacción real en la estación de servicio.

---

## Componentes Técnicos Necesarios

Para implementar voz se necesitan cuatro piezas fundamentales:

### 1. STT (Speech-to-Text)
Convertir el audio del humano a texto para que el LLM (ChatGPT) pueda procesarlo.

### 2. TTS (Text-to-Speech)
Convertir la respuesta de texto del bot a audio para que el humano la escuche.

### 3. Captura de Audio en el Browser
Usar la **MediaRecorder API** del navegador para grabar audio desde el micrófono del usuario. Esto es estándar y funciona en todos los browsers modernos (Chrome, Edge, Firefox, Safari).

### 4. Lógica de Detección de Tipo de Input
El sistema debe detectar si el mensaje enviado es texto o audio, y responder en el mismo formato:

```
Humano envía texto  → Bot responde texto (flujo actual, sin cambios)
Humano envía audio  → STT → LLM → TTS → Bot responde audio
```

---

## Flujo Técnico Detallado (Modo Voz)

```
1. Humano presiona botón "Grabar" en el frontend
   └── MediaRecorder API captura audio del micrófono
   └── Se genera un blob de audio (webm/opus o wav)

2. Humano presiona "Enviar" (o suelta el botón)
   └── Audio se envía al backend (POST multipart/form-data)
   └── Se persiste el archivo de audio en storage

3. Backend procesa el audio
   └── STT convierte audio → texto
   └── Se guarda mensaje del humano (role: human, input_type: audio)
   └── Se construye historial y se llama al LLM (ChatGPT)
   └── Se recibe respuesta de texto del LLM

4. Backend genera audio de respuesta
   └── TTS convierte texto del bot → audio
   └── Se persiste archivo de audio de respuesta
   └── Se guarda mensaje del bot (role: bot, input_type: audio)

5. Frontend reproduce la respuesta
   └── Se descarga/stream el audio
   └── Se reproduce automáticamente con <audio> o Audio API
   └── Opcionalmente se muestra el texto como subtítulo
```

---

## 5 Alternativas de Implementación

### Tabla Resumen

| # | Nombre | STT | TTS | Costo Estimado | Nivel |
|---|--------|-----|-----|----------------|-------|
| A | Browser Nativo | Web Speech API | Web Speech API | $0 | POC |
| B | Todo OpenAI | Whisper API | OpenAI TTS | ~$0.02/min | Producción |
| C | OpenAI Realtime | Realtime API (todo-en-uno) | Realtime API | ~$0.06-0.30/min | Premium |
| D | Mix Económico | Deepgram Nova-3 | ElevenLabs / Deepgram Aura-2 | ~$0.01-0.04/min | Producción |
| E | Self-Hosted | Whisper local o Vosk | Kokoro-82M | Costo de GPU/server | On-premise |

---

### Alternativa A: Browser Nativo (Web Speech API)

**Descripción:** Todo se resuelve en el navegador, sin llamadas a servicios externos de voz. El browser convierte voz a texto (SpeechRecognition API) y texto a voz (SpeechSynthesis API).

**STT:** `window.SpeechRecognition` / `webkitSpeechRecognition`
**TTS:** `window.speechSynthesis`

**Pros:**
- Costo $0 (no se paga por voz)
- Implementación rápida, todo client-side
- No requiere cambios en el backend (se envía texto como siempre)
- Bueno para validar el concepto antes de invertir

**Contras:**
- STT no funciona en Firefox ni Opera (solo Chrome/Edge/Safari parcial)
- STT requiere conexión a internet en Chrome (envía audio a servers de Google)
- Calidad de las voces TTS es robótica y varía mucho entre browsers/OS
- No hay control sobre la voz del bot (suena distinto en cada dispositivo)
- No se persiste audio (todo efímero en el browser)
- Safari en PWA no soporta SpeechRecognition

**Compatibilidad de Browsers:**

| Feature | Chrome | Edge | Safari | Firefox | Opera |
|---------|--------|------|--------|---------|-------|
| STT (SpeechRecognition) | Parcial (v25+) | Si (v139+) | Parcial (v14.1+) | No | No |
| TTS (SpeechSynthesis) | Si (v33+) | Si | Si (v7+) | Si (v42+) | Si |

**Costo:** $0
**Complejidad de integración:** Baja (solo JS en frontend)
**Calidad de voz:** Baja-Media (depende del browser/OS)
**Latencia:** Baja (todo local excepto STT en Chrome)

**Recomendación:** Usar como POC para validar que el flujo funciona. No sirve para producción.

---

### Alternativa B: Todo OpenAI (Whisper + TTS)

**Descripción:** Se usa el ecosistema de OpenAI para ambas funciones: Whisper API para STT y OpenAI TTS para generar audio de la respuesta del bot. Ya usamos OpenAI para el LLM, así que se mantiene un solo proveedor.

**STT:** OpenAI Whisper API / GPT-4o Transcribe
**TTS:** OpenAI TTS-1 / TTS-1-HD / GPT-4o-mini-TTS

**Pros:**
- Un solo proveedor (OpenAI) → una sola API key, una sola factura
- Whisper tiene excelente accuracy en español
- TTS tiene 6 voces naturales (Alloy, Echo, Fable, Onyx, Nova, Shimmer)
- Múltiples formatos de salida (MP3, Opus, AAC, FLAC, WAV)
- Integración simple: ya tenemos el SDK de OpenAI en el proyecto
- Buena documentación y soporte

**Contras:**
- Costo por uso (se suma al costo del LLM)
- Requiere internet (cloud-only)
- Las voces son buenas pero no customizables (no voice cloning)
- Latencia: requiere dos llamadas extra al API (STT + TTS) además del LLM

**Pricing Detallado:**

| Servicio | Modelo | Costo |
|----------|--------|-------|
| STT | Whisper | $0.006/min |
| STT | GPT-4o Transcribe | $0.006/min |
| STT | GPT-4o Mini Transcribe | $0.003/min |
| TTS | TTS-1 (Standard) | $15/1M caracteres |
| TTS | TTS-1-HD | $30/1M caracteres |
| TTS | GPT-4o-mini-TTS | ~$0.015/min |

**Cálculo ejemplo (1 minuto de conversación):**
- Humano habla 30 seg → STT Whisper: $0.003
- Bot responde ~200 caracteres → TTS-1: $0.003
- Total voz: ~$0.006/min (+ costo LLM existente)
- Para una conversación de 5 minutos: ~$0.03 solo voz

**Complejidad de integración:** Media (agregar endpoints de upload/download audio, llamar APIs de Whisper y TTS)
**Calidad de voz:** Alta (voces naturales, buen español)
**Latencia:** Media (~1-3 seg adicionales por STT + TTS)

**Recomendación:** Mejor opción calidad/precio para producción. Mantiene todo en OpenAI.

---

### Alternativa C: OpenAI Realtime API

**Descripción:** API de OpenAI diseñada para conversaciones de voz en tiempo real. Funciona como un pipeline unificado: recibe audio, procesa con el LLM, y devuelve audio. Todo en una conexión WebSocket persistente.

**STT + LLM + TTS:** Todo integrado en la Realtime API

**Pros:**
- Latencia ultra-baja (streaming bidireccional)
- Pipeline unificado: no hay que orquestar STT → LLM → TTS manualmente
- Soporte nativo para interrupciones (el humano puede cortar al bot)
- Voice Activity Detection (VAD) incluido
- Experiencia más natural y conversacional

**Contras:**
- Significativamente más caro que la alternativa B
- Requiere WebSockets (más complejo que REST)
- Arquitectura diferente al flujo actual de mensajes
- Difícil de integrar con el modelo actual de persistencia (mensajes discretos)
- Los silencios también cuentan si se hace streaming continuo
- El system prompt se envía con cada turno (costo adicional oculto)

**Pricing Detallado (por 1M tokens):**

| Modelo | Text Input | Text Output | Audio Input | Audio Output |
|--------|-----------|-------------|-------------|--------------|
| gpt-realtime | $4.00 | $16.00 | $32.00 | $64.00 |
| gpt-4o-mini-realtime | $0.60 | $2.40 | $10.00 | $20.00 |

**Costo por minuto (estimado):**
- Audio input: ~$0.06/min (gpt-realtime) o ~$0.02/min (mini)
- Audio output: ~$0.24/min (gpt-realtime) o ~$0.08/min (mini)
- Total gpt-realtime: ~$0.30/min
- Total gpt-4o-mini-realtime: ~$0.10/min
- Para una conversación de 5 minutos (mini): ~$0.50

**Complejidad de integración:** Alta (WebSockets, cambio de arquitectura, manejo de streaming)
**Calidad de voz:** Muy alta (la mejor de OpenAI)
**Latencia:** Muy baja (streaming en tiempo real)

**Recomendación:** Overkill para este proyecto. El costo es 10-50x mayor que la alternativa B, y la experiencia conversacional en tiempo real no es necesaria para un entrenamiento de playeros donde los turnos son discretos.

---

### Alternativa D: Mix Económico (Deepgram + ElevenLabs/Deepgram Aura)

**Descripción:** Combinar proveedores especializados en voz: Deepgram para STT y ElevenLabs o Deepgram Aura-2 para TTS. Esto permite elegir el mejor de cada categoría.

**STT:** Deepgram Nova-3
**TTS:** ElevenLabs (Starter) o Deepgram Aura-2

**Pros:**
- Deepgram Nova-3 es competitivo en accuracy y más barato que Whisper
- ElevenLabs tiene las voces más naturales del mercado
- Deepgram Aura-2 es muy económico y con buena calidad enterprise
- $200 en créditos gratis de Deepgram para empezar
- Facturación por segundo en Deepgram (más justo para audios cortos)

**Contras:**
- Múltiples proveedores: más API keys, más puntos de fallo, más complejidad
- ElevenLabs tiene pricing por plan ($5/mes Starter = 60K caracteres)
- Si el volumen crece, hay que gestionar múltiples contratos/facturación
- Menos documentación en español que OpenAI

**Pricing Detallado:**

| Servicio | Proveedor | Costo |
|----------|-----------|-------|
| STT | Deepgram Nova-3 (Pay-as-you-go) | $0.0077/min |
| STT | Deepgram Nova-3 (Growth) | $0.0065/min |
| TTS | ElevenLabs Starter | $5/mes (60K caracteres) |
| TTS | Deepgram Aura-2 | $0.030/1K caracteres |

**Opción D1: Deepgram STT + ElevenLabs TTS**
- STT: $0.0077/min
- TTS: $5/mes fijo (cubre ~60K caracteres ≈ ~60 minutos de audio)
- Bueno para volumen bajo-medio con voces premium

**Opción D2: Deepgram STT + Deepgram Aura-2 TTS**
- STT: $0.0077/min
- TTS: $0.030/1K caracteres (~$0.03/min de audio)
- Un solo proveedor (Deepgram), pricing puro pay-as-you-go
- $200 gratis para empezar

**Complejidad de integración:** Media-Alta (integrar SDKs de 1-2 proveedores nuevos)
**Calidad de voz:** Alta (ElevenLabs) o Media-Alta (Deepgram Aura-2)
**Latencia:** Baja-Media (Deepgram STT es muy rápido: ~150ms TTFB)

**Recomendación:** Buena opción si se quiere maximizar calidad de voz (ElevenLabs) o minimizar costos (todo Deepgram). Agrega complejidad por usar proveedores adicionales.

---

### Alternativa E: Self-Hosted (Whisper local + Vosk / Kokoro)

**Descripción:** Ejecutar los modelos de STT y TTS en infraestructura propia (servidor con GPU o CPU potente). No hay costos por API, pero sí por infraestructura.

**STT:** Whisper (self-hosted) o Vosk
**TTS:** Kokoro-82M (open source, Apache 2.0)

**Pros:**
- Sin costos de API (una vez montada la infra)
- Privacidad total: el audio nunca sale del servidor
- Sin límites de uso ni rate limits
- Whisper self-hosted tiene excelente accuracy
- Kokoro-82M logra calidad comparable a modelos 5-15x más grandes
- Kokoro corre en CPU (no necesita GPU obligatoriamente)
- Vosk funciona 100% offline, soporta 20+ idiomas, modelos de ~50MB

**Contras:**
- Requiere infraestructura adicional (GPU recomendada para buen rendimiento)
- Setup y mantenimiento más complejo
- Whisper self-hosted necesita GPU para velocidad aceptable
- Vosk tiene menor accuracy que Whisper
- Kokoro tiene 26 voces pero limitadas en español (foco en inglés)
- Hay que gestionar actualizaciones de modelos manualmente
- No escala tan fácil como una API cloud

**Modelos recomendados:**

| Componente | Modelo | Tamaño | Requisitos |
|------------|--------|--------|------------|
| STT | Whisper large-v3 | ~3GB | GPU (VRAM 6GB+) |
| STT | Whisper medium | ~1.5GB | GPU o CPU potente |
| STT | Vosk (es-small) | ~50MB | CPU (Raspberry Pi+) |
| TTS | Kokoro-82M | ~82M params | CPU o GPU |

**Kokoro-82M en detalle:**
- 82M parámetros (muy liviano)
- ~210x real-time en GPU (RTX 4090), ~90x en RTX 3090 Ti
- Funciona en CPU sin problemas
- API compatible con OpenAI TTS (drop-in replacement)
- Docker image disponible: `ghcr.io/eduardolat/kokoro-web:latest`
- Licencia Apache 2.0 (uso comercial libre)

**Vosk en detalle:**
- 20+ idiomas soportados (incluye español)
- Streaming API para transcripción en tiempo real
- Modelos portables de ~50MB
- Bindings para Python, Java, Node.js, C#, Go
- Funciona en Raspberry Pi, Android, iOS

**Costo estimado infraestructura:**
- GPU cloud (ej: T4 en GCP): ~$0.35/hora (~$250/mes 24/7)
- Solo CPU (Kokoro + Vosk): servidor existente podría alcanzar
- Break-even vs API: ~500+ horas de transcripción/mes

**Complejidad de integración:** Alta (setup de modelos, Docker, gestión de GPU)
**Calidad de voz:** Media-Alta (Whisper STT excelente, Kokoro TTS bueno en inglés, regular en español)
**Latencia:** Variable (depende del hardware)

**Recomendación:** Solo justificable si hay requerimientos de privacidad estrictos o volumen muy alto. Para este proyecto MVP, es overkill.

---

## Tabla Comparativa Final

| Criterio | A: Browser | B: OpenAI | C: Realtime | D: Mix | E: Self-Hosted |
|----------|-----------|-----------|-------------|--------|----------------|
| **Costo mensual (100 conv/mes, 3 min c/u)** | $0 | ~$2 | ~$30-90 | ~$5-10 | $0-250 (infra) |
| **Calidad STT** | Baja-Media | Alta | Muy Alta | Alta | Alta (Whisper) / Media (Vosk) |
| **Calidad TTS** | Baja | Alta | Muy Alta | Muy Alta (ElevenLabs) / Alta (Aura) | Media-Alta |
| **Latencia adicional** | ~0ms | ~1-3s | ~0.2-0.5s | ~1-2s | ~1-5s (depende HW) |
| **Complejidad** | Baja | Media | Alta | Media-Alta | Alta |
| **Dependencia terceros** | Ninguna | OpenAI | OpenAI | 1-2 proveedores | Ninguna |
| **Funciona offline** | Parcial (TTS si) | No | No | No | Si |
| **Soporte español** | Depende browser | Excelente | Excelente | Bueno | Variable |
| **Escalabilidad** | N/A (client) | Alta | Alta | Alta | Limitada por HW |
| **Setup inicial** | Minutos | Horas | Dias | Horas | Dias |

---

## Cambios Necesarios en el Sistema Actual

### Base de Datos

#### Tabla `messages` - Nuevos campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| input_type | enum(`text`, `audio`) | Tipo de input del mensaje (default: `text`) |
| audio_path | varchar(500) nullable | Path al archivo de audio en storage |
| audio_duration_ms | int nullable | Duración del audio en milisegundos |

```sql
ALTER TABLE messages
  ADD COLUMN input_type ENUM('text', 'audio') DEFAULT 'text' AFTER role,
  ADD COLUMN audio_path VARCHAR(500) NULL AFTER content,
  ADD COLUMN audio_duration_ms INT NULL AFTER audio_path;
```

#### Campo `meta` - Nuevos datos (mensajes con audio)

```json
{
    "model": "gpt-4o-mini",
    "prompt_tokens": 150,
    "completion_tokens": 80,
    "total_tokens": 230,
    "cost_usd": 0.000345,
    "response_time_ms": 1250,
    "stt_model": "whisper-1",
    "stt_duration_ms": 3200,
    "stt_cost_usd": 0.000032,
    "tts_model": "tts-1",
    "tts_characters": 180,
    "tts_cost_usd": 0.0027,
    "voice_total_cost_usd": 0.005732
}
```

### Storage

- Crear directorio `storage/app/audio/chats/{chat_id}/`
- Guardar archivos de audio con naming: `{message_id}_{role}.{ext}` (ej: `42_human.webm`, `43_bot.mp3`)
- Considerar limpieza periódica de audios antiguos

### API - Nuevos Endpoints / Modificaciones

#### Enviar mensaje con audio

```
POST /api/chats/{chat}/messages
Content-Type: multipart/form-data

Campos:
  - audio: file (webm/wav/mp3)  ← nuevo
  - content: string             ← existente (texto, opcional si hay audio)
```

#### Obtener audio de un mensaje

```
GET /api/chats/{chat}/messages/{message}/audio
Response: audio file (stream)
```

#### Respuesta de mensaje (modificada)

```json
{
    "status": true,
    "data": {
        "human_message": {
            "id": 42,
            "role": "human",
            "input_type": "audio",
            "content": "Hola, quiero cargar nafta super",
            "audio_url": "/api/chats/5/messages/42/audio",
            "audio_duration_ms": 3200
        },
        "bot_message": {
            "id": 43,
            "role": "bot",
            "input_type": "audio",
            "content": "Buen dia! Cuantos litros de super quiere?",
            "audio_url": "/api/chats/5/messages/43/audio",
            "audio_duration_ms": 2800
        }
    },
    "message": "OK",
    "errors": []
}
```

### Backend - Nuevo Servicio

```
app/Services/VoiceService.php
```

Responsabilidades:
- `transcribe(UploadedFile $audio): string` → STT
- `synthesize(string $text): string` → TTS, retorna path del audio generado
- Encapsular el proveedor elegido (facilitar cambio futuro)

### Frontend - Cambios en UI

```
┌─────────────────────────────────────────────┐
│ Chat con Cliente Apurado                    │
├─────────────────────────────────────────────┤
│                                             │
│  [Humano] 🎤 Audio (3.2s)  [▶ Play]        │
│           "Hola, quiero cargar nafta super" │
│                                             │
│  [Bot]    🔊 Audio (2.8s)  [▶ Play]        │
│           "Buen dia! Cuantos litros..."     │
│                                             │
├─────────────────────────────────────────────┤
│  [  Escribir mensaje...  ] [🎤] [Enviar]   │
└─────────────────────────────────────────────┘
```

Cambios necesarios:
- Botón de micrófono (🎤) junto al input de texto
- Lógica de grabación con MediaRecorder API
- Indicador visual de "grabando..." (animación)
- Reproductor de audio inline para mensajes de voz
- Mostrar transcripción como subtítulo debajo del audio
- Estado "generando respuesta de voz..." (loader)

---

## Approach Incremental Recomendado

### Fase 1: POC con Browser Nativo (Alternativa A)

**Objetivo:** Validar el concepto sin costo ni cambios en backend.

- Agregar botón de micrófono en el frontend
- Usar `webkitSpeechRecognition` para convertir voz → texto
- Enviar texto al API existente (sin cambios en backend)
- Usar `speechSynthesis` para leer la respuesta del bot en voz alta
- Sin persistencia de audio

**Resultado esperado:** El playero puede hablar y escuchar, pero todo es efímero y la calidad es limitada. Sirve para validar si el feature tiene valor.

### Fase 2: Producción con OpenAI (Alternativa B)

**Objetivo:** Feature completo con calidad de producción.

- Migración de base de datos (nuevos campos en `messages`)
- Implementar `VoiceService` con Whisper + OpenAI TTS
- Crear endpoint de upload de audio
- Crear endpoint de descarga de audio
- Actualizar frontend con MediaRecorder + reproductor
- Persistir archivos de audio
- Tracking de costos de voz en `meta`

**Resultado esperado:** Feature de voz completo, con audio persistido, buena calidad, y costos controlados (~$2/mes para uso moderado).

### Fase 3: Optimización (Opcional, si el volumen lo justifica)

- Evaluar migrar a Deepgram (D) si los costos de OpenAI crecen
- Evaluar Realtime API (C) si se necesita experiencia conversacional fluida
- Evaluar self-hosted (E) si hay requisitos de privacidad

---

## Decisiones Pendientes

1. **Formato de audio del browser:** ¿webm/opus (más liviano) o wav (más compatible)?
2. **Duración máxima de audio:** ¿Limitar a 30 seg? ¿60 seg?
3. **Auto-play de respuestas:** ¿Reproducir automáticamente el audio del bot o esperar click?
4. **Modo de grabación:** ¿Push-to-talk (mantener presionado) o toggle (click para iniciar/parar)?
5. **Fallback:** Si STT falla, ¿mostrar error o pedir que repita?

---

## Referencias de Pricing

> Precios verificados a enero 2026. Consultar las páginas oficiales para valores actualizados.

- [OpenAI Pricing](https://platform.openai.com/docs/pricing)
- [Deepgram Pricing](https://deepgram.com/pricing)
- [ElevenLabs Pricing](https://elevenlabs.io/pricing)
- [Kokoro-82M (Hugging Face)](https://huggingface.co/hexgrad/Kokoro-82M)
- [Vosk (GitHub)](https://github.com/alphacep/vosk-api)
- [Web Speech API (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API)
- [MediaRecorder API (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder)
