<?php
require_once "./_common.php";

$signature_info = sql_fetch("SELECT * FROM a_signature WHERE mb_id = '{$mb_id}' ORDER BY sg_idx DESC LIMIT 1");

echo result_data(true, "", $signature_info);