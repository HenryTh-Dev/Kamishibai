<?php
// Top navbar do Kamishibai Admin
// $pageTitle pode ser definido antes do include para exibir outro título
?>
<div class="d-flex align-items-center">
    <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    <h5 class="mb-0"><?= htmlspecialchars($pageTitle ?? 'Painel') ?></h5>
</div>

<div class="d-flex align-items-center">
    <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Sair
                </a>
            </li>
        </ul>
    </div>
</div>
