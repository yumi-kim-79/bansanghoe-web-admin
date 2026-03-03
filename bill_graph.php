<?php
require_once "./_common.php";

// print_r2($_POST);

$nowYear = $year;

$bill_item_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' AND dong_name = '{$dong_name}' ORDER BY bi_idx ASC";
$bill_item_list_res = sql_query($bill_item_list);

$bill_item_arr = array();

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $bill_item_list;
}

// exit;

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

// $bi_option = explode("|", $bi_opt_new_arr);
// print_r2($bi_option);
// $ho_indx = array_search($ho_name, $bi_option);

//내 납부금액
$my_bill_price_arr = array();
for($i=1;$i<=12;$i++){

    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$building_id}' and is_submit ='Y' and bill_month = '{$i}' and STR_TO_DATE(CONCAT(bill_year, '-', LPAD(bill_month, 2, '0'), '-01'), '%Y-%m-%d') >= '{$ho_tenant_at_de}'";
    $g_bill_row = sql_fetch($g_bill_sql);

    //print_r2($g_bill_row);

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo $ho_indx.'<br>';
    }

    $my_bill_price;
    if($g_bill_row['cnt'] > 0){

        $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '동호'";
        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_items_ho.'<br>';
        $bill_item_ho_row = sql_fetch($bill_items_ho);

        $bi_option = explode("|", $bill_item_ho_row['bi_option']);

        $bi_opt_new_arr = array();
        foreach($bi_option as $key => $row){
            $opt_re = preg_replace('/[^0-9\-\|]/u', '', $row);
            array_push($bi_opt_new_arr, $opt_re);
        }

        $bi_opt_new_arr = implode("|", $bi_opt_new_arr);
        $bi_option = explode("|", $bi_opt_new_arr);
        
        $ho_indx = array_search($ho_name, $bi_option);


     
        $bill_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' AND dong_name = '{$dong_name}' and bi_name = '당월분합계'";
        // $bill_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' AND dong_name = '{$dong_name}' and bi_name = '납기내금액'";
        $bill_price_row = sql_fetch($bill_price_sql);

        if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
            // $bill_price_sql2 = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' and dong_name = '{$dong_name}' and bi_name = '납기내금액'";
            // echo $bill_price_sql.'<br>';
            // echo $ho_indx.'<br>';

            // SELECT * FROM a_bill_item WHERE bill_id = '4' and dong_name = '1동' and bi_name = '납기내금액'

            // echo $bill_price_sql2.'<br>';
        }

        $bill_price_val_arr = explode("|", $bill_price_row['bi_option']);

        $my_bill_price = (int) str_replace(',', '', $bill_price_val_arr[$ho_indx]);

    }else{
        $my_bill_price = 0;
    }

    array_push($my_bill_price_arr, $my_bill_price);
}

$my_bill_price_data = implode(",", $my_bill_price_arr);

// echo $my_bill_price_data.'<br>';


//단지 납부금액
$build_price_arr = array();
for($i=1;$i<=12;$i++){
    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$building_id}' and bill_year = '{$nowYear}' and bill_month = '{$i}' and is_submit = 'Y' ";

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $g_bill_sql.'<br>';
    $g_bill_row = sql_fetch($g_bill_sql);

    $build_price;
    if($g_bill_row['cnt'] > 0){

        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) print_r2($g_bill_row);
        $bill_items_dong_sum = sql_fetch("SELECT bi_option FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' and bi_name = '동호'");

        $dong_ho_arr = explode("|", $bill_items_dong_sum['bi_option']);

        $dong_sum_idx = array_search('동합계', $dong_ho_arr);
        $dong_all_sum_idx = array_search('전체합계', $dong_ho_arr);

        //당월분합계
        // $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' and bi_name = '납기내금액' ";
        $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' and bi_name = '당월분합계' ";
        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bill_items_ho.'<br>';
        $bill_item_ho_row = sql_fetch($bill_items_ho);

        $bi_options_arr = explode("|", $bill_item_ho_row['bi_option']);
        
        

        if($dong_sum_idx != ''){
           unset($bi_options_arr[$dong_sum_idx]);
        }

        if($dong_all_sum_idx != ''){
            unset($bi_options_arr[$dong_all_sum_idx]);
         }


        //  if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) {
        //     print_r2($bi_options_arr);
          
        //     // echo '동합계'.$dong_sum_idx.'<br>';
        //     // echo '동 전체합계'.$dong_all_sum_idx.'<br>';
        // }

        $option_sum = 0;
        for($j=0;$j<count($bi_options_arr);$j++){

            $bi_options_price = (int) str_replace(',', '', $bi_options_arr[$j]);

            $option_sum += $bi_options_price;

            // echo $bi_options_arr[$j].'<br>';
        }

        $option_cnt = count($bi_options_arr);

        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $option_sum.'<br>';
        // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo count($bi_options_arr).'<br>';
        /////


        $build_price = floor($option_sum / $option_cnt);
        // $last = end($bill_item_arr);

        // $sum_price_index = array_search('납기내금액', $item_array); //납기내 금액 index
        // $sum_price_val_arr = $bill_item_arr[$sum_price_index]['option']; //납기내 금액

        // $sum_price_val = end($sum_price_val_arr);

        // $sum_price_val = (int) str_replace(',', '', $sum_price_val);

        // $build_price = floor($sum_price_val / count($sum_price_val_arr));
    }else{
        $build_price = 0;
    }

    array_push($build_price_arr, $build_price);
}

$build_price_data = implode(",", $build_price_arr);

// echo $build_price_data.'<br>';
?>
<div class="line_chart">
    <canvas id="line_1"></canvas>
</div>
<script>
//top30 vs 나
const _option = {
    indexAxis: 'x',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false // 범례 표시 안 함
        }
    },
    scales: {
        y: {
            ticks: {					
                padding: 5           // 축과 라벨 사이의 여백
            }
        },
        x: {
            beginAtZero: false
        }
    }
}

let line_1 = document.getElementById('line_1');
let myChart2 = new Chart(line_1, {
    type: 'line',
    data: {
        labels: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        datasets: [
            {
                label: '우리집',
                data: [<?php echo $my_bill_price_data; ?>],
                backgroundColor: '#FFA81C',
                borderColor:'#FFA81C'
            },
            {
                label: '단지 평균',
                data: [<?php echo $build_price_data; ?>],
                backgroundColor: '#C4C4C4',
                borderColor:'#C4C4C4'
            }
        ]
    },
    options: _option,
});
</script>