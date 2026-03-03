<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title"><?php echo $building_row['building_name']; ?></p>
            </div>
        </div>
        <div class="car_content">
            <div class="inner">
                <ul class="tab_lnb">
                    <li class="tab01 on" onclick="tab_handler('1', 'all')">전체</li>
                    <li class="tab02" onclick="tab_handler('2', 'public')">공문</li>
                    <li class="tab03" onclick="tab_handler('3', 'infomation')">안내문</li>
                    <li class="tab04" onclick="tab_handler('4', 'event')">이벤트</li>
                </ul>
                <div class="content_box_wrap ver2 ">
                </div>
            </div>
        </div>
        
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";

tab_handler(tabIdx, 'all');

function tab_handler(index, code){
    let building_id = "<?php echo $building_id; ?>";
    let post_id = "<?php echo $building_row['post_id']; ?>";

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    $.ajax({

    url : "/building_news_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id, "type":"sm", "post_id":post_id}, //파라미터 값
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