<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$dongho_sql = "";
if($mt_type != "OUT"){
    $dongho_sql = " dong_id = '{$dong_id}',
                    ho_id = '{$ho_id}', ";
}else{
    $dongho_sql = "";
}

if($w == "u"){

    //관리단 정보 조회
    $mt_row = sql_fetch("SELECT * FROM a_mng_team WHERE mt_id = '{$mt_id}'");
    
    //외부인의 경우 이름, 휴대폰번호가 변경되면
    if($mt_type == "OUT" && ($mt_row['mt_hp'] != $ho_tenant_hp || $mt_row['mt_name'] != $ho_tenant)){

        //입주자 회원인지 체크
        if($mt_row['mt_hp'] != $ho_tenant_hp){
            $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0 and mb_type = 'IN'");
            
            if($confirm_mb['cnt'] > 0) alert("입주자 회원으로 등록된 휴대폰 번호입니다.\\n외부인으로 등록 불가능합니다.");
        }
       

        // 회원정보 변경
        $update_member = "UPDATE a_member SET
                                mb_name = '{$ho_tenant}',
                                mb_hp = '{$ho_tenant_hp}'
                                WHERE mb_id = '{$mb_id}'";
        sql_query($update_member);
    }


    $update_query = "UPDATE a_mng_team SET
                        mt_type = '{$mt_type}',
                        post_id = '{$post_id}',
                        build_id = '{$building_id}',
                        {$dongho_sql}
                        mb_id = '{$mb_id}',
                        mt_name = '{$ho_tenant}',
                        mt_hp = '{$ho_tenant_hp}',
                        mt_grade = '{$mt_grade}',
                        mt_memo = '{$mt_memo}'
                        WHERE mt_id = '{$mt_id}'";

    //echo $update_query;
    sql_query($update_query);

}else{

    if($mt_type == "OUT"){
        //외부인의 경우

        //입주자 회원인지 체크
        $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0 and mb_type = 'IN'");
        if($confirm_mb['cnt'] > 0) alert("입주자 회원으로 등록된 휴대폰 번호입니다.\\n외부인으로 등록 불가능합니다.");

        //같은 단지 같은 직책으로 등록되었는지
        $confirm_mng = "SELECT COUNT(*) as cnt FROM a_mng_team WHERE building_id = '{$building_id}' and mt_hp = '{$ho_tenant_hp}' and mt_grade = '{$mt_grade}' and is_del = 0";


        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        //     echo $confirm_mng.'<br>';
        //     exit;
        // }
        $confirm_mng_row = sql_fetch($confirm_mng);
        if($confirm_mng_row['cnt'] > 0) alert("이미 해당 단지에 같은 직책으로 등록되어 있습니다.");


        //외부인 회원인지 체크
        $confirm_mb_out = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0 and mb_type = 'OUT'");

        if($confirm_mb_out['cnt'] == 0){
            //비밀번호 생성을 위해 휴대폰번호 배열로
            $mb_password = explode("-", $ho_tenant_hp);
            $pws = get_encrypt_string($mb_password[2]); //뒷자리 가져옴

            //관리자에서 가입된 회원 수 체크 
            //아이디를 만들기 위함
            $mb_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_admin = 'Y'");
            $mb_cnts = $mb_cnt['cnt'] + 1;
            $mb_id = "bansang_mb_".$mb_cnts;

            $insert_member = "INSERT INTO a_member SET
                                mb_type = 'OUT',
                                mb_id = '{$mb_id}',
                                mb_password = '{$pws}',
                                mb_name = '{$ho_tenant}',
                                mb_hp = '{$ho_tenant_hp}',
                                mb_admin = 'Y',
                                created_at = '{$today}'";
            sql_query($insert_member);
      
        }else{
            //이미 회원가입 되어있을 때 아이디만 조회
            $mb_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");
 
            $mb_id = $mb_info['mb_id'];
        }

    }else{
        //입주민인 경우
        $confirm_mng = "SELECT COUNT(*) as cnt FROM a_mng_team WHERE ho_id = '{$ho_id}' and mt_hp = '{$ho_tenant_hp}' and mt_grade = '{$mt_grade}' and is_del = 0";

        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        //     echo $confirm_mng.'<br>';
        //     exit;
        // }
        $confirm_mng_row = sql_fetch($confirm_mng);

        if($confirm_mng_row['cnt'] > 0) alert("이미 해당 단지에 같은 직책으로 등록되어 있습니다.");
    }
    

   //호수 입력
   $insert_query = "INSERT INTO a_mng_team SET
                    mt_type = '{$mt_type}',
                    post_id = '{$post_id}',
                    build_id = '{$building_id}',
                    {$dongho_sql}
                    mb_id = '{$mb_id}',
                    mt_name = '{$ho_tenant}',
                    mt_hp = '{$ho_tenant_hp}',
                    mt_grade = '{$mt_grade}',
                    mt_memo = '{$mt_memo}',
                    created_at = '{$today}'";

    //echo $insert_query.'<br>';
    //exit;
    sql_query($insert_query);
    $mt_id = sql_insert_id(); //매니져 idx
}

//exit;

if($w == 'u'){
    alert('관리단이 수정되었습니다.');
}else{
    // alert('관리단이 등록되었습니다.', './manage_form.php?'. $qstr . '&amp;w=u&amp;mt_id=' . $mt_id);
    alert('관리단이 등록되었습니다.', './manage_list.php');
}