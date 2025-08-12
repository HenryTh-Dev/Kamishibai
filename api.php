<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');



ini_set('display_errors', 0);
ini_set('log_errors',     1);
error_reporting(E_ALL);
ini_set('error_log',      __DIR__ . '/php-error.log');


// Configuração do banco de dados
$pdo = new PDO('sqlite:database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET['action'] ?? '';
$month = $_GET['month'] ?? date('Y-m');

switch ($action) {
    case 'dashboard_data':
        getDashboardData($pdo, $month);
        break;
    case 'get_footer_status':
        getFooterStatus($pdo);
        break;
    case 'category_progress':
        getCategoryProgress($pdo, $month);
        break;
    case 'nurse_activities':
        getNurseActivities($pdo);
        break;
    case 'record_activity':
        recordActivity($pdo);
        break;
    case 'get_activities_by_date':
        getActivitiesByDate($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação não especificada']);
}

function getDashboardData($pdo, $month) {
    try {
        // Buscar dados de progresso por categoria para o mês especificado
        $sql = "
            SELECT 
                c.id,
                c.name as category_name,
                c.description,
                COUNT(DISTINCT i.id) as total_items,
           COUNT(CASE WHEN ar.status = 'C'  THEN 1 END) as completed_records,
          COUNT(CASE WHEN ar.status = 'NC' THEN 1 END) as not_completed_records,
         COUNT(CASE WHEN ar.status = 'NA' THEN 1 END) as na_records,
                COUNT(ar.item_id) as total_records
            FROM categories c
            LEFT JOIN items i ON c.id = i.category_id
            LEFT JOIN activity_records ar ON i.id = ar.item_id 
                AND strftime('%Y-%m', ar.record_date) = ?
            GROUP BY c.id, c.name, c.description
            ORDER BY c.name
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$month]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular totais gerais
        $totals = [
            'total_categories' => count($categories),
            'total_items' => 0,
            'total_completed' => 0,
            'total_not_completed' => 0,
            'total_na'           => 0,
            'total_records' => 0
        ];
        
        foreach ($categories as &$category) {
            $totals['total_items'] += $category['total_items'];
            $totals['total_completed'] += $category['completed_records'];
            $totals['total_na'] = ($totals['total_na'] ?? 0) + $category['na_records'];
            $totals['total_not_completed'] += $category['not_completed_records'];
            $totals['total_records'] += $category['total_records'];
            
            // Calcular percentual de conclusão
            if ($category['total_records'] > 0) {
                $done = $category['completed_records'] + $category['na_records'];
                $category['completion_percentage'] = round(
                    ($done / $category['total_records']) * 100,
                    1
                );
            } else {
                $category['completion_percentage'] = 0;
            }
        }
        
        // Calcular percentual geral
        if ($totals['total_records'] > 0) {
            $doneAll = $totals['total_completed'] + $totals['total_na'];
            $totals['completion_percentage'] = round(
                ($doneAll / $totals['total_records']) * 100,
                1
            );
        } else {
            $totals['completion_percentage'] = 0;
        }
        
        echo json_encode([
            'success' => true,
            'month' => $month,
            'categories' => $categories,
            'totals' => $totals
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
    }
}

function getCategoryProgress($pdo, $month) {
    try {
        $category_id = $_GET['category_id'] ?? null;
        
        if (!$category_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID da categoria não especificado']);
            return;
        }
        
        // Buscar detalhes da categoria
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }
        
        // Buscar progresso dos itens da categoria
        $sql = "
            SELECT 
                i.id,
                i.description,
                i.order_num,
                COUNT(CASE WHEN ar.status IN ('C','NA') THEN 1 END) as completed_count,
                COUNT(CASE WHEN ar.status = 'NC' THEN 1 END) as not_completed_count,
                COUNT(ar.id) as total_records
            FROM items i
            LEFT JOIN activity_records ar ON i.id = ar.item_id 
                AND strftime('%Y-%m', ar.record_date) = ?
            WHERE i.category_id = ?
            GROUP BY i.id, i.description, i.order_num
            ORDER BY i.order_num, i.id
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$month, $category_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular percentuais para cada item
        foreach ($items as &$item) {
            if ($item['total_records'] > 0) {
                $item['completion_percentage'] = round(($item['completed_count'] / $item['total_records']) * 100, 1);
            } else {
                $item['completion_percentage'] = 0;
            }
        }
        
        echo json_encode([
            'success' => true,
            'category' => $category,
            'items' => $items,
            'month' => $month
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar progresso da categoria: ' . $e->getMessage()]);
    }
}

function getNurseActivities($pdo) {
    try {
        // Buscar todas as categorias e itens ativos
        $sql = "
            SELECT 
                c.id as category_id,
                c.name as category_name,
                c.description as category_description,
                i.id as item_id,
                i.description as item_description,
                i.order_num
            FROM categories c
            INNER JOIN items i ON c.id = i.category_id
            ORDER BY c.name, i.order_num, i.id
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organizar por categoria
        $categories = [];
        foreach ($results as $row) {
            $categoryId = $row['category_id'];
            
            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $row['category_name'],
                    'description' => $row['category_description'],
                    'items' => []
                ];
            }
            
            $categories[$categoryId]['items'][] = [
                'id' => $row['item_id'],
                'description' => $row['item_description'],
                'order_num' => $row['order_num']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'categories' => array_values($categories)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar atividades: ' . $e->getMessage()]);
    }
}
function getFooterStatus($pdo) {
    $stmt = $pdo->query("SELECT footer_enabled FROM status WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'footer_enabled' => (int)$row['footer_enabled']
    ]);
}

function getActivitiesByDate($pdo) {
    try {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Validar data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de data inválido']);
            return;
        }
        
        // Buscar atividades e seus registros para a data específica
        $sql = "
            SELECT 
                c.id as category_id,
                c.name as category_name,
                c.description as category_description,
                i.id as item_id,
                i.description as item_description,
                i.order_num,
                ar.status,
                ar.created_at as recorded_at
            FROM categories c
            INNER JOIN items i ON c.id = i.category_id
            LEFT JOIN activity_records ar ON i.id = ar.item_id AND ar.record_date = ?
            ORDER BY c.name, i.order_num, i.id
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organizar por categoria
        $categories = [];
        foreach ($results as $row) {
            $categoryId = $row['category_id'];
            
            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $row['category_name'],
                    'description' => $row['category_description'],
                    'items' => []
                ];
            }
            
            $categories[$categoryId]['items'][] = [
                'id' => $row['item_id'],
                'description' => $row['item_description'],
                'order_num' => $row['order_num'],
                'status' => $row['status'],
                'notes' => $row['notes'],
                'recorded_at' => $row['recorded_at']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'date' => $date,
            'categories' => array_values($categories)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar atividades por data: ' . $e->getMessage()]);
    }
}

function recordActivity($pdo) {
    try {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        // Obter dados do POST
        $input = json_decode(file_get_contents('php://input'), true);
        
        $item_id = $input['item_id'] ?? null;
        $status = $input['status'] ?? null;
        $record_date = $input['record_date'] ?? date('Y-m-d');
        $notes = $input['notes'] ?? null;
        
        // Validações
        if (!$item_id || !$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID e status são obrigatórios']);
            return;
        }

        if (!in_array($status, ['C', 'NC', 'NA'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status deve ser C, NC ou NA']);
            return;
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $record_date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de data inválido']);
            return;
        }
        
        // Verificar se o item existe
        $stmt = $pdo->prepare("SELECT id FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Item não encontrado']);
            return;
        }
        
        // Inserir ou atualizar registro
        $sql = "
        INSERT INTO activity_records (item_id, record_date, status, notes, created_at)
        VALUES (?, ?, ?, ?, datetime('now'))
        ON CONFLICT(item_id, record_date)
        DO UPDATE SET
            status     = excluded.status,
            notes      = excluded.notes,
            created_at = datetime('now')
    ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$item_id, $record_date, $status, $notes]);

        echo json_encode([
            'success' => true,
            'message' => 'Atividade registrada com sucesso',
            'data'    => ['item_id'=>$item_id,'status'=>$status,'record_date'=>$record_date,'notes'=>$notes]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao registrar atividade: ' . $e->getMessage()]);
    }
}

?>

