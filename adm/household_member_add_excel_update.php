<?php
include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

$today = date("Y-m-d H:i:s");

$dong_sql = "SELECT ho.*, building.building_name, dong.dong_name FROM a_building_ho as ho
             LEFT JOIN a_building as building ON ho.building_id = building.building_id
             LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
             WHERE ho.ho_id = '{$ho_id}'";
//echo $dong_sql;
$dong_row = sql_fetch($dong_sql);

$building_name = $dong_row['building_name'].' '.$dong_row['dong_name'];

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

$j=0;
for($i=3;$i<count($spread_data);$i++){
    
    $excel_data[$j]['hh_relationship'] = $spread_data[$i][0]; //호수
    $excel_data[$j]['hh_name'] = $spread_data[$i][1]; //소유자
    $excel_data[$j]['hh_hp'] = $spread_data[$i][2]; //소유자 연락처
    
    $j++;
}

if(count($excel_data) == 0) alert("내용을 입력해주세요.");

$total_count = 0;
$fail_count = 0;
// print_r2($excel_data);

for($i=0;$i<count($excel_data);$i++){

    if($excel_data[$i]['hh_relationship'] != ''){
        $insert_hh = "INSERT INTO a_building_household SET
                        post_id = '{$dong_row['post_id']}',
                        building_id = '{$dong_row['building_id']}',
                        dong_id = '{$dong_row['dong_id']}',
                        ho_id = '{$ho_id}',
                        hh_relationship = '{$excel_data[$i]['hh_relationship']}',
                        hh_name = '{$excel_data[$i]['hh_name']}',
                        hh_hp = '{$excel_data[$i]['hh_hp']}',
                        created_at = '{$today}'";
        // echo $insert_hh.'<br>';
        sql_query($insert_hh);

        $total_count++;
    }else{
        $fail_count++;
    }
    
}

$g5['title'] = '세대구성원 정보 엑셀등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>세대구성원 등록을 완료했습니다.</p>
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