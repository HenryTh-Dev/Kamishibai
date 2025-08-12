<?php
session_start();
require_once '../config.php';

// Verificar se o usuário está logado e é enfermeira
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nurse') {
    header('Location: ../login.php?redirect=nurse');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Enfermeira';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Atividades - Enfermeira</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="nurse.css" rel="stylesheet">
</head>
<body>
<!-- Header -->
<div class="header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">
                    <img src="../logo.png" width="50px" style="opacity: 0.9;">
                    Registro de Atividades UTI
                </h1>
                <p class="mb-0 mt-2 opacity-75">Bem-vinda, <?= htmlspecialchars($user_name) ?>!</p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="logout-btn" onclick="logout()">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Sair
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Seletor de Data -->
<div class="date-selector">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h4 class="mb-0">
                <i class="bi bi-calendar-check me-2"></i>
                Data do Registro
            </h4>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="changeDate(-1)">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <input type="date" id="record-date" class="form-control" style="max-width: 200px;" onchange="loadActivities()">
                <button type="button" class="btn btn-outline-secondary ms-2" onclick="changeDate(1)">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="loading">
    <div class="loading-spinner"></div>
    <h5>Carregando atividades...</h5>
</div>

<!-- Resumo -->
<div id="summary-card" class="summary-card" style="display: none;">
    <h5>
        <i class="bi bi-clipboard-data me-2"></i>
        Resumo do Dia
    </h5>
    <div class="summary-stats">
        <div class="stat-item">
            <div class="stat-number text-success" id="completed-count">0</div>
            <div class="stat-label">Concluídas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-danger" id="not-completed-count">0</div>
            <div class="stat-label">Não Concluídas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-warning" id="pending-count">0</div>
            <div class="stat-label">Pendentes</div>
        </div>
        <div class="stat-item">
            <div class="stat-number text-info" id="completion-rate">0%</div>
            <div class="stat-label">Taxa de Conclusão</div>
        </div>
    </div>
</div>

<!-- Atividades -->
<div id="activities-container">
    <!-- Será preenchido via JavaScript -->
</div>

<!-- Mensagem de Sucesso -->
<div id="success-message" class="success-message">
    <i class="bi bi-check-circle-fill me-2"></i>
    Atividade registrada com sucesso!
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Agora usamos o username para auditoria
    const currentUsername = '<?= addslashes($_SESSION['user_username'] ?? '') ?>';
    let currentActivities = [];

    // Debug opcional
    console.debug('DEBUG currentUsername =', currentUsername);

    // Inicializar página
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('record-date').value = today;
        loadActivities();
    });

    function changeDate(direction) {
        const dateInput = document.getElementById('record-date');
        const currentDate = new Date(dateInput.value);
        currentDate.setDate(currentDate.getDate() + direction);
        const today = new Date();
        if (currentDate > today) {
            alert('Não é possível registrar atividades para datas futuras.');
            return;
        }
        dateInput.value = currentDate.toISOString().split('T')[0];
        loadActivities();
    }

    function loadActivities() {
        const date = document.getElementById('record-date').value;
        const scrollPos = window.scrollY;

        document.getElementById('loading').style.display = 'block';
        document.getElementById('activities-container').innerHTML = '';
        document.getElementById('summary-card').style.display = 'none';

        fetch(`../api.php?action=get_activities_by_date&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentActivities = data.categories;
                    renderActivities(data.categories);
                    updateSummary();
                } else {
                    console.error('Erro na API:', data.error);
                    showError('Erro ao carregar atividades: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showError('Erro de conexão com o servidor');
            })
            .finally(() => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('summary-card').style.display = 'block';
                window.scrollTo(0, scrollPos);
            });
    }

    function renderActivities(categories) {
        const container = document.getElementById('activities-container');
        container.innerHTML = '';

        categories.forEach(category => {
            const categoryCard = document.createElement('div');
            categoryCard.className = 'category-card';

            categoryCard.innerHTML = `
                <div class="category-header">
                    <div class="category-title">${category.name}</div>
                    <div class="category-description">${category.description || 'Sem descrição'}</div>
                </div>
                <div class="items-container">
                    ${category.items.map(item => `
                        <div class="item-card ${getItemStatusClass(item.status)}" style="position: relative;">
                            ${item.status ? `<div class="status-indicator status-${item.status === 'C' ? 'completed' : item.status === 'NC' ? 'not-completed' : 'na'}">
                                <i class="bi bi-${item.status === 'C' ? 'check' : item.status === 'NC' ? 'x' : 'dash'}"></i>
                            </div>` : ''}

                            <div class="item-description">${item.description}</div>

                            <div class="status-buttons">
                                <button type="button"
                                        class="status-btn btn-completed ${item.status === 'C' ? 'active' : ''}"
                                        onclick="recordActivity(${item.id}, 'C')">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Concluído
                                </button>

                                <button type="button"
                                        class="status-btn btn-not-completed ${item.status === 'NC' ? 'active' : ''}"
                                        onclick="recordActivity(${item.id}, 'NC')">
                                    <i class="bi bi-x-circle-fill"></i>
                                    Não Concluído
                                </button>

                                <button type="button"
                                        class="status-btn btn-na ${item.status === 'NA' ? 'active' : ''}"
                                        onclick="recordActivity(${item.id}, 'NA')">
                                    <i class="bi bi-dash-circle-fill"></i>
                                    N/A
                                </button>
                            </div>

                            ${item.recorded_at ? `<small class="text-muted mt-2 d-block text-center">
                                Registrado em: ${new Date(item.recorded_at).toLocaleString('pt-BR')}
                            </small>` : ''}
                        </div>
                    `).join('')}
                </div>
            `;

            container.appendChild(categoryCard);
        });

        if (categories.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h5 class="mt-3 text-muted">Nenhuma atividade encontrada</h5>
                    <p class="text-muted">Não há atividades cadastradas para registro.</p>
                </div>
            `;
        }
    }

    function getItemStatusClass(status) {
        switch (status) {
            case 'C': return 'completed';
            case 'NC': return 'not-completed';
            case 'NA': return 'na';
            default: return '';
        }
    }

    function recordActivity(itemId, status) {
        const date = document.getElementById('record-date').value;

        const data = {
            item_id:     itemId,
            status:      status,
            record_date: date,
            notes:       currentUsername   // << grava o username no campo notes
        };

        fetch('../api.php?action=record_activity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showSuccessMessage();
                    loadActivities();
                } else {
                    alert('Erro ao registrar atividade: ' + result.error);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão ao registrar atividade');
            });
    }

    function updateSummary() {
        let completed = 0;
        let notCompleted = 0;
        let pending = 0;
        let total = 0;

        currentActivities.forEach(category => {
            category.items.forEach(item => {
                total++;
                switch (item.status) {
                    case 'C':
                    case 'NA':
                        completed++;
                        break;
                    case 'NC':
                        notCompleted++;
                        break;
                    default:
                        pending++;
                }
            });
        });

        const completionRate = total > 0 ? Math.round((completed / total) * 100) : 0;

        document.getElementById('completed-count').textContent = completed;
        document.getElementById('not-completed-count').textContent = notCompleted;
        document.getElementById('pending-count').textContent = pending;
        document.getElementById('completion-rate').textContent = completionRate + '%';
    }

    function showSuccessMessage() {
        const message = document.getElementById('success-message');
        message.classList.add('show');
        setTimeout(() => message.classList.remove('show'), 2000);
    }

    function showError(message) { alert(message); }

    function logout() {
        if (confirm('Tem certeza que deseja sair?')) {
            window.location.href = '../logout.php';
        }
    }
</script>
</body>
</html>
