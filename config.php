<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Caminho do banco
$db_file = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Habilita FKs no SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');

    // --------- Funções auxiliares de migração ----------
    $hasColumn = function(PDO $pdo, string $table, string $column): bool {
        $stmt = $pdo->prepare("PRAGMA table_info($table)");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if (strcasecmp($col['name'], $column) === 0) return true;
        }
        return false;
    };


    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(10) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS status (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            footer_enabled INTEGER DEFAULT 1 
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

        CREATE TABLE IF NOT EXISTS activity_records (
            item_id      INTEGER    NOT NULL,
            record_date  TEXT       NOT NULL,
            status       VARCHAR(10) NOT NULL,
            notes        TEXT       NULL,
            created_at   TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (item_id, record_date),
            FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
        );
    ");

    // --------- Migração: se existir 'email' e não existir 'username' em users, migra ----------
    if ($hasColumn($pdo, 'users', 'email') && !$hasColumn($pdo, 'users', 'username')) {
        $pdo->beginTransaction();
        try {
            // Cria tabela nova com username
            $pdo->exec("
                CREATE TABLE users_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(10) DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ");

            $count = $pdo->query("SELECT COUNT(*) FROM status")->fetchColumn();
            if ($count == 0) {
                    $pdo->exec("INSERT INTO status (footer_enabled) VALUES (1)");
            }

            // Copia dados: usa o email antigo como username
            $pdo->exec("
                INSERT INTO users_new (id, name, username, password, role, created_at)
                SELECT id, name, email AS username, password, role, created_at
                FROM users;
            ");


            // Troca as tabelas
            $pdo->exec("DROP TABLE users;");
            $pdo->exec("ALTER TABLE users_new RENAME TO users;");

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // --------- Semente admin (por username) ----------
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ((int)$stmt->fetchColumn() === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'Administrador',
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin'
        ]);
    }

} catch (PDOException $e) {
    die('Erro na conexão com o banco de dados: ' . $e->getMessage());
}

// ---------------- Funções auxiliares ----------------
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
