@extends('layouts.master')

@section('title', 'Configuraciones - YPF Chat Station')

@section('breadcrumb')
<li class="breadcrumb-item active">Configuraciones</li>
@endsection

@push('css')
<style>
    :root {
        --ypf-blue: #0033a0;
    }

    .configs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .config-table {
        background: var(--cui-card-bg);
        border-radius: 0.5rem;
        border: 1px solid var(--cui-border-color);
    }

    .config-table .table {
        margin-bottom: 0;
    }

    .config-table .table th {
        background: var(--cui-tertiary-bg);
        border-bottom: 1px solid var(--cui-border-color);
        font-weight: 600;
        padding: 0.875rem 1rem;
    }

    .config-table .table td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
    }

    .config-tag {
        font-family: monospace;
        background: var(--cui-tertiary-bg);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .config-value {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .config-actions {
        display: flex;
        gap: 0.5rem;
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

    .modal-value-textarea {
        min-height: 120px;
        font-family: monospace;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="configs-header">
    <h1 class="h3 mb-0"><i class="fas fa-cog me-2"></i>Configuraciones</h1>
    <button class="btn btn-ypf" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Nueva Configuracion
    </button>
</div>

<div id="configsList">
    <div class="empty-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Cargando configuraciones...</p>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Configuracion</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="configForm">
                    <input type="hidden" id="configId">
                    <div class="mb-3">
                        <label for="configTag" class="form-label">Tag *</label>
                        <input type="text" class="form-control font-monospace" id="configTag" required placeholder="ej: openai-api-key">
                        <small class="text-body-secondary">Identificador unico de la configuracion</small>
                    </div>
                    <div class="mb-3">
                        <label for="configValue" class="form-label">Valor</label>
                        <textarea class="form-control modal-value-textarea" id="configValue" placeholder="Valor de la configuracion"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="configDescription" class="form-label">Descripcion</label>
                        <input type="text" class="form-control" id="configDescription" placeholder="Descripcion opcional">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-ypf" onclick="saveConfig()">
                    <i class="fas fa-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let configModal;

document.addEventListener('DOMContentLoaded', function() {
    configModal = new coreui.Modal(document.getElementById('configModal'));
    loadConfigs();
});

async function loadConfigs() {
    try {
        const response = await apiFetch('/api/configs');
        const data = await response.json();

        if (data.status) {
            renderConfigs(data.data);
        }
    } catch (error) {
        console.error('Error loading configs:', error);
        document.getElementById('configsList').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error al cargar las configuraciones</p>
            </div>
        `;
    }
}

function renderConfigs(configs) {
    const container = document.getElementById('configsList');

    if (configs.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-cog"></i>
                <h3>Sin configuraciones</h3>
                <p>Crea tu primera configuracion para comenzar</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="config-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Valor</th>
                        <th>Descripcion</th>
                        <th style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${configs.map(config => `
                        <tr>
                            <td><span class="config-tag">${escapeHtml(config.tag)}</span></td>
                            <td class="config-value" title="${escapeHtml(config.value || '')}">${escapeHtml(config.value || '-')}</td>
                            <td>${escapeHtml(config.description || '-')}</td>
                            <td>
                                <div class="config-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editConfig(${config.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteConfig(${config.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Nueva Configuracion';
    document.getElementById('configForm').reset();
    document.getElementById('configId').value = '';
    document.getElementById('configTag').disabled = false;
    configModal.show();
}

async function editConfig(id) {
    try {
        const response = await apiFetch(`/api/configs/${id}`);
        const data = await response.json();

        if (data.status) {
            document.getElementById('modalTitle').textContent = 'Editar Configuracion';
            document.getElementById('configId').value = data.data.id;
            document.getElementById('configTag').value = data.data.tag;
            document.getElementById('configTag').disabled = true;
            document.getElementById('configValue').value = data.data.value || '';
            document.getElementById('configDescription').value = data.data.description || '';
            configModal.show();
        }
    } catch (error) {
        console.error('Error loading config:', error);
        alert('Error al cargar la configuracion');
    }
}

async function saveConfig() {
    const id = document.getElementById('configId').value;
    const data = {
        tag: document.getElementById('configTag').value,
        value: document.getElementById('configValue').value || null,
        description: document.getElementById('configDescription').value || null
    };

    if (!data.tag) {
        alert('El tag es requerido');
        return;
    }

    try {
        const url = id ? `/api/configs/${id}` : '/api/configs';
        const method = id ? 'PUT' : 'POST';

        const response = await apiFetch(url, {
            method,
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.status) {
            configModal.hide();
            loadConfigs();
        } else {
            alert(result.message || 'Error al guardar');
        }
    } catch (error) {
        console.error('Error saving config:', error);
        alert('Error al guardar la configuracion');
    }
}

async function deleteConfig(id) {
    if (!confirm('Estas seguro de eliminar esta configuracion?')) return;

    try {
        const response = await apiFetch(`/api/configs/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.status) {
            loadConfigs();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting config:', error);
        alert('Error al eliminar la configuracion');
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
