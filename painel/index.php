<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Atividades - Kamishibai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="painel.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <img src="../logo.png" width="70px" style="opacity: 0.9;">
                        Painel de Atividades Kamishibai
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Acompanhe o progresso das atividades em tempo real</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <span id="current-month-display" style="visibility: hidden;  ">Carregando...</span>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center">
                                <input type="month" id="month-selector" class="form-control mx-2" style="max-width: 200px;" onchange="loadDashboard()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Seletor de Mês -->

        <!-- Loading -->
        <div id="loading" class="loading">
            <div class="loading-spinner"></div>
            <h5>Carregando dados...</h5>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboard-content" style="display: none;">
            <!-- Estatísticas Gerais -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div  class="stat-number" id="total-na" style="color: #ffd34f;" >0</div>
                        <div class="stat-label" >Não Aplica</div>
                        <i class="bi bi-dash-circle-fill" style="font-size: 2rem; color: #ffd34f;"></i>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stat-number text-success" id="total-completed">0</div>
                        <div class="stat-label">Concluídas</div>
                        <i class="bi bi-check-circle-fill" style="font-size: 2rem; color: var(--success-color);"></i>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stat-number text-danger" id="total-not-completed">0</div>
                        <div class="stat-label">Não Concluídas</div>
                        <i class="bi bi-x-circle-fill" style="font-size: 2rem; color: var(--danger-color);"></i>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card text-center">
                        <div class="stat-number text-info" id="completion-percentage">0%</div>
                        <div class="stat-label">Taxa de Conclusão</div>
                        <i class="bi bi-percent" style="font-size: 2rem; color: var(--info-color);"></i>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5 class="mb-3">
                            <i class="bi bi-pie-chart-fill me-2"></i>
                            Progresso Geral
                        </h5>
                        <div class="chart-container">
                            <canvas id="general-chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5 class="mb-3">
                            <i class="bi bi-bar-chart-fill me-2"></i>
                            Progresso por Categoria
                        </h5>
                        <div class="chart-container">
                            <canvas id="category-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Última Atualização -->
        <div class="last-update" id="last-update">
            Última atualização: <span id="update-time">--</span>
        </div>
    </div>

    <!-- Botão de Refresh -->
    <button class="refresh-btn" onclick="loadDashboard()" title="Atualizar dados">
        <i class="bi bi-arrow-clockwise"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let generalChart = null;
        let categoryChart = null;
        
        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            // Definir mês atual
            const now = new Date();
            const currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById('month-selector').value = currentMonth;
            
            // Carregar dados
            loadDashboard();
            
            // Auto-refresh a cada 5 minutos
            setInterval(loadDashboard, 300000);
        });
        
        function changeMonth(direction) {
            const monthSelector = document.getElementById('month-selector');
            const currentDate = new Date(monthSelector.value + '-01');
            currentDate.setMonth(currentDate.getMonth() + direction);
            
            const newMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
            monthSelector.value = newMonth;
            loadDashboard();
        }
        
        function loadDashboard() {
            const month = document.getElementById('month-selector').value;
            
            // Mostrar loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('dashboard-content').style.display = 'none';
            
            // Atualizar display do mês
            const monthNames = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];
            const [year, monthNum] = month.split('-');
            document.getElementById('current-month-display').textContent = 
                monthNames[parseInt(monthNum) - 1] + ' de ' + year;
            
            // Buscar dados da API
            fetch(`../api.php?action=dashboard_data&month=${month}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboard(data);
                    } else {
                        console.error('Erro na API:', data.error);
                        showError('Erro ao carregar dados: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showError('Erro de conexão com o servidor');
                })
                .finally(() => {
                    // Esconder loading
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('dashboard-content').style.display = 'block';
                    
                    // Atualizar timestamp
                    document.getElementById('update-time').textContent = new Date().toLocaleString('pt-BR');
                });
        }
        
        function updateDashboard(data) {
            // Atualizar estatísticas gerais
            document.getElementById('total-na').textContent = data.totals.total_na;
            document.getElementById('total-completed').textContent = data.totals.total_completed;
            document.getElementById('total-not-completed').textContent = data.totals.total_not_completed;
            document.getElementById('completion-percentage').textContent = data.totals.completion_percentage + '%';
            
            // Atualizar gráfico geral
            updateGeneralChart(data.totals);
            
            // Atualizar gráfico por categoria
            updateCategoryChart(data.categories);
            
            // Atualizar detalhes das categorias
            updateCategoriesDetails(data.categories);
        }
        
        function updateGeneralChart(totals) {
            const ctx = document.getElementById('general-chart').getContext('2d');
            
            if (generalChart) {
                generalChart.destroy();
            }
            
            generalChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Concluídas', 'Não Concluídas', 'Não Aplica'],
                    datasets: [{
                        data: [totals.total_completed, totals.total_not_completed, totals.total_na],
                        backgroundColor: [
                            '#28a745',
                            '#dc3545',
                            '#ffd34f'
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

            const labels = categories.map(c => c.description);
            const completedData = categories.map(cat => cat.completed_records);
            const notCompletedData = categories.map(cat => cat.not_completed_records);
            const naData           = categories.map(c => c.na_records);
            
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
                        },
                        {
                            label: 'Não Aplica',
                            data: naData,
                            backgroundColor: '#ffd34f',
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
        
        function updateCategoriesDetails(categories) {
            const container = document.getElementById('categories-details');
            container.innerHTML = '';
            
            categories.forEach(category => {
                const categoryCard = document.createElement('div');
                categoryCard.className = 'category-card';

                categoryCard.innerHTML = `
           <div class="row align-items-center">
               <div class="col-md-6">
                  <!-- usa description como título -->
                   <h6 class="mb-1">${category.description || 'Sem descrição'}</h6>
               </div>
                        <div class="col-md-2 text-center">
                            <div class="stat-number" style="font-size: 1.5rem; color: #28a745;">${category.completed_records}</div>
                            <small class="text-muted">Concluídas</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="stat-number" style="font-size: 1.5rem; color: #dc3545;">${category.not_completed_records}</div>
                            <small class="text-muted">Não Concluídas</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="stat-number" style="font-size: 1.5rem; color: #17a2b8;">${category.completion_percentage}%</div>
                            <small class="text-muted">Taxa</small>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: ${category.completion_percentage}%" 
                             aria-valuenow="${category.completion_percentage}" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                `;
                
                container.appendChild(categoryCard);
            });
            
            if (categories.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="mt-3 text-muted">Nenhuma categoria encontrada</h5>
                        <p class="text-muted">Não há dados para o período selecionado.</p>
                    </div>
                `;
            }
        }
        
        function showError(message) {
            const container = document.getElementById('categories-details');
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }
    </script>
</body>
</html>

