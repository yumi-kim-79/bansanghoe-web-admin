<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

for($i=0;$i<count($car_type);$i++){
    //차종만 입력한경우
    if($car_type[$i] != ""){
        if($car_name[$i] == ""){
            alert(($i + 1)."번째 등록차량의 차량번호를 입력하세요.");
        }
    }

    //차량번호만 입력한 경우
    if($car_name[$i] != ""){
        if($car_type[$i] == ""){
            alert(($i + 1)."번째 등록차량의 차종을 입력하세요.");
        }
    }
}

if($w == "u"){

    //차량관리 추가
    for($i=0;$i<count($car_type);$i++){

        if($car_id[$i] != ""){

            //삭제가 체크되어있으면
            if($car_del[$i]){
                $del_sql = " ,
                            is_del = 1,
                             deleted_at = '{$today}' ";
            }

            $insert_car = "UPDATE a_building_car SET
                        car_type = '{$car_type[$i]}',
                        car_name = '{$car_name[$i]}',
                        mb_id = '{$tenant_id}'
                        {$del_sql}
                        WHERE car_id = '{$car_id[$i]}'";
            sql_query($insert_car);
            //echo $insert_car.'<br>';
        }else{

            if($car_type[$i] != "" && $car_name[$i] != ""){
                $insert_car = "INSERT INTO a_building_car SET
                            building_id = '{$building_id}',
                            dong_id = '{$dong_id}',
                            ho_id = '{$ho_id}',
                            mb_id = '{$tenant_id}',
                            car_type = '{$car_type[$i]}',
                            car_name = '{$car_name[$i]}',
                            ip_info = '{$ip_info}',
                            created_at = '{$today}'";
                sql_query($insert_car);

            }
            
        }
    }
}



if($w == 'u'){
    alert('차량정보가 수정되었습니다.');
}else{
    alert('차량정보가 등록되었습니다.', './car_form.php?'. $qstr . '&amp;w=u&amp;ho_id=' . $ho_id);
}
?>