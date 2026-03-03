<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

if($post_id == "") alert("지역을 선택해주세요.");
if($building_id == "") alert("단지를 선택해주세요.");
if($dong_id == "") alert("동을 선택해주세요.");

if(count($hh_relationship) == 0) alert('세대 구성원을 한명 이상 추가해주세요.');

//세대구성원 중 빠진 값 체크..
for($i=0;$i<count($hh_relationship);$i++){

    //관계만 입력한 경우
    if($hh_relationship[$i] != ""){
        
        if($hh_name[$i] == ""){
            alert(($i + 1)."번째 세대 구성원의 이름을 입력하세요.");
        }

        if($hh_hp[$i] == ""){
            alert(($i + 1)."번째 세대 구성원의 연락처를 입력하세요.");
        }
    }

    //이름만 입력한 경우
    if($hh_name[$i] != ""){
        if($hh_relationship[$i] == ""){
            alert(($i + 1)."번째 세대 구성원과의 관계를 입력하세요.");
        }

        if($hh_hp[$i] == ""){
            alert(($i + 1)."번째 세대 구성원의 연락처를 입력하세요.");
        }
    }

    //연락처만 입력한 경우
    if($hh_hp[$i] != ""){
        if($hh_relationship[$i] == ""){
            alert(($i + 1)."번째 세대 구성원과의 관계를 입력하세요.");
        }

        if($hh_name[$i] == ""){
            alert(($i + 1)."번째 세대 구성원의 이름을 입력하세요.");
        }
    }
}

//수정
if($w == "u"){

    //세대원 추가 및 수정
    for($i=0;$i<count($hh_relationship);$i++){

        if($hh_id[$i] != ""){

            $del_sql = "";
            //삭제가 체크되어있다면...
            if($hh_del[$i]){
                $del_sql = " ,
                            is_del = 1,
                             deleted_at = '{$today}' ";
            }

            $insert_household = "UPDATE a_building_household SET
                            hh_relationship = '{$hh_relationship[$i]}',
                            hh_name = '{$hh_name[$i]}',
                            hh_hp = '{$hh_hp[$i]}'
                            {$del_sql}
                            WHERE hh_id = '{$hh_id[$i]}'";
            
            sql_query($insert_household);
        }else{

            if($hh_relationship[$i] != "" && $hh_name[$i] != "" && $hh_hp[$i] != ""){
                $insert_household = "INSERT INTO a_building_household SET
                            post_id = '{$post_id}',
                            building_id = '{$building_id}',
                            dong_id = '{$dong_id}',
                            ho_id = '{$ho_id}',
                            hh_relationship = '{$hh_relationship[$i]}',
                            hh_name = '{$hh_name[$i]}',
                            hh_hp = '{$hh_hp[$i]}',
                            ip_info = '{$ip_info}',
                            created_at = '{$today}'";
                sql_query($insert_household);
            }
            
        }
        
        // echo $insert_household.'<br>';
    }
}
// exit;

if($w == 'u'){
    alert('세대 구성원 정보가 수정되었습니다.');
}
?>