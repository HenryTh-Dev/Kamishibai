<?php
require_once 'config.php';
requireAuth();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$success = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Todos os campos são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'E-mail inválido.';
        } elseif (strlen($password) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            // Verificar se e-mail já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Este e-mail já está cadastrado.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role])) {
                    $success = 'Usuário criado com sucesso!';
                    $action = 'list';
                } else {
                    $error = 'Erro ao criar usuário.';
                }
            }
        }
    } elseif ($action === 'edit' && $id) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if (empty($name) || empty($email)) {
            $error = 'Nome e e-mail são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'E-mail inválido.';
        } else {
            // Verificar se e-mail já existe para outro usuário
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Este e-mail já está cadastrado para outro usuário.';
            } else {
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'A senha deve ter pelo menos 6 caracteres.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
                        $result = $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $email, $role, $id]);
                }
                
                if (!$error && $result) {
                    $success = 'Usuário atualizado com sucesso!';
                    $action = 'list';
                } elseif (!$error) {
                    $error = 'Erro ao atualizar usuário.';
                }
            }
        }
    }
}

// Deletar usuário
if ($action === 'delete' && $id) {
    // Não permitir deletar o próprio usuário
    if ($id == $_SESSION['user_id']) {
        $error = 'Você não pode excluir sua própria conta.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Usuário excluído com sucesso!';
        } else {
            $error = 'Erro ao excluir usuário.';
        }
    }
    $action = 'list';
}

// Buscar dados
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        redirect('users.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        switch($action) {
            case 'create': echo 'Novo Usuário'; break;
            case 'edit': echo 'Editar Usuário'; break;
            default: echo 'Usuários';
        }
        ?> - Kamishibai Admin
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-width: 280px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--primary-gradient);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
        }
        
        .role-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .role-nurse {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .role-user {
            background: #e9ecef;
            color: #495057;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                <i class="bi bi-kanban me-2"></i>
                Kamishibai Admin
            </h4>
            <p class="mb-0 opacity-75 small">Sistema de Gestão</p>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link">
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
                <a href="users.php" class="nav-link active">
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
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <h5 class="mb-0">
                    <?php 
                    switch($action) {
                        case 'create': echo 'Novo Usuário'; break;
                        case 'edit': echo 'Editar Usuário'; break;
                        default: echo 'Usuários';
                    }
                    ?>
                </h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
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

            <?php if ($action === 'list'): ?>
                <!-- Lista de Usuários -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-people me-2"></i>
                        Gerenciar Usuários
                    </h2>
                    <a href="users.php?action=create" class="btn btn-gradient">
                        <i class="bi bi-person-plus me-2"></i>
                        Novo Usuário
                    </a>
                </div>

                <?php if (!empty($users)): ?>
                    <div class="stats-card">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="60">#</th>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th width="150">Perfil</th>
                                        <th width="120">Criado em</th>
                                        <th width="120">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($users as $index => $user): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $user['id'] ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($user['name']) ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <small class="text-muted">(Você)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?php 
                                                $roleConfig = [
                                                    'admin' => ['class' => 'role-admin', 'icon' => 'shield-check', 'label' => 'Admin'],
                                                    'nurse' => ['class' => 'role-nurse', 'icon' => 'heart-pulse', 'label' => 'Enfermeira']
                                                ];
                                                $config = $roleConfig[$user['role']] ?? $roleConfig['nurse'];
                                                ?>
                                                <span class="role-badge <?= $config['class'] ?>">
                                                    <i class="bi bi-<?= $config['icon'] ?> me-1"></i>
                                                    <?= $config['label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="users.php?action=edit&id=<?= $user['id'] ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="users.php?action=delete&id=<?= $user['id'] ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Excluir"
                                                           onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3 text-muted">Nenhum usuário encontrado</h4>
                        <p class="text-muted">Comece criando o primeiro usuário do sistema.</p>
                        <a href="users.php?action=create" class="btn btn-gradient">
                            <i class="bi bi-person-plus me-2"></i>
                            Criar Primeiro Usuário
                        </a>
                    </div>
                <?php endif; ?>

            <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <!-- Formulário de Usuário -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-<?= $action === 'create' ? 'person-plus' : 'pencil' ?> me-2"></i>
                        <?= $action === 'create' ? 'Novo Usuário' : 'Editar Usuário' ?>
                    </h2>
                    <a href="users.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar
                    </a>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="stats-card">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                <i class="bi bi-person me-2"></i>
                                                Nome Completo *
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="name" 
                                                   name="name" 
                                                   value="<?= htmlspecialchars($user['name'] ?? $_POST['name'] ?? '') ?>" 
                                                   placeholder="Ex: João Silva"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="bi bi-envelope me-2"></i>
                                                E-mail *
                                            </label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '') ?>" 
                                                   placeholder="Ex: joao@exemplo.com"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">
                                                <i class="bi bi-lock me-2"></i>
                                                Senha <?= $action === 'edit' ? '(deixe em branco para manter)' : '*' ?>
                                            </label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Mínimo 6 caracteres"
                                                   <?= $action === 'create' ? 'required' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">
                                                <i class="bi bi-shield me-2"></i>
                                                Perfil de Acesso *
                                            </label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">Selecione o perfil</option>
                                                <option value="nurse" <?= ($user['role'] ?? $_POST['role'] ?? '') === 'nurse' ? 'selected' : '' ?>>
                                                    Enfermeira
                                                </option>
                                                <option value="admin" <?= ($user['role'] ?? $_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                                    Administrador
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Perfis de Acesso:</strong><br>
                                    <strong>Enfermeira:</strong> Acesso para registro de atividades diárias via tablet.<br>
                                    <strong>Administrador:</strong> Acesso completo para gerenciar categorias, itens e usuários.
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-gradient">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <?= $action === 'create' ? 'Criar Usuário' : 'Salvar Alterações' ?>
                                    </button>
                                    <a href="users.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

