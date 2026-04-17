<?php
/**
 * SMS 수신자 전화번호 조회 API
 * GET ?action=recipients&building_id=N&dong_id=M(선택)
 */
header('Content-Type: application/json; charset=utf-8');

try {

    // 환경별 DB 자동 선택
    $host = 'localhost';
    $user = 'sm_user1';
    $pass = 'sm2025@@';

    if(strpos($_SERVER['HTTP_HOST'] ?? '', 'test.') !== false){
        $dbname = 'bansanghoe';
    } else {
        $dbname = 'sinbansang';
    }

    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8');

    if($conn->connect_error){
        echo json_encode(['success' => false, 'msg' => 'DB 연결 실패'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $action = $_GET['action'] ?? '';

    if(!in_array($action, ['recipients', 'search', 'buildings'])){
        echo json_encode(['success' => false, 'msg' => '알 수 없는 action'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // action=buildings: 단지 목록 검색
    if($action == 'buildings'){
        $keyword = $conn->real_escape_string(trim($_GET['keyword'] ?? ''));
        $where = "b.is_del = 0 AND b.is_use = 1";
        if($keyword) $where .= " AND (b.building_name LIKE '%{$keyword}%' OR p.post_name LIKE '%{$keyword}%')";

        $sql = "SELECT b.building_id, b.building_name, p.post_name,
                       (SELECT COUNT(*) FROM a_building_ho h WHERE h.building_id = b.building_id AND h.is_del = 0
                        AND (h.ho_tenant_hp != '' OR h.ho_owner_hp != '')) as ho_count
                FROM a_building b
                LEFT JOIN a_post_addr p ON b.post_id = p.post_idx
                WHERE {$where}
                ORDER BY p.post_name ASC, b.building_name ASC
                LIMIT 50";
        $res = $conn->query($sql);
        $list = [];
        if($res){
            while($row = $res->fetch_assoc()){
                $list[] = [
                    'building_id' => (int)$row['building_id'],
                    'building_name' => $row['building_name'],
                    'post_name' => $row['post_name'],
                    'ho_count' => (int)$row['ho_count'],
                ];
            }
        }
        $conn->close();
        echo json_encode(['success' => true, 'buildings' => $list], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $building_id = $conn->real_escape_string($_GET['building_id'] ?? '');
    $keyword = $conn->real_escape_string(trim($_GET['keyword'] ?? ''));

    // action=recipients: 기존 방식 (building_id 필수)
    // action=search: 키워드 검색 (building_id 선택, keyword 필수)
    if($action == 'recipients' && !$building_id){
        echo json_encode(['success' => false, 'msg' => 'building_id 필수'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if($action == 'search' && !$keyword){
        echo json_encode(['success' => false, 'msg' => '검색어를 입력해주세요.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 단지명 조회
    $building_name = '';
    if($building_id){
        $brow = $conn->query("SELECT building_name FROM a_building WHERE building_id = '{$building_id}'")->fetch_assoc();
        $building_name = $brow['building_name'] ?? '';
    }

    if($action == 'search'){
        // 전체 DB 통합 검색 (단지 필터 선택 시 해당 단지로 제한)
        $where = "h.is_del = 0 AND b.is_del = 0 AND b.is_use = 1";
        if($building_id) $where .= " AND h.building_id = '{$building_id}'";

        $dong_id = $conn->real_escape_string($_GET['dong_id'] ?? '');
        if($dong_id && $dong_id != '-1') $where .= " AND h.dong_id = '{$dong_id}'";

        $where .= " AND (
            h.ho_tenant LIKE '%{$keyword}%'
            OR h.ho_owner LIKE '%{$keyword}%'
            OR h.ho_tenant_hp LIKE '%{$keyword}%'
            OR h.ho_owner_hp LIKE '%{$keyword}%'
            OR h.ho_name LIKE '%{$keyword}%'
            OR CAST(h.dong_id AS CHAR) LIKE '%{$keyword}%'
            OR b.building_name LIKE '%{$keyword}%'
        )";

        $sql = "SELECT h.ho_id, h.dong_id, h.ho_name, h.ho_tenant, h.ho_tenant_hp, h.ho_owner, h.ho_owner_hp,
                       b.building_name, p.post_name
                FROM a_building_ho h
                LEFT JOIN a_building b ON h.building_id = b.building_id
                LEFT JOIN a_post_addr p ON b.post_id = p.post_idx
                WHERE {$where}
                ORDER BY b.building_name ASC, h.dong_id ASC, h.ho_name + 0 ASC
                LIMIT 200";
    } else {
        // 기존 방식
        $where = "is_del = 0 AND building_id = '{$building_id}'";

        $dong_id = $conn->real_escape_string($_GET['dong_id'] ?? '');
        if($dong_id && $dong_id != '-1') $where .= " AND dong_id = '{$dong_id}'";

        $sql = "SELECT ho_id, dong_id, ho_name, ho_tenant, ho_tenant_hp, ho_owner, ho_owner_hp
                FROM a_building_ho
                WHERE {$where}
                ORDER BY dong_id ASC, ho_name + 0 ASC";
    }

    $res = $conn->query($sql);

    $phone_list = [];
    $detail_list = [];

    if($res){
        while($row = $res->fetch_assoc()){
            $phone = trim($row['ho_tenant_hp'] ?? '');
            if(!$phone) $phone = trim($row['ho_owner_hp'] ?? '');
            $phone = preg_replace('/[\s\-]/', '', $phone);

            if(!$phone || strlen($phone) < 10) continue;

            $phone_list[] = $phone;
            $item = [
                'ho_id' => (int)$row['ho_id'],
                'dong_id' => (int)$row['dong_id'],
                'ho_name' => $row['ho_name'],
                'name' => $row['ho_tenant'] ?: $row['ho_owner'],
                'phone' => $phone,
            ];
            if($action == 'search'){
                $item['building_name'] = $row['building_name'] ?? '';
                $item['post_name'] = $row['post_name'] ?? '';
            }
            $detail_list[] = $item;
        }
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'db' => $dbname,
        'building_name' => $building_name,
        'count' => count($detail_list),
        'phone_list' => array_values(array_unique($phone_list)),
        'detail_list' => $detail_list,
    ], JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
