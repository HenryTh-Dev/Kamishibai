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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .date-selector {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .category-card {
            background: white;
            border-radius: 20px;
            margin: 1rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .category-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .category-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .category-description {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .items-container {
            padding: 1.5rem;
        }
        
        .item-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .item-card.completed {
            border-color: var(--success-color);
            background: #d4edda;
        }
        
        .item-card.not-completed {
            border-color: var(--danger-color);
            background: #f8d7da;
        }
        
        .item-description {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .status-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .status-btn {
            flex: 1;
            max-width: 150px;
            height: 60px;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .status-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-completed {
            background: var(--success-color);
            color: white;
        }
        
        .btn-completed:hover {
            background: #218838;
            color: white;
        }
        
        .btn-not-completed {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-not-completed:hover {
            background: #c82333;
            color: white;
        }
        
        .btn-pending {
            background: #e9ecef;
            color: #6c757d;
            border: 2px dashed #dee2e6;
        }
        
        .btn-pending:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        
        .status-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: bold;
        }
        
        .status-completed {
            background: var(--success-color);
            color: white;
        }
        
        .status-not-completed {
            background: var(--danger-color);
            color: white;
        }
        
        .loading {
            text-align: center;
            padding: 3rem;
        }
        
        .loading-spinner {
            width: 3rem;
            height: 3rem;
            border: 0.3rem solid #f3f3f3;
            border-top: 0.3rem solid var(--primary-gradient);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .summary-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--success-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .success-message.show {
            opacity: 1;
        }
        
        .logout-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 1001;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .status-buttons {
                flex-direction: column;
            }
            
            .status-btn {
                max-width: none;
                height: 50px;
            }
            
            .summary-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="bi bi-heart-pulse-fill me-3"></i>
                        Registro de Atividades UTI
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Bem-vinda, <?= htmlspecialchars($user_name) ?>!</p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="logout-btn" onclick="logout()">
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
                    <button class="btn btn-outline-secondary me-2" onclick="changeDate(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <input type="date" id="record-date" class="form-control" style="max-width: 200px;" onchange="loadActivities()">
                    <button class="btn btn-outline-secondary ms-2" onclick="changeDate(1)">
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
        let currentActivities = [];
        
        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            // Definir data atual
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('record-date').value = today;
            
            // Carregar atividades
            loadActivities();
        });
        
        function changeDate(direction) {
            const dateInput = document.getElementById('record-date');
            const currentDate = new Date(dateInput.value);
            currentDate.setDate(currentDate.getDate() + direction);
            
            // Não permitir datas futuras
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
            
            // Mostrar loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('activities-container').innerHTML = '';
            document.getElementById('summary-card').style.display = 'none';
            
            // Buscar atividades da API
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
                    // Esconder loading
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('summary-card').style.display = 'block';
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
                                ${item.status ? `<div class="status-indicator status-${item.status === 'C' ? 'completed' : 'not-completed'}">
                                    <i class="bi bi-${item.status === 'C' ? 'check' : 'x'}"></i>
                                </div>` : ''}
                                <div class="item-description">${item.description}</div>
                                <div class="status-buttons">
                                    <button class="status-btn btn-completed ${item.status === 'C' ? 'active' : ''}" 
                                            onclick="recordActivity(${item.id}, 'C')">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Concluído
                                    </button>
                                    <button class="status-btn btn-not-completed ${item.status === 'NC' ? 'active' : ''}" 
                                            onclick="recordActivity(${item.id}, 'NC')">
                                        <i class="bi bi-x-circle-fill"></i>
                                        Não Concluído
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
                default: return '';
            }
        }
        
        function recordActivity(itemId, status) {
            const date = document.getElementById('record-date').value;
            
            // Dados para envio
            const data = {
                item_id: itemId,
                status: status,
                record_date: date,
                user_id: 1 // Por enquanto, ID fixo
            };
            
            // Enviar para API
            fetch('../api.php?action=record_activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showSuccessMessage();
                    // Recarregar atividades para atualizar status
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
            
            setTimeout(() => {
                message.classList.remove('show');
            }, 2000);
        }
        
        function showError(message) {
            alert(message);
        }
        
        function logout() {
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</body>
</html>

