<?php
include_once('./_common.php');

$today = date("Ymd");
$today2 = date("Y-m-d H:i:s");

if($types == "sm"){
    //매니저 회원
    $mem_sql = "SELECT * FROM g5_member WHERE mb_id = '{$mb_id}'";
    $mem_row = sql_fetch($mem_sql);

    if(!$mem_row) die(result_data(false, "잘못된 접근입니다.", []));

    //탈퇴처리
    $leave_mb = "UPDATE g5_member SET
                 mb_leave_date = '{$today}'
                 WHERE mb_id = '{$mb_id}'";
    sql_query($leave_mb);


    echo result_data(true, "회원 탈퇴가 완료되었습니다.", []);

}else{
    //일반 회원
    $mem_sql = "SELECT * FROM a_member WHERE mb_id = '{$mb_id}'";
    $mem_row = sql_fetch($mem_sql);

    if(!$mem_row) die(result_data(false, "잘못된 접근입니다.", []));

    //탈퇴처리
    $leave_mb = "UPDATE a_member SET
                 is_del = 1,
                 deleted_at = '{$today2}'
                 WHERE mb_id = '{$mb_id}'";
    sql_query($leave_mb);

    echo result_data(true, "회원 탈퇴가 완료되었습니다.", []);
}