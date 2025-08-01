<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard UTI - Tempo Real</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
        }
        
        .live-dot {
            width: 8px;
            height: 8px;
            background: #ff4757;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.1;
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem;
        }
        
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .chart-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .activities-timeline {
            background: white;
            border-radius: 20px;
            margin: 2rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .timeline-completed {
            background: var(--success-color);
            color: white;
        }
        
        .timeline-not-completed {
            background: var(--danger-color);
            color: white;
        }
        
        .timeline-pending {
            background: var(--warning-color);
            color: white;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .timeline-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .timeline-time {
            font-size: 0.8rem;
            color: #adb5bd;
        }
        
        .alert-banner {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 1rem 2rem;
            margin: 2rem;
            border-radius: 15px;
            display: none;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .alert-banner.show {
            display: block;
        }
        
        .date-selector {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            margin: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .refresh-countdown {
            font-weight: bold;
            color: var(--info-color);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-spinner {
            width: 3rem;
            height: 3rem;
            border: 0.3rem solid #f3f3f3;
            border-top: 0.3rem solid var(--primary-gradient);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                margin: 1rem;
            }
            
            .date-selector {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="text-center">
            <div class="loading-spinner"></div>
            <h5 class="mt-3">Atualizando dados...</h5>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="bi bi-activity me-3"></i>
                        Dashboard UTI - Tempo Real
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Monitoramento de atividades de limpeza e higienização</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        AO VIVO
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner -->
    <div id="alert-banner" class="alert-banner">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Atenção!</strong>
                <span id="alert-message">Taxa de conclusão abaixo do esperado.</span>
            </div>
        </div>
    </div>

    <!-- Date Selector -->
    <div class="date-selector">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">
                <i class="bi bi-calendar-event me-2"></i>
                Data:
            </h5>
            <input type="date" id="dashboard-date" class="form-control" style="max-width: 200px;" onchange="loadDashboard()">
        </div>
        <div class="auto-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Próxima atualização em: <span class="refresh-countdown" id="countdown">120</span>s
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stats-card">
            <i class="bi bi-list-check stat-icon"></i>
            <div class="stat-number" id="total-activities">0</div>
            <div class="stat-label">Total de Atividades</div>
        </div>
        <div class="stats-card">
            <i class="bi bi-check-circle-fill stat-icon text-success"></i>
            <div class="stat-number text-success" id="completed-activities">0</div>
            <div class="stat-label">Concluídas</div>
        </div>
        <div class="stats-card">
            <i class="bi bi-x-circle-fill stat-icon text-danger"></i>
            <div class="stat-number text-danger" id="not-completed-activities">0</div>
            <div class="stat-label">Não Concluídas</div>
        </div>
        <div class="stats-card">
            <i class="bi bi-clock-fill stat-icon text-warning"></i>
            <div class="stat-number text-warning" id="pending-activities">0</div>
            <div class="stat-label">Pendentes</div>
        </div>
        <div class="stats-card">
            <i class="bi bi-percent stat-icon text-info"></i>
            <div class="stat-number text-info" id="completion-rate">0%</div>
            <div class="stat-label">Taxa de Conclusão</div>
        </div>
        <div class="stats-card">
            <i class="bi bi-people-fill stat-icon text-primary"></i>
            <div class="stat-number text-primary" id="active-nurses">0</div>
            <div class="stat-label">Enfermeiras Ativas</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart-fill"></i>
                Status das Atividades
            </div>
            <div class="chart-container">
                <canvas id="status-chart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart-fill"></i>
                Progresso por Categoria
            </div>
            <div class="chart-container">
                <canvas id="category-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activities Timeline -->
    <div class="activities-timeline">
        <div class="chart-title">
            <i class="bi bi-clock-history"></i>
            Atividades Recentes
        </div>
        <div id="timeline-container">
            <!-- Será preenchido via JavaScript -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let statusChart = null;
        let categoryChart = null;
        let refreshInterval = null;
        let countdownInterval = null;
        let countdownValue = 120; // 2 minutos
        
        // Inicializar dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Definir data atual
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dashboard-date').value = today;
            
            // Carregar dados iniciais
            loadDashboard();
            
            // Configurar auto-refresh
            startAutoRefresh();
        });
        
        function loadDashboard() {
            const date = document.getElementById('dashboard-date').value;
            
            // Mostrar loading
            document.getElementById('loading-overlay').classList.add('show');
            
            // Buscar dados da API
            Promise.all([
                fetch(`../api.php?action=get_activities_by_date&date=${date}`).then(r => r.json()),
                fetch(`../api.php?action=dashboard_data&month=${date.substring(0, 7)}`).then(r => r.json())
            ])
            .then(([activitiesData, dashboardData]) => {
                if (activitiesData.success && dashboardData.success) {
                    updateDashboard(activitiesData, dashboardData);
                } else {
                    console.error('Erro na API');
                    showError('Erro ao carregar dados do dashboard');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showError('Erro de conexão com o servidor');
            })
            .finally(() => {
                // Esconder loading
                document.getElementById('loading-overlay').classList.remove('show');
            });
        }
        
        function updateDashboard(activitiesData, dashboardData) {
            // Calcular estatísticas do dia
            let totalActivities = 0;
            let completedActivities = 0;
            let notCompletedActivities = 0;
            let pendingActivities = 0;
            let recentActivities = [];
            
            activitiesData.categories.forEach(category => {
                category.items.forEach(item => {
                    totalActivities++;
                    switch (item.status) {
                        case 'C':
                        case 'NA':
                            completedActivities++;
                            if (item.recorded_at) {
                                recentActivities.push({
                                    type: 'completed',
                                    title: item.description,
                                    category: category.name,
                                    time: item.recorded_at
                                });
                            }
                            break;
                        case 'NC':
                            notCompletedActivities++;
                            if (item.recorded_at) {
                                recentActivities.push({
                                    type: 'not-completed',
                                    title: item.description,
                                    category: category.name,
                                    time: item.recorded_at
                                });
                            }
                            break;
                        default:
                            pendingActivities++;
                    }
                });
            });
            
            const completionRate = totalActivities > 0 ? Math.round((completedActivities / totalActivities) * 100) : 0;
            
            // Atualizar estatísticas
            document.getElementById('total-activities').textContent = totalActivities;
            document.getElementById('completed-activities').textContent = completedActivities;
            document.getElementById('not-completed-activities').textContent = notCompletedActivities;
            document.getElementById('pending-activities').textContent = pendingActivities;
            document.getElementById('completion-rate').textContent = completionRate + '%';
            document.getElementById('active-nurses').textContent = '1'; // Simulado
            
            // Atualizar gráficos
            updateStatusChart(completedActivities, notCompletedActivities, pendingActivities);
            updateCategoryChart(dashboardData.categories);
            
            // Atualizar timeline
            updateTimeline(recentActivities);
            
            // Verificar alertas
            checkAlerts(completionRate, pendingActivities);
        }
        
        function updateStatusChart(completed, notCompleted, pending) {
            const ctx = document.getElementById('status-chart').getContext('2d');
            
            if (statusChart) {
                statusChart.destroy();
            }
            
            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Concluídas', 'Não Concluídas', 'Pendentes'],
                    datasets: [{
                        data: [completed, notCompleted, pending],
                        backgroundColor: [
                            '#28a745',
                            '#dc3545',
                            '#ffc107'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 14
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function updateCategoryChart(categories) {
            const ctx = document.getElementById('category-chart').getContext('2d');
            
            if (categoryChart) {
                categoryChart.destroy();
            }
            
            const labels = categories.map(cat => cat.category_name);
            const completedData = categories.map(cat => cat.completed_records);
            const notCompletedData = categories.map(cat => cat.not_completed_records);
            
            categoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Concluídas',
                            data: completedData,
                            backgroundColor: '#28a745',
                            borderRadius: 5
                        },
                        {
                            label: 'Não Concluídas',
                            data: notCompletedData,
                            backgroundColor: '#dc3545',
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 14
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function updateTimeline(activities) {
            const container = document.getElementById('timeline-container');
            
            // Ordenar por tempo (mais recente primeiro)
            activities.sort((a, b) => new Date(b.time) - new Date(a.time));
            
            // Limitar a 10 atividades mais recentes
            const recentActivities = activities.slice(0, 10);
            
            if (recentActivities.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-clock" style="font-size: 3rem; color: #dee2e6;"></i>
                        <h6 class="mt-3 text-muted">Nenhuma atividade registrada hoje</h6>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = recentActivities.map(activity => `
                <div class="timeline-item">
                    <div class="timeline-icon timeline-${activity.type}">
                        <i class="bi bi-${activity.type === 'completed' ? 'check' : 'x'}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">${activity.title}</div>
                        <div class="timeline-subtitle">${activity.category}</div>
                        <div class="timeline-time">${new Date(activity.time).toLocaleString('pt-BR')}</div>
                    </div>
                </div>
            `).join('');
        }
        
        function checkAlerts(completionRate, pendingActivities) {
            const alertBanner = document.getElementById('alert-banner');
            const alertMessage = document.getElementById('alert-message');
            
            if (completionRate < 70) {
                alertMessage.textContent = `Taxa de conclusão baixa: ${completionRate}%. Meta: 70%`;
                alertBanner.classList.add('show');
            } else if (pendingActivities > 5) {
                alertMessage.textContent = `Muitas atividades pendentes: ${pendingActivities}`;
                alertBanner.classList.add('show');
            } else {
                alertBanner.classList.remove('show');
            }
        }
        
        function startAutoRefresh() {
            // Countdown
            countdownInterval = setInterval(() => {
                countdownValue--;
                document.getElementById('countdown').textContent = countdownValue;
                
                if (countdownValue <= 0) {
                    countdownValue = 120;
                    loadDashboard();
                }
            }, 1000);
        }
        
        function showError(message) {
            console.error(message);
            // Aqui poderia mostrar um toast ou modal de erro
        }
        
        // Limpar intervalos ao sair da página
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) clearInterval(refreshInterval);
            if (countdownInterval) clearInterval(countdownInterval);
        });
    </script>
</body>
</html>

