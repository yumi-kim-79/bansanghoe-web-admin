<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

//print_r2($user_building);
$nowYear = date('Y');
$nowMonth = date('n');

//고지서 월
$bill_list = "SELECT * FROM a_bill WHERE is_del = 0 and bill_year = '{$nowYear}' and building_id = '{$user_building['building_id']}' ORDER BY bill_month asc";
$bill_list_res = sql_query($bill_list);

$bill_first_row = "SELECT * FROM a_bill WHERE is_del = 0 and building_id = '{$user_building['building_id']}' ORDER BY bill_month desc limit 0, 1";
$bill_first_rows = sql_fetch($bill_first_row);
if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    echo $bill_first_row.'<br>';
}

$firstDayOfLastMonth = date('Y-m-01', strtotime('first day of last month'));
$lastDayOfLastMonth = date('Y-m-t', strtotime('last day of last month'));

$firstDayOfLastMonth = date('Y년 n월 j일', strtotime($firstDayOfLastMonth));
$lastDayOfLastMonth = date('j', strtotime($lastDayOfLastMonth));

// bill info
// $bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$nowMonth}'");
$bill_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$bill_first_rows['bill_year']}' and bill_month = '{$bill_first_rows['bill_month']}'");

if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    print_r2($bill_info);
}


if($bill_info['cnt'] > 0){
    $bill_items_ho = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$user_building['dong_name']}' and bi_name = '동호'";

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
    $bill_in_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$user_building['dong_name']}' and bi_name = '납기내금액'";
    $bill_in_price_row = sql_fetch($bill_in_price_sql);

    $bill_in_price_arr = explode("|", $bill_in_price_row['bi_option']);
    $bill_in_price = $bill_in_price_arr[$ho_indx];

    //납기연체료
    $bill_items_price = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' and dong_name = '{$user_building['dong_name']}' and bi_name = '납기후연체료'";
    $bill_items_price_row = sql_fetch($bill_items_price);

    $bill_penulty_arr = explode("|", $bill_items_price_row['bi_option']);

    $bill_penulty = $bill_penulty_arr[$ho_indx];

}

$bill_in_price_n = (int) str_replace(',', '', $bill_in_price);
$bill_penulty_n = (int) str_replace(',', '', $bill_penulty);

$percent = ($bill_penulty_n / $bill_in_price_n) * 100;
$percent = number_format($percent, 2);

//납부기간
$bill_due_date = date("n월 j일", strtotime($bill_info['bill_due_date']));


//아이템 가져오기

$bill_item_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_info['bill_id']}' AND dong_name = '{$user_building['dong_name']}' ORDER BY bi_idx ASC";
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
//print_r2($user_building);

?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="parking_building">
                    <?php echo $user_building['building_name']; ?><br>
                    <?php echo $user_building['dong_name']; ?> <?php echo $user_building['ho_name']; ?>호 <?php echo $user_building['ho_tenant']; ?>
                </p>
                <p class="parking_building2 mgt10"><?php echo $nowMonth; ?>월 요금은 <?php echo $firstDayOfLastMonth; ?>부터 <?php echo $lastDayOfLastMonth; ?>일까지 사용한 관리비입니다.</p>
                <div class="bill_info_box mgt15">
                    <div class="bill_info_top">
                        <select name="bill_month" id="bill_month" class="bansang_sel bill">
                            <?php while($bill_list_row = sql_fetch_array($bill_list_res)){?>
                            <option value="<?php echo $bill_list_row['bill_month']; ?>" <?php echo $nowMonth == $bill_list_row['bill_month'] ? 'selected' : ''; ?>><?php echo $bill_list_row['bill_month']; ?>월</option>
                            <?php }?>
                        </select>
                        <div class="bill_info_price_box">
                            <div class="bill_info_price_label">청구 요금</div>
                            <div class="bill_info_price">
                                <span><?php echo number_format($bill_in_price_n);?></span>원
                            </div>
                        </div>
                    </div>
                    <div class="bill_info_bot mgt10">
                        <div class="bill_info_txt"><?php echo $nowMonth; ?>월 요금은 <?php echo $bill_due_date; ?>까지 입금 부탁드립니다.</div>
                        <div class="bill_info_txt">입금 연체시 연체가산금(<?php echo $percent; ?>%) <?php echo $bill_penulty; ?>원 추가됩니다.</div>
                    </div>
                </div>
                <a href="/bill_download.php?ho_id=<?php echo $user_building['ho_id'];?>" class="bill_downs mgt15">고지서 다운로드</a>
            </div>
        </div>
        <div class="mng_bill_info">
            <div class="inner">
                <div class="mng_bill_info_wrap">
                    <div class="bill_label">관리비 상세 내역</div>
                    <div class="mng_bill_list">
                        <ul>
                            <?php foreach($not_item_list as $key => $val){?>
                            <li>
                                <div class="mng_bill_cont"><?php echo $val['item_name']; ?></div>
                                <div class="mng_bill_price"><?php echo $val['item_val']; ?>원</div>
                            </li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
                <?php if($bill_info['vt_add']){?>
                <div class="mng_bill_info_wrap">
                    <div class="bill_label">부가세 내역</div>
                    <div class="mng_bill_list">
                        <ul>
                            <?php foreach($vat_item_list as $key => $val){?>
                            <li>
                                <div class="mng_bill_cont"><?php echo $val['item_name']; ?></div>
                                <div class="mng_bill_price"><?php echo $val['item_val'] == '' ? 0 :  $val['item_val'];?>원</div>
                            </li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
                <?php }?>
                <div class="mnb_bill_total_price">
                    <div class="total_label"><?php echo $nowMonth; ?>월 총 관리비</div>
                    <div class="total_price"><span><?php echo number_format($bill_in_price_n);?></span>원</div>
                </div>
            </div>
        </div>
        <div class="bar ver2"></div>
        <div class="bill_payment_info">
            <div class="inner">
                <div class="bill_payment_box">
                    <div class="bill_payment_label">납부 기한</div>
                    <div class="bill_payment_ct ver2"><?php echo date("Y.m.d", strtotime($bill_info['bill_due_date'])); ?></div>
                </div>
                <div class="bill_payment_box">
                    <div class="bill_payment_label">납부 계좌</div>
                    <div class="bill_payment_ct">
                        <button type="button" onclick="copyToClipboard('<?php echo $user_building['building_bill_account']; ?>');">
                        <?php echo $user_building['building_bill_account_bank']; ?>
                        <?php echo $user_building['building_bill_account']; ?>
                        <img src="/images/copy_icons.svg" alt="">
                        </button>

                        <script>
                            function copyToClipboard(text) {
                                navigator.clipboard.writeText(text).then(() => {
                                    showToast("복사되었습니다. 원하는 곳에 붙여넣기하여 주세요.");
                                }).catch(() => {
                                    prompt("키보드의 ctrl+C 또는 마우스 오른쪽의 복사하기를 이용해주세요.",text);
                                });
                            };
                        </script>
                    </div>
                </div>
                <div class="bill_payment_box bill_payment_box_col">
                    <div class="bill_payment_label">납부 공지사항</div>
                    <div class="bill_payment_ct">
                        <div class="bill_notice_box">
                            <?php echo nl2br($user_building['building_bill_notice']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bar ver2"></div>
        <div class="bill_graph_info">
            <div class="inner">
                <div class="bill_label ver2">관리비 그래프</div>
                <?php 
                $bill_sqls = "SELECT * FROM a_bill WHERE is_del = 0 GROUP BY bill_year ORDER BY bill_year asc, bill_id asc";
                $bill_res = sql_query($bill_sqls);
                // echo $bill_sqls;
                ?>
                <div class="sch_select ver2" onchange="graph_change();">
                    <select name="year_category" id="year_category" class="bansang_sel ver2">
                        <?php while($bill_rows = sql_fetch_array($bill_res)){?>
                            <option value="<?php echo $bill_rows['bill_year'];?>" <?php echo get_selected($nowYear, $bill_rows['bill_year']); ?>><?php echo $bill_rows['bill_year'];?>년</option>
                        <?php }?>
                    </select>
                    <script>
                         function graph_change(){
                            var bill_id = "<?php echo $bill_info['bill_id']; ?>";
                            var yearSelect = document.getElementById("year_category");
                            var yearValue = yearSelect.options[yearSelect.selectedIndex].value;
                            var building_id = "<?php echo $user_building['building_id']; ?>";
                            let dong_name = "<?php echo $user_building['dong_name']; ?>";
                            let ho_indx = "<?php echo $ho_indx; ?>";
                            
                            $.ajax({

                            url : "/bill_graph.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "year":yearValue, "building_id":building_id, "dong_name":dong_name, "ho_indx":ho_indx, "bill_id":bill_id}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                //console.log(msg);
                                $(".line_chart_wrap").html(msg);
                            }

                            });
                        }
                    </script>
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
<?php
//내 납부금액
$my_bill_price_arr = array();
for($i=1;$i<=12;$i++){
    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$i}'";
    $g_bill_row = sql_fetch($g_bill_sql);

    $my_bill_price;
    if($g_bill_row['cnt'] > 0){

     
        $bill_price_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$g_bill_row['bill_id']}' AND dong_name = '{$user_building['dong_name']}' and bi_name = '당월분합계'";
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
    $g_bill_sql = "SELECT *, COUNT(*) as cnt FROM a_bill WHERE building_id = '{$user_building['building_id']}' and bill_year = '{$nowYear}' and bill_month = '{$i}'";
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
                backgroundColor: '#C4C4C4',
                borderColor:'#C4C4C4'
            },
            {
                label: '단지 평균',
                data: [<?php echo $build_price_data; ?>],
                backgroundColor: '#388FCD',
                borderColor:'#388FCD'
            }
        ]
    },
    options: _option,
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>