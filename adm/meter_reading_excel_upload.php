<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if($_FILES['excelfile']['size'] <= 0) alert("엑셀 파일이 없습니다.");

$type_t = $type == 'electro' ? '전기' : '수도';

$building_row = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}' and is_del = 0");

$dates = date("Y-m-d");
$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

$tmp_name   = $_FILES['excelfile']['tmp_name'];
$file_name  = $_FILES['excelfile']['name'];
$file_type  = pathinfo($file_name, PATHINFO_EXTENSION);

if ($file_type =='xls')       $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
else if ($file_type =='xlsx') $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
else                          alert("엑셀 파일만 업로드 가능합니다.");

$spread_sheet = $reader->load($tmp_name);
$spread_data  = $spread_sheet->getActiveSheet()->toArray();
$excel_data    = [];

//print_r2($spread_data);

$meter_dates = $spread_data[1][11]; //검침일자
$meter_date = date('Y-m-d', strtotime($meter_dates));

$mr_year = $mr_year != '' ? $mr_year : date('Y', strtotime($meter_dates));
$mr_month = $mr_month != '' ? $mr_month : date('n', strtotime($meter_dates));

$j = 0;
for($i=3;$i<count($spread_data);$i++){

    $excel_data[$j]['dong_name'] = trim($spread_data[$i][0]); //동
    $excel_data[$j]['ho_name'] = trim($spread_data[$i][1]); //호수
    $excel_data[$j]['val'] = trim($spread_data[$i][2]); //호수

    $j++;
}

// print_r2($excel_data);
$mngs = get_manger($mr_id);

$meter_building_confirm = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$mr_year}' and mr_month = '{$mr_month}'");

if($meter_building_confirm['cnt'] > 0){

    $mr_idx = $meter_building_confirm['mr_idx'];

    if($type == 'electro'){
        $mb_date_sql = " electro_date = '{$meter_date}' ";
    }else{
        $mb_date_sql = " water_date = '{$meter_date}' ";
    }

    $update_query = "UPDATE a_meter_building SET
                    {$mb_date_sql}
                    WHERE mr_idx = '{$mr_idx}'";
    // echo $update_query."<br>";

    sql_query($update_query);
    
}else{

    if($type == 'electro'){
        $mb_date_sql = " electro_date = '{$meter_date}', ";
    }else{
        $mb_date_sql = " water_date = '{$meter_date}', ";
    }

    $insert_query = "INSERT INTO a_meter_building SET
                    building_id = '{$building_id}',
                    mr_department = '{$mr_department}',
                    wid = '{$mr_id}',
                    mr_year = '{$mr_year}',
                    mr_month = '{$mr_month}',
                    {$mb_date_sql}
                    created_at = '{$today}'";

    // echo $insert_query."<br>";
    sql_query($insert_query);
    $mr_idx = sql_insert_id(); 
}


$total_count = 0;
$fail_count = 0;


$total_val = 0;
foreach($excel_data as $ii => $row){

    $dong_row = sql_fetch("SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and dong_name = '{$row['dong_name']}'");

    $ho_row = sql_fetch("SELECT ho_id FROM a_building_ho WHERE building_id = '{$building_id}' and ho_name = '{$row['ho_name']}'");

    //echo $excel_data[$ii]['ho_name']."<br>";
    //echo $ho_row['ho_id']."<br>";

    if(!$ho_row){
        $fail_count++;
    }else{

        $meter_confirm = sql_fetch("SELECT mr_id, COUNT(*) as cnt FROM a_meter_reading WHERE ho_id = '{$ho_row['ho_id']}' and mr_idx = '{$mr_idx}' and mr_type = '{$type}' and is_del = 0");

        if($meter_confirm['cnt'] > 0){

            $sql = "UPDATE a_meter_reading SET
                    dong_id = '{$dong_row['dong_id']}',
                    ho_id = '{$ho_row['ho_id']}',
                    mr_val = '{$excel_data[$ii]['val']}'
                    WHERE mr_id = '{$meter_confirm['mr_id']}'";
            //echo $sql."<br>";

            sql_query($sql);

        }else{


            $sql = "INSERT INTO a_meter_reading SET
                    mr_idx = '{$mr_idx}',
                    building_id = '{$building_id}',
                    dong_id = '{$dong_row['dong_id']}',
                    ho_id = '{$ho_row['ho_id']}',
                    mr_type = '{$type}',
                    mr_val = '{$excel_data[$ii]['val']}',
                    created_at = '{$today}',
                    mr_ip = '{$ip_info}'";
            //echo $sql."<br>";
            
            sql_query($sql);
        }

        $total_val += $excel_data[$ii]['val'];

        $total_count++; //총 업데이트 수
    }
}

// if($type == 'electro'){
//     $mb_sql = " total_electro = '{$total_val}' ";
// }else{
//     $mb_sql = " total_water = '{$total_val}' ";
// }

// $update_total = "UPDATE a_meter_building SET
//                     {$mb_sql}
//                     WHERE mr_idx = '{$mr_idx}'";
// //echo $update_total.'<br>';
// sql_query($update_total);

$g5['title'] = $building_row['building_name'].' '.$type_t.' 검침 엑셀등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p><?php echo $type_t; ?> 검침 값 등록을 완료했습니다.</p>
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
        <button type="button" onclick="closeAndRedirect();">창닫기</button>
    </div>

</div>
<script>
    function closeAndRedirect() {
        // 부모 창을 리다이렉트
        if (window.opener) {
            // 부모 창 리다이렉트 URL
           // window.opener.location.href = './meter_reading_form.php?w=u&mr_idx=<?php echo $mr_idx; ?>'; 
        //    window.opener.location.href = './meter_reading_adm.php?<?php echo $qstr; ?>'; 
           window.opener.location.href = './meter_reading_form.php?<?php echo $qstr; ?>&w=u&mr_idx=<?php echo $mr_idx; ?>'; 
        }
        // 현재 창 닫기
        window.close();
    }
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>