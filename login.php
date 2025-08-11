<?php
require_once 'config.php';

$error = '';
$success = '';

// Verificar se usuário já está logado e redirecionar baseado no perfil
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'nurse') {
        header('Location: nurse/');
        exit;
    } elseif ($_SESSION['user_role'] === 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}

// Verificar se há mensagem de erro
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'access_denied':
            $error = 'Acesso negado. Você não tem permissão para acessar esta área.';
            break;
        default:
            $error = 'Erro desconhecido.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Redirecionar baseado no perfil do usuário
            if ($user['role'] === 'nurse') {
                header('Location: nurse/');
            } elseif ($user['role'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Nome de usuário ou senha incorretos.';
        }
    }
}

if (isset($_GET['logout'])) {
    $success = 'Logout realizado com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kamishibai Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/login.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="login-card">
                <div class="login-header">
                    <img src="logo-completa.png" width="150vmin" style="opacity: 0.9;">
                    <h3 class="mb-0">Kamishibai Logon</h3>
                    <p class="mb-0 opacity-75">Área Administrativa</p>
                </div>

                <div class="login-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group">
                            <i class="bi bi-person"></i>
                            <input type="text"
                                   class="form-control"
                                   name="username"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   placeholder="Nome de usuário"
                                   required
                                   autofocus>
                        </div>

                        <div class="input-group">
                            <i class="bi bi-lock"></i>
                            <input type="password"
                                   class="form-control"
                                   name="password"
                                   placeholder="Senha"
                                   required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Entrar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
