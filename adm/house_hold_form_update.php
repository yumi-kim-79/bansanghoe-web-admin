<?php
require_once "./_common.php";

// if($_SERVER['REMOTE_ADDR'] != "59.16.155.80"){
//     echo "수정중입니다.";
//     exit;
// }


$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

if($post_id == "") alert("지역을 선택해주세요.");
if($building_id == "") alert("단지를 선택해주세요.");
if($dong_id == "") alert("동을 선택해주세요.");
// if($ho_size == "") alert("면적을 입력해주세요.");
// if($ho_owner == "") alert("소유자를 입력해주세요.");
// if($ho_owner_hp == "") alert("소유자 연락처를 입력해주세요.");
// if($ho_owner_sale_date == "") alert("소유자 매매일을 입력해주세요.");

//수정이거나 새로 입주할 때 현재 호는 검사에서 제외
if($w == "u" || $w == "a"){
    $and_my = " and ho_id != '{$ho_id}' ";
}else{
    $and_my = "";
}

//같은 호 이름이 존재하는지 검사
$confirm_ho_name = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_name = '{$ho_name}' and post_id = '{$post_id}' and building_id = '{$building_id}' and dong_id = '{$dong_id}' {$and_my}");
if($confirm_ho_name['cnt'] > 0) alert("같은 단지와 동에 같은 이름의 호수가 존재합니다.");

//다른 호수에 같은 번호로 입주자가 있는지 확인
// $confirm_ho = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_tenant_hp = '{$ho_tenant_hp}' {$and_my} ");
// if($confirm_ho['cnt'] > 0) alert("다른 단지에 입주가 등록된 입주자입니다.");

//세대구성원 중 빠진 값 체크..
for($i=0;$i<count($hh_relationship);$i++){

    //관계만 입력한 경우
    if($hh_relationship[$i] != ""){
        
        if($hh_name[$i] == ""){
            alert(($i + 1)."번째 세대 구성원의 이름을 입력하세요.");
        }

        // if($hh_hp[$i] == ""){
        //     alert(($i + 1)."번째 세대 구성원의 연락처를 입력하세요.");
        // }
    }

    //이름만 입력한 경우
    if($hh_name[$i] != ""){
        if($hh_relationship[$i] == ""){
            alert(($i + 1)."번째 세대 구성원과의 관계를 입력하세요.");
        }

        // if($hh_hp[$i] == ""){
        //     alert(($i + 1)."번째 세대 구성원의 연락처를 입력하세요.");
        // }
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


//수정
if($w == "u"){

    //호 정보 가져오기
    $ho_info = sql_fetch("SELECT * FROM a_building_ho WHERE ho_id = '{$ho_id}' and is_del = 0");

    $sql_hp = "";
    $mb_sql_hp = "";

    //입주자 연락처가 변경되었다면
    if($ho_tenant_hp != $ho_info['ho_tenant_hp']){

        $sql_hp .= " ho_tenant_hp = '{$ho_tenant_hp}', ";

    }

    $ho_tenant_id_up = "";
    
     //기존에 등록된 번호가 있었을 때
     if($ho_info['ho_tenant_hp'] != '' && $ho_tenant_hp != $ho_info['ho_tenant_hp']){
 
         // 휴대폰번호를 삭제했다면 단지에서 입주자 아이디 비우기
         if($ho_tenant_hp == ''){ 
             $update_query2 = "UPDATE a_building_ho SET
                                 ho_tenant_id = ''
                                 WHERE ho_id = '{$ho_id}'";
             sql_query($update_query2);
 
             //차량에 배정된 아이디도 삭제
             $update_carcar = "UPDATE a_building_car SET
                                 mb_id = ''
                                 WHERE ho_id = '{$ho_id}'";
             sql_query($update_carcar);
         }else{

            $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0 and mb_id != '{$ho_info['ho_tenant_id']}'");


            $confirm_mb_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0 and mb_id != '{$ho_info['ho_tenant_id']}'");

            // if( $confirm_mb['cnt'] > 0 && $ho_status == "Y"){
    
            //     //alert('이미 등록된 입주자 연락처입니다. 다른 연락처를 입력해주세요.');
            // }

            $mb_sql_hp = " mb_hp = '{$ho_tenant_hp}', "; //회원 연락처 변경

             //번호나 입주자명이 바뀌면 해당 정보로 등록된 다른 호의 번호도 바꿈
            //  || $ho_tenant != $ho_info['ho_tenant']
            //  --  mb_id = '{$confirm_mb_info['mb_id']}',
             if($ho_tenant_hp != $ho_info['ho_tenant_hp']){
                
                //  $update_ho = "UPDATE a_building_ho SET
                //                  ho_tenant = '{$ho_tenant}',
                //                  ho_tenant_hp = '{$ho_tenant_hp}'
                //                  WHERE ho_tenant_hp = '{$ho_info['ho_tenant_hp']}' and is_del = 0";
                $update_ho = "UPDATE a_building_ho SET
                              
                                 ho_tenant_hp = '{$ho_tenant_hp}'
                                 WHERE ho_id = '{$ho_id}'";
         
                sql_query($update_ho);
             }
         }
 
         
 
     }else{
       

 
        if($ho_tenant_hp != ''){
             $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");
 
 
             if($confirm_mb['cnt'] == 0){
 
                 //휴대폰 뒷자리를 초기 비밀번호로 사용
                 if($mb_password  == ""){
                     $mb_passwords = explode("-", $ho_tenant_hp);
                     $pws = get_encrypt_string($mb_passwords[2]);
                 }else{
                     $pws = get_encrypt_string($mb_password);
                 }
               
              
                 //관리자에서 가입된 회원 수 체크 
                 //아이디를 만들기 위함
                 //mb_admin 관리자에서 가입
                 $mb_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_admin = 'Y'");
                 $mb_cnts = $mb_cnt['cnt'] + 1;
                 $mb_id = "bansang_mb_".$mb_cnts;
         
                 $insert_member = "INSERT INTO a_member SET
                                     mb_type = 'IN',
                                     mb_id = '{$mb_id}',
                                     mb_password = '{$pws}',
                                     mb_name = '{$ho_tenant}',
                                     mb_hp = '{$ho_tenant_hp}',
                                     mb_admin = 'Y',
                                     created_at = '{$today}'";
                 // echo $insert_member.'<br>';
                 sql_query($insert_member);
 
                 $ho_tenant_id_up = $mb_id;
                 
             }else{
 
                 $mb_infos = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");
         
                 $ho_tenant_id_up = $mb_infos['mb_id'];
             }
 
             $update_query2 = "UPDATE a_building_ho SET
                                 ho_tenant_id = '{$ho_tenant_id_up}'
                                 WHERE ho_id = '{$ho_id}'";
             sql_query($update_query2);
 
             // echo $update_query.'1<br>';
             // exit;
         }

         $history_sqls = "SELECT * FROM a_building_household_history WHERE ho_id = '{$ho_id}' and history_status = 'IN' ORDER BY created_at DESC LIMIT 1";
        //  echo $history_sqls.'<br>';
         $history_rows = sql_fetch($history_sqls);
 
         //입주자 연락처가 입력되었다면
         $up_history = "UPDATE a_building_household_history SET
                                 history_id = '{$ho_tenant_id_up}',
                                 history_hp = '{$ho_tenant_hp}'
                                 WHERE history_idx = '{$history_rows['history_idx']}'";
 
        //  echo $up_history.'<br>';
         sql_query($up_history);


         //비밀번호가 입력되었다면
         $sql_pwd = "";
 
         if($mb_password != ""){
 
             $pws = get_encrypt_string($mb_password);
             $sql_pwd = " mb_password = '{$pws}', ";
         }

        //회원정보 업데이트
        // {$mb_sql_hp}
        $update_member = "UPDATE a_member SET
                        {$sql_pwd}
                        {$mb_sql_hp}
                        mb_name = '{$ho_tenant}'
                        WHERE mb_hp = '{$ho_info['ho_tenant_hp']}'";
        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
        //     echo $update_member.'<br>';
        //     exit;
        // }
        sql_query($update_member);

        
     }

     //입주자명이 변경되었다면
     if($ho_tenant != $ho_info['ho_tenant']){
        $history_sqls = "SELECT * FROM a_building_household_history WHERE ho_id = '{$ho_id}' and history_status = 'IN' ORDER BY created_at DESC LIMIT 1";
        //  echo $history_sqls.'<br>';
         $history_rows = sql_fetch($history_sqls);
 
         //입퇴실 내역 가장 최근의 입주자도 변경
         $up_history = "UPDATE a_building_household_history SET
                                 history_name = '{$ho_tenant}'
                                 WHERE history_idx = '{$history_rows['history_idx']}'";
 
        //  echo $up_history.'<br>';
         sql_query($up_history);
     }
    //  exit;

    //호 정보 업데이트
    $update_query = "UPDATE a_building_ho SET
                    ho_owner = '{$ho_owner}',
                    ho_owner_hp = '{$ho_owner_hp}',
                    ho_owner_sale_date = '{$ho_owner_sale_date}',
                    ho_tenant = '{$ho_tenant}',
                    {$sql_hp}
                    ho_tenant_at = '{$ho_tenant_at}',
                    ho_size = '{$ho_size}',
                    ho_memo = '{$ho_memo}'
                    WHERE ho_id = '{$ho_id}'";
   
    sql_query($update_query);

   

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

            if($hh_relationship[$i] != "" && $hh_name[$i] != ""){
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
        
    }


    //회원아이디 가져오기
    $user = get_user_hp($ho_tenant_hp);


    //차량관리 추가
    for($i=0;$i<count($car_type);$i++){

        if($car_id[$i] != ""){

            $del_sql = "";
            //삭제가 체크되어있으면
            if($car_del[$i]){
                $del_sql = " ,
                            is_del = 1,
                             deleted_at = '{$today}' ";
            }

            $insert_car = "UPDATE a_building_car SET
                        mb_id = '{$ho_tenant_id_up}',
                        car_type = '{$car_type[$i]}',
                        car_name = '{$car_name[$i]}'
                        {$del_sql}
                        WHERE car_id = '{$car_id[$i]}'";
            sql_query($insert_car);

        }else{

            if($car_type[$i] != "" && $car_name[$i] != ""){
                $insert_car = "INSERT INTO a_building_car SET
                            building_id = '{$building_id}',
                            dong_id = '{$dong_id}',
                            ho_id = '{$ho_id}',
                            mb_id = '{$ho_tenant_id_up}',
                            car_type = '{$car_type[$i]}',
                            car_name = '{$car_name[$i]}',
                            ip_info = '{$ip_info}',
                            created_at = '{$today}'";
                sql_query($insert_car);
            }
        }
        
    }

    // 퇴실 처리시
    if($ho_status == "N"){
   
        $ho_status_t = "OUT";

        //해당 호수의 세대구성원 삭제
        $update_household = "UPDATE a_building_household SET
                            is_del = 1,
                            deleted_at = '{$today}'
                            WHERE ho_id = '{$ho_id}'";
        sql_query($update_household);

        //해당 호수의 차량정보 삭제
        $update_car = "UPDATE a_building_car SET
                        is_del = 1,
                        deleted_at = '{$today}'
                        WHERE ho_id = '{$ho_id}'";
        sql_query($update_car);


        //퇴실 내역 추가
        $history_tenant_date = date("Y-m-d");

        $insert_history = "INSERT INTO a_building_household_history SET
                        ho_id = '{$ho_id}',
                        history_id = '{$ho_info['ho_tenant_id']}',
                        history_name = '{$ho_tenant}',
                        history_hp = '{$ho_tenant_hp}',
                        history_status = '{$ho_status_t}',
                        history_tenant_date = '{$history_tenant_date}',
                        created_at = '{$today}'";

        //echo $insert_history.'<br>';
        sql_query($insert_history);
        

        //호수에서 입주자 비우기
        $delete_ho = "UPDATE a_building_ho SET
                            ho_tenant_id = '',
                            ho_tenant = '',
                            ho_tenant_hp = '',
                            ho_tenant_at = '',
                            ho_status = 'N'
                            WHERE ho_id = '{$ho_id}'";
        sql_query($delete_ho);


        //해당 아이디로 등록된 호수가 있다면
        $mb_chk = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_tenant_id = '{$ho_info['ho_tenant_id']}' and ho_status = 'Y'");

        //관리단 삭제
        $update_mng_team = "UPDATE a_mng_team SET
                            is_del = 1,
                            deleted_at = '{$today}'
                            WHERE ho_id = '{$ho_id}' and mb_id = '{$ho_info['ho_tenant_id']}'";
        sql_query($update_mng_team);
  
        //회원정보는 실제 삭제하지 않음
        if($mb_chk['cnt'] == 0){

            //퇴실한 사람 회원 삭제처리 자동로그인 및 토큰도 삭제
            $delete_member = "UPDATE a_member SET 
                                mb_auto = 0,
                                mb_token = '',
                                is_del = 1,
                                deleted_at = '{$today}'
                                WHERE mb_id = '{$ho_info['ho_tenant_id']}'";
            sql_query($delete_member);
        }

    }

}else{

    //입주자 새로 등록
    //250904 삭제..
    // if($ho_tenant_hp == "") alert("입주자 연락처를 입력해주세요.");
    if($ho_tenant_at == "") alert("입주일을 선택해주세요.");

    //호 정보 업데이트
    $update_query = "UPDATE a_building_ho SET
                    ho_owner = '{$ho_owner}',
                    ho_owner_hp = '{$ho_owner_hp}',
                    ho_owner_sale_date = '{$ho_owner_sale_date}',
                    ho_tenant = '{$ho_tenant}',
                    ho_tenant_hp = '{$ho_tenant_hp}',
                    ho_tenant_at = '{$ho_tenant_at}',
                    ho_size = '{$ho_size}',
                    ho_status = 'Y',
                    ho_memo = '{$ho_memo}'
                    WHERE ho_id = '{$ho_id}'";
    

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    //     echo $update_query.'<br>';
    //     exit;
    // }
    sql_query($update_query);


    //입주자 연락처가 있을 때
    $ho_tenant_id = '';
    if($ho_tenant_hp != ''){

        //이미 존재하는 회원인지 검사
        $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");

        // print_r2($confirm_mb);

        //없으면 회원추가
        if($confirm_mb['cnt'] == 0){

            //휴대폰 뒷자리를 초기 비밀번호로 사용
            $mb_passwords = explode("-", $ho_tenant_hp);
            $pws = get_encrypt_string($mb_passwords[2]);
        
            //관리자에서 가입된 회원 수 체크 
            //아이디를 만들기 위함
            //mb_admin 관리자에서 가입
            $mb_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_admin = 'Y'");
            $mb_cnts = $mb_cnt['cnt'] + 1;
            $mb_id = "bansang_mb_".$mb_cnts;

            $insert_member = "INSERT INTO a_member SET
                                mb_type = 'IN',
                                mb_id = '{$mb_id}',
                                mb_password = '{$pws}',
                                mb_name = '{$ho_tenant}',
                                mb_hp = '{$ho_tenant_hp}',
                                mb_admin = 'Y',
                                created_at = '{$today}'";
            sql_query($insert_member);


            $ho_tenant_id = $mb_id;
            //ho_tenant_id 업데이트
            $update_query = "UPDATE a_building_ho SET
                                ho_tenant_id = '{$ho_tenant_id}'
                                WHERE ho_id = '{$ho_id}'";
            sql_query($update_query);

        }else{

            $mb_infos = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");

            $ho_tenant_id = $mb_infos['mb_id'];

            $update_query = "UPDATE a_building_ho SET
                                ho_tenant_id = '{$ho_tenant_id}'
                                WHERE ho_id = '{$ho_id}'";
            sql_query($update_query);
        }
    }
   

    

    //세대원 추가 및 수정
    for($i=0;$i<count($hh_relationship);$i++){

        if($hh_relationship[$i] != '' && $hh_name[$i] != ''){

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

  //입주자 연락처가 있다면
    if($ho_tenant_hp != ''){
        //가입된 회원아이디 가져오기
        $user = get_user_hp($ho_tenant_hp);
        $mb_id_new = $user['mb_id'];
    }else{
        $mb_id_new = "";
    }


    //차량관리 추가
    for($i=0;$i<count($car_type);$i++){

        if($car_type[$i] != '' && $car_name[$i] != ""){

            $insert_car = "INSERT INTO a_building_car SET
                            building_id = '{$building_id}',
                            dong_id = '{$dong_id}',
                            ho_id = '{$ho_id}',
                            mb_id = '{$mb_id_new}',
                            car_type = '{$car_type[$i]}',
                            car_name = '{$car_name[$i]}',
                            ip_info = '{$ip_info}',
                            created_at = '{$today}'";
            sql_query($insert_car);
        }
        
    }


    // 입주 내역 추가
    $insert_history = "INSERT INTO a_building_household_history SET
                    ho_id = '{$ho_id}',
                    history_id = '{$ho_tenant_id}',
                    history_name = '{$ho_tenant}',
                    history_hp = '{$ho_tenant_hp}',
                    history_status = 'IN',
                    history_tenant_date = '{$ho_tenant_at}',
                    created_at = '{$today}'";

    //echo $insert_history.'<br>';
    sql_query($insert_history);
    
}

//exit;

if($w == 'u'){
    if($ho_status == "N"){
        // alert("퇴실처리 되었습니다.", './house_hold_form.php?'. $qstr . '&amp;w=a&amp;ho_id=' . $ho_id);

        alert("퇴실처리 되었습니다.", './house_hold_form.php?'. $qstr . '&amp;w=a&amp;ho_id=' . $ho_id);
    }else{
        alert('세대 정보가 수정되었습니다.');
        //alert('세대 정보가 수정되었습니다.', './house_hold_form.php?'. $qstr . '&amp;w=u&amp;ho_id=' . $ho_id);
    }
}else{
    alert('세대가 등록되었습니다.', './house_hold_form.php?'. $qstr . '&amp;w=u&amp;ho_id=' . $ho_id);
}
?>