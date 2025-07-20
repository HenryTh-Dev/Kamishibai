<?php
require_once 'config.php';
requireAuth();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$category_id = $_GET['category_id'] ?? null;
$success = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' && $category_id) {
        $description = trim($_POST['description'] ?? '');
        $order_num = $_POST['order_num'] ?? null;
        
        if (empty($description)) {
            $error = 'A descrição do item é obrigatória.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO items (category_id, description, order_num) VALUES (?, ?, ?)");
            if ($stmt->execute([$category_id, $description, $order_num ?: null])) {
                $success = 'Item criado com sucesso!';
                redirect("categories.php?action=view&id=$category_id");
            } else {
                $error = 'Erro ao criar item.';
            }
        }
    } elseif ($action === 'edit' && $id) {
        $description = trim($_POST['description'] ?? '');
        $order_num = $_POST['order_num'] ?? null;
        
        if (empty($description)) {
            $error = 'A descrição do item é obrigatória.';
        } else {
            $stmt = $pdo->prepare("UPDATE items SET description = ?, order_num = ? WHERE id = ?");
            if ($stmt->execute([$description, $order_num ?: null, $id])) {
                $success = 'Item atualizado com sucesso!';
                // Buscar category_id para redirect
                $stmt = $pdo->prepare("SELECT category_id FROM items WHERE id = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($item) {
                    redirect("categories.php?action=view&id=" . $item['category_id']);
                }
            } else {
                $error = 'Erro ao atualizar item.';
            }
        }
    }
}

// Deletar item
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT category_id FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Item excluído com sucesso!';
        } else {
            $error = 'Erro ao excluir item.';
        }
        redirect("categories.php?action=view&id=" . $item['category_id']);
    }
}

// Buscar dados
if ($action === 'create' && $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        redirect('categories.php');
    }
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM items i JOIN categories c ON i.category_id = c.id WHERE i.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
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
        <?= $action === 'create' ? 'Novo Item' : 'Editar Item' ?> - Kamishibai Admin
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
                <h5 class="mb-0"><?= $action === 'create' ? 'Novo Item' : 'Editar Item' ?></h5>
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="bi bi-<?= $action === 'create' ? 'plus-circle' : 'pencil' ?> me-2"></i>
                        <?= $action === 'create' ? 'Novo Item' : 'Editar Item' ?>
                    </h2>
                    <p class="text-muted mb-0">
                        Categoria: <strong>
                            <?= htmlspecialchars($action === 'create' ? $category['name'] : $item['category_name']) ?>
                        </strong>
                    </p>
                </div>
                <a href="<?= $action === 'create' ? "categories.php?action=view&id=$category_id" : "categories.php?action=view&id=" . $item['category_id'] ?>" 
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="stats-card">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="bi bi-list-check me-2"></i>
                                    Descrição do Item *
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Ex: Lavar as mãos com água e sabão por pelo menos 20 segundos"
                                          required><?= htmlspecialchars($item['description'] ?? $_POST['description'] ?? '') ?></textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Seja específico e claro sobre o que deve ser verificado.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="order_num" class="form-label">
                                    <i class="bi bi-sort-numeric-up me-2"></i>
                                    Ordem de Exibição
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="order_num" 
                                       name="order_num" 
                                       value="<?= htmlspecialchars($item['order_num'] ?? $_POST['order_num'] ?? '') ?>" 
                                       min="1"
                                       placeholder="1">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Define a ordem em que este item aparecerá na lista (opcional).
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-gradient">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?= $action === 'create' ? 'Criar Item' : 'Salvar Alterações' ?>
                                </button>
                                <a href="<?= $action === 'create' ? "categories.php?action=view&id=$category_id" : "categories.php?action=view&id=" . $item['category_id'] ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Category Info -->
                    <div class="stats-card mt-4">
                        <h6 class="mb-3">
                            <i class="bi bi-folder me-2"></i>
                            Sobre a Categoria
                        </h6>
                        <h5 class="mb-2"><?= htmlspecialchars($action === 'create' ? $category['name'] : $item['category_name']) ?></h5>
                        <?php if ($action === 'create' && $category['description']): ?>
                            <p class="text-muted mb-2"><?= htmlspecialchars($category['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

