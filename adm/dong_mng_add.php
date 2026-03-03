<?php
$sub_menu = "200400";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '동/호수 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');


$sql = "SELECT dong.*, building.building_id, building.post_id, building.building_name, building.building_addr, post.post_name FROM a_building_dong as dong
        LEFT JOIN a_building as building on dong.building_id = building.building_id
        LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
        WHERE dong_id = {$dong_id}";
$row = sql_fetch($sql);

//차량 수 가져오기
$max_car = "SELECT COUNT(*) as cnt FROM a_building_car WHERE dong_id = '{$dong_id}' and is_del = 0 GROUP BY ho_id ORDER BY cnt desc limit 1";
$max_car_row = sql_fetch($max_car);  // 먼저 실행


//세대 구성원 수 가져오기
$max_hh = "SELECT COUNT(*) as cnt FROM a_building_household WHERE dong_id = '{$dong_id}' and is_del = 0 GROUP BY ho_id ORDER BY cnt desc limit 1";
$max_hh_row = sql_fetch($max_hh);  // 먼저 실행



$ho_sql = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0 ORDER BY ho_name + 0 asc, ho_id desc";
// echo $ho_sql;
$ho_res = sql_query($ho_sql);
$total_count = sql_num_rows($ho_res);

$rows = 10;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$ho_sql2 = "SELECT * FROM a_building_ho WHERE dong_id = '{$dong_id}' and is_del = 0 ORDER BY CAST(SUBSTRING_INDEX(ho_name, '-', 1) AS UNSIGNED) ASC, 
  CASE WHEN ho_name REGEXP '-' THEN 1 ELSE 0 END ASC,         
  CAST(SUBSTRING_INDEX(ho_name, '-', -1) AS UNSIGNED) ASC, 
  ho_name ASC limit {$from_record}, {$rows}";
$ho_res2 = sql_query($ho_sql2);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $sql.'<br>';
    echo $ho_sql2.'<br>';
    echo $max_hh.'<br>';
    //print_r2($row);
}



// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fho" id="fho" action="./dong_mng_add_update.php" onsubmit="return fho_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="dong_id" value="<?php echo $dong_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">동/호수 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>지역</th>
                    <td>
                        <input type="text" name="st_name" id="st_name" class="bansang_ipt read_only" value="<?php echo $row['post_name']; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <th>주소</th>
                    <td>
                        <input type="text" name="addr" class="bansang_ipt" size="100" value="<?php echo $row['building_addr']; ?>" readonly>
                    </td>
                </tr>
                <tr>
                  <th>단지</th>
                    <td>
                        <input type="text" name="st_name" id="st_name" class="bansang_ipt" value="<?php echo $row['building_name']; ?>" readonly size="50" >
                    </td>
                </tr>
                <tr>
                    <th>
                        동
                    </th>
                    <td>
                        <input type="text" name="" class="bansang_ipt" value="<?php echo $row['dong_name']; ?>" readonly>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <style>
        .ho_tables_wrap {overflow: auto;}
        .ho_tables {min-width: 115%;max-width: 200%;table-layout:fixed;}
        .ho_tables tr th.ho_th1 {width: 80px;}
        .ho_tables tr th.ho_th2 {width: 100px;}
        .ho_tables tr th.ho_th3 {width: 160px;}
        .ho_tables tr th.ho_th4 {width: 180px;}
        .ho_tables tr td {text-align: center;}
    </style>
    <div class="tbl_frm01 tbl_wrap">
        <div class="h2_frm_wraps">
            <h2 class="h2_frm">입주자 정보</h2>
            <!-- <button type="submit" class="btn btn_04">입주자 정보 엑셀 업로드</button> -->
            <a href="./dong_mng_add_excel.php?dong_id=<?php echo $dong_id; ?>" onclick="return excelform(this.href);"  class="btn btn_04">입주자 정보 엑셀 업로드</a>
        </div>
        <div class="ho_tables_wrap">
            <table class="ho_tables">
                <caption>동/호수 관리-입주자정보</caption>
                <thead>
                    <tr>
                        <th class="ho_th1">번호</th>
                        <th class="ho_th1">관리</th>
                        <th class="ho_th1">상태</th>
                        <th class="ho_th1">호수</th>
                        <th class="ho_th1">면적</th>
                        <th class="ho_th2">소유자</th>
                        <th class="ho_th3">소유자 연락처</th>
                        <th class="ho_th2">입주자</th>
                        <th class="ho_th3">입주자 연락처</th>
                        <th class="ho_th3">입주일</th>
                        <?php for($j=0;$j<$max_car_row['cnt'];$j++){?>
                        <th class="ho_th4">등록차량</th>
                        <?php }?>
                        <?php for($h=0;$h<$max_hh_row['cnt'];$h++){?>
                        <th class="ho_th4">세대구성원</th>
                        <?php }?>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php
                    for($i=0;$ho_row = sql_fetch_array($ho_res2);$i++){ 

                        // print_r2($ho_row);
                        //차량정보 가져오기
                        $my_car_sql = "SELECT * FROM a_building_car WHERE ho_id = '{$ho_row['ho_id']}' and is_del = 0 ORDER BY car_id asc";
                        $my_car_res = sql_query($my_car_sql);

                        $my_car_arr = array();

                        foreach($my_car_res as $idx => $my_car_row){
                            $my_car_arr[$idx]['car_type'] = $my_car_row['car_type'];
                            $my_car_arr[$idx]['car_name'] = $my_car_row['car_name'];
                        }


                        //세대정보 가져오기
                        $hh_sql = "SELECT * FROM a_building_household WHERE ho_id = '{$ho_row['ho_id']}' and is_del = 0 ORDER BY hh_id asc";
                        $hh_res = sql_query($hh_sql);

                        $hh_arr = array();

                        foreach($hh_res as $idx => $hh_row){
                            $hh_arr[$idx]['hh_relationship'] = $hh_row['hh_relationship'];
                            $hh_arr[$idx]['hh_name'] = $hh_row['hh_name'];
                            $hh_arr[$idx]['hh_hp'] = $hh_row['hh_hp'];
                        }

                        
                    ?>
                    <tr class="<?php echo $ho_row['ho_status'] == 'N' ? 'status_n' : '';?>">
                        <td style="width:100px">
                            <?php
                            $startNumber = $total_count - (($page - 1) * $rows);

                            echo $startNumber - $i;
                            ?>
                        </td>
                        <td>
                            <a href="/adm/house_hold_form.php?w=u&ho_id=<?php echo $ho_row['ho_id']; ?>" class="btn btn_03">관리</a>
                        </td>
                        <td><?php echo $ho_row['ho_status'] == 'Y' ? '입주' : '퇴실';?></td>
                        <td>
                            <input type="hidden" name="ho_id[]" value="<?php echo $ho_row['ho_id']; ?>">
                            <?php echo $ho_row['ho_name'];?>
                        </td>
                        <td>
                            <?php echo $ho_row['ho_size'];?>
                        </td>
                        <td>
                            <?php echo $ho_row['ho_owner'];?>
                        </td>
                        <td><?php echo $ho_row['ho_owner_hp'];?></td>
                        <td>
                            <input type="hidden" name="ho_tenant[]" value="<?php echo $ho_row['ho_tenant']; ?>">
                            <?php echo $ho_row['ho_tenant']; ?>
                        </td>
                        <td>
                            <input type="hidden" name="ho_tenant_hp[]" value="<?php echo $ho_row['ho_tenant_hp']; ?>">
                            <?php echo $ho_row['ho_tenant_hp']; ?>
                        </td>
                        <td>
                            <input type="hidden" name="ho_tenant_at[]" class="tenant_at" value="<?php echo $ho_row['ho_tenant_at']; ?>" readonly>
                            <?php echo $ho_row['ho_tenant_at']; ?>
                        </td>
                        <?php for($j=0;$j<$max_car_row['cnt'];$j++){?>
                        <td>
                            <?php if($my_car_arr[$j]['car_type'] != ''){?>
                                차종 : <?php echo $my_car_arr[$j]['car_type']; ?><br>
                                번호 : <?php echo $my_car_arr[$j]['car_name']; ?>
                            <?php }?>
                        </td>
                        <?php }?>
                        <?php for($h=0;$h<$max_hh_row['cnt'];$h++){?>
                        <td>
                            <?php if($hh_arr[$h]['hh_relationship'] != ''){?>
                                관계 : <?php echo $hh_arr[$h]['hh_relationship']; ?><br>
                                이름 : <?php echo $hh_arr[$h]['hh_name']; ?><br>
                                <?php if($hh_arr[$h]['hh_hp'] != ""){?>
                                연락처 : <?php echo $hh_arr[$h]['hh_hp']; ?>
                                <?php }?>
                            <?php }?>
                        </td>
                        <?php }?>
                    </tr>
                    <?php 
                    }
                    ?>
                    <?php if($i==0){?>
                        <tr>
                            <td colspan='10'>등록된 입주자 정보가 없습니다.</td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?w='.$w.'&amp;dong_id='.$dong_id); ?>
    </div>

    <div class="btn_fixed_top">
        <a href="./dong_mng.php" class="btn btn_02">목록</a>
        <!-- <input type="submit" value="수정" class="btn_submit btn btn_03" accesskey='s'> -->
    </div>
</form>

<script>
$(function(){
    $(".tenant_at").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});


function excelform(url) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=600,height=450,left=10,top=10"; 

    window.open(url, "win_excel", opt); 

    return false; 

}

function fho_submit(f) {


    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

