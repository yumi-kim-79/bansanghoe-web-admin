<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 결재(결재서류함, a_sign_off) 승인 처리 공통 로직 — 2026-09 신설
 *
 * 왜 만들었나
 *  - 같은 승인 처리가 관리자웹(adm/approval_form_check.php)과
 *    매니저앱(holiday_reqeust_info_sign_ajax.php) 두 벌로 복사돼 있었고,
 *    2026-08 에 관리자웹만 고쳐지면서 앱판에는 버그가 남아 있었다.
 *      · 존재 확인은 a_sign_off_mng_sign 에 하고 UPDATE 는 a_sign_off_img 에 하던 오타
 *      · 저장된 서명 재사용 시 업서트 없이 무조건 INSERT -> 재서명하면 중복 행
 *  - 일괄결재는 같은 처리를 N번 돌려야 하므로 로직이 한 곳에 있어야 한다.
 *
 * 무엇이 달라졌나 (기존 대비)
 *  - 서버측 권한·차례 검증 추가.
 *    기존에는 $_POST['mb_id'] 를 로그인 세션과 대조하지 않았고,
 *    "지금이 이 사람 차례인지", "이미 결재한 건인지" 도 검사하지 않았다.
 *    화면에서 버튼을 숨기는 것으로만 막고 있어, 요청을 조작하면
 *    남의 결재를 대신 찍을 수 있었다. 일괄결재는 결재건 목록을 통째로 받으므로
 *    이 검증 없이는 만들면 안 된다.
 *  - 결재 차수(1/2/3차)를 클라이언트가 보낸 값이 아니라 DB 에서 판정한다.
 */

/**
 * 이 사용자가 지금 결재할 차례인지 판정한다.
 *  성공: array('idx'=>1|2|3, 'key'=>'sign_off_mng_idN', 'col'=>'sign_off_statusN')
 *  실패: array('idx'=>0, 'code'=>..., 'msg'=>...)
 *
 *  ※ 차수 컬럼명이 1차만 'sign_off_status' 로 숫자가 없다. (2차 status2, 3차 status3)
 *    이 비대칭이 실수 유발 지점이라 여기 한 곳에서만 다룬다.
 */
function approval_find_my_slot($row, $mb_id)
{
    $isDone = function ($v) {
        $v = is_string($v) ? trim($v) : $v;
        return ($v === 1 || $v === '1' || $v === 'Y' || $v === 'y' || $v === true);
    };

    $slots = array(
        1 => array('key' => 'sign_off_mng_id1', 'col' => 'sign_off_status'),
        2 => array('key' => 'sign_off_mng_id2', 'col' => 'sign_off_status2'),
        3 => array('key' => 'sign_off_mng_id3', 'col' => 'sign_off_status3'),
    );

    $mine = 0;
    foreach ($slots as $i => $s) {
        $approver = isset($row[$s['key']]) ? trim((string)$row[$s['key']]) : '';
        if ($approver === '' || $approver !== $mb_id) continue;
        // 같은 사람이 여러 차수에 들어간 경우: 아직 안 찍은 가장 앞 차수를 잡는다
        if (!$isDone(isset($row[$s['col']]) ? $row[$s['col']] : '')) { $mine = $i; break; }
        $mine = -$i; // 본인 차수이긴 한데 이미 처리됨
    }

    if ($mine === 0) {
        return array('idx' => 0, 'code' => 'not_approver', 'msg' => '이 문서의 결재자가 아닙니다.');
    }
    if ($mine < 0) {
        return array('idx' => 0, 'code' => 'already_signed', 'msg' => '이미 결재한 문서입니다.');
    }

    // 앞 순번 결재가 끝나지 않았으면 아직 내 차례가 아니다
    for ($i = 1; $i < $mine; $i++) {
        $prev = $slots[$i];
        $prev_approver = isset($row[$prev['key']]) ? trim((string)$row[$prev['key']]) : '';
        if ($prev_approver === '') continue; // 결재자 미지정 차수는 건너뜀
        if (!$isDone(isset($row[$prev['col']]) ? $row[$prev['col']] : '')) {
            return array('idx' => 0, 'code' => 'not_my_turn', 'msg' => '앞 순번 결재가 아직 완료되지 않았습니다.');
        }
    }

    return array('idx' => $mine, 'key' => $slots[$mine]['key'], 'col' => $slots[$mine]['col']);
}

/**
 * 결재 1건 승인 처리.
 *
 * @param int    $sign_id  a_sign_off.sign_id
 * @param string $mb_id    결재자(로그인 사용자) 아이디
 * @param string $signdata 서명 base64 data URI
 * @param bool   $do_push  다음 결재자 푸시 발송 여부 (기본 true)
 */
function approval_sign_one($sign_id, $mb_id, $signdata, $do_push = true)
{
    global $member;

    $today    = date("Y-m-d H:i:s");
    $sign_id  = (int)$sign_id;
    $mb_id    = trim((string)$mb_id);
    $signdata = trim((string)$signdata);

    $fail = function ($code, $msg) use ($sign_id) {
        return array('ok' => false, 'code' => $code, 'msg' => $msg, 'sign_id' => $sign_id);
    };

    // 0) 기본 검증
    if ($sign_id <= 0)    return $fail('bad_request', '잘못된 요청입니다.(결재번호 없음)');
    if ($signdata === '') return $fail('no_sign', '서명을 입력해주세요.');

    // 로그인 사용자 본인만 결재할 수 있다 (기존에는 이 검사가 없었다)
    $login_id = isset($member['mb_id']) ? trim((string)$member['mb_id']) : '';
    if ($login_id === '') return $fail('not_login', '로그인 후 이용해 주세요.');
    if ($mb_id === '')    $mb_id = $login_id;
    if ($mb_id !== $login_id) return $fail('forbidden', '본인 결재만 처리할 수 있습니다.');

    $row = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");
    if (!$row || !isset($row['sign_id'])) return $fail('not_found', '결재 정보를 찾을 수 없습니다.');
    if (isset($row['is_del']) && (int)$row['is_del'] === 1) return $fail('deleted', '삭제된 결재 문서입니다.');

    $cur_status = isset($row['sign_status']) ? trim((string)$row['sign_status']) : 'N';
    if ($cur_status === 'E') return $fail('already_done', '이미 결재가 완료된 문서입니다.');
    if ($cur_status === 'R') return $fail('rejected', '반려된 문서는 결재할 수 없습니다.');

    // 1) 내 차례인지 판정
    $slot = approval_find_my_slot($row, $mb_id);
    if (empty($slot['idx'])) return $fail($slot['code'], $slot['msg']);

    $current_idx = $slot['idx'];
    $data        = $slot['key'];   // sign_off_mng_id1|2|3
    $sign_chk    = $slot['col'];   // sign_off_status|status2|status3

    // 2) 서명 이미지 저장 / 재사용 (a_signature)
    //    같은 base64 면 기존 행을 재사용한다. 일괄결재에서 N건에 같은 서명을 써도
    //    a_signature 는 1행, 파일도 1개만 생긴다.
    $sd_esc = sql_real_escape_string($signdata);

    $sg_idx = 0;
    $exist = sql_fetch("SELECT sg_idx FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$sd_esc}' ORDER BY sg_idx DESC LIMIT 1");

    if ($exist && !empty($exist['sg_idx'])) {
        $sg_idx = (int)$exist['sg_idx'];
    } else {
        $parts = explode(",", $signdata);
        if (count($parts) < 2) return $fail('bad_sign', '서명 데이터 형식이 올바르지 않습니다.');

        $decoded = base64_decode($parts[1]);
        if ($decoded === false || $decoded === '') return $fail('bad_sign', '서명 데이터를 읽을 수 없습니다.');

        $file_name = md5(uniqid(rand(), TRUE)).".png";
        $file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);

        $file_path = G5_DATA_PATH.'/file/approval';
        @mkdir($file_path, G5_DIR_PERMISSION, true);
        @chmod($file_path, G5_DIR_PERMISSION);

        if (file_put_contents($file_path.'/'.$file_name, $decoded) === false) {
            return $fail('save_fail', '서명 이미지를 저장하지 못했습니다.');
        }

        sql_query("INSERT INTO a_signature SET
                    mb_id = '{$mb_id}',
                    signature_data = '{$sd_esc}',
                    fil_name = '{$file_name}',
                    created_at = '{$today}'");
        $sg_idx = (int)sql_insert_id();
    }

    if (!$sg_idx) return $fail('save_fail', '서명 저장에 실패했습니다.');

    // 3) 문서-결재자-서명 연결 (a_sign_off_mng_sign) — 업서트
    $has_link = sql_fetch("SELECT COUNT(*) as cnt FROM a_sign_off_mng_sign
                           WHERE mng_id = '{$mb_id}' AND sign_mng_data = '{$data}' AND sign_id = '{$sign_id}' AND is_del = 0");

    if ((int)$has_link['cnt'] > 0) {
        sql_query("UPDATE a_sign_off_mng_sign SET sg_idx = '{$sg_idx}'
                   WHERE sign_id = '{$sign_id}' AND sign_mng_data = '{$data}' AND mng_id = '{$mb_id}' AND is_del = 0");
    } else {
        sql_query("INSERT INTO a_sign_off_mng_sign SET
                    sg_idx = '{$sg_idx}',
                    sign_mng_data = '{$data}',
                    sign_id = '{$sign_id}',
                    mng_id = '{$mb_id}',
                    created_at = '{$today}',
                    is_del = 0");
    }

    // 4) 해당 차수 완료 플래그
    sql_query("UPDATE a_sign_off SET {$sign_chk} = '1' WHERE sign_id = '{$sign_id}'");

    $sign_row = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");

    // 5) 문서 전체 상태 계산 — 결재자로 실제 지정된 차수 수만큼 서명되면 완료(E)
    $isDone = function ($v) {
        $v = is_string($v) ? trim($v) : $v;
        return ($v === 1 || $v === '1' || $v === 'Y' || $v === 'y' || $v === true);
    };

    $has1 = isset($sign_row['sign_off_mng_id1']) && trim($sign_row['sign_off_mng_id1']) !== '';
    $has2 = isset($sign_row['sign_off_mng_id2']) && trim($sign_row['sign_off_mng_id2']) !== '';
    $has3 = isset($sign_row['sign_off_mng_id3']) && trim($sign_row['sign_off_mng_id3']) !== '';

    $required = ($has1 ? 1 : 0) + ($has2 ? 1 : 0) + ($has3 ? 1 : 0);

    $done = 0;
    if ($has1 && $isDone(isset($sign_row['sign_off_status'])  ? $sign_row['sign_off_status']  : '')) $done++;
    if ($has2 && $isDone(isset($sign_row['sign_off_status2']) ? $sign_row['sign_off_status2'] : '')) $done++;
    if ($has3 && $isDone(isset($sign_row['sign_off_status3']) ? $sign_row['sign_off_status3'] : '')) $done++;

    if ($done <= 0) $status = 'N';
    else if ($required > 0 && $done >= $required) $status = 'E';
    else $status = 'P';

    sql_query("UPDATE a_sign_off SET sign_status = '{$status}' WHERE sign_id = '{$sign_id}'");

    // 6) 다음 결재자 푸시
    if ($do_push && $status === 'P') {
        $next_id = '';
        if ($current_idx === 1 && $has2) $next_id = trim($sign_row['sign_off_mng_id2']);
        if ($current_idx === 2 && $has3) $next_id = trim($sign_row['sign_off_mng_id3']);

        if ($next_id !== '') approval_push_next($sign_id, $next_id, $sign_row, $today);
    }

    return array(
        'ok'          => true,
        'code'        => 'ok',
        'msg'         => '서명이 완료되었습니다.',
        'sign_id'     => $sign_id,
        'sign_status' => $status,
        'required'    => $required,
        'done'        => $done,
        'current'     => $data,
    );
}

/** 다음 결재자에게 결재요청 푸시 + 알림함 기록 */
function approval_push_next($sign_id, $next_id, $sign_row, $today = '')
{
    if ($today === '') $today = date("Y-m-d H:i:s");

    $approval_name = approval_category_name($sign_row['sign_off_category']);
    $wid   = $sign_row['mng_id'];
    $wname = get_member($wid)['mb_name'];

    $push_title   = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
    $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";

    $next_info = get_member($next_id);

    if (!empty($next_info['mb_token']) && !empty($next_info['noti1'])) {
        try {
            fcm_send($next_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
        } catch (Exception $e) {
            // 푸시 실패가 결재 처리를 막지 않도록 무시
        }
    }

    $t_title   = sql_real_escape_string($push_title);
    $t_content = sql_real_escape_string($push_content);

    sql_query("INSERT INTO a_push SET
                recv_id = '{$next_id}',
                recv_id_type = 'sm',
                push_title = '{$t_title}',
                push_content = '{$t_content}',
                wid = '{$wid}',
                push_type = 'sign_off',
                push_idx = '{$sign_id}',
                created_at = '{$today}'");
}
