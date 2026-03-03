<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

// if($_SERVER['REMOTE_ADDR'] != '59.16.155.80'){
//     echo "현재 점검 중인 페이지입니다.";
//     exit;
// }

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$dong_sql = "SELECT dong.*, building.post_id, building.building_name FROM a_building_dong as dong
             LEFT JOIN a_building as building ON dong.building_id = building.building_id
             WHERE dong.dong_id = '{$dong_id}'";
$dong_row = sql_fetch($dong_sql);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if($_FILES['excelfile']['size'] <= 0) alert("엑셀 파일이 없습니다.");

$tmp_name   = $_FILES['excelfile']['tmp_name'];
$file_name  = $_FILES['excelfile']['name'];
$file_type  = pathinfo($file_name, PATHINFO_EXTENSION);

if ($file_type =='xls')       $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
else if ($file_type =='xlsx') $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
else                          alert("엑셀 파일만 업로드 가능합니다.");

$spread_sheet = $reader->load($tmp_name);
$spread_data  = $spread_sheet->getActiveSheet()->toArray();
$excel_data    = [];



// print_r2($spread_data);

// exit;
$j=0;
for($i=6;$i<count($spread_data);$i++){

    //호수가 없는 경우 실패처리
    if($spread_data[$i][0] != '') {
   
        $excel_data[$j]['ho_name'] = $spread_data[$i][0]; //호수
        $excel_data[$j]['ho_size'] = $spread_data[$i][1]; //면적
        $excel_data[$j]['ho_status'] = $spread_data[$i][2]; //상태

        $excel_data[$j]['ho_owner'] = $spread_data[$i][3]; //소유자
        $excel_data[$j]['ho_owner_hp'] = $spread_data[$i][4]; //소유자 연락처

        $excel_data[$j]['ho_tenant'] = $spread_data[$i][5]; // 입주자
        $excel_data[$j]['ho_tenant_hp'] = $spread_data[$i][6]; //입주자 연락처
        $excel_data[$j]['ho_tenant_at'] = $spread_data[$i][7]; //입주일
        
        $excel_data[$j]['car'] = [];
        $excel_data[$j]['car'][0]['car_type'] = $spread_data[$i][8]; //차량정보
        $excel_data[$j]['car'][0]['car_name'] = $spread_data[$i][9]; //차량정보

        $excel_data[$j]['car'][1]['car_type'] = $spread_data[$i][10]; //차량정보
        $excel_data[$j]['car'][1]['car_name'] = $spread_data[$i][11]; //차량정보

        $excel_data[$j]['car'][2]['car_type'] = $spread_data[$i][12]; //차량정보
        $excel_data[$j]['car'][2]['car_name'] = $spread_data[$i][13]; //차량정보
        
        $excel_data[$j]['hh'] = [];
        $excel_data[$j]['hh'][0]['hh_relationship'] = $spread_data[$i][14]; //1구성원 관계
        $excel_data[$j]['hh'][0]['hh_name'] = $spread_data[$i][15]; //1구성원 이름
        $excel_data[$j]['hh'][0]['hh_hp'] = $spread_data[$i][16]; //1구성원 연락처

        $excel_data[$j]['hh'][1]['hh_relationship'] = $spread_data[$i][17]; //2구성원 관계
        $excel_data[$j]['hh'][1]['hh_name'] = $spread_data[$i][18]; //2구성원 이름
        $excel_data[$j]['hh'][1]['hh_hp'] = $spread_data[$i][19]; //3구성원 연락처

        $excel_data[$j]['hh'][2]['hh_relationship'] = $spread_data[$i][20]; //3구성원 관계
        $excel_data[$j]['hh'][2]['hh_name'] = $spread_data[$i][21]; //3구성원 이름
        $excel_data[$j]['hh'][2]['hh_hp'] = $spread_data[$i][22]; //3구성원 연락처

        $excel_data[$j]['hh'][3]['hh_relationship'] = $spread_data[$i][23]; //4구성원 관계
        $excel_data[$j]['hh'][3]['hh_name'] = $spread_data[$i][24]; //4구성원 이름
        $excel_data[$j]['hh'][3]['hh_hp'] = $spread_data[$i][25]; //4구성원 연락처

        $excel_data[$j]['hh'][4]['hh_relationship'] = $spread_data[$i][26]; //5구성원 관계
        $excel_data[$j]['hh'][4]['hh_name'] = $spread_data[$i][27]; //5구성원 이름
        $excel_data[$j]['hh'][4]['hh_hp'] = $spread_data[$i][28]; //5구성원 연락처

        $j++;
    }
 
}

// print_r2($excel_data);

// exit;

if(count($excel_data) == 0) alert("내용을 입력해주세요.");

$total_count = 0;
$fail_count = 0;
//초기화...


// if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
//     print_r2($excel_data);

//     exit;
// }


//등록된 호수가 있는지 체크
$total_ho = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE is_del = 0 and dong_id = '{$dong_id}'");

if($total_ho['cnt'] > 0){ //이미 호가 존재하면 덮어쓰기 위해 삭제처리
    //호 삭제
    $del_hoho = "UPDATE a_building_ho SET 
                is_del = 1,
                deleted_at = '{$today}'
                WHERE dong_id = '{$dong_id}'";
    // echo $del_hoho.'<br>';

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        sql_query($del_hoho);
    }
}

//엑셀 데이터 입력
$sql_arr = array();
for($i=0;$i<count($excel_data);$i++){

    $ho_name = $excel_data[$i]['ho_name'];

    //호수가 없는 경우 실패처리
    if($ho_name == "") {
        $fail_count++;
        continue;
    }

    $ho_status_trim = trim($excel_data[$i]['ho_status']); //앞뒤 공백 제거

    $ho_size = $excel_data[$i]['ho_size'];
    $ho_status = $ho_status_trim == "입주" ? "Y" : "N";
    $ho_status2 = $ho_status == 'Y' ? 'IN' : 'OUT'; //히스토리용

    $ho_owner =  $excel_data[$i]['ho_owner'];
    $ho_owner_hp = $excel_data[$i]['ho_owner_hp'];

    $ho_tenant =  $excel_data[$i]['ho_tenant'];
    $ho_tenant_hp = $excel_data[$i]['ho_tenant_hp'];
   
    if($excel_data[$i]['ho_tenant_at'] != ''){
        $ho_tenant_at = date("Y-m-d", strtotime($excel_data[$i]['ho_tenant_at']));
    }else{
        $ho_tenant_at = "";
    }

    $car_data = $excel_data[$i]['car'];
    $household_data = $excel_data[$i]['hh'];

    //입주자 연락처가 입력되었을 때
    if($ho_tenant_hp != ""){

        //회원인지 체크
        $confirm_mb = sql_fetch("SELECT COUNT(*) as cnt FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");

         //회원가입 회원이 아닌경우
         if($confirm_mb['cnt'] == 0){
            $mb_password = explode("-", $ho_tenant_hp);
            $pws = get_encrypt_string($mb_password[2]);

            //관리자에서 가입된 회원 수 체크 
            //아이디를 만들기 위함
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
            // echo $insert_member.'<br><br>';
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                sql_query($insert_member);
            }
            
        }else{
            $mb_info = sql_fetch("SELECT * FROM a_member WHERE mb_hp = '{$ho_tenant_hp}' and is_del = 0");

            $mb_id = $mb_info['mb_id'];
        }
    }else{
        $mb_id = "";
    }
    
    // 해당 호수가 등록되어있는지
    $ho_confirm = "SELECT *, COUNT(*) as cnt FROM a_building_ho WHERE 
                        post_id = '{$dong_row['post_id']}' and
                        building_id = '{$dong_row['building_id']}' and
                        dong_id = '{$dong_row['dong_id']}' and
                        ho_name = '{$ho_name}'";
    $ho_confirm_row = sql_fetch($ho_confirm);

    
    
    $sql_common = " ho_name = '{$ho_name}',
                    ho_size = '{$ho_size}',
                    ho_owner = '{$excel_data[$i]['ho_owner']}',
                    ho_owner_hp = '{$excel_data[$i]['ho_owner_hp']}',
                    ho_status = '{$ho_status}', ";

    if($ho_status == 'Y'){
        $sql_common .= " ho_tenant_id = '{$mb_id}',
                    ho_tenant = '{$ho_tenant}',
                    ho_tenant_hp = '{$ho_tenant_hp}',
                    ho_tenant_at = '{$ho_tenant_at}', ";
    }


   

    if($ho_confirm_row['cnt'] > 0){

       
        // if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
        //     sql_query($del_hh);
        // }

        $update_query = "UPDATE a_building_ho SET
                            {$sql_common}
                            is_del = 0,
                            deleted_at = NULL,
                            created_at = '{$today}'
                            WHERE ho_id = '{$ho_confirm_row['ho_id']}'";
        
        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            //echo $update_query.'<br>';
            sql_query($update_query);
        }else{
            array_push($sql_arr, $update_query);
        }

        

        $ho_id = $ho_confirm_row['ho_id']; // 호수 idx

        

        $insert_history = "INSERT INTO a_building_household_history SET
                            ho_id = '{$ho_id}',
                            history_id = '{$mb_id}',
                            history_name = '{$ho_tenant}',
                            history_hp = '{$ho_tenant_hp}',
                            history_status = '{$ho_status2}',
                            history_tenant_date = '{$ho_tenant_at}',
                            created_at = '{$today}'";
        // echo $insert_hh.'<br>';
        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            sql_query($insert_history);
        }

    }else{
        $insert_query = "INSERT INTO a_building_ho SET
                            post_id = '{$dong_row['post_id']}',
                            building_id = '{$dong_row['building_id']}',
                            dong_id = '{$dong_row['dong_id']}',
                            {$sql_common}
                            created_at = '{$today}'";

        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            sql_query($insert_query);
            $ho_id = sql_insert_id(); //호수 idx
        }else{
            array_push($sql_arr, $insert_query);
        }

        //입주자 연락처 있는 경우에만
        if($ho_tenant_hp != ""){    
            //입퇴실 히스토리 신규 추가
            $insert_history = "INSERT INTO a_building_household_history SET
                                ho_id = '{$ho_id}',
                                history_id = '{$mb_id}',
                                history_name = '{$ho_tenant}',
                                history_hp = '{$ho_tenant_hp}',
                                history_status = '{$ho_status2}',
                                history_tenant_date = '{$ho_tenant_at}',
                                created_at = '{$today}'";
            // echo $insert_hh.'<br>';
            if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                sql_query($insert_history);
            }
        }
    }

    //차량정보
    if(count($car_data) > 0){

        //해당 호수로 등록된 차량정보 삭제
        $del_car = "UPDATE a_building_car SET is_del = 1 WHERE ho_id = '{$ho_id}' and mb_id = '{$mb_id}'";
        sql_query($del_car);

        // for($j=0;$j<count($car_data);$j++){
        for($j=0;$j<count($car_data);$j++){ //3대까지만 등록

            //새로 추가
            if($car_data[$j]['car_type'] != ''){
                $insert_car = "INSERT INTO a_building_car SET
                                building_id = '{$dong_row['building_id']}',
                                dong_id = '{$dong_row['dong_id']}',
                                ho_id = '{$ho_id}',
                                mb_id = '{$mb_id}',
                                car_type = '{$car_data[$j]['car_type']}',
                                car_name = '{$car_data[$j]['car_name']}',
                                ip_info = '{$ip_info}',
                                created_at = '{$today}'";
                // echo $insert_car.'<br>';
                if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                    sql_query($insert_car);
                }
            }
           
        }
    }

    //구성원
    if(count($household_data) > 0){

        //해당 호수로 등록된 세대정보 삭제
        $del_hh = "UPDATE a_building_household SET is_del = 1 WHERE ho_id = '{$ho_id}'";
        if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            sql_query($del_hh);
        }

        for($k=0;$k<count($household_data);$k++){
           
            //다시 등록
            if($household_data[$k]['hh_relationship'] != '' && $household_data[$k]['hh_name'] != ''){
                $insert_hh = "INSERT INTO a_building_household SET
                                post_id = '{$dong_row['post_id']}',
                                building_id = '{$dong_row['building_id']}',
                                dong_id = '{$dong_row['dong_id']}',
                                ho_id = '{$ho_id}',
                                hh_relationship = '{$household_data[$k]['hh_relationship']}',
                                hh_name = '{$household_data[$k]['hh_name']}',
                                hh_hp = '{$household_data[$k]['hh_hp']}',
                                created_at = '{$today}'";
                // echo $insert_hh.'<br>';
           
                if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
                    sql_query($insert_hh);
                }
            }
           
        }
    }

    $total_count++; //총 업데이트 수
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    print_r2($sql_arr);
    exit;
}
$g5['title'] = '입주자 정보 엑셀등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>입주자 등록을 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>등록건수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
    </dl>

    <dl id="excelfile_result">
        <dt>실패건수</dt>
        <dd><?php echo number_format($fail_count); ?></dd>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="closeAndReload();">창닫기</button>
    </div>

</div>
<script>
function closeAndReload() {
    if (window.opener && !window.opener.closed) {
        window.opener.location.reload();
    }
    window.close();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>