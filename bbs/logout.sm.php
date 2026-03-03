<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');

@extract($_SESSION);
@extract($_COOKIE);

$sql= " update g5_member set mb_auto = '0', mb_token = '' where mb_id = '{$_SESSION["ss_mb_id"]}' ";
sql_query($sql);

if(!empty($_SESSION["ss_mb_id"])) {
    unset($_SESSION["ss_mb_id"]);
    unset($_SESSION["ss_mb_key"]);
    unset($_SESSION["ss_mb_token_key"]);
//		session_unset();	// ��� ���Ǻ����� �������� ������
//		session_destroy();	// ����������
}

echo "<script>document.location='/bbs/login_sm.php';</script>";