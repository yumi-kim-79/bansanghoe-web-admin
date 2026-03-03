<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$sql = "SELECT * FROM a_building_bbs WHERE is_del = 0 and is_view = 1 and is_before = 0 and (building_id = '{$user_building['building_id']}' or building_id = '-1') ORDER BY bb_id desc";
// echo $sql;
$res = sql_query($sql);

$bbs_array = array();

while($row = sql_fetch_array($res)){

    //print_r2($row);
    if($row['bbs_type'] == 'infomation'){
        if($row['is_submit'] == 'S'){
            array_push($bbs_array, $row);
        }
    }else{
        array_push($bbs_array, $row);
    }
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($user_building);
}
//print_r2($bbs_array);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb">
                <li class="tab01 on" onclick="tab_handler('1', 'all')">전체</li>
                <li class="tab02" onclick="tab_handler('2', 'public')">공문</li>
                <li class="tab03" onclick="tab_handler('3', 'infomation')">안내문</li>
                <li class="tab04" onclick="tab_handler('4', 'event')">이벤트</li>
            </ul>
            <div class="content_box_wrap">
                <?php for($i=0;$i<count($bbs_array);$i++){
                    
                    switch($bbs_array[$i]['bbs_type']){
                        case "public":
                            $cate = "공문";
                            break;
                        case "event":
                            $cate = "이벤트";
                            break;
                        case "infomation":
                            $cate = "안내문";
                            break;
                    }
                ?>
                <a href="/building_new_info.php?bb_id=<?php echo $bbs_array[$i]['bb_id']; ?>" class="content_box">
                    <div class="content_box_icons">
                        <img src="/images/build_icons.svg" alt="">
                    </div>
                    <div class="content_box_ct">
                        <div class="content_box_ct1">
                            <span><?php echo $cate; ?></span> <?php echo date('Y.m.d', strtotime($bbs_array[$i]['created_at'])); ?>
                        </div>
                        <div class="content_box_ct2">
                            <?php echo $bbs_array[$i]['bb_title']; ?>
                        </div>
                    </div>
                </a>
                <?php }?>
                <?php if($i==0){?>
                <div class="building_news_empty complain_empty">
                    등록된 내역이 없습니다.
                </div>
                <?php }?>
            </div>
        </div>
    </div>
</div>

<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";

tab_handler(tabIdx, 'all');

function tab_handler(index, code){
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let post_id = "<?php echo $user_building['post_id']; ?>";
    let building_id = "<?php echo $user_building['building_id']; ?>";
    let ho_tenant_at_de = "<?php echo $ho_tenant_at_de; ?>";
    let building_dates = "<?php echo $user_building['created_at']; ?>";

    $.ajax({

    url : "/building_news_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id, "post_id":post_id, "ho_tenant_at_de":ho_tenant_at_de, "building_dates":building_dates}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>