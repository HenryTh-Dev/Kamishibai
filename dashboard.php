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
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" width="50px" style="opacity: 0.9;">
            <h4 class="mb-0">
                Kamishibai Admin
            </h4>
            <p class="mb-0 opacity-75 small">Santa Casa de Araçatuba</p>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="bi bi-folder"></i>
                    Categorias
                </a>
            </div>
            <div class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="bi bi-people"></i>
                    Usuários
                </a>
            </div>
            <div class="nav-item">
                <a href="/painel/index.php" class="nav-link">
                    <i class="bi bi-bar-chart-line"></i>
                    Painel
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link">
                    <i class="bi bi-calendar"></i>
                    Relatório Mensal
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link">
                    <i class="bi bi-archive"></i>
                    Auditoria de Dados
                </a>
            </div>
            <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.1);">
            <div class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    Sair
                </a>
            </div>
            <footer class="app-footer mt-auto py-3">
  <div class="container text-center">
    <small>Desenvolvido por <a href="https://github.com/HenryTh-Dev"  target="_blank">Henry Thiago</a> — <a href="https://www.santacasadearacatuba.com.br/" target="_blank">Santa Casa de Araçatuba</a></small>
  </div>
</footer>
        </div>
    </nav>

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
            <!-- Welcome Card -->
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
                            <a href="categories.php" class="btn btn-outline-primary">
                                <i class="bi bi-folder me-2"></i>
                                Gerenciar Categorias
                            </a>
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
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Sistema funcionando normalmente
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-shield-check text-primary me-2"></i>
                                Autenticação ativa
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-database text-info me-2"></i>
                                Banco de dados conectado
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>

