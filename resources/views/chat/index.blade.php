@extends('layouts.master')

@section('title', 'Chat - YPF Chat Station')

@section('breadcrumb')
<li class="breadcrumb-item active">Chat</li>
@endsection

@push('css')
<style>
    :root {
        --ypf-blue: #0033a0;
        --ypf-red: #e30613;
        --ypf-yellow: #ffd100;
    }

    /* Layout principal del chat */
    .chat-wrapper {
        display: flex;
        height: calc(100vh - 180px);
        margin: -1rem -1.5rem;
        border-radius: 0.5rem;
        overflow: hidden;
        background: var(--cui-body-bg);
        border: 1px solid var(--cui-border-color);
    }

    /* Sidebar (columna izquierda) */
    .chat-sidebar {
        width: 280px;
        min-width: 280px;
        background: var(--cui-tertiary-bg);
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--cui-border-color);
    }

    .sidebar-header {
        padding: 1rem;
        border-bottom: 1px solid var(--cui-border-color);
        flex-shrink: 0;
    }

    .new-chat-btn {
        width: 100%;
        background: var(--ypf-blue);
        border: none;
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .new-chat-btn:hover {
        background: #002680;
    }

    /* Lista de chats */
    .chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
    }

    .chat-list-empty {
        text-align: center;
        color: var(--cui-secondary-color);
        padding: 2rem 1rem;
        font-size: 0.875rem;
    }

    .chat-item {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        background: transparent;
        border: 1px solid transparent;
        color: var(--cui-body-color);
    }

    .chat-item:hover {
        background: var(--cui-secondary-bg);
    }

    .chat-item.active {
        background: var(--ypf-blue);
        color: white;
    }

    .chat-item-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        min-width: 0;
    }

    .chat-item-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        background: var(--cui-secondary-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .chat-item.active .chat-item-icon {
        background: rgba(255,255,255,0.2);
    }

    .chat-item-title {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .chat-item-delete {
        opacity: 0;
        color: var(--cui-secondary-color);
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .chat-item:hover .chat-item-delete {
        opacity: 1;
    }

    .chat-item.active .chat-item-delete {
        color: rgba(255,255,255,0.7);
    }

    .chat-item-delete:hover {
        color: var(--cui-danger) !important;
    }

    /* Area principal del chat */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--cui-body-bg);
        overflow: hidden;
    }

    /* Header del chat */
    .chat-header {
        padding: 1rem 1.5rem;
        background: var(--cui-tertiary-bg);
        border-bottom: 1px solid var(--cui-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .chat-header-info h2 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--cui-body-color);
    }

    .chat-header-agent {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.25rem;
    }

    .chat-header-agent .badge {
        background: var(--cui-success);
        font-weight: 500;
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
    }

    .chat-header-agent .badge-model {
        background: var(--cui-info);
    }

    /* Model info bajo los mensajes del bot */
    .message-meta {
        font-size: 0.6875rem;
        color: var(--cui-secondary-color);
        margin-top: 0.375rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .message-meta .model-badge {
        background: var(--cui-secondary-bg);
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-family: monospace;
    }

    .chat-stats {
        display: flex;
        gap: 1rem;
    }

    .chat-stat {
        text-align: center;
        padding: 0.5rem 1rem;
        background: var(--cui-secondary-bg);
        border-radius: 0.5rem;
    }

    .chat-stat-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--ypf-blue);
    }

    .chat-stat-label {
        font-size: 0.6875rem;
        color: var(--cui-secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Contenedor de mensajes */
    .messages-container {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
    }

    /* Mensajes */
    .message {
        margin-bottom: 1.25rem;
        display: flex;
        gap: 0.875rem;
        max-width: 85%;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message-human {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
    }

    .message-human .message-avatar {
        background: var(--cui-primary);
        color: white;
    }

    .message-bot .message-avatar {
        background: var(--ypf-blue);
        color: white;
    }

    .message-bubble {
        padding: 0.875rem 1.125rem;
        border-radius: 1rem;
        line-height: 1.6;
        font-size: 0.9375rem;
    }

    .message-human .message-bubble {
        background: var(--cui-primary);
        color: white;
        border-bottom-right-radius: 0.25rem;
    }

    .message-bot .message-bubble {
        background: var(--cui-tertiary-bg);
        color: var(--cui-body-color);
        border-bottom-left-radius: 0.25rem;
        border: 1px solid var(--cui-border-color);
    }

    .message-bubble p {
        margin: 0;
    }

    /* Empty state */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        color: var(--cui-secondary-color);
        text-align: center;
        padding: 2.5rem;
    }

    .empty-state-icon {
        width: 6rem;
        height: 6rem;
        border-radius: 50%;
        background: var(--cui-secondary-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .empty-state-icon i {
        font-size: 2.5rem;
        color: var(--ypf-blue);
    }

    .empty-state h3 {
        color: var(--cui-body-color);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--cui-secondary-color);
        max-width: 300px;
    }

    /* Typing indicator */
    .typing-indicator {
        display: flex;
        gap: 5px;
        padding: 0.25rem 0;
    }

    .typing-indicator span {
        width: 10px;
        height: 10px;
        background: var(--cui-secondary-color);
        border-radius: 50%;
        animation: bounce 1.4s infinite ease-in-out;
    }

    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* Input container */
    .input-container {
        padding: 1rem 1.5rem;
        background: var(--cui-tertiary-bg);
        border-top: 1px solid var(--cui-border-color);
        flex-shrink: 0;
    }

    .input-wrapper {
        display: flex;
        gap: 0.75rem;
        max-width: 900px;
        margin: 0 auto;
        background: var(--cui-body-bg);
        border-radius: 1rem;
        padding: 0.5rem;
        border: 2px solid var(--cui-border-color);
        transition: all 0.2s ease;
    }

    .input-wrapper:focus-within {
        border-color: var(--ypf-blue);
    }

    .message-input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.75rem 1rem;
        resize: none;
        min-height: 1.5rem;
        max-height: 200px;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: var(--cui-body-color);
    }

    .message-input:focus {
        outline: none;
    }

    .message-input::placeholder {
        color: var(--cui-secondary-color);
    }

    .send-btn {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: var(--ypf-blue);
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .send-btn:hover:not(:disabled) {
        background: #002680;
    }

    .send-btn:disabled {
        background: var(--cui-secondary-bg);
        color: var(--cui-secondary-color);
        cursor: not-allowed;
    }

    /* Voice button */
    .voice-btn {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: var(--cui-secondary-bg);
        border: none;
        color: var(--cui-body-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
        position: relative;
    }

    .voice-btn:hover:not(:disabled) {
        background: var(--cui-tertiary-bg);
        color: var(--ypf-blue);
    }

    .voice-btn.recording {
        background: var(--cui-danger);
        color: white;
        animation: pulse-recording 1.5s ease-in-out infinite;
        transform: scale(1.1);
    }

    .voice-btn.recording:hover {
        background: #c0392b;
    }

    .voice-btn.recording::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        border-radius: 0.75rem;
        border: 2px solid var(--cui-danger);
        transform: translate(-50%, -50%);
        animation: recording-ring 1s ease-out infinite;
    }

    @keyframes pulse-recording {
        0%, 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); }
        50% { box-shadow: 0 0 0 12px rgba(231, 76, 60, 0); }
    }

    @keyframes recording-ring {
        0% { width: 100%; height: 100%; opacity: 1; }
        100% { width: 150%; height: 150%; opacity: 0; }
    }

    /* Cursor para indicar que es push-to-talk */
    .voice-btn:not(:disabled) {
        cursor: grab;
    }

    .voice-btn:not(:disabled):active {
        cursor: grabbing;
    }

    .voice-btn:disabled {
        background: var(--cui-secondary-bg);
        color: var(--cui-secondary-color);
        cursor: not-allowed;
        animation: none;
    }

    /* Voice status indicator */
    .voice-status {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
        color: var(--cui-secondary-color);
        background: var(--cui-tertiary-bg);
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .voice-status.active {
        display: flex;
    }

    .voice-status.recording {
        color: var(--cui-danger);
    }

    /* Audio waveform animation (WhatsApp style) */
    .audio-waveform {
        display: flex;
        align-items: center;
        gap: 3px;
        height: 24px;
        padding: 0 8px;
    }

    .audio-waveform .bar {
        width: 3px;
        background: var(--cui-danger);
        border-radius: 2px;
        animation: waveform 1s ease-in-out infinite;
    }

    .audio-waveform .bar:nth-child(1) { height: 8px; animation-delay: 0s; }
    .audio-waveform .bar:nth-child(2) { height: 16px; animation-delay: 0.1s; }
    .audio-waveform .bar:nth-child(3) { height: 12px; animation-delay: 0.2s; }
    .audio-waveform .bar:nth-child(4) { height: 20px; animation-delay: 0.3s; }
    .audio-waveform .bar:nth-child(5) { height: 14px; animation-delay: 0.4s; }
    .audio-waveform .bar:nth-child(6) { height: 18px; animation-delay: 0.5s; }
    .audio-waveform .bar:nth-child(7) { height: 10px; animation-delay: 0.6s; }
    .audio-waveform .bar:nth-child(8) { height: 22px; animation-delay: 0.7s; }
    .audio-waveform .bar:nth-child(9) { height: 14px; animation-delay: 0.8s; }
    .audio-waveform .bar:nth-child(10) { height: 8px; animation-delay: 0.9s; }

    @keyframes waveform {
        0%, 100% { transform: scaleY(0.5); opacity: 0.7; }
        50% { transform: scaleY(1); opacity: 1; }
    }

    /* Recording timer */
    .recording-timer {
        font-family: monospace;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--cui-danger);
        min-width: 36px;
    }

    /* TTS Debug button */
    .tts-debug-btn {
        background: transparent;
        border: 1px solid var(--cui-border-color);
        color: var(--cui-secondary-color);
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .tts-debug-btn:hover {
        background: var(--cui-secondary-bg);
        color: var(--cui-body-color);
    }

    /* TTS Debug logs */
    .tts-debug-logs {
        max-height: 400px;
        overflow-y: auto;
        font-family: monospace;
        font-size: 0.8125rem;
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 1rem;
        border-radius: 0.5rem;
    }

    .tts-log-entry {
        padding: 0.25rem 0;
        border-bottom: 1px solid #333;
    }

    .tts-log-entry:last-child {
        border-bottom: none;
    }

    .tts-log-time {
        color: #888;
        margin-right: 0.5rem;
    }

    .tts-log-info { color: #4fc3f7; }
    .tts-log-success { color: #81c784; }
    .tts-log-warning { color: #ffb74d; }
    .tts-log-error { color: #e57373; }

    .voice-status.speaking {
        color: var(--cui-success);
    }

    .voice-status i {
        font-size: 0.875rem;
    }

    /* TTS toggle */
    .tts-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        color: var(--cui-secondary-color);
        cursor: pointer;
        user-select: none;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .tts-toggle:hover {
        background: var(--cui-secondary-bg);
    }

    .tts-toggle input {
        display: none;
    }

    .tts-toggle .toggle-icon {
        font-size: 1rem;
        color: var(--cui-secondary-color);
    }

    .tts-toggle.active .toggle-icon {
        color: var(--cui-success);
    }

    /* Voice not supported warning */
    .voice-warning {
        background: var(--cui-warning-bg-subtle);
        color: var(--cui-warning);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        margin-bottom: 0.5rem;
        display: none;
    }

    .voice-warning.show {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Loading spinner */
    .loading-spinner {
        width: 2.5rem;
        height: 2.5rem;
        border: 3px solid var(--cui-border-color);
        border-top-color: var(--ypf-blue);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 992px) {
        .chat-stats {
            display: none;
        }
        .chat-sidebar {
            width: 240px;
            min-width: 240px;
        }
    }

    @media (max-width: 768px) {
        .chat-wrapper {
            flex-direction: column;
            height: calc(100vh - 160px);
        }
        .chat-sidebar {
            width: 100%;
            min-width: 100%;
            max-height: 200px;
        }
    }
</style>
@endpush

@section('content')
<div class="chat-wrapper">
    {{-- Sidebar --}}
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <button class="new-chat-btn" onclick="createNewChat()">
                <i class="fas fa-plus"></i>
                <span>Nueva Conversacion</span>
            </button>
        </div>
        <div class="chat-list" id="chatList">
            <div class="chat-list-empty">
                <i class="fas fa-spinner fa-spin mb-2"></i>
                <div>Cargando conversaciones...</div>
            </div>
        </div>
    </div>

    {{-- Main Chat --}}
    <div class="chat-main">
        <div class="chat-header" id="chatHeader" style="display: none;">
            <div class="chat-header-info">
                <h2 id="chatTitle">Conversacion</h2>
                <div class="chat-header-agent">
                    <span class="badge" id="chatAgentBadge">
                        <i class="fas fa-robot me-1"></i>
                        <span id="chatAgent">-</span>
                    </span>
                    <span class="badge badge-model" id="chatModelBadge" style="display: none;">
                        <i class="fas fa-microchip me-1"></i>
                        <span id="chatModel">-</span>
                    </span>
                </div>
            </div>
            <div class="chat-stats">
                <div class="chat-stat">
                    <div class="chat-stat-value" id="chatTokens">0</div>
                    <div class="chat-stat-label">Tokens</div>
                </div>
                <div class="chat-stat">
                    <div class="chat-stat-value">$<span id="chatCost">0.00</span></div>
                    <div class="chat-stat-label">Costo</div>
                </div>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            <div class="empty-state" id="emptyState">
                <div class="empty-state-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Bienvenido a YPF Chat Station</h3>
                <p>Selecciona una conversacion existente o crea una nueva para comenzar a practicar</p>
            </div>
        </div>

        <div class="input-container" id="inputContainer" style="display: none;">
            {{-- Voice warnings and status --}}
            <div class="voice-warning" id="voiceWarning">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="voiceWarningText">Tu navegador no soporta reconocimiento de voz</span>
            </div>
            <div class="voice-status" id="voiceStatus">
                <i class="fas fa-microphone"></i>
                <span class="recording-timer" id="recordingTimer" style="display: none;">0:00</span>
                <div class="audio-waveform" id="audioWaveform" style="display: none;">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <span id="voiceStatusText">Escuchando...</span>
            </div>

            <div class="input-wrapper">
                <textarea class="message-input" id="messageInput"
                          placeholder="Escribe tu mensaje aqui..."
                          rows="1"></textarea>
                <!-- Hidden input para almacenar texto de voz (push-to-talk) -->
                <input type="hidden" id="voiceTranscript" value="">
                <button class="voice-btn" id="voiceBtn" title="Mantener presionado para hablar">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>

            {{-- TTS Toggle --}}
            <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem; gap: 1rem;">
                <button class="tts-debug-btn" id="ttsDebugBtn" onclick="openTTSDebugModal()" title="Ver logs de TTS">
                    <i class="fas fa-bug"></i>
                </button>
                <label class="tts-toggle active" id="ttsToggle" title="Activar/desactivar lectura automatica de respuestas">
                    <input type="checkbox" id="ttsEnabled" checked>
                    <i class="fas fa-volume-up toggle-icon"></i>
                    <span>Leer respuestas</span>
                </label>
            </div>
        </div>
    </div>
</div>

{{-- TTS Debug Modal --}}
<div class="modal fade" id="ttsDebugModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bug me-2"></i>TTS Debug Log</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Estado actual:</strong>
                    <div id="ttsDebugStatus" class="mt-2 p-2 bg-light rounded">
                        <div><strong>TTS Soportado:</strong> <span id="debugTtsSupported">-</span></div>
                        <div><strong>TTS Habilitado:</strong> <span id="debugTtsEnabled">-</span></div>
                        <div><strong>Voces disponibles:</strong> <span id="debugVoicesCount">-</span></div>
                        <div><strong>Voz seleccionada:</strong> <span id="debugSelectedVoice">-</span></div>
                        <div><strong>speechSynthesis.speaking:</strong> <span id="debugIsSpeaking">-</span></div>
                        <div><strong>speechSynthesis.pending:</strong> <span id="debugIsPending">-</span></div>
                        <div><strong>speechSynthesis.paused:</strong> <span id="debugIsPaused">-</span></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Logs:</strong>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="testTTSFromModal()">
                            <i class="fas fa-play me-1"></i>Test TTS
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearTTSLogs()">
                            <i class="fas fa-trash me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
                <div id="ttsDebugLogs" class="tts-debug-logs"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentChatId = null;
let isLoading = false;
let isSwitchingChat = false;

document.addEventListener('DOMContentLoaded', function() {
    loadChats();
    initVoice(); // Inicializar funcionalidades de voz

    const messageInput = document.getElementById('messageInput');

    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        stopSpeaking(); // Detener TTS si el usuario escribe
    });

    // Stop TTS when user focuses on input
    messageInput.addEventListener('focus', stopSpeaking);
});

async function loadChats() {
    try {
        const response = await apiFetch('/api/chats');
        const data = await response.json();

        if (data.status) {
            renderChatList(data.data);
        }
    } catch (error) {
        console.error('Error loading chats:', error);
        document.getElementById('chatList').innerHTML = `
            <div class="chat-list-empty">
                <i class="fas fa-exclamation-triangle text-warning mb-2"></i>
                <div>Error al cargar conversaciones</div>
            </div>
        `;
    }
}

function renderChatList(chats) {
    const chatList = document.getElementById('chatList');

    if (chats.length === 0) {
        chatList.innerHTML = `
            <div class="chat-list-empty">
                <i class="fas fa-inbox mb-2" style="font-size: 24px;"></i>
                <div>No hay conversaciones</div>
                <div style="font-size: 12px; margin-top: 4px;">Crea una nueva para comenzar</div>
            </div>
        `;
        return;
    }

    chatList.innerHTML = '';

    chats.forEach(chat => {
        const div = document.createElement('div');
        div.className = 'chat-item' + (chat.id === currentChatId ? ' active' : '');
        div.setAttribute('data-chat-id', chat.id);
        div.innerHTML = `
            <div class="chat-item-content">
                <div class="chat-item-icon">
                    <i class="fas fa-message"></i>
                </div>
                <span class="chat-item-title">${escapeHtml(chat.title || 'Sin titulo')}</span>
            </div>
            <i class="fas fa-trash chat-item-delete" onclick="deleteChat(event, ${chat.id})" title="Eliminar"></i>
        `;
        div.addEventListener('click', function(e) {
            if (!e.target.classList.contains('chat-item-delete')) {
                selectChat(chat.id);
            }
        });
        chatList.appendChild(div);
    });
}

async function createNewChat() {
    try {
        const btn = document.querySelector('.new-chat-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';

        const response = await apiFetch('/api/chats', { method: 'POST' });
        const data = await response.json();

        if (data.status) {
            await loadChats();
            await selectChat(data.data.id);
        } else {
            alert(data.message || 'Error al crear la conversacion');
        }
    } catch (error) {
        console.error('Error creating chat:', error);
        alert('Error al crear la conversacion');
    } finally {
        const btn = document.querySelector('.new-chat-btn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i><span>Nueva Conversacion</span>';
    }
}

async function selectChat(chatId) {
    if (chatId === currentChatId || isSwitchingChat) return;

    isSwitchingChat = true;
    clearChatUI();
    currentChatId = chatId;

    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
        if (parseInt(item.getAttribute('data-chat-id')) === chatId) {
            item.classList.add('active');
        }
    });

    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('inputContainer').style.display = 'block';

    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.innerHTML = `
        <div class="empty-state">
            <div class="loading-spinner"></div>
            <p style="margin-top: 16px;">Cargando mensajes...</p>
        </div>
    `;

    document.getElementById('chatTitle').textContent = 'Cargando...';
    document.getElementById('chatAgent').textContent = '-';
    document.getElementById('chatModel').textContent = '-';
    document.getElementById('chatModelBadge').style.display = 'none';
    document.getElementById('chatTokens').textContent = '0';
    document.getElementById('chatCost').textContent = '0.00';

    try {
        const [chatResponse, messagesResponse] = await Promise.all([
            apiFetch(`/api/chats/${chatId}`),
            apiFetch(`/api/chats/${chatId}/messages`)
        ]);

        const chatData = await chatResponse.json();
        const messagesData = await messagesResponse.json();

        if (currentChatId !== chatId) {
            isSwitchingChat = false;
            return;
        }

        if (chatData.status) {
            document.getElementById('chatTitle').textContent = chatData.data.title || 'Conversacion';
            document.getElementById('chatAgent').textContent = chatData.data.agent?.name || 'Bot';
            document.getElementById('chatTokens').textContent = chatData.data.total_tokens || 0;
            document.getElementById('chatCost').textContent = parseFloat(chatData.data.total_cost || 0).toFixed(4);
        }

        if (messagesData.status) {
            renderMessages(messagesData.data);
        }

        document.getElementById('messageInput').focus();

    } catch (error) {
        console.error('Error loading chat:', error);
        messagesContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon" style="background: var(--cui-danger-bg-subtle);">
                    <i class="fas fa-exclamation-triangle" style="color: var(--cui-danger);"></i>
                </div>
                <h3>Error al cargar</h3>
                <p>No se pudieron cargar los mensajes.</p>
            </div>
        `;
    } finally {
        isSwitchingChat = false;
    }
}

function clearChatUI() {
    document.getElementById('messagesContainer').innerHTML = '';
    const messageInput = document.getElementById('messageInput');
    messageInput.value = '';
    messageInput.style.height = 'auto';
    document.getElementById('chatTitle').textContent = '-';
    document.getElementById('chatAgent').textContent = '-';
    document.getElementById('chatModel').textContent = '-';
    document.getElementById('chatModelBadge').style.display = 'none';
    document.getElementById('chatTokens').textContent = '0';
    document.getElementById('chatCost').textContent = '0.00';
}

function renderMessages(messages) {
    const container = document.getElementById('messagesContainer');
    container.innerHTML = '';

    if (!messages || messages.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h3>Comienza la conversacion</h3>
                <p>Escribe un mensaje para iniciar el entrenamiento con el agente</p>
            </div>
        `;
        return;
    }

    let lastModel = null;
    let lastProvider = null;

    messages.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'message message-' + msg.role;

        let metaHtml = '';
        if (msg.role === 'bot' && msg.model) {
            metaHtml = `
                <div class="message-meta">
                    <span class="model-badge">${escapeHtml(msg.provider || '')}/${escapeHtml(msg.model)}</span>
                </div>
            `;
            lastModel = msg.model;
            lastProvider = msg.provider;
        }

        div.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-${msg.role === 'human' ? 'user' : 'robot'}"></i>
            </div>
            <div class="message-bubble">
                <p>${escapeHtml(msg.content)}</p>
                ${metaHtml}
            </div>
        `;
        container.appendChild(div);
    });

    // Update header with last used model
    if (lastModel) {
        document.getElementById('chatModel').textContent = lastModel;
        document.getElementById('chatModelBadge').style.display = 'inline-flex';
    }

    requestAnimationFrame(() => {
        container.scrollTop = container.scrollHeight;
    });
}

async function sendMessage() {
    if (isLoading || !currentChatId) return;

    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    if (!content) return;

    isLoading = true;
    document.getElementById('sendBtn').disabled = true;
    input.value = '';
    input.style.height = 'auto';

    const container = document.getElementById('messagesContainer');
    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    const humanDiv = document.createElement('div');
    humanDiv.className = 'message message-human';
    humanDiv.innerHTML = `
        <div class="message-avatar"><i class="fas fa-user"></i></div>
        <div class="message-bubble"><p>${escapeHtml(content)}</p></div>
    `;
    container.appendChild(humanDiv);

    const typingDiv = document.createElement('div');
    typingDiv.className = 'message message-bot';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <div class="message-avatar"><i class="fas fa-robot"></i></div>
        <div class="message-bubble">
            <div class="typing-indicator"><span></span><span></span><span></span></div>
        </div>
    `;
    container.appendChild(typingDiv);
    container.scrollTop = container.scrollHeight;

    try {
        const response = await apiFetch(`/api/chats/${currentChatId}/messages`, {
            method: 'POST',
            body: JSON.stringify({ content })
        });

        const data = await response.json();
        document.getElementById('typingIndicator')?.remove();

        if (data.status) {
            const botMsg = data.data.bot_message;
            const usage = data.data.usage;

            let metaHtml = '';
            if (usage && usage.model) {
                metaHtml = `
                    <div class="message-meta">
                        <span class="model-badge">${escapeHtml(usage.provider || '')}/${escapeHtml(usage.model)}</span>
                    </div>
                `;
                // Update header model badge
                document.getElementById('chatModel').textContent = usage.model;
                document.getElementById('chatModelBadge').style.display = 'inline-flex';
            }

            const botDiv = document.createElement('div');
            botDiv.className = 'message message-bot';
            botDiv.innerHTML = `
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="message-bubble">
                    <p>${escapeHtml(botMsg.content)}</p>
                    ${metaHtml}
                </div>
            `;
            container.appendChild(botDiv);
            container.scrollTop = container.scrollHeight;

            // TTS: Leer respuesta del bot en voz alta si está habilitado
            speakText(botMsg.content);

            if (usage) {
                const currentTokens = parseInt(document.getElementById('chatTokens').textContent) || 0;
                const currentCost = parseFloat(document.getElementById('chatCost').textContent) || 0;
                document.getElementById('chatTokens').textContent = currentTokens + (usage.total_tokens || 0);
                document.getElementById('chatCost').textContent = (currentCost + (usage.cost || 0)).toFixed(4);
            }

            loadChats();
        } else {
            alert(data.message || 'Error al enviar el mensaje');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        document.getElementById('typingIndicator')?.remove();
        alert('Error al enviar el mensaje.');
    } finally {
        isLoading = false;
        document.getElementById('sendBtn').disabled = false;
        document.getElementById('messageInput').focus();
    }
}

async function deleteChat(event, chatId) {
    event.stopPropagation();
    if (!confirm('Eliminar esta conversacion?')) return;

    try {
        const response = await apiFetch(`/api/chats/${chatId}`, { method: 'DELETE' });
        const data = await response.json();

        if (data.status) {
            if (currentChatId === chatId) {
                currentChatId = null;
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('inputContainer').style.display = 'none';
                document.getElementById('messagesContainer').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-comments"></i></div>
                        <h3>Bienvenido a YPF Chat Station</h3>
                        <p>Selecciona una conversacion existente o crea una nueva para comenzar a practicar</p>
                    </div>
                `;
            }
            loadChats();
        } else {
            alert(data.message || 'Error al eliminar');
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
        alert('Error al eliminar la conversacion');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

// ===========================================
// VOZ - Opción A: Browser Nativo (Web Speech API)
// ===========================================

let recognition = null;
let isRecording = false;
let voiceSupported = false;
let ttsSupported = false;
let recordingTimerInterval = null;
let recordingSeconds = 0;

// Initialize Voice Features
function initVoice() {
    // Check STT support (SpeechRecognition)
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognition) {
        voiceSupported = true;
        recognition = new SpeechRecognition();
        recognition.lang = 'es-AR'; // Español Argentina
        recognition.continuous = true; // Mantener escuchando mientras el boton esta presionado
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;

        recognition.onstart = function() {
            isRecording = true;
            updateVoiceUI('recording');
            // Limpiar el transcript hidden al iniciar
            document.getElementById('voiceTranscript').value = '';
            console.log('Voice recognition started (push-to-talk mode)');
        };

        recognition.onresult = function(event) {
            let finalTranscript = '';
            let interimTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            // Guardar en el input hidden (NO en el visible)
            const hiddenInput = document.getElementById('voiceTranscript');
            const currentText = hiddenInput.value;

            if (finalTranscript) {
                // Acumular texto final
                hiddenInput.value = currentText + finalTranscript;
                console.log('Final transcript accumulated:', hiddenInput.value);
            }

            // Mantener el indicador de grabando activo
            if (interimTranscript || finalTranscript) {
                updateVoiceStatus('Grabando...');
            }
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            isRecording = false;
            updateVoiceUI('idle');

            let errorMsg = 'Error de reconocimiento de voz';
            switch(event.error) {
                case 'no-speech':
                    errorMsg = 'No se detecto voz. Intenta de nuevo.';
                    break;
                case 'audio-capture':
                    errorMsg = 'No se pudo acceder al microfono.';
                    break;
                case 'not-allowed':
                    errorMsg = 'Permiso de microfono denegado.';
                    break;
                case 'network':
                    errorMsg = 'Error de red. Se requiere conexion a internet.';
                    break;
                case 'aborted':
                    // Esto es normal cuando se detiene manualmente
                    return;
            }
            showVoiceWarning(errorMsg);
        };

        recognition.onend = function() {
            // Solo procesar si estamos en modo recording (evita doble-envio)
            if (!isRecording) return;

            isRecording = false;
            updateVoiceUI('idle');
            console.log('Voice recognition ended');

            // Enviar el mensaje desde el hidden input
            sendVoiceMessage();
        };

        // Configurar eventos push-to-talk en el boton
        setupPushToTalk();

    } else {
        voiceSupported = false;
        const voiceBtn = document.getElementById('voiceBtn');
        if (voiceBtn) {
            voiceBtn.disabled = true;
            voiceBtn.title = 'Tu navegador no soporta reconocimiento de voz';
        }
        console.warn('Speech Recognition not supported in this browser');
    }

    // Check TTS support (SpeechSynthesis)
    if ('speechSynthesis' in window) {
        ttsSupported = true;
        ttsLog('TTS soportado: Si', 'success');

        // Load voices (they may load asynchronously)
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = loadVoices;
        }
        loadVoices();
    } else {
        ttsSupported = false;
        ttsLog('TTS soportado: No - navegador no compatible', 'error');
        const ttsToggle = document.getElementById('ttsToggle');
        if (ttsToggle) {
            ttsToggle.style.display = 'none';
        }
    }

    // TTS toggle event
    const ttsCheckbox = document.getElementById('ttsEnabled');
    if (ttsCheckbox) {
        ttsCheckbox.addEventListener('change', function() {
            const toggle = document.getElementById('ttsToggle');
            if (this.checked) {
                toggle.classList.add('active');
                // Stop any ongoing speech when disabling
            } else {
                toggle.classList.remove('active');
                if (speechSynthesis.speaking) {
                    speechSynthesis.cancel();
                }
            }
        });
    }
}

let availableVoices = [];

function loadVoices() {
    availableVoices = speechSynthesis.getVoices();
    ttsLog(`Voces cargadas: ${availableVoices.length} disponibles`, 'info');

    // Log Spanish voices
    const spanishVoices = availableVoices.filter(v => v.lang.startsWith('es'));
    if (spanishVoices.length > 0) {
        ttsLog(`Voces en espanol: ${spanishVoices.map(v => v.name).join(', ')}`, 'info');
    } else {
        ttsLog('No se encontraron voces en espanol', 'warning');
    }
}

function getSpanishVoice() {
    // Prefer Spanish voices
    const spanishVoice = availableVoices.find(v =>
        v.lang.startsWith('es') && (v.lang.includes('AR') || v.lang.includes('ES') || v.lang.includes('MX'))
    );
    if (spanishVoice) return spanishVoice;

    // Fallback to any Spanish voice
    const anySpanish = availableVoices.find(v => v.lang.startsWith('es'));
    if (anySpanish) return anySpanish;

    // Return default
    return null;
}

// Configurar eventos push-to-talk (mantener presionado para grabar)
function setupPushToTalk() {
    const voiceBtn = document.getElementById('voiceBtn');
    if (!voiceBtn) return;

    // Prevenir el menu contextual en el boton (para long-press en mobile)
    voiceBtn.addEventListener('contextmenu', (e) => e.preventDefault());

    // Mouse events (desktop)
    voiceBtn.addEventListener('mousedown', startVoiceRecording);
    voiceBtn.addEventListener('mouseup', stopVoiceRecording);
    voiceBtn.addEventListener('mouseleave', stopVoiceRecording); // Por si el usuario suelta fuera del boton

    // Touch events (mobile)
    voiceBtn.addEventListener('touchstart', (e) => {
        e.preventDefault(); // Prevenir comportamiento por defecto del touch
        startVoiceRecording(e);
    });
    voiceBtn.addEventListener('touchend', (e) => {
        e.preventDefault();
        stopVoiceRecording(e);
    });
    voiceBtn.addEventListener('touchcancel', stopVoiceRecording);
}

async function startVoiceRecording(e) {
    if (!voiceSupported) {
        showVoiceWarning('Tu navegador no soporta reconocimiento de voz. Usa Chrome o Edge.');
        return;
    }

    if (!currentChatId) {
        showVoiceWarning('Selecciona o crea una conversacion primero.');
        return;
    }

    if (isLoading || isRecording) {
        return;
    }

    try {
        // Solicitar permiso de microfono
        await navigator.mediaDevices.getUserMedia({ audio: true });

        // Limpiar transcript previo
        document.getElementById('voiceTranscript').value = '';

        // Iniciar reconocimiento
        recognition.start();
        console.log('Push-to-talk: recording started');
    } catch (error) {
        console.error('Error starting recognition:', error);
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            showVoiceWarning('Permiso de microfono denegado. Habilita el microfono en la configuracion del navegador.');
        } else if (error.name === 'NotFoundError') {
            showVoiceWarning('No se encontro un microfono. Conecta uno e intenta de nuevo.');
        } else {
            showVoiceWarning('Error al iniciar el microfono: ' + error.message);
        }
    }
}

function stopVoiceRecording(e) {
    if (!isRecording) return;

    console.log('Push-to-talk: stopping recording');
    recognition.stop();
}

// Enviar mensaje capturado por voz
function sendVoiceMessage() {
    const hiddenInput = document.getElementById('voiceTranscript');
    const content = hiddenInput.value.trim();

    if (!content) {
        console.log('No voice content to send');
        return;
    }

    if (!currentChatId || isLoading) {
        console.log('Cannot send: no chat selected or loading');
        return;
    }

    console.log('Sending voice message:', content);

    // Copiar al input visible temporalmente para que sendMessage() lo use
    const messageInput = document.getElementById('messageInput');
    messageInput.value = content;

    // Limpiar hidden input
    hiddenInput.value = '';

    // Enviar mensaje
    sendMessage();
}

function updateVoiceUI(state) {
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    const voiceStatusText = document.getElementById('voiceStatusText');

    const waveform = document.getElementById('audioWaveform');
    const timer = document.getElementById('recordingTimer');

    switch(state) {
        case 'recording':
            voiceBtn.classList.add('recording');
            voiceBtn.innerHTML = '<i class="fas fa-microphone-alt"></i>';
            voiceBtn.title = 'Suelta para enviar';
            voiceStatus.classList.add('active', 'recording');
            voiceStatus.classList.remove('speaking');
            voiceStatus.querySelector('i').style.display = 'none';
            timer.style.display = 'inline';
            waveform.style.display = 'flex';
            voiceStatusText.textContent = 'Grabando...';
            startRecordingTimer();
            break;
        case 'speaking':
            stopRecordingTimer();
            voiceStatus.classList.add('active', 'speaking');
            voiceStatus.classList.remove('recording');
            voiceStatus.querySelector('i').className = 'fas fa-volume-up';
            voiceStatus.querySelector('i').style.display = 'inline';
            timer.style.display = 'none';
            waveform.style.display = 'none';
            voiceStatusText.textContent = 'Hablando...';
            break;
        case 'idle':
        default:
            stopRecordingTimer();
            voiceBtn.classList.remove('recording');
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            voiceBtn.title = 'Mantener presionado para grabar';
            voiceStatus.classList.remove('active', 'recording', 'speaking');
            voiceStatus.querySelector('i').className = 'fas fa-microphone';
            voiceStatus.querySelector('i').style.display = 'inline';
            timer.style.display = 'none';
            waveform.style.display = 'none';
            break;
    }
}

function updateVoiceStatus(text) {
    const voiceStatusText = document.getElementById('voiceStatusText');
    if (voiceStatusText) {
        voiceStatusText.textContent = text;
    }
}

function showVoiceWarning(message) {
    const warning = document.getElementById('voiceWarning');
    const warningText = document.getElementById('voiceWarningText');
    if (warning && warningText) {
        warningText.textContent = message;
        warning.classList.add('show');
        setTimeout(() => {
            warning.classList.remove('show');
        }, 4000);
    }
}

function startRecordingTimer() {
    recordingSeconds = 0;
    updateTimerDisplay();
    recordingTimerInterval = setInterval(() => {
        recordingSeconds++;
        updateTimerDisplay();
    }, 1000);
}

function stopRecordingTimer() {
    if (recordingTimerInterval) {
        clearInterval(recordingTimerInterval);
        recordingTimerInterval = null;
    }
    recordingSeconds = 0;
}

function updateTimerDisplay() {
    const timer = document.getElementById('recordingTimer');
    if (timer) {
        const minutes = Math.floor(recordingSeconds / 60);
        const seconds = recordingSeconds % 60;
        timer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
}

let ttsRetryCount = 0;
const TTS_MAX_RETRIES = 3;
let pendingTTSText = null;

function speakText(text) {
    ttsLog(`speakText() llamado con texto de ${text?.length || 0} caracteres`, 'info');

    if (!ttsSupported) {
        ttsLog('TTS no soportado, abortando', 'error');
        return;
    }

    const ttsEnabled = document.getElementById('ttsEnabled');
    if (!ttsEnabled || !ttsEnabled.checked) {
        ttsLog('TTS deshabilitado por usuario, abortando', 'warning');
        return;
    }

    // Guardar texto pendiente y resetear contador de reintentos
    pendingTTSText = text;
    ttsRetryCount = 0;

    // Cancel any ongoing speech
    if (speechSynthesis.speaking || speechSynthesis.pending) {
        ttsLog('Cancelando speech anterior...', 'warning');
        speechSynthesis.cancel();
    }

    // Delay para evitar interferencia con scroll/focus del mensaje nuevo
    ttsLog('Esperando 500ms antes de hablar (evitar interferencia UI)...', 'info');
    setTimeout(() => doSpeak(text), 500);
}

function doSpeak(text) {
    ttsLog(`Preparando utterance para: "${text.substring(0, 50)}${text.length > 50 ? '...' : ''}"`, 'info');

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'es-AR';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;
    utterance.volume = 1.0;

    // Try to use a Spanish voice
    const voice = getSpanishVoice();
    if (voice) {
        utterance.voice = voice;
        ttsLog(`Usando voz: ${voice.name} (${voice.lang})`, 'info');
    } else {
        ttsLog('No se encontro voz en espanol, usando default', 'warning');
    }

    utterance.onstart = function() {
        ttsLog('EVENT: onstart - Comenzo a reproducir', 'success');
        ttsRetryCount = 0; // Reset en éxito
        pendingTTSText = null;
        updateVoiceUI('speaking');
    };

    utterance.onend = function() {
        ttsLog('EVENT: onend - Termino de reproducir', 'success');
        updateVoiceUI('idle');
    };

    utterance.onerror = function(event) {
        ttsLog(`EVENT: onerror - Error: ${event.error}`, 'error');
        updateVoiceUI('idle');

        // Reintentar si fue "interrupted" y no superamos el máximo
        if (event.error === 'interrupted' && ttsRetryCount < TTS_MAX_RETRIES && pendingTTSText) {
            ttsRetryCount++;
            ttsLog(`Reintentando TTS (intento ${ttsRetryCount}/${TTS_MAX_RETRIES}) en 300ms...`, 'warning');
            setTimeout(() => {
                if (pendingTTSText) {
                    doSpeak(pendingTTSText);
                }
            }, 300);
        } else if (ttsRetryCount >= TTS_MAX_RETRIES) {
            ttsLog('Maximo de reintentos alcanzado, abortando TTS', 'error');
            pendingTTSText = null;
        }
    };

    utterance.onpause = () => ttsLog('EVENT: onpause', 'warning');
    utterance.onresume = () => ttsLog('EVENT: onresume', 'info');

    ttsLog('Llamando speechSynthesis.speak()...', 'info');
    speechSynthesis.speak(utterance);

    // Verificar estado inmediatamente
    setTimeout(() => {
        ttsLog(`Estado post-speak: speaking=${speechSynthesis.speaking}, pending=${speechSynthesis.pending}, paused=${speechSynthesis.paused}`, 'info');

        // Workaround para Chrome: a veces se queda "pending" sin empezar
        if (speechSynthesis.pending && !speechSynthesis.speaking) {
            ttsLog('Detectado estado pending sin speaking, intentando resume()...', 'warning');
            speechSynthesis.resume();
        }
    }, 200);
}

// Stop TTS when user starts typing or recording
function stopSpeaking() {
    if (ttsSupported && speechSynthesis.speaking) {
        speechSynthesis.cancel();
        updateVoiceUI('idle');
    }
}

// ===========================================
// TTS DEBUG MODAL
// ===========================================

let ttsLogs = [];

function ttsLog(message, type = 'info') {
    const now = new Date();
    const time = now.toLocaleTimeString('es-AR', { hour12: false }) + '.' + now.getMilliseconds().toString().padStart(3, '0');
    const entry = { time, message, type };
    ttsLogs.push(entry);

    // Keep only last 100 logs
    if (ttsLogs.length > 100) {
        ttsLogs.shift();
    }

    // Also log to console
    console.log(`[TTS ${type.toUpperCase()}] ${message}`);

    // Update modal if open
    updateTTSDebugLogs();
}

function updateTTSDebugLogs() {
    const logsContainer = document.getElementById('ttsDebugLogs');
    if (!logsContainer) return;

    logsContainer.innerHTML = ttsLogs.map(log => `
        <div class="tts-log-entry">
            <span class="tts-log-time">${log.time}</span>
            <span class="tts-log-${log.type}">${log.message}</span>
        </div>
    `).join('');

    // Auto-scroll to bottom
    logsContainer.scrollTop = logsContainer.scrollHeight;
}

function updateTTSDebugStatus() {
    document.getElementById('debugTtsSupported').textContent = ttsSupported ? 'Si' : 'No';
    document.getElementById('debugTtsEnabled').textContent = document.getElementById('ttsEnabled')?.checked ? 'Si' : 'No';
    document.getElementById('debugVoicesCount').textContent = availableVoices.length;

    const voice = getSpanishVoice();
    document.getElementById('debugSelectedVoice').textContent = voice ? `${voice.name} (${voice.lang})` : 'Ninguna';

    if (ttsSupported) {
        document.getElementById('debugIsSpeaking').textContent = speechSynthesis.speaking ? 'Si' : 'No';
        document.getElementById('debugIsPending').textContent = speechSynthesis.pending ? 'Si' : 'No';
        document.getElementById('debugIsPaused').textContent = speechSynthesis.paused ? 'Si' : 'No';
    }
}

function openTTSDebugModal() {
    updateTTSDebugStatus();
    updateTTSDebugLogs();

    const modal = new coreui.Modal(document.getElementById('ttsDebugModal'));
    modal.show();

    // Update status every second while modal is open
    const statusInterval = setInterval(updateTTSDebugStatus, 1000);

    document.getElementById('ttsDebugModal').addEventListener('hidden.coreui.modal', () => {
        clearInterval(statusInterval);
    }, { once: true });
}

function clearTTSLogs() {
    ttsLogs = [];
    updateTTSDebugLogs();
}

function testTTSFromModal() {
    ttsLog('Iniciando test manual de TTS...', 'info');

    if (!ttsSupported) {
        ttsLog('ERROR: TTS no soportado en este navegador', 'error');
        return;
    }

    // Cancel any ongoing speech
    if (speechSynthesis.speaking) {
        ttsLog('Cancelando speech anterior...', 'warning');
        speechSynthesis.cancel();
    }

    const testText = 'Hola, esto es una prueba de texto a voz.';
    ttsLog(`Texto a reproducir: "${testText}"`, 'info');

    const utterance = new SpeechSynthesisUtterance(testText);
    utterance.lang = 'es-AR';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;
    utterance.volume = 1.0;

    const voice = getSpanishVoice();
    if (voice) {
        utterance.voice = voice;
        ttsLog(`Usando voz: ${voice.name} (${voice.lang})`, 'info');
    } else {
        ttsLog('No se encontro voz en espanol, usando default', 'warning');
    }

    utterance.onstart = () => {
        ttsLog('EVENT: onstart - Comenzo a hablar', 'success');
        updateTTSDebugStatus();
    };

    utterance.onend = () => {
        ttsLog('EVENT: onend - Termino de hablar', 'success');
        updateTTSDebugStatus();
    };

    utterance.onerror = (event) => {
        ttsLog(`EVENT: onerror - Error: ${event.error}`, 'error');
        updateTTSDebugStatus();
    };

    utterance.onpause = () => ttsLog('EVENT: onpause - Pausado', 'warning');
    utterance.onresume = () => ttsLog('EVENT: onresume - Resumido', 'info');

    ttsLog('Llamando speechSynthesis.speak()...', 'info');
    speechSynthesis.speak(utterance);
    ttsLog(`Estado despues de speak(): speaking=${speechSynthesis.speaking}, pending=${speechSynthesis.pending}`, 'info');
}

// Test function - run from console: testTTS()
function testTTS() {
    testTTSFromModal();
}

</script>
@endpush
