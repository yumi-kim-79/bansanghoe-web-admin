<?php
/**
 * 세대(Units) API (PDO 직접 연결)
 *
 * GET ?action=units&building_id=N
 *   단지 내 세대 목록 (입주일 포함)
 *   응답: [{ ho_id, dong_id, dong_name, ho_name, ho_owner, ho_size, move_in_date }, ...]
 *
 * 패턴은 api/building_settings_api.php 와 동일하게 유지.
 * meter_api.php(서버 전용) 에 합치는 대신 신규 파일로 분리.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    http_response_code(200);
    exit;
}

// DB 연결 (PDO) — building_settings_api.php 와 동일 자격증명
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

    case 'units':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $sql = "SELECT ho.ho_id,
                       ho.dong_id,
                       dong.dong_name,
                       ho.ho_name,
                       ho.ho_owner,
                       ho.ho_size,
                       ho.ho_tenant_at
                FROM a_building_ho AS ho
                LEFT JOIN a_building_dong AS dong ON ho.dong_id = dong.dong_id
                WHERE ho.building_id = :bid
                  AND ho.is_del = 0
                ORDER BY dong.dong_name ASC, ho.ho_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':bid' => $building_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach($rows as $row){
            // ho_tenant_at 이 비어있거나 '0000-00-00' 이면 null 로 응답
            $movein = $row['ho_tenant_at'];
            if(empty($movein) || $movein === '0000-00-00'){
                $movein = null;
            }

            $list[] = [
                'ho_id'        => (int)$row['ho_id'],
                'dong_id'      => (int)$row['dong_id'],
                'dong_name'    => $row['dong_name'] ?: '',
                'ho_name'      => $row['ho_name'] ?: '',
                'ho_owner'     => $row['ho_owner'] ?: '',
                'ho_size'      => $row['ho_size'] ?: '',
                'move_in_date' => $movein,
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'msg' => '알 수 없는 action: '.$action]);
        break;
}
