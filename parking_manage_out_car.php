<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$update_query = "UPDATE a_building_visit_car SET
                    out_status = 'Y',
                    out_at = '{$today}'
                    WHERE car_id = '{$car_id}'";

sql_query($update_query);