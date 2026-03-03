<?php
include_once('./_common.php');

@extract($_SESSION);
@extract($_COOKIE);

$sql= " update a_member set mb_auto = '0', mb_token = '' where mb_id = '{$_SESSION["users"]["id"]}' ";
sql_query($sql);

if(!empty($_SESSION["users"]["id"])) {
    unset($_SESSION["users"]);
//		session_unset();	// ��� ���Ǻ����� �������� ������
//		session_destroy();	// ����������
}
echo "<script>document.location='/bbs/login.php';</script>";