<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$tenant_at = date("Y-m-d");
$ip_info = $_SERVER['REMOTE_ADDR'];

if($mb_name == "") die(result_data(false, "세대주명을 입력해주세요.", "mb_name"));
if($mb_hp == "") die(result_data(false, "연락처를 입력해주세요.", "mb_hp"));

$confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_name = '{$mb_name}' and mb_hp = '{$mb_hp}' and is_del = 0");

if($confirm_mb['cnt'] == 0) die(result_data(false, "가입된 정보가 없습니다.", []));

echo result_data(true, "아이디 정보", $mb_hp);