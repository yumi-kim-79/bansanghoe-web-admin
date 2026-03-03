<?php
include_once('./_common.php');
if($types == "sm"){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');
}

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) print_r2($_SESSION);

$today = date("Y-m-d");
$vote_sql = "SELECT * FROM a_online_vote WHERE dong_id = '{$user_building['dong_id']}' and is_del = 0 ORDER BY vt_id desc ";
//echo $vote_sql;
$vote_res = sql_query($vote_sql);

$vote_arr = array();

while($vote_row = sql_fetch_array($vote_res)){

    if($vote_row['vt_period_type'] == 'period'){
        if($vote_row['vt_edate'] >= $today && $vote_row['vt_sdate'] <= $today){
            array_push($vote_arr, $vote_row);
        }
    }else{
        array_push($vote_arr, $vote_row);
    }
    
}

// $total_page = array($vote_arr);

// print_r2($vote_arr);

// $rows = 10;

?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb">
                <li class="tab01 on" onclick="tab_handler('1', 'prg')">진행</li>
                <li class="tab02" onclick="tab_handler('2', 'end')">종료</li>
            </ul>
            <div class="content_box_wrap">
            </div>
            <?php
                //페이징 영역입니다. 그누보드에서 기본으로 제공하는 걸텐데 아마 이거 사용하면 될겁니다!!
                //아니면 사용하시는 거 가져다 쓰시면 됩니다. 화면을 보지 못했지만 css는 적용해놨어요.
               // echo get_paging(5, $page, $total_page, '?st_idxs='.$st_idxs.'page='); 
            ?>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ? $tabIdx : '1'; ?>";
let tabCode = "<?php echo $tabIdx == '2' ? 'end' : 'prg'; ?>";

tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabIdx = index;
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let building_id = "<?php echo $building_id != "" ? $building_id : $user_building['building_id']; ?>";
    let dong_id = "<?php echo $user_building['dong_id']; ?>";
    let types = "<?php echo $types; ?>";
    let ho_tenant_at_de = "<?php echo $ho_tenant_at_de; ?>";

    $.ajax({

    url : "/online_vote_tab_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "dong_id":dong_id, "code":code, "building_id":building_id, "types":types, "ho_tenant_at_de":ho_tenant_at_de}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}

// $(".tab_lnb li").on("click", function(){

//     let index = $(this).index() + 1;
    
//     if(index == 1){
//         $(".content_box_icons img").attr("src", "/images/vote_icon_on.svg");
//     }else{
//         $(".content_box_icons img").attr("src", "/images/vote_icon_off.svg");
//     }

//     $(".tab_lnb li").removeClass("on");
//     $(this).addClass("on");
// })
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>