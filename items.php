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
    <link href="/css/items.css" rel="stylesheet">
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
                <h5 class="mb-0"><?= $action === 'create' ? 'Novo Item' : 'Editar Item' ?></h5>
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
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

