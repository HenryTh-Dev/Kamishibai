<?php
session_start();

// Configuração do banco de dados
$db_file = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabelas se não existirem
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(10) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            description VARCHAR(255) NOT NULL,
            order_num INTEGER NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        );
    ");
    
    // Criar usuário admin se não existir
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['admin@kamishibai.com']);
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@kamishibai.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    }
    
} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

// Funções auxiliares
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    // Se for enfermeira tentando acessar área admin, redirecionar
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'nurse') {
        header('Location: nurse/');
        exit;
    }
    
    // Se não for admin, negar acesso
    if (!isAdmin()) {
        session_destroy();
        header('Location: login.php?error=access_denied');
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>

