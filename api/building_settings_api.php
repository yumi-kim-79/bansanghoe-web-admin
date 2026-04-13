<?php
/**
 * 단지 설정 API (PDO 직접 연결)
 *
 * GET  ?action=building_managers&building_id=N       단지별 담당자 목록
 * GET  ?action=building_managers_all                  전체 단지 담당자 목록
 * GET  ?action=building_settings&building_id=N       단지별 연체요율 조회
 * GET  ?action=building_settings_all                  전체 단지 연체요율 목록
 * POST ?action=update_building_settings               단지 연체요율 수정
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    http_response_code(200);
    exit;
}

// DB 연결 (PDO)
$host   = 'localhost';
$dbname = 'sinbansang';
$user   = 'sm_user1';
$pass   = 'sm2025@@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'msg' => 'DB연결실패: '.$e->getMessage()]);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch($action){

    // ─── 담당자 조회 ───

    case 'building_managers':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT building_id, building_name FROM a_building WHERE building_id = :bid AND is_del = 0");
        $stmt->execute([':bid' => $building_id]);
        $brow = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$brow){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        echo json_encode([
            'building_id' => (int)$brow['building_id'],
            'building_name' => $brow['building_name'],
            'managers' => _get_building_managers($pdo, $building_id),
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'building_managers_all':
        $stmt = $pdo->query("SELECT building_id, building_name FROM a_building WHERE is_del = 0 AND is_use = 1 ORDER BY building_name ASC");
        $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach($buildings as $brow){
            $list[] = [
                'building_id' => (int)$brow['building_id'],
                'building_name' => $brow['building_name'],
                'managers' => _get_building_managers($pdo, $brow['building_id']),
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // ─── 연체요율 조회/수정 ───

    case 'building_settings':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT building_id, building_name, late_fee_rate, late_fee_base FROM a_building WHERE building_id = :bid AND is_del = 0");
        $stmt->execute([':bid' => $building_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        echo json_encode([
            'building_id' => (int)$row['building_id'],
            'building_name' => $row['building_name'],
            'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
            'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'building_settings_all':
        $stmt = $pdo->query("SELECT building_id, building_name, late_fee_rate, late_fee_base FROM a_building WHERE is_del = 0 AND is_use = 1 ORDER BY building_name ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach($rows as $row){
            $list[] = [
                'building_id' => (int)$row['building_id'],
                'building_name' => $row['building_name'],
                'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
                'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    case 'update_building_settings':
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            echo json_encode(['error' => true, 'msg' => 'POST 요청만 허용']);
            exit;
        }

        $building_id = $_POST['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT building_id FROM a_building WHERE building_id = :bid AND is_del = 0");
        $stmt->execute([':bid' => $building_id]);
        if(!$stmt->fetch()){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        $late_fee_rate = max(0, min(100, (float)($_POST['late_fee_rate'] ?? 0)));
        $late_fee_base = $_POST['late_fee_base'] ?? '미납금액';
        if(!in_array($late_fee_base, ['미납금액', '당월금액'])) $late_fee_base = '미납금액';

        $stmt = $pdo->prepare("UPDATE a_building SET late_fee_rate = :rate, late_fee_base = :base WHERE building_id = :bid");
        $stmt->execute([':rate' => $late_fee_rate, ':base' => $late_fee_base, ':bid' => $building_id]);

        echo json_encode([
            'error' => false,
            'msg' => '저장되었습니다.',
            'building_id' => (int)$building_id,
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'msg' => '알 수 없는 action: ' . $action]);
        break;
}


// ─── 내부 함수 ───

function _get_building_managers($pdo, $building_id){
    $stmt = $pdo->prepare("SELECT mng.mng_id, mng.mng_name, mng.mng_hp, mng.mng_email,
                   dept.md_name, grade.mg_name, mng.mng_certi
            FROM a_mng_building AS mb
            LEFT JOIN a_mng AS mng ON mb.mb_id = mng.mng_id
            LEFT JOIN a_mng_department AS dept ON mng.mng_department = dept.md_idx
            LEFT JOIN a_mng_grade AS grade ON mng.mng_grades = grade.mg_idx
            WHERE mb.building_id = :bid
            AND mb.is_del = 0
            AND mng.mng_status = 1
            AND mng.is_del = 0
            ORDER BY dept.is_prior ASC, grade.is_prior DESC");
    $stmt->execute([':bid' => $building_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $managers = [];
    foreach($rows as $row){
        $managers[] = [
            'mng_id' => $row['mng_id'],
            'mng_name' => $row['mng_name'] ?: '',
            'mng_phone' => $row['mng_hp'] ?: '',
            'mng_email' => $row['mng_email'] ?: '',
            'department' => $row['md_name'] ?: '',
            'grade' => $row['mg_name'] ?: '',
            'certi' => $row['mng_certi'] ?: '',
        ];
    }
    return $managers;
}
