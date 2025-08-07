<?php
// Sidebar principal do Kamishibai Admin
?>
<div class="sidebar-header">
    <img src="../logo.png" width="50px" style="opacity: 0.9;" alt="Logotipo Kamishibai">
    <h4 class="mb-0">Kamishibai Admin</h4>
    <p class="mb-0 opacity-75 small">Santa Casa de Araçatuba</p>
</div>

<div class="sidebar-nav">
    <div class="nav-item">
        <a href="/dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>
    <div class="nav-item">
        <a href="/categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <i class="bi bi-folder"></i> Categorias
        </a>
    </div>
    <div class="nav-item">
        <a href="/users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Usuários
        </a>
    </div>
    <div class="nav-item">
        <a href="/painel/index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i> Painel
        </a>
    </div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-calendar"></i> Relatório Mensal
        </a>
    </div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-archive"></i> Auditoria de Dados
        </a>
    </div>
    <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.1);">
    <div class="nav-item">
        <a href="/logout.php" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> Sair
        </a>
    </div>
</div>

<footer class="app-footer mt-auto py-3">
    <div class="container text-center">
        <?php include __DIR__ . '/footer.php'; ?>
    </div>
</footer>
