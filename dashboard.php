<?php
require_once 'config.php';
requireAuth();

// Buscar estatísticas
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$total_categories = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM items");
$total_items = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kamishibai Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/dashboard.css" rel="stylesheet">
    <style>
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php
    include "includes/sidebar.php";
    ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0">Dashboard</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">Bem-vindo ao Painel Administrativo!</h2>
                        <p class="mb-0 opacity-75">
                            Gerencie categorias e itens do sistema Kamishibai de forma fácil e intuitiva.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <i class="bi bi-kanban" style="font-size: 4rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon primary">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h3 class="mb-1"><?= $total_categories ?></h3>
                        <p class="text-muted mb-0">Categorias</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon success">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <h3 class="mb-1"><?= $total_items ?></h3>
                        <p class="text-muted mb-0">Itens</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon warning">
                            <i class="bi bi-clipboard-data"></i>
                        </div>
                        <h3 class="mb-1">0</h3>
                        <p class="text-muted mb-0">Registros</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon info">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="mb-1"><?= $total_users ?></h3>
                        <p class="text-muted mb-0">Usuários</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5 class="mb-3">
                            <i class="bi bi-lightning-charge me-2"></i>
                            Ações Rápidas
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="categories.php?action=create" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>
                                Nova Categoria
                            </a>

                            <button onclick="setFooterStatus(1)" class="btn btn-gradient">
                                <i class="bi bi-toggle-on me-2"></i>
                                Ligar Footer
                            </button>


                            <button onclick="setFooterStatus(0)" class="btn btn-gradient">
                                <i class="bi bi-toggle-off me-2"></i>
                                Desligar Footer
                            </button>

                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5 class="mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Informações do Sistema
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-shield-check text-primary me-2"></i>
                                Autenticação ativa
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-database text-info me-2"></i>
                                Banco de dados conectado
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-toggles2 text-primary me-2"></i>
                                Status do Footer: <span id="statusFooter">a</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onload = function() {
            checkFooterStatus();
        };
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        function setFooterStatus(value) {
            fetch(`../api.php?action=set_footer_status&value=${value}`, {
                method: 'POST'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        checkFooterStatus(); // atualiza o footer na hora
                    } else {
                        alert("Erro: " + data.error);
                    }
                })
                .catch(err => console.error("Erro ao atualizar footer:", err));
        }
        function checkFooterStatus() {
            fetch(`../api.php?action=get_footer_status`)
                .then(response => response.json())
                .then(data => {
                    const footer = document.getElementById('footer-section');
                    const statusText = document.getElementById('statusFooter');

                    if (data.success && Number(data.footer_enabled) === 1) {

                        statusText.textContent = "Ativo";
                        statusText.className = "text-success fw-bold"; // verde
                    } else {
                        statusText.textContent = "Desativado";
                        statusText.className = "text-danger fw-bold"; // vermelho
                    }
                })
                .catch(err => console.error("Erro ao buscar status do footer:", err));
        }

    </script>
</body>
</html>

