<?php
// admin/audit.php — Tela de Auditoria
session_start();
require_once 'config.php';

// ---- Segurança: somente admin ----
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Administrador';

// em vez de usar direto $input['...'], faça:
$input = filter_input_array(INPUT_GET, [
    'start'   => FILTER_SANITIZE_STRING,
    'end'     => FILTER_SANITIZE_STRING,
    'status'  => FILTER_SANITIZE_STRING,
    'cat'     => FILTER_VALIDATE_INT,
    'user'    => FILTER_SANITIZE_STRING,
    'page'    => FILTER_VALIDATE_INT,
    'export'  => FILTER_SANITIZE_STRING,
]) ?? []; // <-- fallback pra []

$start  = $input['start']  ?? date('Y-m-01');
$end    = $input['end']    ?? date('Y-m-d');
$status = $input['status'] ?? '';
$catId  = $input['cat']    ?? null;
$userQ  = trim($input['user'] ?? '');
$page   = max(1, (int)($input['page'] ?? 1));
$export = (($input['export'] ?? '') === 'csv');
$status = in_array($status, ['C','NC','NA',''], true) ? $status : '';


// ---------- Opções auxiliares ----------
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// Carregar categorias p/ select
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ---------- Montar WHERE dinâmico ----------
$where  = [];
$params = [];

$where[] = "date(ar.record_date) BETWEEN :start AND :end";
$params[':start'] = $start;
$params[':end']   = $end;

if ($status) {
    $where[] = "ar.status = :status";
    $params[':status'] = $status;
}
if ($catId) {
    $where[] = "it.category_id = :cat";
    $params[':cat'] = $catId;
}
if ($userQ !== '') {
    // notes armazena quem registrou (username/email)
    $where[] = "ar.notes LIKE :userq";
    $params[':userq'] = "%{$userQ}%";
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ---------- SQL base ----------
$sqlBase = "
    FROM activity_records ar
    JOIN items it       ON it.id = ar.item_id
    LEFT JOIN categories c ON c.id = it.category_id
";

// Seleção principal (com COALESCE para timestamp)
$selectCols = "
    ar.item_id,
    it.description AS item_description,
    c.name AS category_name,
    ar.status,
    ar.notes,
    ar.record_date,
    COALESCE(ar.created_at, ar.record_date) AS ts
";

// ---------- Export CSV ----------
if ($export) {
    $stmt = $pdo->prepare("
        SELECT $selectCols
        $sqlBase
        $whereSql
        ORDER BY ts DESC
        LIMIT 50000
    ");
    $stmt->execute($params);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="auditoria_kamishibai_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output', 'w');
    // BOM para Excel
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, ['Data/Hora', 'Data Registro', 'Categoria', 'Item', 'Status', 'Usuário (notes)', 'Item ID'], ';');

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            date('d/m/Y H:i', strtotime($row['ts'])),
            date('d/m/Y', strtotime($row['record_date'])),
            $row['category_name'] ?: '',
            $row['item_description'] ?: '',
            $row['status'],
            $row['notes'],
            $row['item_id'],
        ], ';');
    }
    fclose($out);
    exit;
}

// ---------- Contagem total ----------
$stmtCount = $pdo->prepare("SELECT COUNT(*) $sqlBase $whereSql");
$stmtCount->execute($params);
$totalRows = (int) $stmtCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// ---------- Buscar página ----------
$stmt = $pdo->prepare("
    SELECT $selectCols
    $sqlBase
    $whereSql
    ORDER BY ts DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// KPIs simples (no período filtrado)
$stmtKpi = $pdo->prepare("
    SELECT
        SUM(CASE WHEN ar.status IN ('C','NA') THEN 1 ELSE 0 END) AS total_ok,
        SUM(CASE WHEN ar.status = 'NC' THEN 1 ELSE 0 END)         AS total_nc,
        COUNT(*) AS total
    $sqlBase
    $whereSql
");
$stmtKpi->execute($params);
$kpi = $stmtKpi->fetch(PDO::FETCH_ASSOC) ?: ['total_ok'=>0,'total_nc'=>0,'total'=>0];
$rate = ($kpi['total'] ?? 0) > 0 ? round(($kpi['total_ok'] / $kpi['total']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria de Dados - Kamishibai Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/audit.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<?php
include "includes/sidebar.php";
?>

<!-- Main -->
<div class="main-content">
    <!-- Topbar -->
    <div class="top-navbar">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Auditoria de Dados</h5>
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

    <!-- Conteúdo -->
    <div class="content-area">
        <!-- Filtros -->
        <div class="stats-card mb-3">
            <form class="row gy-2 gx-2 align-items-end" method="get">
                <div class="col-md-3">
                    <label class="form-label">De</label>
                    <input type="date" class="form-control" name="start" value="<?= htmlspecialchars($start) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Até</label>
                    <input type="date" class="form-control" name="end" value="<?= htmlspecialchars($end) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" name="cat">
                        <option value="">Todas</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($catId==$c['id']?'selected':'') ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        <option value="C"  <?= $status==='C'?'selected':'' ?>>Concluído (C)</option>
                        <option value="NC" <?= $status==='NC'?'selected':'' ?>>Não Concluído (NC)</option>
                        <option value="NA" <?= $status==='NA'?'selected':'' ?>>N/A (NA)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Usuário:</label>
                    <input type="text" class="form-control" name="user" placeholder="Nome de Usuário" value="<?= htmlspecialchars($userQ) ?>">
                </div>
                <div class="col-md-8 d-flex gap-2">
                    <button class="btn btn-success" type="submit"><i class="bi bi-funnel me-2"></i>Aplicar filtros</button>
                    <a class="btn btn-outline-secondary" href="audit.php"><i class="bi bi-x-circle me-2"></i>Limpar</a>
                    <a class="btn btn-outline-primary" href="<?= 'audit.php?'.http_build_query(array_filter(['start'=>$start,'end'=>$end,'cat'=>$catId,'status'=>$status,'user'=>$userQ,'export'=>'csv'])) ?>">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="h2 mb-0 text-success"><?= (int)$kpi['total_ok'] ?></div>
                    <div class="text-muted">Registros OK (C + NA)</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="h2 mb-0 text-danger"><?= (int)$kpi['total_nc'] ?></div>
                    <div class="text-muted">Não Concluídos (NC)</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <div class="h2 mb-0"><?= (int)$kpi['total'] ?></div>
                    <div class="text-muted">Total no período (<?= $rate ?>% concl.)</div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="stats-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th style="min-width:140px">Data/Hora</th>
                        <th>Data</th>
                        <th>Categoria</th>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Usuário</th>
                        <th>ID Item</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Sem dados para os filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['ts']))) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['record_date']))) ?></td>
                                <td><?= htmlspecialchars($r['category_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($r['item_description'] ?: '-') ?></td>
                                <td>
                                    <span class="badge-status badge-<?= htmlspecialchars($r['status']) ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['notes'] ?: '-') ?></td>
                                <td><span class="badge bg-light text-dark"><?= (int)$r['item_id'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php
                        $base = $_GET; unset($base['page']); $baseQuery = http_build_query($base);
                        $mk = function($p) use ($baseQuery){ return 'audit.php?'.$baseQuery.'&page='.$p; };
                        ?>
                        <li class="page-item <?= $page<=1?'disabled':'' ?>">
                            <a class="page-link" href="<?= $mk(max(1,$page-1)) ?>">&laquo;</a>
                        </li>
                        <?php
                        $startP = max(1, $page-2);
                        $endP   = min($totalPages, $page+2);
                        for ($p=$startP; $p<=$endP; $p++): ?>
                            <li class="page-item <?= $p==$page?'active':'' ?>">
                                <a class="page-link" href="<?= $mk($p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                            <a class="page-link" href="<?= $mk(min($totalPages,$page+1)) ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
