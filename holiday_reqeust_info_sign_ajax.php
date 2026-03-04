<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($signdata == "") die(result_data(false, "서명을 입력해주세요.", []));

$singature_row = sql_fetch("SELECT COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}'");

$sign_info = sql_fetch("SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'");
$approval_name = approval_category_name($sign_info['sign_off_category']);
$wname = get_member($sign_info['mng_id'])['mb_name'];
$wid = $sign_info['mng_id'];

if($singature_row['cnt'] == 0){
    //서명 이미지 저장
    if($signdata != ""){
        $encoded_image = explode(",", $signdata);
        $decoded_image = base64_decode($encoded_image[1]);

        $file_name = md5(uniqid(rand(), TRUE)).".png";
        $file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);

        $file_path = G5_DATA_PATH.'/file/approval';

        @mkdir($file_path, G5_DIR_PERMISSION);
        @chmod($file_path, G5_DIR_PERMISSION);

        $file_name2 = $file_name;

        $tgt = $file_path.'/'.$file_name2;

        file_put_contents($tgt, $decoded_image);

        //서명저장
        $signature_insert = "INSERT INTO a_signature SET
                             mb_id = '{$mb_id}',
                             signature_data = '{$signdata}',
                             fil_name = '{$file_name2}',
                             created_at = '{$today}'";
        sql_query($signature_insert);
        $sg_idx = sql_insert_id(); //저장된 서명 idx

        //approval_sm 결재담당자로 사인
        $img_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_sign_off_mng_sign WHERE mng_id = '{$mb_id}' and sign_mng_data = '{$data}' and sign_id = '{$sign_id}'");

        //사인 이미지 경로 및 데이터 저장
        if($img_confirm['cnt'] > 0){
            $update_img = "UPDATE a_sign_off_img SET
                    sg_idx = '{$sg_idx}'
                    WHERE sign_id = '{$sign_id}' and sign_mng_data = '{$data}'";
            sql_query($update_img);

        }else{

            $insert_img = "INSERT INTO a_sign_off_mng_sign SET
                    sg_idx = '{$sg_idx}',
                    sign_mng_data = '{$data}',
                    sign_id = '{$sign_id}',
                    mng_id = '{$mb_id}',
                    created_at = '{$today}'";
            sql_query($insert_img);
        }
        
    }

}else{

    $singature_row2 = sql_fetch("SELECT * FROM a_signature WHERE mb_id = '{$mb_id}' and signature_data = '{$signdata}'");

    $insert_img = "INSERT INTO a_sign_off_mng_sign SET
                    sg_idx = '{$singature_row2['sg_idx']}',
                    sign_mng_data = '{$data}',
                    sign_id = '{$sign_id}',
                    mng_id = '{$mb_id}',
                    created_at = '{$today}'";
    sql_query($insert_img);
}

//결재완료하려는 결재자 변수
//$sign_chk = $data;
$sign_chk = "";
switch($data){
    case 'sign_off_mng_id1':
        $sign_chk = 'sign_off_status';
        break;
    case 'sign_off_mng_id2':
        $sign_chk = 'sign_off_status2';
        break;
    case 'sign_off_mng_id3':
        $sign_chk = 'sign_off_status3';
        break;
}

$update_sign = "UPDATE a_sign_off SET
                $sign_chk = '1'
                WHERE sign_id = '{$sign_id}'";
sql_query($update_sign);

//결재 후 결재상태 체크용
$sign_sql = "SELECT * FROM a_sign_off WHERE sign_id = '{$sign_id}'";
$sign_row = sql_fetch($sign_sql);

$sum_sign = $sign_row['sign_off_status'] + $sign_row['sign_off_status2'] + $sign_row['sign_off_status3'];

// 실제 결재자 수 계산 (빈 값이 아닌 결재자만 카운트)
$total_approver = 0;
if($sign_row['sign_off_mng_id1'] != "") $total_approver++;
if($sign_row['sign_off_mng_id2'] != "") $total_approver++;
if($sign_row['sign_off_mng_id3'] != "") $total_approver++;

if ($sum_sign === 0) {
    $status = 'N'; // 아무도 서명 안함 → 승인대기
} elseif ($sum_sign >= $total_approver) {
    $status = 'E'; // 전체 결재자 서명 완료 → 승인완료
} else {
    $status = 'P'; // 일부 서명 완료 → 승인중

     //1차 결재자 승인 후 2차 결재자에게 푸시발송
     if($sign_chk == 'sign_off_status'){
        //2차 결재권자 푸시발송
       $sign_off_id_info = get_member($sign_info['sign_off_mng_id2']); //1차 결재자 정보

       $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

       $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
       $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";
   

       if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
           fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
       }

       $insert_push = "INSERT INTO a_push SET
                       recv_id = '{$sign_info['sign_off_mng_id2']}',
                       recv_id_type = 'sm',
                       push_title = '{$push_title}',
                       push_content = '{$push_content}',
                       wid = '{$wid}',
                       push_type = 'sign_off',
                       push_idx = '{$sign_id}',
                       created_at = '{$today}'";
       sql_query($insert_push);
  
   }

   //2차 결재자 승인 후 3차 결재자에게 푸시발송
   if($sign_chk == 'sign_off_status2'){
        //3차 결재권자 푸시발송
        $sign_off_id_info = get_member($sign_info['sign_off_mng_id3']); //1차 결재자 정보

        $sign_off_id = $sign_off_id_info['mb_id']; //1차 결재자 아이디

        $push_title = '[결재요청] '.$approval_name." 결재 요청이 있습니다.";
        $push_content = $wname.'님의 '.$approval_name." 결재 요청이 있습니다.";


        if($sign_off_id_info['mb_token'] != "" && $sign_off_id_info['noti1']){ //토큰이 있는경우 푸시 발송
            fcm_send($sign_off_id_info['mb_token'], $push_title, $push_content, "sign_off", "{$sign_id}", "/holiday_reqeust_info.php?mng=Y&sign_id=");
        }

        $insert_push = "INSERT INTO a_push SET
                        recv_id = '{$sign_info['sign_off_mng_id3']}',
                        recv_id_type = 'sm',
                        push_title = '{$push_title}',
                        push_content = '{$push_content}',
                        wid = '{$wid}',
                        push_type = 'sign_off',
                        push_idx = '{$sign_id}',
                        created_at = '{$today}'";
        sql_query($insert_push);
    }
}

//상태 값 변경
$update_sign_status = "UPDATE a_sign_off SET
                        sign_status = '{$status}'
                        WHERE sign_id = '{$sign_id}'";
sql_query($update_sign_status);

echo result_data(true, "서명이 완료되었습니다.", []);
//die(result_data(false, $update_sign_status, []));
