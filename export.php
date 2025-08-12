<?php
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', '1');
}
ini_set('zlib.output_compression', 'Off');

require_once 'config.php';
requireAuth();

$error  = '';
$period = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $period = $_POST['period'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
        $error = 'Formato inválido. Use AAAA-MM, ex: 2025-08';
    } else {
        // Chama o script Python
        $python = '/usr/bin/python3';
        $script = '/var/www/html/kamishibai/script.py';
        $cmd    = escapeshellcmd("$python $script " . escapeshellarg($period));
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            $error = 'Erro ao gerar o relatório:<br>' . implode("<br>", $output);
        } else {
            $filePath = '';
            foreach ($output as $line) {
                if (strpos($line, 'Resumo gerado:') !== false) {
                    $filePath = trim(substr($line, strpos($line, ':') + 1));
                    break;
                }
            }
            if (!$filePath || !file_exists($filePath)) {
                $error = 'Arquivo não encontrado: ' . htmlspecialchars($filePath);
            } else {
                // Limpa todos os buffers de saída
                while (ob_get_level()) {
                    ob_end_clean();
                }

                // Headers para download binário
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));

                // Envia o arquivo em blocos
                $fp = fopen($filePath, 'rb');
                if ($fp) {
                    while (!feof($fp)) {
                        echo fread($fp, 8192);
                    }
                    fclose($fp);
                }
                exit;
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório Mensal – Kamishibai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/export.css" rel="stylesheet">
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
            <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h5 class="mb-0">Relatório Mensal</h5>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-3">Bem-vindo, <?= htmlspecialchars($_SESSION['user_name']); ?>!</span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-area">
        <div class="export-card">
            <h1>Exportar Resumo Kamishibai</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="period" class="form-label">Mês de referência</label>
                    <input
                            type="month"
                            class="form-control"
                            id="period"
                            name="period"
                            required
                            value="<?= htmlspecialchars($period); ?>"
                    >
                    <div class="form-text">Formato: AAAA-MM (ex: 2025-08)</div>
                </div>
                <button type="submit" class="btn btn-gradient">Gerar e Baixar</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('show');
    });
</script>
</body>
</html>
