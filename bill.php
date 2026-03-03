<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

// print_r2($user_building);
$nowYear = date('Y');
$nowMonth = date('n');

//임시
// $ho_tenant_at_de = '2025-06-02'; 
// echo $ho_tenant_at_de;
$select_month = "";
if($bill_ids != ''){

    $select_month_sql = "SELECT * FROM a_bill WHERE bill_id = '{$bill_ids}'";
    $select_month_row = sql_fetch($select_month_sql);
    $select_month = $select_month_row['bill_month'];
}


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($user_building);
    // echo $select_month;
}

$dong_name = $user_building['dong_name'].'동';


//고지서 월
$bill_list = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and bill_year = '{$nowYear}' and building_id = '{$user_building['building_id']}' ORDER BY bill_month asc";
// echo $bill_list;
$bill_list_res = sql_query($bill_list);
$bill_list_total = sql_num_rows($bill_list_res);

$bill_first_row = "SELECT * FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$user_building['building_id']}' and created_at >= '{$ho_tenant_at_de}' ORDER BY bill_month desc limit 0, 1";

// echo $bill_first_row;
$bill_first_rows = sql_fetch($bill_first_row);

$firstDayOfLastMonth = date('Y-m-01', strtotime('first day of last month'));
$lastDayOfLastMonth = date('Y-m-t', strtotime('last day of last month'));

$firstDayOfLastMonth = date('Y년 n월 j일', strtotime($firstDayOfLastMonth));
$lastDayOfLastMonth = date('j', strtotime($lastDayOfLastMonth));



// bill info
// $bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$nowMonth}'");
$bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_submit = 'Y' and building_id = '{$user_building['building_id']}' and bill_year = '{$bill_first_rows['bill_year']}' and bill_month = '{$bill_first_rows['bill_month']}'");


if($bill_info['cnt'] > 0){
    $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '동호'";

    $bill_item_ho_row = sql_fetch($bill_items_ho);

    $bi_option = explode("|", $bill_item_ho_row['bi_option']);

    $bi_opt_new_arr = array();
    foreach($bi_option as $key => $row){
        $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
        array_push($bi_opt_new_arr, $opt_re);
    }

    $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
    $bi_option = explode("|", $bi_opt_new_arr);
    
    $ho_indx = array_search($user_building['ho_name'], $bi_option);

    //납기내 금액
    $bill_in_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기내금액'";
    $bill_in_price_row = sql_fetch($bill_in_price_sql);

    $bill_in_price_arr = explode("|", $bill_in_price_row['bi_option']);
    $bill_in_price = $bill_in_price_arr[$ho_indx];

    //납기연체료
    $bill_items_price = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기후연체료'";
    $bill_items_price_row = sql_fetch($bill_items_price);

    $bill_penulty_arr = explode("|", $bill_items_price_row['bi_option']);

    $bill_penulty = $bill_penulty_arr[$ho_indx];



    $bill_in_price_n = (int) str_replace(',', '', $bill_in_price);
    $bill_penulty_n = (int) str_replace(',', '', $bill_penulty);

    // $percent = ($bill_penulty_n / $bill_in_price_n) * 100;
    // $percent = number_format($percent, 2);


    if($bill_penulty_n != 0 && $bill_in_price_n != 0){
        $percent = ($bill_penulty_n / $bill_in_price_n) * 100;
        $percent = number_format($percent, 2);
    }else{
        $percent = 0;
    }

    //납부기간
    $bill_due_date = date("n월 j일", strtotime($bill_info['bill_due_date']));




    //아이템 가져오기

    $bill_item_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' AND dong_name = '{$dong_name}' ORDER BY bi_idx ASC";
    $bill_item_list_res = sql_query($bill_item_list);

    $item_array = array();
    $bill_item_arr = array();

    for($i=0;$bill_item_list_row = sql_fetch_array($bill_item_list_res);$i++){
        $item_array[$i] = $bill_item_list_row['bi_name'];

        $bill_item_arr[$i]['item'] = $bill_item_list_row['bi_name'];
        $bill_item_arr[$i]['option'] = array();

        if($bill_item_list_row['bi_name'] == '동호'){

            $bi_option = explode("|", $bill_item_list_row['bi_option']);

            $bi_opt_new_arr = array();
            foreach($bi_option as $key => $row){
                $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
                array_push($bi_opt_new_arr, $opt_re);
            }

            $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
            $bi_option = explode("|", $bi_opt_new_arr);

        }else{
            $bi_option = explode("|", $bill_item_list_row['bi_option']);
        }

        foreach($bi_option as $key => $row){
            $bill_item_arr[$i]['option'][$key] = $row;
        }
    }

    //항목명
    $not_item = ['동호', '면적', '성명(상가)', '전기사용량', '온수사용량', '수도사용량', '난방사용량', '가스사용량', '공급가액', '부가세', '비과세', '면세', '당월분합계', '할인금액', '미납금액', '미납연체료', '납기내금액', '납기후연체료', '납기후금액', '자동이체', '입주일자'];

    $not_item_arr = array_diff($item_array, $not_item);
    $not_item_list = array();
    $bcnt = 0;
    foreach($not_item_arr as $key => $row){

        $not_item_val = $bill_item_arr[$key]['option'][$ho_indx];

        if($not_item_val != ''){
            $not_item_list[$bcnt]['item_name'] = $row;
            $not_item_list[$bcnt]['item_val'] = $not_item_val;
        }

        $bcnt++;
    }

    $vat_item = ['공급가액', '부가세', '비과세', '면세'];
    $vat_item_arr = array_intersect($item_array, $vat_item);

    $vat_item_list = array();
    $vcnt = 0;

    foreach($vat_item_arr as $key => $row){

        $vat_item_val = $bill_item_arr[$key]['option'][$ho_sch_index];

    
            $vat_item_list[$vcnt]['item_name'] = $row;
            $vat_item_list[$vcnt]['item_val'] = $vat_item_val;
        // if($vat_item_val != ''){
        // }

        $vcnt++;
    }

}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="wrappers">
    <div class="wrap_container">
        <div class="bill_ax_info_wrap"></div>
        <div class="bar ver2"></div>
        <div class="bill_graph_info">
            <div class="inner">
                <div class="bill_label ver2">관리비 그래프</div>
                <?php 
                $bill_sqls = "SELECT * FROM a_bill WHERE building_id = '{$user_building['building_id']}' and is_del = 0 and is_submit = 'Y' GROUP BY bill_year ORDER BY bill_year asc, bill_id asc";
                $bill_res = sql_query($bill_sqls);
                $bill_list_total = sql_num_rows($bill_res);
                // echo $bill_sqls;
                if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                    // print_r2($bill_sqls);
                }
                
                ?>
                 <div class="graph_top_box">
                 <div class="graph_top_box_select">
                    <div class="sch_select ver2 full" >
                        <?php if($bill_list_total > 0){?>
                        <select name="year_category" id="year_category" class="bansang_sel ver2" onchange="graph_change();">
                            <?php while($bill_rows = sql_fetch_array($bill_res)){?>
                                <option value="<?php echo $bill_rows['bill_year'];?>" <?php echo get_selected($nowYear, $bill_rows['bill_year']); ?>><?php echo $bill_rows['bill_year'];?>년</option>
                            <?php }?>
                        </select>
                        <?php }else{?>
                        <select name="year_category" id="year_category" class="bansang_sel ver2">
                            <opiton value="">발행된 고지서가 없습니다.</option>
                        </select>
                        <?php }?>
                    </div>
                    <script>
                        graph_change();
                         function graph_change(){
                            var bill_id = "<?php echo $bill_info['bill_id']; ?>";
                            var yearSelect = document.getElementById("year_category");
                            var yearValue = yearSelect.options[yearSelect.selectedIndex].value;
                            var building_id = "<?php echo $user_building['building_id']; ?>";
                            let dong_name = "<?php echo $dong_name; ?>";
                            let ho_name = "<?php echo $user_building['ho_name'];?>";
                            let ho_indx = "<?php echo $ho_indx; ?>";
                            let ho_tenant_at_de = "<?php echo $ho_tenant_at_de; ?>";
                            
                            $.ajax({

                            url : "/bill_graph.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "year":yearValue, "building_id":building_id, "dong_name":dong_name, "ho_indx":ho_indx, "bill_id":bill_id, "ho_tenant_at_de":ho_tenant_at_de, 'ho_name':ho_name}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                //console.log(msg);
                                $(".line_chart_wrap").html(msg);
                            }

                            });
                        }
                    </script>
                </div>
              
                <div class="graph_color_line_box">
                    <div class="line_box_wrap">
                        <div class="line_box orange"></div>
                        <div class="line_label">우리집</div>
                    </div>
                    <div class="line_box_wrap">
                        <div class="line_box gray"></div>
                        <div class="line_label">단지</div>
                    </div>
                </div>
        
                </div>
                <div class="line_chart_wrap">
                    <div class="line_chart">
                        <canvas id="line_1"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    
let select_month = "<?php echo $select_month; ?>";
bill_info_ajax(select_month);
function bill_info_ajax(selectMonth = ''){

    let ho_tenant_at_de = "<?php echo $ho_tenant_at_de; ?>";

    $.ajax({

    url : "/bill_info_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_id":"<?php echo $user_building['building_id'];?>", "building_name":"<?php echo $user_building['building_name']; ?>", "dong_name":"<?php echo $user_building['dong_name']; ?>", "ho_id":"<?php echo $user_building['ho_id']; ?>", "ho_name":"<?php echo $user_building['ho_name']; ?>", "ho_tenant":"<?php echo $user_building['ho_tenant']; ?>", "building_bill_account":"<?php echo $user_building['building_bill_account']; ?>", "building_bill_account_bank":"<?php echo $user_building['building_bill_account_bank']; ?>", "building_bill_account_name":"<?php echo $user_building['building_bill_account_name']; ?>", "selectMonth":selectMonth, "ho_tenant_at_de":ho_tenant_at_de }, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".bill_ax_info_wrap").html(msg);
    }

    });
}

function bill_month_change(){
    var monthElement = document.getElementById("bill_month");
    var monthValue = monthElement.options[monthElement.selectedIndex].value;

    console.log(monthValue);
    bill_info_ajax(monthValue)
}
</script>
<?php
//내 납부금액
$my_bill_price_arr = array();


for($i=1;$i<=12;$i++){
    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$i}'";
    // echo $g_bill_sql.'<br>';
    $g_bill_row = sql_fetch($g_bill_sql);

    // print_2($g_bill_row);

    $my_bill_price;
    if($g_bill_row['cnt'] > 0){

     
        $bill_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' AND dong_name = '{$dong_name}' and bi_name = '당월분합계'";
        $bill_price_row = sql_fetch($bill_price_sql);

        $bill_price_val_arr = explode("|", $bill_price_row['bi_option']);

        $my_bill_price = (int) str_replace(',', '', $bill_price_val_arr[$ho_indx]);

    }else{
        $my_bill_price = 0;
    }

    array_push($my_bill_price_arr, $my_bill_price);
}

$my_bill_price_data = implode(",", $my_bill_price_arr);



//단지 납부금액
$build_price_arr = array();
for($i=1;$i<=12;$i++){
    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE is_del = 0 and is_submit = 'Y' and building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$i}'";
    $g_bill_row = sql_fetch($g_bill_sql);


    $build_price;
    if($g_bill_row['cnt'] > 0){

        $last = end($bill_item_arr);

        $sum_price_index = array_search('당월분합계', $item_array); //납기내 금액 index
        $sum_price_val_arr = $bill_item_arr[$sum_price_index]['option']; //납기내 금액

        $sum_price_val = end($sum_price_val_arr);

        $sum_price_val = (int) str_replace(',', '', $sum_price_val);

        $build_price = floor($sum_price_val / count($sum_price_val_arr));
    }else{
        $build_price = 0;
    }

    array_push($build_price_arr, $build_price);
}

$build_price_data = implode(",", $build_price_arr);
//print_r2($build_price_arr);
?>

<?php
include_once(G5_PATH.'/tail.php');
?>