@extends('layouts.master')

@section('title', 'Chat - YPF Chat Station')

@push('css')
<style>
    :root {
        --ypf-blue: #0033a0;
        --ypf-red: #e30613;
        --ypf-yellow: #ffd100;
    }

    /* === FULL-HEIGHT: ocultar header, footer, breadcrumb del master === */
    .header { display: none !important; }
    .footer { display: none !important; }
    .body { padding: 0 !important; }
    .body > .container-lg { max-width: 100% !important; padding: 0 !important; }
    .wrapper { min-height: 100vh !important; }

    /* Layout principal del chat - ocupa toda la ventana */
    .chat-wrapper {
        display: flex;
        height: 100vh;
        margin: 0;
        border-radius: 0;
        overflow: hidden;
        background: var(--cui-body-bg);
        border: none;
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

    /* Header del chat - 1 sola fila compacta */
    .chat-header {
        padding: 0.4rem 1rem;
        background: var(--cui-tertiary-bg);
        border-bottom: 1px solid var(--cui-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .chat-header-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
        flex: 1;
    }

    .chat-header-info h2 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--cui-body-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-header-agent .badge {
        background: var(--cui-success);
        font-weight: 500;
        padding: 0.15rem 0.4rem;
        font-size: 0.65rem;
    }

    .chat-header-agent .badge-model {
        background: var(--cui-info);
    }

    .chat-header-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
    }

    /* Total cost inline badge */
    .chat-cost-badge {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        background: var(--cui-secondary-bg);
        border-radius: 0.375rem;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .chat-cost-badge:hover {
        border-color: var(--ypf-blue);
    }

    .chat-cost-badge .cost-value {
        font-weight: 600;
        color: var(--ypf-blue);
    }

    .chat-cost-badge .cost-label {
        color: var(--cui-secondary-color);
    }

    /* Stats popover/dropdown */
    .stats-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--cui-body-bg);
        border: 1px solid var(--cui-border-color);
        border-radius: 0.5rem;
        padding: 0.75rem;
        min-width: 220px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
    }

    .stats-dropdown.show {
        display: block;
    }

    .stats-row {
        display: flex;
        justify-content: space-between;
        padding: 0.25rem 0;
        font-size: 0.75rem;
    }

    .stats-row-label {
        color: var(--cui-secondary-color);
    }

    .stats-row-value {
        font-weight: 600;
        color: var(--ypf-blue);
    }

    .stats-row-divider {
        border-top: 1px solid var(--cui-border-color);
        margin: 0.35rem 0;
    }

    /* Header icon buttons */
    .header-icon-btn {
        background: var(--cui-secondary-bg);
        border: 1px solid var(--cui-border-color);
        color: var(--cui-body-color);
        padding: 0.3rem 0.5rem;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.2s ease;
    }

    .header-icon-btn:hover {
        background: var(--cui-tertiary-bg);
        border-color: var(--ypf-blue);
    }

    /* Settings dropdown */
    .chat-settings-wrapper {
        position: relative;
    }

    .chat-settings-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--cui-body-bg);
        border: 1px solid var(--cui-border-color);
        border-radius: 0.5rem;
        padding: 0.75rem;
        min-width: 260px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: none;
    }

    .chat-settings-dropdown.show {
        display: block;
    }

    .settings-group {
        margin-bottom: 0.75rem;
    }

    .settings-group:last-child {
        margin-bottom: 0;
    }

    .settings-group-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--cui-secondary-color);
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .settings-group select {
        width: 100%;
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
        border-radius: 0.375rem;
        border: 1px solid var(--cui-border-color);
        background: var(--cui-tertiary-bg);
        color: var(--cui-body-color);
        cursor: pointer;
    }

    .settings-group select:focus {
        outline: none;
        border-color: var(--ypf-blue);
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

    .message-system {
        align-self: center;
        max-width: 90%;
    }

    .message-system .message-bubble {
        background: transparent;
        color: var(--cui-secondary-color);
        font-style: italic;
        text-align: center;
        border: 1px dashed var(--cui-border-color);
        border-radius: 0.75rem;
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
        .chat-sidebar {
            width: 240px;
            min-width: 240px;
        }
    }

    @media (max-width: 768px) {
        .chat-wrapper {
            flex-direction: column;
            height: 100vh;
        }

        .chat-sidebar {
            width: 100%;
            min-width: 100%;
            max-height: 180px;
        }

        .chat-header {
            padding: 0.35rem 0.75rem;
        }
    }

    /* ============================
       Evaluation Badge in Sidebar
       ============================ */
    .chat-item-badge {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.6rem;
        margin-right: 0.25rem;
    }

    .chat-item-badge.passed {
        background: var(--cui-success);
        color: white;
    }

    .chat-item-badge.failed {
        background: var(--cui-danger);
        color: white;
    }

    /* ============================
       Evaluation Modal
       ============================ */
    .eval-score-header {
        text-align: center;
        padding: 1.5rem 1rem;
    }

    .eval-score-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem;
        font-weight: 700;
        border: 4px solid;
    }

    .eval-score-circle.passed {
        border-color: var(--cui-success);
        color: var(--cui-success);
        background: rgba(25, 135, 84, 0.1);
    }

    .eval-score-circle.failed {
        border-color: var(--cui-danger);
        color: var(--cui-danger);
        background: rgba(220, 53, 69, 0.1);
    }

    .eval-score-label {
        font-size: 0.875rem;
        color: var(--cui-secondary-color);
    }

    .eval-progress-bar {
        height: 8px;
        border-radius: 4px;
        background: var(--cui-secondary-bg);
        margin: 0.75rem 0 1.5rem;
        overflow: hidden;
    }

    .eval-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .eval-progress-fill.passed {
        background: var(--cui-success);
    }

    .eval-progress-fill.failed {
        background: var(--cui-danger);
    }

    .eval-criteria-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .eval-criterion {
        padding: 0.75rem;
        border-bottom: 1px solid var(--cui-border-color);
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .eval-criterion:last-child {
        border-bottom: none;
    }

    .eval-criterion:hover {
        background: var(--cui-secondary-bg);
    }

    .eval-criterion-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .eval-criterion-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.7rem;
    }

    .eval-criterion-icon.passed {
        background: var(--cui-success);
        color: white;
    }

    .eval-criterion-icon.failed {
        background: var(--cui-danger);
        color: white;
    }

    .eval-criterion-name {
        flex: 1;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .eval-criterion-score {
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.15rem 0.5rem;
        border-radius: 0.25rem;
        background: var(--cui-secondary-bg);
    }

    .eval-criterion-justification {
        display: none;
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        color: var(--cui-secondary-color);
        background: var(--cui-secondary-bg);
        border-radius: 0.375rem;
        margin-left: 2rem;
    }

    .eval-criterion.expanded .eval-criterion-justification {
        display: block;
    }

    .eval-overall-feedback {
        padding: 1rem;
        background: var(--cui-secondary-bg);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: var(--cui-body-color);
        margin-top: 1rem;
    }

    /* Finished panel (replaces input) */
    .finished-panel-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        max-width: 900px;
        margin: 0 auto;
        padding: 0.5rem 0;
    }

    .finished-panel-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--cui-secondary-color);
        font-size: 0.875rem;
    }

    .finished-panel-info i {
        color: var(--ypf-blue);
    }

    .finished-panel-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Evaluating indicator */
    .evaluating-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem;
        color: var(--ypf-blue);
        font-weight: 500;
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

            <div class="chat-header-actions">
                {{-- Cost summary badge + stats popover --}}
                <div class="chat-settings-wrapper">
                    <div class="chat-cost-badge" onclick="toggleStatsDropdown()" title="Ver costos detallados">
                        <span class="cost-label"><i class="fas fa-coins"></i></span>
                        <span class="cost-value">U$<span id="chatTotalUsd">0.00</span></span>
                    </div>
                    <div class="stats-dropdown" id="statsDropdown">
                        <div class="stats-row">
                            <span class="stats-row-label"><i class="fas fa-microchip me-1"></i>Tokens</span>
                            <span class="stats-row-value" id="chatTokens">0</span>
                        </div>
                        <div class="stats-row-divider"></div>
                        <div class="stats-row">
                            <span class="stats-row-label">LLM</span>
                            <span class="stats-row-value">$<span id="chatLlmCost">0.00</span></span>
                        </div>
                        <div class="stats-row">
                            <span class="stats-row-label">TTS</span>
                            <span class="stats-row-value">$<span id="chatTtsCost">0.00</span></span>
                        </div>
                        <div class="stats-row">
                            <span class="stats-row-label">STT</span>
                            <span class="stats-row-value">$<span id="chatSttCost">0.00</span></span>
                        </div>
                        <div class="stats-row" id="chatEvalStat" style="display: none;">
                            <span class="stats-row-label">Eval</span>
                            <span class="stats-row-value">$<span id="chatEvalCost">0.00</span></span>
                        </div>
                        <div class="stats-row-divider"></div>
                        <div class="stats-row">
                            <span class="stats-row-label"><strong>Total USD</strong></span>
                            <span class="stats-row-value"><strong>U$<span id="chatTotalUsdDetail">0.00</span></strong></span>
                        </div>
                        <div class="stats-row">
                            <span class="stats-row-label"><strong>Total ARS</strong></span>
                            <span class="stats-row-value" style="color: var(--cui-success);"><strong>$<span id="chatTotalArs">0</span></strong></span>
                        </div>
                    </div>
                </div>

                {{-- Voice settings --}}
                <div class="chat-settings-wrapper">
                    <button class="header-icon-btn" onclick="toggleSettingsDropdown()" title="Configuracion de voz">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                    <div class="chat-settings-dropdown" id="settingsDropdown">
                        <div class="settings-group">
                            <div class="settings-group-label">
                                <i class="fas fa-microphone me-1"></i>Speech-to-Text
                            </div>
                            <select id="sttProvider" onchange="onSttProviderChange()">
                                <option value="native">Navegador (Nativo)</option>
                                <option value="openai">OpenAI Whisper</option>
                            </select>
                        </div>
                        <div class="settings-group">
                            <div class="settings-group-label">
                                <i class="fas fa-volume-up me-1"></i>Text-to-Speech
                            </div>
                            <select id="ttsProvider" onchange="onTtsProviderChange()">
                                <option value="native">Navegador (Nativo)</option>
                                <option value="openai">OpenAI TTS</option>
                            </select>
                        </div>
                        <div class="settings-group" id="ttsVoiceSelector" style="display: none;">
                            <div class="settings-group-label">Voz OpenAI</div>
                            <select id="ttsVoice" onchange="onTtsVoiceChange()">
                                <option value="alloy">Alloy</option>
                                <option value="echo">Echo</option>
                                <option value="fable">Fable</option>
                                <option value="onyx">Onyx</option>
                                <option value="nova">Nova</option>
                                <option value="shimmer">Shimmer</option>
                            </select>
                        </div>
                    </div>
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
                <label class="tts-toggle active" id="ttsToggle" title="Activar/desactivar lectura automatica de respuestas">
                    <input type="checkbox" id="ttsEnabled" checked>
                    <i class="fas fa-volume-up toggle-icon"></i>
                    <span>Leer respuestas</span>
                </label>
            </div>
        </div>

        {{-- Finished chat panel (replaces input when conversation ends) --}}
        <div class="input-container" id="finishedPanel" style="display: none;">
            <div class="finished-panel-content">
                <div class="finished-panel-info">
                    <i class="fas fa-flag-checkered"></i>
                    <span>Conversacion finalizada</span>
                </div>
                <div class="finished-panel-actions">
                    <button class="btn btn-sm btn-outline-primary" id="finishedEvalBtn" onclick="handleFinishedEvalAction()">
                        <i class="fas fa-clipboard-check me-1"></i>
                        <span id="finishedEvalBtnText">Ver Evaluacion</span>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="createNewChat()">
                        <i class="fas fa-plus me-1"></i>Nueva Conversacion
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Evaluation Modal --}}
<div class="modal fade" id="evaluationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Evaluacion de la Conversacion</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="eval-score-header">
                    <div class="eval-score-circle" id="evalScoreCircle">
                        <span id="evalScoreValue">0</span>
                    </div>
                    <div class="eval-score-label" id="evalScoreLabel">Puntaje General</div>
                    <div class="eval-progress-bar">
                        <div class="eval-progress-fill" id="evalProgressFill" style="width: 0%"></div>
                    </div>
                    <div id="evalUsageInfo" style="display: none; font-size: 0.75rem; color: var(--cui-secondary-color); display: flex; justify-content: center; gap: 1rem; margin-top: 0.25rem;">
                        <span><i class="fas fa-microchip me-1"></i><span id="evalModelName">-</span></span>
                        <span><i class="fas fa-coins me-1"></i>Tokens: <span id="evalTokens">0</span></span>
                        <span><i class="fas fa-dollar-sign me-1"></i>Costo: $<span id="evalCost">0.00</span></span>
                    </div>
                </div>

                <ul class="eval-criteria-list" id="evalCriteriaList">
                </ul>

                <div class="eval-overall-feedback" id="evalOverallFeedback" style="display: none;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="createNewChat()" data-coreui-dismiss="modal">
                    <i class="fas fa-plus me-1"></i>Nueva Conversacion
                </button>
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
const USD_TO_ARS = 2000;

function updateTotalCost() {
    const llm = parseFloat(document.getElementById('chatLlmCost').textContent) || 0;
    const tts = parseFloat(document.getElementById('chatTtsCost').textContent) || 0;
    const stt = parseFloat(document.getElementById('chatSttCost').textContent) || 0;
    const eval_ = parseFloat(document.getElementById('chatEvalCost').textContent) || 0;
    const totalUsd = llm + tts + stt + eval_;
    document.getElementById('chatTotalUsd').textContent = totalUsd.toFixed(4);
    const detailEl = document.getElementById('chatTotalUsdDetail');
    if (detailEl) detailEl.textContent = totalUsd.toFixed(4);
    document.getElementById('chatTotalArs').textContent = (totalUsd * USD_TO_ARS).toFixed(2);
}

function toggleStatsDropdown() {
    document.getElementById('statsDropdown')?.classList.toggle('show');
}

document.addEventListener('DOMContentLoaded', function() {
    loadChats();
    initVoice(); // Inicializar funcionalidades de voz
    initSpeechProviders(); // Inicializar selectores de proveedores STT/TTS

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
        stopSpeakingUnified(); // Detener TTS si el usuario escribe
    });

    // Stop TTS when user focuses on input
    messageInput.addEventListener('focus', stopSpeakingUnified);
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

        let badgeHtml = '';
        if (chat.evaluation) {
            if (chat.evaluation.passed) {
                badgeHtml = `<div class="chat-item-badge passed" title="Aprobado: ${chat.evaluation.overall_score}%"><i class="fas fa-check"></i></div>`;
            } else {
                badgeHtml = `<div class="chat-item-badge failed" title="No aprobado: ${chat.evaluation.overall_score}%"><i class="fas fa-times"></i></div>`;
            }
        }

        div.innerHTML = `
            <div class="chat-item-content">
                <div class="chat-item-icon">
                    <i class="fas fa-message"></i>
                </div>
                ${badgeHtml}
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
    document.getElementById('chatLlmCost').textContent = '0.00';
    document.getElementById('chatTtsCost').textContent = '0.00';
    document.getElementById('chatSttCost').textContent = '0.00';

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
            document.getElementById('chatLlmCost').textContent = parseFloat(chatData.data.total_llm_cost || 0).toFixed(4);
            document.getElementById('chatTtsCost').textContent = parseFloat(chatData.data.total_tts_cost || 0).toFixed(4);
            document.getElementById('chatSttCost').textContent = parseFloat(chatData.data.total_stt_cost || 0).toFixed(4);

            // Eval cost from evaluation relation
            if (chatData.data.evaluation && chatData.data.evaluation.cost) {
                document.getElementById('chatEvalCost').textContent = parseFloat(chatData.data.evaluation.cost).toFixed(4);
                document.getElementById('chatEvalStat').style.display = '';
            } else {
                document.getElementById('chatEvalCost').textContent = '0.00';
                document.getElementById('chatEvalStat').style.display = 'none';
            }

            updateTotalCost();

            // Handle finished chats
            if (chatData.data.status === 'finished') {
                showFinishedPanel(!!chatData.data.evaluation);
            }
        }

        if (messagesData.status) {
            renderMessages(messagesData.data);
        }

        if (chatData.data?.status !== 'finished') {
            document.getElementById('messageInput').focus();
        }

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
    document.getElementById('chatLlmCost').textContent = '0.00';
    document.getElementById('chatTtsCost').textContent = '0.00';
    document.getElementById('chatSttCost').textContent = '0.00';
    document.getElementById('chatEvalCost').textContent = '0.00';
    document.getElementById('chatEvalStat').style.display = 'none';
    document.getElementById('chatTotalUsd').textContent = '0.00';
    const detailEl = document.getElementById('chatTotalUsdDetail');
    if (detailEl) detailEl.textContent = '0.00';
    document.getElementById('chatTotalArs').textContent = '0';
    showInputPanel();
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

        const avatarHtml = msg.role === 'system'
            ? ''
            : `<div class="message-avatar"><i class="fas fa-${msg.role === 'human' ? 'user' : 'robot'}"></i></div>`;

        div.innerHTML = `
            ${avatarHtml}
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

    // Prepare request body with optional STT metadata
    const requestBody = { content };
    if (currentSttMetadata) {
        Object.assign(requestBody, currentSttMetadata);
        // Update STT cost UI
        if (currentSttMetadata.stt_cost > 0) {
            const currentSttCost = parseFloat(document.getElementById('chatSttCost').textContent) || 0;
            document.getElementById('chatSttCost').textContent = (currentSttCost + currentSttMetadata.stt_cost).toFixed(4);
            updateTotalCost();
        }
    }
    const sttMetadataUsed = currentSttMetadata;
    currentSttMetadata = null; // Clear for next message

    try {
        const response = await apiFetch(`/api/chats/${currentChatId}/messages`, {
            method: 'POST',
            body: JSON.stringify(requestBody)
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
            speakTextUnified(botMsg.content, botMsg.id);

            if (usage) {
                const currentTokens = parseInt(document.getElementById('chatTokens').textContent) || 0;
                const currentLlmCost = parseFloat(document.getElementById('chatLlmCost').textContent) || 0;
                document.getElementById('chatTokens').textContent = currentTokens + (usage.total_tokens || 0);
                document.getElementById('chatLlmCost').textContent = (currentLlmCost + (usage.cost || 0)).toFixed(4);
                updateTotalCost();
            }

            // Check if conversation ended
            if (data.data.conversation_ended) {
                showFinishedPanel(false);
                triggerEvaluation(currentChatId);
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

    // Mouse events (desktop) - usar funciones unificadas
    voiceBtn.addEventListener('mousedown', startVoiceRecordingUnified);
    voiceBtn.addEventListener('mouseup', stopVoiceRecordingUnified);
    voiceBtn.addEventListener('mouseleave', stopVoiceRecordingUnified); // Por si el usuario suelta fuera del boton

    // Touch events (mobile)
    voiceBtn.addEventListener('touchstart', (e) => {
        e.preventDefault(); // Prevenir comportamiento por defecto del touch
        startVoiceRecordingUnified(e);
    });
    voiceBtn.addEventListener('touchend', (e) => {
        e.preventDefault();
        stopVoiceRecordingUnified(e);
    });
    voiceBtn.addEventListener('touchcancel', stopVoiceRecordingUnified);
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
// TTS Logging (console only)
// ===========================================

function ttsLog(message, type = 'info') {
    console.log(`[TTS ${type.toUpperCase()}] ${message}`);
}

// ===========================================
// OpenAI Speech Providers (STT/TTS)
// ===========================================

let sttProvider = 'native';
let ttsProvider = 'native';
let openaiMediaRecorder = null;
let openaiAudioChunks = [];
let currentSttMetadata = null; // Para guardar metadata STT del mensaje actual

function initSpeechProviders() {
    // Cargar preferencias de localStorage
    sttProvider = localStorage.getItem('sttProvider') || 'native';
    ttsProvider = localStorage.getItem('ttsProvider') || 'native';
    const ttsVoice = localStorage.getItem('ttsVoice') || 'alloy';

    // Actualizar selectores
    const sttSelect = document.getElementById('sttProvider');
    const ttsSelect = document.getElementById('ttsProvider');
    const voiceSelect = document.getElementById('ttsVoice');

    if (sttSelect) sttSelect.value = sttProvider;
    if (ttsSelect) ttsSelect.value = ttsProvider;
    if (voiceSelect) voiceSelect.value = ttsVoice;

    // Mostrar/ocultar selector de voz
    updateVoiceSelectorVisibility();

    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('settingsDropdown');
        const btn = document.querySelector('.header-icon-btn');
        if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.remove('show');
        }
        const statsDD = document.getElementById('statsDropdown');
        const costBadge = document.querySelector('.chat-cost-badge');
        if (statsDD && costBadge && !statsDD.contains(e.target) && !costBadge.contains(e.target)) {
            statsDD.classList.remove('show');
        }
    });

    console.log(`Speech providers initialized: STT=${sttProvider}, TTS=${ttsProvider}`);
}

function toggleSettingsDropdown() {
    const dropdown = document.getElementById('settingsDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

function onTtsVoiceChange() {
    const voiceSelect = document.getElementById('ttsVoice');
    if (voiceSelect) {
        localStorage.setItem('ttsVoice', voiceSelect.value);
        console.log(`TTS voice changed to: ${voiceSelect.value}`);
    }
}

function onSttProviderChange() {
    const select = document.getElementById('sttProvider');
    sttProvider = select.value;
    localStorage.setItem('sttProvider', sttProvider);
    console.log(`STT provider changed to: ${sttProvider}`);
}

function onTtsProviderChange() {
    const select = document.getElementById('ttsProvider');
    ttsProvider = select.value;
    localStorage.setItem('ttsProvider', ttsProvider);
    updateVoiceSelectorVisibility();
    console.log(`TTS provider changed to: ${ttsProvider}`);
}

function updateVoiceSelectorVisibility() {
    const voiceSelector = document.getElementById('ttsVoiceSelector');
    if (voiceSelector) {
        voiceSelector.style.display = ttsProvider === 'openai' ? 'flex' : 'none';
    }
}

// ===========================================
// OpenAI Whisper STT (Speech-to-Text)
// ===========================================

async function startOpenAIRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        openaiAudioChunks = [];
        openaiMediaRecorder = new MediaRecorder(stream, {
            mimeType: 'audio/webm;codecs=opus'
        });

        openaiMediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                openaiAudioChunks.push(event.data);
            }
        };

        openaiMediaRecorder.onstop = async () => {
            // Detener todas las pistas del stream
            stream.getTracks().forEach(track => track.stop());

            const audioBlob = new Blob(openaiAudioChunks, { type: 'audio/webm' });
            await transcribeWithWhisper(audioBlob);
        };

        openaiMediaRecorder.start();
        isRecording = true;
        updateVoiceUI('recording');
        console.log('OpenAI recording started');
    } catch (error) {
        console.error('Error starting OpenAI recording:', error);
        showVoiceWarning('Error al acceder al microfono: ' + error.message);
    }
}

function stopOpenAIRecording() {
    if (openaiMediaRecorder && openaiMediaRecorder.state !== 'inactive') {
        openaiMediaRecorder.stop();
        isRecording = false;
        console.log('OpenAI recording stopped');
    }
}

async function transcribeWithWhisper(audioBlob) {
    updateVoiceStatus('Transcribiendo...');

    try {
        const formData = new FormData();
        formData.append('audio', audioBlob, 'audio.webm');
        formData.append('language', 'es');
        if (currentChatId) {
            formData.append('chat_id', currentChatId);
        }

        // Usar API_TOKEN global y no setear Content-Type (el browser lo hace automatico para FormData)
        const response = await fetch('/api/speech/transcribe', {
            method: 'POST',
            headers: {
                'X-Auth-Token': API_TOKEN,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();

        if (data.status && data.data.text) {
            const text = data.data.text.trim();
            console.log('Whisper transcription:', text);

            // Guardar metadata STT para el mensaje
            currentSttMetadata = {
                stt_provider: data.data.stt_provider,
                stt_model: data.data.stt_model,
                stt_duration_ms: data.data.stt_duration_ms,
                stt_cost: data.data.stt_cost
            };

            // Poner texto en el input y enviar
            const messageInput = document.getElementById('messageInput');
            messageInput.value = text;
            updateVoiceUI('idle');
            sendMessage();
        } else {
            console.error('Whisper transcription failed:', data.message);
            showVoiceWarning(data.message || 'Error al transcribir el audio');
            updateVoiceUI('idle');
        }
    } catch (error) {
        console.error('Whisper transcription error:', error);
        showVoiceWarning('Error al transcribir: ' + error.message);
        updateVoiceUI('idle');
    }
}

// ===========================================
// OpenAI TTS (Text-to-Speech)
// ===========================================

let currentAudio = null;

async function speakWithOpenAI(text, messageId = null) {
    ttsLog(`speakWithOpenAI() llamado con texto de ${text?.length || 0} caracteres`, 'info');

    const ttsEnabled = document.getElementById('ttsEnabled');
    if (!ttsEnabled || !ttsEnabled.checked) {
        ttsLog('TTS deshabilitado por usuario, abortando', 'warning');
        return;
    }

    // Detener audio anterior si existe
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    updateVoiceUI('speaking');

    try {
        const voice = document.getElementById('ttsVoice')?.value || 'alloy';

        const response = await apiFetch('/api/speech/synthesize', {
            method: 'POST',
            body: JSON.stringify({
                text: text,
                voice: voice,
                message_id: messageId
            })
        });

        const data = await response.json();

        if (data.status && data.data.audio_base64) {
            ttsLog(`Audio recibido: ${data.data.tts_characters} chars, voz: ${data.data.tts_voice}`, 'success');

            // Update TTS cost in UI
            if (data.data.tts_cost > 0) {
                const currentTtsCost = parseFloat(document.getElementById('chatTtsCost').textContent) || 0;
                document.getElementById('chatTtsCost').textContent = (currentTtsCost + data.data.tts_cost).toFixed(4);
                updateTotalCost();
            }

            // Crear elemento de audio y reproducir
            const audioData = `data:${data.data.content_type};base64,${data.data.audio_base64}`;
            currentAudio = new Audio(audioData);

            currentAudio.onended = () => {
                ttsLog('Audio terminado de reproducir', 'success');
                updateVoiceUI('idle');
                currentAudio = null;
            };

            currentAudio.onerror = (e) => {
                ttsLog(`Error reproduciendo audio: ${e.message || 'unknown'}`, 'error');
                updateVoiceUI('idle');
                currentAudio = null;
            };

            await currentAudio.play();
            ttsLog('Audio comenzando a reproducir', 'success');
        } else {
            ttsLog(`Error de OpenAI TTS: ${data.message}`, 'error');
            updateVoiceUI('idle');
        }
    } catch (error) {
        ttsLog(`Error en speakWithOpenAI: ${error.message}`, 'error');
        updateVoiceUI('idle');
    }
}

// ===========================================
// Funciones Unificadas (Native vs OpenAI)
// ===========================================

// Wrapper para iniciar grabacion (decide native vs openai)
async function startVoiceRecordingUnified(e) {
    if (!currentChatId) {
        showVoiceWarning('Selecciona o crea una conversacion primero.');
        return;
    }

    if (isLoading || isRecording) {
        return;
    }

    if (sttProvider === 'openai') {
        await startOpenAIRecording();
    } else {
        await startVoiceRecording(e);
    }
}

// Wrapper para detener grabacion (decide native vs openai)
function stopVoiceRecordingUnified(e) {
    if (!isRecording) return;

    if (sttProvider === 'openai') {
        stopOpenAIRecording();
    } else {
        stopVoiceRecording(e);
    }
}

// Wrapper para TTS (decide native vs openai)
function speakTextUnified(text, messageId = null) {
    if (ttsProvider === 'openai') {
        speakWithOpenAI(text, messageId);
    } else {
        speakText(text);
    }
}

// Detener cualquier tipo de audio
function stopSpeakingUnified() {
    // Detener audio OpenAI
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }

    // Detener TTS nativo
    if (ttsSupported && speechSynthesis.speaking) {
        speechSynthesis.cancel();
    }

    updateVoiceUI('idle');
}

// ===========================================
// EVALUATION SYSTEM
// ===========================================

let finishedChatHasEvaluation = false;

function showFinishedPanel(hasEvaluation) {
    finishedChatHasEvaluation = hasEvaluation;
    document.getElementById('inputContainer').style.display = 'none';
    document.getElementById('finishedPanel').style.display = 'block';

    const btnText = document.getElementById('finishedEvalBtnText');
    const btn = document.getElementById('finishedEvalBtn');

    if (hasEvaluation) {
        btnText.textContent = 'Ver Evaluacion';
        btn.className = 'btn btn-sm btn-outline-primary';
    } else {
        btnText.textContent = 'Evaluar Conversacion';
        btn.className = 'btn btn-sm btn-primary';
    }
}

function showInputPanel() {
    document.getElementById('finishedPanel').style.display = 'none';
    document.getElementById('inputContainer').style.display = 'block';
}

function handleFinishedEvalAction() {
    if (finishedChatHasEvaluation) {
        showEvaluationForChat(currentChatId);
    } else {
        triggerEvaluation(currentChatId);
    }
}

async function triggerEvaluation(chatId) {
    // Remove existing banner
    const existing = document.querySelector('.chat-finished-banner');
    if (existing) existing.remove();

    // Show evaluating indicator
    const container = document.getElementById('messagesContainer');
    const indicator = document.createElement('div');
    indicator.className = 'evaluating-indicator';
    indicator.id = 'evaluatingIndicator';
    indicator.innerHTML = `
        <div class="loading-spinner" style="width: 1.5rem; height: 1.5rem; border-width: 2px;"></div>
        <span>Evaluando conversacion...</span>
    `;
    container.appendChild(indicator);
    container.scrollTop = container.scrollHeight;

    try {
        console.log('triggerEvaluation: calling POST /api/chats/' + chatId + '/evaluate');
        const response = await apiFetch(`/api/chats/${chatId}/evaluate`, {
            method: 'POST'
        });

        const data = await response.json();
        console.log('triggerEvaluation: response', data);

        // Remove indicator
        document.getElementById('evaluatingIndicator')?.remove();

        if (data.status) {
            // Update eval cost in header
            if (data.data.cost) {
                document.getElementById('chatEvalCost').textContent = parseFloat(data.data.cost).toFixed(4);
                document.getElementById('chatEvalStat').style.display = '';
                updateTotalCost();
            }

            showFinishedPanel(true);
            showEvaluationModal(data.data);
            loadChats(); // Refresh sidebar badges
        } else {
            showFinishedPanel(false);
            console.error('triggerEvaluation: API error', data.message);
            alert(data.message || 'Error al evaluar la conversacion');
        }
    } catch (error) {
        console.error('triggerEvaluation: exception', error);
        document.getElementById('evaluatingIndicator')?.remove();
        showFinishedPanel(false);
        alert('Error al evaluar la conversacion');
    }
}

async function showEvaluationForChat(chatId) {
    try {
        const response = await apiFetch(`/api/chats/${chatId}/evaluation`);
        const data = await response.json();

        if (data.status) {
            showEvaluationModal(data.data);
        } else {
            alert(data.message || 'No se encontro la evaluacion');
        }
    } catch (error) {
        console.error('Error loading evaluation:', error);
        alert('Error al cargar la evaluacion');
    }
}

const criteriaNames = {
    greeting: 'Saludo amable y profesional',
    focus_on_client: 'Foco exclusivo en el cliente',
    persuasion: 'Gatillo mental / persuasion',
    reciprocity: 'Principio de reciprocidad',
    objections: 'Objeciones con empatia',
    strategic_questions: 'Preguntas estrategicas',
    cross_selling: 'Venta cruzada',
    upselling: 'Venta adicional (producto superior)',
    payment_methods: 'Medios de pago',
    communication_style: 'Comunicacion adecuada al arquetipo',
    discounts_promos: 'Descuentos y promociones',
    wow_effect: 'Efecto WOW',
    farewell: 'Despedida amable',
};

function showEvaluationModal(evaluation) {
    const scoreCircle = document.getElementById('evalScoreCircle');
    const scoreValue = document.getElementById('evalScoreValue');
    const scoreLabel = document.getElementById('evalScoreLabel');
    const progressFill = document.getElementById('evalProgressFill');
    const criteriaList = document.getElementById('evalCriteriaList');
    const feedbackDiv = document.getElementById('evalOverallFeedback');

    const score = Math.round(evaluation.overall_score);
    const passed = evaluation.passed;
    const passClass = passed ? 'passed' : 'failed';

    // Score circle
    scoreCircle.className = 'eval-score-circle ' + passClass;
    scoreValue.textContent = score + '%';
    scoreLabel.textContent = passed ? 'Aprobado' : 'No aprobado';

    // Progress bar
    progressFill.className = 'eval-progress-fill ' + passClass;
    setTimeout(() => {
        progressFill.style.width = score + '%';
    }, 100);

    // Usage info (tokens, cost, model)
    const usageInfo = document.getElementById('evalUsageInfo');
    if (evaluation.cost || evaluation.total_tokens) {
        document.getElementById('evalModelName').textContent = evaluation.model || '-';
        document.getElementById('evalTokens').textContent = evaluation.total_tokens || 0;
        document.getElementById('evalCost').textContent = parseFloat(evaluation.cost || 0).toFixed(4);
        usageInfo.style.display = 'flex';
    } else {
        usageInfo.style.display = 'none';
    }

    // Criteria list
    criteriaList.innerHTML = '';
    if (evaluation.criteria_results && evaluation.criteria_results.length > 0) {
        evaluation.criteria_results.forEach(criterion => {
            const li = document.createElement('li');
            li.className = 'eval-criterion';
            const cPassClass = criterion.passed ? 'passed' : 'failed';
            const cIcon = criterion.passed ? 'fa-check' : 'fa-times';
            const cName = criteriaNames[criterion.key] || criterion.key;

            li.innerHTML = `
                <div class="eval-criterion-header">
                    <div class="eval-criterion-icon ${cPassClass}">
                        <i class="fas ${cIcon}"></i>
                    </div>
                    <span class="eval-criterion-name">${escapeHtml(cName)}</span>
                    <span class="eval-criterion-score">${criterion.score}/10</span>
                </div>
                <div class="eval-criterion-justification">${escapeHtml(criterion.justification || '')}</div>
            `;

            li.addEventListener('click', function() {
                this.classList.toggle('expanded');
            });

            criteriaList.appendChild(li);
        });
    }

    // Feedback
    if (evaluation.overall_feedback) {
        feedbackDiv.style.display = 'block';
        feedbackDiv.innerHTML = '<strong><i class="fas fa-comment-alt me-1"></i>Feedback general:</strong><br>' + escapeHtml(evaluation.overall_feedback);
    } else {
        feedbackDiv.style.display = 'none';
    }

    // Show modal
    const modal = new coreui.Modal(document.getElementById('evaluationModal'));
    modal.show();
}

</script>
@endpush
