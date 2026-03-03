<?php
$sub_menu = "300500";
require_once './_common.php';

$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '검침 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
// require_once G5_EDITOR_LIB;

$sql = "SELECT * FROM a_meter_building WHERE mr_idx = '{$mr_idx}'";
$row = sql_fetch($sql);

$buidling_info = sql_fetch("SELECT building_id, building_name, post_id FROM a_building WHERE building_id = '{$row['building_id']}'");

$post_info = sql_fetch("SELECT post_name FROM a_post_addr WHERE post_idx = '{$buidling_info['post_id']}'");

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql;

    //print_r2($buidling_info);
}
?>

<form name="fmeter" id="fmeter" action="./meter_reading_form_update.php" onsubmit="return fmeter_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="mr_idx" value="<?php echo $row['mr_idx']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <div class="h2_frm_wraps">
            <h2 class="h2_frm">검침 정보</h2>
            <button type="button" onclick="excelDownloadHandler();" class="btn btn_04">검침 엑셀 다운로드</button>
            <script>
                function excelDownloadHandler(){

                var selectedBuildingId = "<?php echo $row['building_id']; ?>";	
                var mridxValue = "<?php echo $mr_idx; ?>";	

                location.href = "./meter_reading_adm_excel2.php?building_id=" + selectedBuildingId + "&mr_idx=" + mridxValue;
                }
            </script>
        </div>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>지역</th>
                    <td colspan='3'>
                        <?php if($w == 'u'){?>
                        <input type="hidden" name="post_id" id="post_id" value="<?php echo $buidling_info['post_id']; ?>">
                        <input type="text" name="post_name" class="bansang_ipt" value="<?php echo $post_info['post_name']; ?>" readonly>
                        <?php }else{?>
                        <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();">
                            <option value="">지역선택</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($post_row['post_idx'], $buidling_info['post_id']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                        <?php }?>
                        <script>
                            function post_change(){
                                var postSelect = document.getElementById("post_id");
                                var postValue = postSelect.options[postSelect.selectedIndex].value;

                                console.log('postValue', postValue);

                                $.ajax({

                                url : "./post_building_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "post_id":postValue}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $("#building_id").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td>
                        <?php if($w == 'u'){?>
                            <input type="hidden" name="building_id" id="building_id" value="<?php echo $row['building_id']; ?>">
                            <input type="text" name="building_name" id="building_name" value="<?php echo $buidling_info['building_name']; ?>" class="bansang_ipt" size="40">
                        <?php }else{ ?>
                        <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();">
                            <option value="">단지 선택</option>
                            <?php while($row_building = sql_fetch_array($res_building)){ ?>
                            <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($row['building_id'], $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                            <?php }?>
                        </select>
                        <?php }?>
                        <script>
                            function building_change(){
                                var buildingSelect = document.getElementById("building_id");
                                var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

                                console.log('buildingValue', buildingValue);

                                let mr_year = $("#mr_year").val();
                                let mr_month = $("#mr_month").val();
                                let mr_department = $("#mr_department").val();
                                let mr_id = $("#mr_id").val();
                                let mr_name = $("#mr_name").val();

                                $.ajax({

                                url : "./meter_reading_table_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "building_id":buildingValue, "mr_year":mr_year, "mr_month":mr_month, "mr_department":mr_department, "mr_id":mr_id, "mr_name":mr_name}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $(".tbl_frm01_wrapper").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>년도</th>
                    <td><input type="text" name="mr_year" id="mr_year" class="bansang_ipt <?php echo $w == 'u' ? '' : 'ver2'; ?>" value="<?php echo $w == 'u' ? $row['mr_year'] : date('Y'); ?>" readonly></td>
                    <th>월</th>
                    <td><input type="text" name="mr_month" id="mr_month" class="bansang_ipt <?php echo $w == 'u' ? '' : 'ver2'; ?>" value="<?php echo $w == 'u' ? $row['mr_month'] : date('n'); ?>" readonly></td>
                </tr>
                <?php 
                if($member['mb_level'] == '9'){
                    $mng_infos = $w == 'u' ? get_manger($row['wid']) :  get_manger($member['mb_id']);
                ?>
                <tr>
                    <th>부서</th>
                    <td>
                        <?php
                        $department_info = get_department_name($row['mr_department']);
                        ?>
                        <input type="hidden" name="mr_department" id="mr_department" value="<?php echo $mng_infos['mng_department']; ?>">
                        <input type="text" name="mr_department_name" id="mr_department_name" class="bansang_ipt <?php echo $w == 'u' ? '' : 'ver2'; ?>" value="<?php echo $w =='u' ? $department_info : $mng_infos['md_name']; ?>" readonly>
                    </td>
                    <th>작성자</th>
                    <td>
                        <input type="hidden" name="mr_id" id="mr_id" value="<?php echo $w == 'u' ? $row['wid'] : $mng_infos['mng_id'];?>">
                        <input type="text" name="mr_name" id="mr_name" class="bansang_ipt <?php echo $w == 'u' ? '' : 'ver2'; ?>" value="<?php echo $mng_infos['mng_name']; ?>" readonly>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    <div class="tbl_frm01_wrapper">
        <?php if($w == 'u'){
        
        $month_de = "전월";
        $bf_electro_val = 0;

        //전월
        $bf_years = $row['mr_month'] == 1 ? $row['mr_year'] - 1 : $row['mr_year'];
        $bf_months = $row['mr_month'] == 1 ? 12 : $row['mr_month'] - 1;
       
        //전기 전월 검침 값 조회
        //and total_electro != '' 
        $bf_electro_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";

        // echo $bf_electro_meter_sql.'<br>';
        
        $bf_electro_meter_row = sql_fetch($bf_electro_meter_sql);

        //전월
        if($bf_electro_meter_row['cnt'] > 0){

            //전월 값 가져오기
            //and total_electro != '' 
            $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";

            // echo $bf_electro_meter_sql_total.'<br>';
            $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

            $bf_electro_val = $bf_elector_meter_total_row['total_electro'] == '' ? 0 : $bf_elector_meter_total_row['total_electro'];

            // print_r2($bf_elector_meter_total_row);
            

        }else{
            //익월

            //익월로 계산
            $bf_months = $row['mr_month'] == 1 ? 11 : $row['mr_month'] - 2;

            $month_de = "익월";

            $bf_electro_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
            $bf_electro_meter_row2 = sql_fetch($bf_electro_meter_sql2);

            if($bf_electro_meter_row2['cnt'] > 0){

                //익월의 값을 가져옵니다.
                $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and electro_date != '' ";
                $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

                $bf_electro_val = $bf_elector_meter_total_row['total_electro'] != '' ? $bf_elector_meter_total_row['total_electro'] : 0;

            }else{
                $bf_months = $row['mr_month'] == 1 ? 12 : $row['mr_month'] - 1;

                $month_de = "전월";

                $bf_electro_val = 0;
            }

        }
        // echo $bf_electro_meter_sql2.'<br>';

        // echo $bf_electro_val;
        $total_electro_val = $row['total_electro'] != '' ? $row['total_electro'] : 0;
        ?>
        <div class="tbl_frm01_wrap">
            <div class="tbl_frm01 tbl_wrap">
                <h2 class="h2_frm">전기</h2>
                <p class="h2_frm_sub_text mgt20">메인 검침</p>
                <table class="sub_table mgt10">
                    <thead>
                        <tr>
                            <th>
                                <?php echo $month_de; ?>(<?php echo $bf_months;?>월) 검침값
                            </th>
                            <th>당월 검침값</th>
                            <th>사용량(kWh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                echo $bf_electro_val;
                                ?>
                            </td>
                            <td><input type="tel" name="total_electro" class="meter_input" min="0" value="<?php echo $total_electro_val; ?>" required></td>
                            <td><?php echo $total_electro_val - $bf_electro_val; ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php
                // $mr_sql = "SELECT * FROM a_building_ho WHERE building_id = '{$row['building_id']}' and ho_status = 'Y' ORDER BY ho_name + 1 asc";

                $mr_sql = "SELECT ho.*, dong.dong_id, dong.dong_name FROM a_building_ho as ho
                LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
                WHERE ho.is_del = 0 and ho.building_id = '{$row['building_id']}' ORDER BY ho.dong_id + 1 asc, ho.ho_name + 1 asc, ho.ho_id asc";
                

                $mr_res = sql_query($mr_sql);
                ?>
                <p class="h2_frm_sub_text mgt20">세대 검침</p>
                <p class="h2_frm_text mgt20">검침날짜</p>
                <div class="meter_reading_dates_wrap">
                    <div class="meter_reading_dates">
                        <input type="text" name="electro_date" id="electro_date" class="bansang_ipt ver2 ipt_date mgt10" value="<?php echo $row['electro_date']; ?>">
                    </div>
                    <a href="./meter_reading_excel.php?building_id=<?php echo $row['building_id']; ?>&mr_year=<?php echo $row['mr_year'];?>&mr_month=<?php echo $row['mr_month'];?>&type=electro&mr_department=<?php echo $row['mr_department'];?>&mr_id=<?php echo $row['wid'];?>" onclick="return excelform(this.href);" class="btn btn_04">전기 엑셀 업로드</a>
                </div>
                <table class="sub_table mgt10">
                    <thead>
                        <tr>
                            <th>동</th>
                            <th>호</th>
                            <th><?php echo $month_de; ?>(<?php echo $bf_months;?>월) 검침값</th>
                            <th>당월 검침값</th>
                            <th>사용량(kWh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $bf_val = 0;
                        $idx = 0;
                        while($mr_row = sql_fetch_array($mr_res)){
                            
                            $bf_sql = "SELECT mr.*, mb.mr_year, mb.mr_month FROM a_meter_reading as mr
                                        LEFT JOIN a_meter_building as mb on mr.mr_idx = mb.mr_idx
                                        WHERE mr.building_id = '{$row['building_id']}' and mr.mr_type = 'electro' and mr.ho_id = '{$mr_row['ho_id']}' and mb.mr_year = '{$bf_years}' and mb.mr_month = '{$bf_months}' ";
                            // echo $bf_sql.'<br>';
                            $bf_electro_val = sql_fetch($bf_sql);

                            $bf_val = $bf_electro_val['mr_val'] == '' ? 0 : $bf_electro_val['mr_val'];

                            //당월 검침값
                            $mr_val_sql_e = "SELECT * FROM a_meter_reading WHERE ho_id = '{$mr_row['ho_id']}' and mr_idx = '{$row['mr_idx']}' and mr_type = 'electro'";
                            
                            // if($idx == 1) echo $mr_val_sql_e.'<br>';
                            $mr_val_row2 = sql_fetch($mr_val_sql_e);
                        
                            $mr_electro_val = $mr_val_row2['mr_val'] != '' ? $mr_val_row2['mr_val'] : 0;

                            $idx++;
                        ?>
                        <tr>
                            <td><?php echo $mr_row['dong_name']; ?>동</td>
                            <td>
                                <input type="hidden" name="ho_id_e[]" value="<?php echo $mr_row['ho_id']; ?>">
                                <?php echo $mr_row['ho_name']; ?>호
                            </td>
                            <td>
                                <?php
                                echo $bf_val;
                                ?>
                            </td>
                            <td><input type="tel" name="mr_val_e[]" class="meter_input" min="0" value="<?php echo $mr_electro_val == "0" ? "" : $mr_electro_val; ?>"></td>
                            <td>
                                <?php 
                                
                                $redCl = ''; 
                                $use_val_e = $mr_electro_val - $bf_val;

                                if( $use_val_e > 400 || $use_val_e < 0){
                                    $redCl = 'red';
                                }
                                ?>
                                <div class="<?php echo $redCl; ?>">
                                <?php echo $mr_electro_val - $bf_val; ?>
                                </div>
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
            <?php
            
            $month_de = "전월";
            $bf_water_val = 0;

            //전월
            $bf_years = $row['mr_month'] == 1 ? $row['mr_year'] - 1 : $row['mr_year'];
            $bf_months = $row['mr_month'] == 1 ? 12 : $row['mr_month'] - 1;
        
            //전기 전월 검침 값 조회
            $bf_electro_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and water_date != '' ";
            $bf_electro_meter_row = sql_fetch($bf_electro_meter_sql);

            if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                // echo $bf_electro_meter_sql.'<br>';
            }

            //전월
            if($bf_electro_meter_row['cnt'] > 0){

                //전월 값 가져오기
                $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and water_date != '' ";
                $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

                $bf_water_val = $bf_elector_meter_total_row['total_water'] != '' ? $bf_elector_meter_total_row['total_water'] : 0;

            }else{
                //익월

                //익월로 계산
                $bf_months = $row['mr_month'] == 1 ? 11 : $row['mr_month'] - 2;

                $month_de = "익월";

                $bf_electro_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and water_date != '' ";
                $bf_electro_meter_row2 = sql_fetch($bf_electro_meter_sql2);

                if($bf_electro_meter_row2['cnt'] > 0){

                    //익월의 값을 가져옵니다.
                    $bf_electro_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$row['building_id']}' and mr_year = '{$bf_years}' and mr_month = '{$bf_months}' and water_date != '' ";
                    $bf_elector_meter_total_row = sql_fetch($bf_electro_meter_sql_total);

                    $bf_water_val = $bf_elector_meter_total_row['total_water'] != '' ? $bf_elector_meter_total_row['total_water'] : 0;

                }else{
                    $bf_months = $row['mr_month'] == 1 ? 12 : $row['mr_month'] - 1;

                    $month_de = "전월";

                    $bf_water_val = 0;
                }

            }

            // echo $bf_water_val;
            $total_water_val = $row['total_water'] != '' ? $row['total_water'] : 0;
            ?>
            <div class="tbl_frm01 tbl_wrap">
                <h2 class="h2_frm">수도</h2>
                
                <p class="h2_frm_sub_text mgt20">메인 검침</p>
                <table class="sub_table mgt10">
                    <thead>
                        <tr>
                            <th><?php echo $month_de; ?>(<?php echo $bf_months;?>월) 검침값</th>
                            <th>당월 검침값</th>
                            <th>사용량(t)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                echo $bf_water_val;
                                ?>
                            </td>
                            <td><input type="tel" name="total_water" class="meter_input" min="0" value="<?php echo $total_water_val; ?>"></td>
                            <td><?php echo $total_water_val - $bf_water_val; ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php

                $mr_sql_w = "SELECT ho.*, dong.dong_id, dong.dong_name FROM a_building_ho as ho
                LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
                WHERE ho.is_del = 0 and ho.building_id = '{$row['building_id']}' ORDER BY ho.dong_id + 1 asc, ho.ho_name + 1 asc, ho.ho_id asc";

                // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $mr_sql_w;

                $mr_res_w = sql_query($mr_sql_w);
                // echo $mr_sql_w;
                ?>
                <p class="h2_frm_sub_text mgt20">세대 검침</p>
                <p class="h2_frm_text mgt20">검침날짜</p>
                <div class="meter_reading_dates_wrap">
                    <div class="meter_reading_dates">
                        <input type="text" name="water_date" id="water_date" class="bansang_ipt ver2 ipt_date mgt10" value="<?php echo $row['water_date']; ?>">
                    </div>
                    <a href="./meter_reading_excel.php?building_id=<?php echo $row['building_id']; ?>&mr_year=<?php echo $row['mr_year'];?>&mr_month=<?php echo $row['mr_month'];?>&type=water&mr_department=<?php echo $row['mr_department'];?>&mr_id=<?php echo $row['wid'];?>" onclick="return excelform(this.href);" class="btn btn_04">수도 엑셀 업로드</a>
                </div>
                <table class="sub_table mgt10">
                    <thead>
                        <tr>
                            <th>동</th>
                            <th>호</th>
                            <th><?php echo $month_de; ?>(<?php echo $bf_months;?>월) 검침값</th>
                            <th>당월 검침값</th>
                            <th>사용량(t)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    
                    while($mr_row_w = sql_fetch_array($mr_res_w)){

                        $bf_sql = "SELECT mr.*, mb.mr_year, mb.mr_month FROM a_meter_reading as mr
                                    LEFT JOIN a_meter_building as mb on mr.mr_idx = mb.mr_idx
                                    WHERE mr.building_id = '{$row['building_id']}' and mr.mr_type = 'water' and mr.ho_id = '{$mr_row_w['ho_id']}' and mb.mr_year = '{$bf_years}' and mb.mr_month = '{$bf_months}' ";
                        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                            // echo $bf_sql.'<br>';
                        }
                        $bf_water_vals = sql_fetch($bf_sql);

                        $bf_w_val= 0;
                        $bf_w_val = $bf_water_vals['mr_val'] == '' ? 0 : $bf_water_vals['mr_val'];

                        //당월 검침값
                        $mr_val_sql = "SELECT * FROM a_meter_reading WHERE ho_id = '{$mr_row_w['ho_id']}' and mr_idx = '{$row['mr_idx']}' and mr_type = 'water'";
                        $mr_val_row = sql_fetch($mr_val_sql);
                    
                        $mr_water_val = $mr_val_row['mr_val'] != '' ? $mr_val_row['mr_val'] : 0;
                    ?>
                    <tr>
                        <td><?php echo $mr_row_w['dong_name']; ?>동</td>
                        <td>
                            <input type="hidden" name="ho_id_w[]" value="<?php echo $mr_row_w['ho_id']; ?>">
                            <?php echo $mr_row_w['ho_name']; ?>호
                        </td>
                        <td>
                            <?php
                            echo $bf_w_val;
                            ?>
                        </td>
                        <td><input type="tel" name="mr_val_w[]" class="meter_input" min="0"  value="<?php echo $mr_water_val == 0 ? '' : $mr_water_val; ?>"></td>
                        <td>
                            <?php 
                            $redCl = '';

                            $use_val_w = $mr_water_val - $bf_w_val;
                            if($use_val_w > 50 || $use_val_w < 0){
                                $redCl = 'red';
                            }
                            ?>
                            <div class="<?php echo $redCl; ?>">
                            <?php echo $use_val_w; ?>
                            </div>
                        </td>
                    </tr>
                    <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php }?>
    </div>
    <div class="btn_fixed_top">
        <a href="./meter_reading_adm.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>



<script>

window.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('input[type="text"], input[type="tel"], input:not([type])');

    inputs.forEach(input => {
        input.addEventListener('focus', function () {
        const el = this;
        setTimeout(() => {
            const length = el.value.length;

            console.log('length', length);
            el.setSelectionRange(length, length);
        }, 0); // 이벤트 큐 다음 순서로 실행
        });
    });
});

document.addEventListener('input', function (e) {
    const el = e.target;
    if (el.matches('input[type="tel"]')) {
        el.value = el.value.replace(/[^0-9]/g, ''); // 숫자만 남기고 제거
    }
});

$(function(){
    $("#electro_date, #water_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"-365d" });
});



function excelform(url) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=600,height=450,left=10,top=10"; 

    window.open(url, "win_excel", opt); 

    return false; 

}

function fstudent_submit(f) {
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

