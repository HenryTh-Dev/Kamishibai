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
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $error = 'O nome da categoria é obrigatório.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                $success = 'Categoria criada com sucesso!';
                $action = 'list';
            } else {
                $error = 'Erro ao criar categoria.';
            }
        }
    } elseif ($action === 'edit' && $id) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $error = 'O nome da categoria é obrigatório.';
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                $success = 'Categoria atualizada com sucesso!';
                $action = 'list';
            } else {
                $error = 'Erro ao atualizar categoria.';
            }
        }
    }
}

// Deletar categoria
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Categoria excluída com sucesso!';
    } else {
        $error = 'Erro ao excluir categoria.';
    }
    $action = 'list';
}

// Buscar dados
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(i.id) as item_count 
        FROM categories c 
        LEFT JOIN items i ON c.id = i.category_id 
        GROUP BY c.id 
        ORDER BY c.created_at DESC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        redirect('categories.php');
    }
} elseif ($action === 'view' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM items WHERE category_id = ? ORDER BY order_num ASC, id ASC");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$category) {
        redirect('categories.php');
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
            case 'create': echo 'Nova Categoria'; break;
            case 'edit': echo 'Editar Categoria'; break;
            case 'view': echo $category['name'] ?? 'Categoria'; break;
            default: echo 'Categorias';
        }
        ?> - Kamishibai Admin
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/categories.css" rel="stylesheet">
    <style>
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
                <a href="categories.php" class="nav-link active">
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
                        case 'create': echo 'Nova Categoria'; break;
                        case 'edit': echo 'Editar Categoria'; break;
                        case 'view': echo $category['name'] ?? 'Categoria'; break;
                        default: echo 'Categorias';
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
                <!-- Lista de Categorias -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-folder me-2"></i>
                        Gerenciar Categorias
                    </h2>
                    <a href="categories.php?action=create" class="btn btn-gradient">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nova Categoria
                    </a>
                </div>

                <?php if (!empty($categories)): ?>
                    <div class="row g-4">
                        <?php foreach($categories as $category): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="stats-card h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="mb-0"><?= htmlspecialchars($category['name']) ?></h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="categories.php?action=view&id=<?= $category['id'] ?>">
                                                        <i class="bi bi-eye me-2"></i>Visualizar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="categories.php?action=edit&id=<?= $category['id'] ?>">
                                                        <i class="bi bi-pencil me-2"></i>Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="categories.php?action=delete&id=<?= $category['id'] ?>"
                                                       onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                                        <i class="bi bi-trash me-2"></i>Excluir
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <?php if ($category['description']): ?>
                                        <p class="text-muted mb-3"><?= htmlspecialchars(substr($category['description'], 0, 100)) ?><?= strlen($category['description']) > 100 ? '...' : '' ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">
                                            <?= $category['item_count'] ?> 
                                            <?= $category['item_count'] == 1 ? 'item' : 'itens' ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($category['created_at'])) ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <a href="categories.php?action=view&id=<?= $category['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="bi bi-arrow-right me-2"></i>
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3 text-muted">Nenhuma categoria encontrada</h4>
                        <p class="text-muted">Comece criando sua primeira categoria de cuidados.</p>
                        <a href="categories.php?action=create" class="btn btn-gradient">
                            <i class="bi bi-plus-circle me-2"></i>
                            Criar Primeira Categoria
                        </a>
                    </div>
                <?php endif; ?>

            <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <!-- Formulário de Categoria -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-<?= $action === 'create' ? 'plus-circle' : 'pencil' ?> me-2"></i>
                        <?= $action === 'create' ? 'Nova Categoria' : 'Editar Categoria' ?>
                    </h2>
                    <a href="categories.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar
                    </a>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="stats-card">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-tag me-2"></i>
                                        Nome da Categoria *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= htmlspecialchars($category['name'] ?? $_POST['name'] ?? '') ?>" 
                                           placeholder="Ex: Higienização das Mãos"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">
                                        <i class="bi bi-text-paragraph me-2"></i>
                                        Abreviação
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="1"
                                              placeholder="Abrevie a Categoria para o Painel"><?= htmlspecialchars($category['description'] ?? $_POST['description'] ?? '') ?></textarea>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        A descrição ajuda a contextualizar a categoria para os usuários.
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-gradient">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <?= $action === 'create' ? 'Criar Categoria' : 'Salvar Alterações' ?>
                                    </button>
                                    <a href="categories.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php elseif ($action === 'view'): ?>
                <!-- Visualizar Categoria -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>
                            <i class="bi bi-folder me-2"></i>
                            <?= htmlspecialchars($category['name']) ?>
                        </h2>
                        <?php if ($category['description']): ?>
                            <p class="text-muted mb-0"><?= htmlspecialchars($category['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="items.php?action=create&category_id=<?= $category['id'] ?>" class="btn btn-gradient">
                            <i class="bi bi-plus-circle me-2"></i>
                            Novo Item
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="categories.php?action=edit&id=<?= $category['id'] ?>">
                                        <i class="bi bi-pencil me-2"></i>Editar Categoria
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="categories.php?action=delete&id=<?= $category['id'] ?>"
                                       onclick="return confirm('Tem certeza que deseja excluir esta categoria e todos os seus itens?')">
                                        <i class="bi bi-trash me-2"></i>Excluir Categoria
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <a href="categories.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>

                <!-- Category Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-icon primary mx-auto">
                                <i class="bi bi-list-check"></i>
                            </div>
                            <h3 class="mb-1"><?= count($items) ?></h3>
                            <p class="text-muted mb-0"><?= count($items) == 1 ? 'Item' : 'Itens' ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <div class="stats-icon success mx-auto">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h3 class="mb-1"><?= date('d/m/Y', strtotime($category['created_at'])) ?></h3>
                            <p class="text-muted mb-0">Data de Criação</p>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Itens de Verificação
                        </h5>
                        <?php if (!empty($items)): ?>
                            <span class="badge bg-primary"><?= count($items) ?> <?= count($items) == 1 ? 'item' : 'itens' ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($items)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="60">#</th>
                                        <th>Descrição</th>
                                        <th width="100">Ordem</th>
                                        <th width="120">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $index => $item): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark"><?= $index + 1 ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($item['description']) ?></td>
                                            <td>
                                                <?php if ($item['order_num']): ?>
                                                    <span class="badge bg-secondary"><?= $item['order_num'] ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="items.php?action=edit&id=<?= $item['id'] ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="items.php?action=delete&id=<?= $item['id'] ?>&category_id=<?= $category['id'] ?>" 
                                                       class="btn btn-outline-danger" 
                                                       title="Excluir"
                                                       onclick="return confirm('Tem certeza que deseja excluir este item?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-list-ul" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">Nenhum item cadastrado</h5>
                            <p class="text-muted">Adicione itens de verificação para esta categoria.</p>
                            <a href="items.php?action=create&category_id=<?= $category['id'] ?>" class="btn btn-gradient">
                                <i class="bi bi-plus-circle me-2"></i>
                                Adicionar Primeiro Item
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

