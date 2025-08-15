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
        $name     = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';

        if (empty($name) || empty($username) || empty($password)) {
            $error = 'Todos os campos são obrigatórios.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            $error = 'O nome de usuário deve ter 3–50 caracteres e usar apenas letras, números, ponto, traço e underline.';
        } elseif (strlen($password) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            // Verificar se username já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = ?");
            $stmt->execute([$username]);

            if ($stmt->fetchColumn() > 0) {
                $error = 'Este nome de usuário já está cadastrado.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role])) {
                    $success = 'Usuário criado com sucesso!';
                    $action = 'list';
                } else {
                    $error = 'Erro ao criar usuário.';
                }
            }
        }
    } elseif ($action === 'edit' && $id) {
        $name     = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';

        if (empty($name) || empty($username)) {
            $error = 'Nome e usuário são obrigatórios.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            $error = 'O nome de usuário deve ter 3–50 caracteres e usar apenas letras, números, ponto, traço e underline.';
        } else {
            // Verificar se username já existe para outro usuário
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = ? AND id != ?");
            $stmt->execute([$username, $id]);

            if ($stmt->fetchColumn() > 0) {
                $error = 'Este nome de usuário já está cadastrado para outro usuário.';
            } else {
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'A senha deve ter pelo menos 6 caracteres.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, password = ?, role = ? WHERE id = ?");
                        $result = $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role, $id]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, role = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $username, $role, $id]);
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
// Ativar/Desativar usuário
if ($action === 'toggle_active' && $id) {
    if ($id == $_SESSION['user_id']) {
        $error = 'Você não pode desativar/reativar a própria conta enquanto estiver logado.';
    } else {
        // pega status atual
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        if ($current === false) {
            $error = 'Usuário não encontrado.';
        } else {
            $nowBr = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
            $actor = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $_SESSION['user_name'] ?? 'desconhecido';

            if ((int)$current === 1) {
                // desativar
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET is_active = 0,
                        deactivated_at = ?,
                        deactivated_by = ?
                    WHERE id = ?
                ");
                $ok = $stmt->execute([$nowBr, $actor, $id]);
                $success = $ok ? 'Usuário desativado.' : 'Erro ao desativar usuário.';
            } else {
                // reativar
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET is_active = 1,
                        deactivated_at = NULL,
                        deactivated_by = NULL
                    WHERE id = ?
                ");
                $ok = $stmt->execute([$id]);
                $success = $ok ? 'Usuário reativado.' : 'Erro ao reativar usuário.';
            }
        }
    }
    header("Location: users.php");
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
    <link href="/css/users.css" rel="stylesheet">
</head>
<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirmar ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage" class="mb-0">Tem certeza?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-gradient" id="confirmModalYes">
                    <i class="bi bi-check-circle me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<body>
<!-- Sidebar -->
<?php include "includes/sidebar.php"; ?>

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
                                <th>Usuário</th>
                                <th width="150">Perfil</th>
                                <th width="120">Status</th>
                                <th width="120">Criado em</th>
                                <th width="200">Ações</th>
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
                                    <td><?= htmlspecialchars($user['username']) ?></td>
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
                                        <?php if ((int)$user['is_active'] === 1): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Ativo</span>
                                        <?php else: ?>
                                            <?php if (!empty($user['deactivated_by'])): ?>
                                            <span class="text-muted"><span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i>Inativo por <?= htmlspecialchars($user['deactivated_by']) ?><br><?= htmlspecialchars($user['deactivated_at']) ?></span></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
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
                                                <?php if ((int)$user['is_active'] === 1): ?>
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            title="Desativar"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmModal"
                                                            data-title="Desativar usuário"
                                                            data-message="Desativar este usuário? Ele não poderá acessar o sistema."
                                                            data-href="users.php?action=toggle_active&id=<?= $user['id'] ?>">
                                                        <i class="bi bi-pause-circle"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-outline-success"
                                                            title="Reativar"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#confirmModal"
                                                            data-title="Reativar usuário"
                                                            data-message="Reativar este usuário?"
                                                            data-href="users.php?action=toggle_active&id=<?= $user['id'] ?>">
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="Excluir"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmModal"
                                                        data-title="Excluir usuário"
                                                        data-message="Tem certeza que deseja excluir este usuário? Esta ação é irreversível."
                                                        data-href="users.php?action=delete&id=<?= $user['id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                                        <label for="username" class="form-label">
                                            <i class="bi bi-person-badge me-2"></i>
                                            Usuário *
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="username"
                                               name="username"
                                               value="<?= htmlspecialchars($user['username'] ?? $_POST['username'] ?? '') ?>"
                                               placeholder="Ex: joao.silva"
                                               required>
                                        <div class="form-text">
                                            Use 3–50 caracteres (letras, números, ponto, traço e underline).
                                        </div>
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
<script>
    (function(){
        const modalEl = document.getElementById('confirmModal');
        let targetHref = null;

        modalEl.addEventListener('show.bs.modal', function (event) {
            const button   = event.relatedTarget;
            const title    = button.getAttribute('data-title')   || 'Confirmar ação';
            const message  = button.getAttribute('data-message') || 'Tem certeza?';
            targetHref     = button.getAttribute('data-href');

            modalEl.querySelector('#confirmModalTitle').textContent = title;
            modalEl.querySelector('#confirmModalMessage').textContent = message;
        });

        document.getElementById('confirmModalYes').addEventListener('click', function(){
            if (targetHref) {
                window.location.href = targetHref; // mantém sua lógica via GET
            }
        });
    })();
</script>

</body>
</html>
