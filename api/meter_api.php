<?php
/**
 * 검침 / 단지 / 세대 통합 API (PDO 직접 연결)
 *
 * GET ?action=buildings
 *   - 운영 중 단지 목록
 *   - 응답: [{ building_id, building_name, address, total_units }]
 *
 * GET ?action=units&building_id=N
 *   - 단지 내 세대 목록 (입주일 포함, units_api.php 와 동일)
 *   - 응답: [{ ho_id, dong_id, dong_name, ho_name, ho_owner, ho_size, move_in_date }]
 *
 * GET ?action=meter&building_id=N&year=YYYY&month=M[&type=electro|water]
 *   - 단지의 해당 월 검침값 (전월 대비 사용량 포함). type 미지정 시 'electro'
 *   - 응답: [{ ho_id, dong_name, ho_name, prev_value, curr_value, usage }]
 *
 * 패턴은 api/building_settings_api.php / api/units_api.php 와 동일.
 * 본 파일은 서버에만 있던 기존 meter_api.php 가 git clean 으로 소실되어 재작성됨.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    http_response_code(200);
    exit;
}

// DB 연결 (PDO) — 다른 api/*.php 와 동일 자격증명
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

    // ─── 단지 목록 ───
    case 'buildings':
        $sql = "SELECT b.building_id,
                       b.building_name,
                       b.building_addr,
                       b.building_addr2,
                       (SELECT COUNT(*) FROM a_building_ho WHERE building_id = b.building_id AND is_del = 0) AS total_units
                FROM a_building AS b
                WHERE b.is_del = 0
                  AND b.is_use = 1
                ORDER BY b.building_name ASC";

        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach($rows as $r){
            $address = trim(($r['building_addr'] ?? '').' '.($r['building_addr2'] ?? ''));
            $list[] = [
                'building_id'   => (int)$r['building_id'],
                'building_name' => $r['building_name'] ?: '',
                'address'       => $address,
                'total_units'   => (int)$r['total_units'],
            ];
        }
        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // ─── 단지 내 세대 목록 ───
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
        foreach($rows as $r){
            $movein = $r['ho_tenant_at'];
            if(empty($movein) || $movein === '0000-00-00') $movein = null;

            $list[] = [
                'ho_id'        => (int)$r['ho_id'],
                'dong_id'      => (int)$r['dong_id'],
                'dong_name'    => $r['dong_name'] ?: '',
                'ho_name'      => $r['ho_name'] ?: '',
                'ho_owner'     => $r['ho_owner'] ?: '',
                'ho_size'      => $r['ho_size'] ?: '',
                'move_in_date' => $movein,
            ];
        }
        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // ─── 검침값 (전월 대비 사용량) ───
    case 'meter':
        $building_id = $_GET['building_id'] ?? '';
        $year        = (int)($_GET['year']  ?? 0);
        $month       = (int)($_GET['month'] ?? 0);
        $type        = $_GET['type'] ?? 'electro';

        if(!$building_id || !$year || !$month){
            echo json_encode(['error' => true, 'msg' => 'building_id, year, month 필수']);
            exit;
        }
        if(!in_array($type, ['electro', 'water'], true)){
            echo json_encode(['error' => true, 'msg' => "type 은 'electro' 또는 'water' 만 허용"]);
            exit;
        }

        // 당월 mr_idx
        $stmt = $pdo->prepare("SELECT mr_idx FROM a_meter_building
                               WHERE building_id = :bid AND mr_year = :y AND mr_month = :m
                               LIMIT 1");
        $stmt->execute([':bid' => $building_id, ':y' => $year, ':m' => $month]);
        $curr_mr_idx = $stmt->fetchColumn();

        // 전월 mr_idx (1월이면 전년 12월)
        $prev_year  = $month == 1 ? $year - 1 : $year;
        $prev_month = $month == 1 ? 12       : $month - 1;
        $stmt = $pdo->prepare("SELECT mr_idx FROM a_meter_building
                               WHERE building_id = :bid AND mr_year = :y AND mr_month = :m
                               LIMIT 1");
        $stmt->execute([':bid' => $building_id, ':y' => $prev_year, ':m' => $prev_month]);
        $prev_mr_idx = $stmt->fetchColumn();

        // 단지 세대 + 당월/전월 검침값 LEFT JOIN
        $sql = "SELECT ho.ho_id,
                       dong.dong_name,
                       ho.ho_name,
                       prev_r.mr_val AS prev_value,
                       curr_r.mr_val AS curr_value
                FROM a_building_ho AS ho
                LEFT JOIN a_building_dong AS dong ON ho.dong_id = dong.dong_id
                LEFT JOIN a_meter_reading AS curr_r
                       ON curr_r.ho_id = ho.ho_id
                      AND curr_r.mr_idx = :curr_idx
                      AND curr_r.mr_type = :type1
                      AND curr_r.is_del = 0
                LEFT JOIN a_meter_reading AS prev_r
                       ON prev_r.ho_id = ho.ho_id
                      AND prev_r.mr_idx = :prev_idx
                      AND prev_r.mr_type = :type2
                      AND prev_r.is_del = 0
                WHERE ho.building_id = :bid
                  AND ho.is_del = 0
                ORDER BY dong.dong_name ASC, ho.ho_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':bid'      => $building_id,
            ':curr_idx' => $curr_mr_idx ?: 0,
            ':prev_idx' => $prev_mr_idx ?: 0,
            ':type1'    => $type,
            ':type2'    => $type,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = [];
        foreach($rows as $r){
            $prev = ($r['prev_value'] === null || $r['prev_value'] === '') ? null : (float)$r['prev_value'];
            $curr = ($r['curr_value'] === null || $r['curr_value'] === '') ? null : (float)$r['curr_value'];
            $usage = ($prev !== null && $curr !== null) ? ($curr - $prev) : null;

            $list[] = [
                'ho_id'      => (int)$r['ho_id'],
                'dong_name'  => $r['dong_name'] ?: '',
                'ho_name'    => $r['ho_name'] ?: '',
                'prev_value' => $prev,
                'curr_value' => $curr,
                'usage'      => $usage,
            ];
        }
        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'msg' => '알 수 없는 action: '.$action]);
        break;
}
