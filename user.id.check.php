<?php
require_once "./_common.php";

$confirm_id = "SELECT COUNT(*) as cnt FROM a_member WHERE mb_id = '{$mb_id}' ";
$confirm_row = sql_fetch($confirm_id);

if($confirm_row['cnt'] > 0) die(result_data(false, "이미 등록된 아이디입니다.", []));

echo result_data(true, "사용 가능한 아이디입니다.", []);