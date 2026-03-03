<?php
switch($basename){
    case "index.php":
        $headerType = "ver1";
        $headerbg = "bg";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "sm_index.php":
        $headerType = "ver1";
        $headerTitle = "SM 매니저";
        $headerbg = "bg";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "sm_login_agree.php":
        $headerType = "ver2";
        $headerTitle = "약관 동의";
    break;
    case "bill_sm.php":
        $headerType = "ver1";
        $headerTitle = "결재관리";
        $footerType = "ver_sm";
        $tab_on2 = "on";
    break;
    case "building_sch.php":
        $headerType = "ver1";
        $headerTitle = "단지검색";
        $headerbg = "bg";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "board_list.php":
        $headerType = "ver1";
        $headerTitle = "게시판";
        $footerType = "ver_sm";
        $tab_on31 = "on";
    break;
    case "find_id.php":
    case "find_id_res.php":
    case "find_pw.php":
    case "find_pw_res.php":
        $headerType = "ver2";
        $footerType = "";
    break;
    case "register.php":
        $headerType = "ver2";
        $footerType = "";
    break;
    case "building_news.php":
        $headerType = "ver2";
        $headerTitle = "단지소식";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "building_new_info.php":
        $headerType = "ver2";
        $headerTitle = "단지소식";
    break;
    case "online_vote.php":
        $headerType = "ver2";
        $headerTitle = "온라인 투표";
        if($types == "sm"){
            $footerType = "ver_sm";
            $tab_on3 = "on";
        }else{
            $footerType = "ver1";
            $tab_on1 = "on";
        }
    break;
    case "online_vote_info.php":
        $headerType = "ver2";
        $headerTitle = "온라인 투표";
    break;
    case "online_vote_result.php":
        $headerType = "ver2";
        $headerTitle = "투표자 리스트";
    break;
    case "expense_report.php":
        $headerType = "ver2";
        $headerTitle = "품의서";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "expense_report_info.php":
        $headerType = "ver2";
        $headerTitle = "품의서";
    break;
    case "expense_report_adm.php":
        $headerType = "ver2";
        $headerTitle = "품의서 [관리단 결재]";
        $footerType = "ver1";
        $tab_on4 = "on";
    break;
    case "expense_report_adm_info.php":
        $headerType = "ver2";
        $headerTitle = "품의서 [관리단 결재]";
        $footerType = "ver1";
        $tab_on4 = "on";
    break;
    case "move_request.php":
        $headerType = "ver2";
        $headerTitle = "이사(전출) 신청";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "parking_manage.php":
        $headerType = "ver2";
        $headerTitle = "주차 관리";
        $tab_on1 = "on";
    break;
    case "inspection_info.php":
        $headerType = "ver2";
        $headerTitle = "점검일지";
    break;
    case "inspection_end.php":
        $headerType = "ver2";
        $headerTitle = "점검일지 완료";
    break;
    case "inspection_lists.php":
        $headerType = "ver2";
        $headerTitle = "점검일지";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "mng_company.php":
        $headerType = "ver2";
        $headerTitle = "관리업체";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "mng_policy.php":
        $headerType = "ver2";
        $headerTitle = "관리 규약";
        $footerType = "ver1";
        $tab_on1 = "on";
    break;
    case "mng_policy2.php":
        $headerType = "ver2";
        $headerTitle = "관리 규약";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "bill.php":
        $headerType = "ver1";
        $headerTitle = "고지서";
        $footerType = "ver1";
        $tab_on2 = "on";
    break;
    case "online_complain.php":
        $headerType = "ver1";
        $headerTitle = "온라인 민원";
        $footerType = "ver1";
        $tab_on3 = "on";
    break;
    case "online_complain_info.php":
        $headerType = "ver2";
        $headerTitle = "온라인 민원";
        $footerType = "ver1";
        $tab_on3 = "on";
    break;
    case "online_complain_form.php":
        $headerType = "ver2";
        $headerTitle = "온라인 민원";
        $footerType = "ver1";
        $tab_on3 = "on";
    break;
    case "mypage.php":
        $headerType = "ver1";
        $headerTitle = "마이페이지";
         if($types == "sm"){
            $footerType = "ver_sm";
        }else{
            $footerType = "ver1";
        }
        $tab_on4 = "on";
    break;
    case "policy.php":
        $headerType = "ver2";
        if($co_id == 'privacy' || $co_id == 'privacy_sm'){
            $headerTitle = "개인정보처리방침";
        }else if($co_id == 'provision' || $co_id == 'provision_sm'){
            $headerTitle = "서비스 이용약관";
        }else if($co_id == 'qr_privacy'){
            $headerTitle = 'QR 체커 서비스 이용약관';
        }
        if($types == "sm"){
            $footerType = "ver_sm";
        }else{
            $footerType = "ver1";
        }
        $tab_on4 = "on";
    break;
    case "policy_use.php":
        $headerType = "ver2";
        $headerTitle = "이용약관";
        $footerType = "ver1";
        $tab_on4 = "on";
    break;
    case "notification_setting.php":
        $headerType = "ver2";
        $headerTitle = "알림설정";
        if($types == "sm"){
            $footerType = "ver_sm";
        }else{
            $footerType = "ver1";
        }
        $tab_on4 = "on";
    break;
    case "my_info.php":
        $headerType = "ver2";
        $headerTitle = "내정보";
    break;
    case "app_info.php":
        $headerType = "ver2";
        $headerTitle = "앱정보";
    break;
    case "notification_list.php":
        $headerType = "ver2";
        $headerTitle = "알림";
        if($types == "sm"){
            $footerType = "ver_sm";
        }else{
            $footerType = "ver1";
        }
        $tab_on1 = "on";
    break;
    case "schedule_add.php":
    case "schedule_add2.php":
        $headerType = "ver2";
        if($w == 'u'){
            $headerTitle = "일정 변경";
        }else if($w == 'i'){
            $headerTitle = "일정 상세";
        }else{
            $headerTitle = "일정 추가";
        }
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "sm_move.php":
        $headerType = "ver2";
        $headerTitle = "이사/전출";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "sm_complain_info.php":
        $headerType = "ver2";
        $headerTitle = "민원";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "inspection_log_list.php":
        $headerType = "ver2";
        $headerTitle = "점검일지";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "inspection_info_sm.php":
        $headerType = "ver2";
        $headerTitle = "점검일지";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "inspection_form.php":
        $headerType = "ver2";
        $headerTitle = "점검일지 작성";
        $tab_on1 = "on";
    break;
    case "meter_reading.php":
        $headerType = "ver2";
        $headerTitle = "검침";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "meter_reading_info.php":
        $headerType = "ver2";
        $headerTitle = "검침";
        $footerType = "ver_sm";
        $tab_on1 = "on";
    break;
    case "approval_document.php":
        $headerType = "ver2";
        $headerTitle = "결재 서류함";
        $footerType = "ver_sm";
        $tab_on2 = "on";
    break;
    case "building_mng.php":
        $headerType = "ver2";
        $headerTitle = "단지 관리";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "building_info.php":
        $headerType = "ver2";
        $headerTitle = "단지 정보";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "building_info_form.php":
        $headerType = "ver2";
        $headerTitle = "단지 정보 수정";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "household_mng.php":
        $headerType = "ver2";
        $headerTitle = "세대 관리";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "household_mng_info.php":
        $headerType = "ver2";
        $headerTitle = "세대 정보";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_manage_info.php":
        $headerType = "ver2";
        $headerTitle = "관리단 정보";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_car_manage.php":
        $headerType = "ver2";
        $headerTitle = "차량 관리";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_manage_company.php":
        $headerType = "ver2";
        $headerTitle = "관리 업체";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_mng_company_info.php":
        $headerType = "ver2";
        $headerTitle = "관리 업체";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "expense_report_list.php":
        $headerType = "ver2";
        $headerTitle = "품의서";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "expense_report_form.php":
        $headerType = "ver2";
        $headerTitle = "품의서";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_board.php":
        $headerType = "ver2";
        $headerTitle = "공문/안내문/이벤트";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "sm_board_info.php":
        $headerType = "ver2";
        if($types == "public"){
            $headerTitle = "공문";
        }else if($types == "info"){
            $headerTitle = "안내문";
        }else if($types == "event"){
            $headerTitle = "이벤트";
        }
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "building_memo.php":
        $headerType = "ver2";
        $headerTitle = "단지메모";
        $footerType = "ver_sm";
        $tab_on3 = "on";
    break;
    case "board_info.php":
        $headerType = "ver2";
        $headerTitle = "게시판";
        $footerType = "ver_sm";
        $tab_on31 = "on";
    break;
    case "board_write.php":
        $headerType = "ver2";
        $headerTitle = "글작성";
        $footerType = "ver_sm";
        $tab_on31 = "on";
    break;
    case "holiday_reqeust.php":
        $headerType = "ver2";

        $hd_titles = sql_fetch("SELECT sign_cate_name FROM a_sign_off_category WHERE sign_cate_code = '{$types}'");
        $headerTitle = $hd_titles['sign_cate_name'];
        $footerType = "ver_sm";
        $tab_on2 = "on";
    break;
    case "holiday_reqeust_form.php":
        $headerType = "ver2";
        $hd_titles = sql_fetch("SELECT sign_cate_name FROM a_sign_off_category WHERE sign_cate_code = '{$types}'");
        $headerTitle = $hd_titles['sign_cate_name'];
        $footerType = "ver_sm";
        $tab_on2 = "on";
    break;
    case "holiday_reqeust_info.php":
        $headerType = "ver2";
        $hd_titles = sql_fetch("SELECT sign_cate_name FROM a_sign_off_category WHERE sign_cate_code = '{$types}'");
        $headerTitle = $hd_titles['sign_cate_name'];
        $footerType = "ver_sm";
        $tab_on2 = "on";
    break;
}
?>