@extends('layouts.master')

@section('title', 'Agentes - YPF Chat Station')

@section('breadcrumb')
<li class="breadcrumb-item active">Agentes</li>
@endsection

@push('css')
<style>
    :root {
        --ypf-blue: #0033a0;
    }

    .agents-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .agent-card {
        margin-bottom: 1rem;
        border: 1px solid var(--cui-border-color);
    }

    .agent-card-header {
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--cui-tertiary-bg);
        border-bottom: 1px solid var(--cui-border-color);
    }

    .agent-card-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .agent-card-title h3 {
        margin: 0;
        font-size: 1.125rem;
    }

    .agent-card-body {
        padding: 1rem 1.25rem;
    }

    .agent-description {
        color: var(--cui-secondary-color);
        margin-bottom: 0.75rem;
    }

    .agent-prompt {
        background: var(--cui-tertiary-bg);
        padding: 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        white-space: pre-wrap;
        max-height: 150px;
        overflow-y: auto;
        font-family: monospace;
        border: 1px solid var(--cui-border-color);
    }

    .agent-actions {
        display: flex;
        gap: 0.5rem;
    }

    .modal-prompt-textarea {
        min-height: 200px;
        font-family: monospace;
        font-size: 0.8125rem;
    }

    .empty-state {
        text-align: center;
        padding: 3.75rem 1.25rem;
        color: var(--cui-secondary-color);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--cui-tertiary-color);
        margin-bottom: 1rem;
    }

    .btn-ypf {
        background-color: var(--ypf-blue);
        border-color: var(--ypf-blue);
        color: white;
    }

    .btn-ypf:hover {
        background-color: #002680;
        border-color: #002680;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="agents-header">
    <h1 class="h3 mb-0"><i class="fas fa-robot me-2"></i>Agentes</h1>
    <button class="btn btn-ypf" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Nuevo Agente
    </button>
</div>

<div id="agentsList">
    <div class="empty-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Cargando agentes...</p>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="agentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Agente</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="agentForm">
                    <input type="hidden" id="agentId">
                    <div class="mb-3">
                        <label for="agentName" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="agentName" required>
                    </div>
                    <div class="mb-3">
                        <label for="agentDescription" class="form-label">Descripcion</label>
                        <input type="text" class="form-control" id="agentDescription">
                    </div>
                    <div class="mb-3">
                        <label for="agentPrompt" class="form-label">System Prompt *</label>
                        <textarea class="form-control modal-prompt-textarea" id="agentPrompt" required></textarea>
                        <small class="text-body-secondary">Define el comportamiento y personalidad del bot</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="agentActive" checked>
                        <label class="form-check-label" for="agentActive">Activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-ypf" onclick="saveAgent()">
                    <i class="fas fa-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let agentModal;

document.addEventListener('DOMContentLoaded', function() {
    agentModal = new coreui.Modal(document.getElementById('agentModal'));
    loadAgents();
});

async function loadAgents() {
    try {
        const response = await apiFetch('/api/agents');
        const data = await response.json();

        if (data.status) {
            renderAgents(data.data);
        }
    } catch (error) {
        console.error('Error loading agents:', error);
        document.getElementById('agentsList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error al cargar los agentes</p>
            </div>
        `;
    }
}

function renderAgents(agents) {
    const container = document.getElementById('agentsList');

    if (agents.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-robot"></i>
                <h3>Sin agentes</h3>
                <p>Crea tu primer agente para comenzar</p>
            </div>
        `;
        return;
    }

    container.innerHTML = agents.map(agent => `
        <div class="card agent-card">
            <div class="agent-card-header">
                <div class="agent-card-title">
                    <i class="fas fa-robot text-primary"></i>
                    <h3>${escapeHtml(agent.name)}</h3>
                    <span class="badge ${agent.is_active ? 'bg-success' : 'bg-danger'}">
                        ${agent.is_active ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                <div class="agent-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="editAgent(${agent.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAgent(${agent.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="agent-card-body">
                ${agent.description ? `<p class="agent-description">${escapeHtml(agent.description)}</p>` : ''}
                <div class="agent-prompt">${escapeHtml(agent.system_prompt || 'Sin prompt configurado')}</div>
            </div>
        </div>
    `).join('');
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nuevo Agente';
    document.getElementById('agentForm').reset();
    document.getElementById('agentId').value = '';
    document.getElementById('agentActive').checked = true;
    agentModal.show();
}

async function editAgent(id) {
    try {
        const response = await apiFetch(`/api/agents/${id}`);
        const data = await response.json();

        if (data.status) {
            document.getElementById('modalTitle').textContent = 'Editar Agente';
            document.getElementById('agentId').value = data.data.id;
            document.getElementById('agentName').value = data.data.name;
            document.getElementById('agentDescription').value = data.data.description || '';
            document.getElementById('agentPrompt').value = data.data.system_prompt;
            document.getElementById('agentActive').checked = data.data.is_active;
            agentModal.show();
        }
    } catch (error) {
        console.error('Error loading agent:', error);
        alert('Error al cargar el agente');
    }
}

async function saveAgent() {
    const id = document.getElementById('agentId').value;
    const data = {
        name: document.getElementById('agentName').value,
        description: document.getElementById('agentDescription').value || null,
        system_prompt: document.getElementById('agentPrompt').value,
        is_active: document.getElementById('agentActive').checked
    };

    if (!data.name || !data.system_prompt) {
        alert('Nombre y System Prompt son requeridos');
        return;
    }

    try {
        const url = id ? `/api/agents/${id}` : '/api/agents';
        const method = id ? 'PUT' : 'POST';

        const response = await apiFetch(url, {
            method,
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.status) {
            agentModal.hide();
            loadAgents();
        } else {
            alert(result.message || 'Error al guardar');
        }
    } catch (error) {
        console.error('Error saving agent:', error);
        alert('Error al guardar el agente');
    }
}

async function deleteAgent(id) {
    if (!confirm('Estas seguro de eliminar este agente?')) return;

    try {
        const response = await apiFetch(`/api/agents/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.status) {
            loadAgents();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting agent:', error);
        alert('Error al eliminar el agente');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
