<?php
// password.php — Alterar senha da própria conta (enfermeira)

require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// exige login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

// cria token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$userId   = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Usuária(o)';

// carrega dados básicos do usuário (para exibir username)
$stmt = $pdo->prepare("SELECT id, name, username, password, role, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$currentUser) {
    // sessão inválida
    session_destroy();
    header('Location: login.php');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // valida CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Falha de verificação. Atualize a página e tente novamente.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // validações
        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error = 'Preencha todos os campos.';
        } elseif (!password_verify($current_password, $currentUser['password'])) {
            $error = 'Sua senha atual está incorreta.';
        } elseif (strlen($new_password) < 6) {
            $error = 'A nova senha deve ter pelo menos 6 caracteres.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'A confirmação não confere com a nova senha.';
        } elseif (password_verify($new_password, $currentUser['password'])) {
            $error = 'A nova senha não pode ser igual à senha atual.';
        } else {
            // atualiza senha
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($up->execute([$hash, $userId])) {
                $success = 'Senha alterada com sucesso!';
                // gira token CSRF após sucesso
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            } else {
                $error = 'Não foi possível atualizar sua senha. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
Alterar senha - Kamishibai
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="password.css" rel="stylesheet">
</head>
<body>
<!-- Sidebar -->

<nav class="sidebar">
    <div class="sidebar-header">
        <img src="../logo.png" width="50px" style="opacity: 0.8;">
        <h4 class="mb-0">
            Kamishibai Enfermagem
        </h4>
        <p class="mb-0 opacity-75 small">Santa Casa de Araçatuba</p>
    </div>

    <div class="sidebar-nav">
        <div class="nav-item">
            <a href=".." class="nav-link">
                <i class="bi bi-kanban"></i>
                Kanban
            </a>
            <a href="password.php" class="nav-link">
                <i class="bi bi-people"></i>
                Alterar Senha
            </a>
            <a href="export.php" class="nav-link">
                <i class="bi bi-calendar"></i>
                Relatório Mensal
            </a>
        </div>
        <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.1);">
        <div class="nav-item">
            <a href="../logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i>
                Sair
            </a>
        </div>
        <footer class="app-footer mt-auto py-3">
            <div class="container text-center">
                <small>Desenvolvido por <a href="https://www.santacasadearacatuba.com.br/" target="_blank">Tecnologia da Informação Santa Casa de Araçatuba</a></small>
            </div>
        </footer>
    </div>
</nav>
<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="d-flex align-items-center">
            <h5 class="mb-0">
                Alterar senha
            </h5>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="../logout.php">
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
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="stats-card">
                    <div class="mb-3">
                        <div class="small text-muted">Usuária(o)</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($currentUser['name'] ?? $userName) ?>
                        </div>
                    </div>

                    <form method="POST" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock me-2"></i> Senha atual
                            </label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-shield me-2"></i> Nova senha
                            </label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                            <div class="form-text">Mínimo de 6 caracteres.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-shield-check me-2"></i> Confirmar nova senha
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-check-circle me-2"></i> Salvar nova senha
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">
                    <div class="small text-muted">
                        Por segurança, evite reutilizar senhas e não compartilhe suas credenciais.
                    </div>
                </div>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
