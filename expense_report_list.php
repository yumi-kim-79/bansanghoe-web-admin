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
        <div class="inner">
            <div class="request_write_wrap">
                <a href="/expense_report_form.php?building_id=<?php echo $building_id; ?>" class="approval_btn request_write">품의서 작성</a>
            </div>
            <ul class="tab_lnb">
                <li class="tab01 on" onclick="tab_handler('1', '')">전체</li>
                <li class="tab02" onclick="tab_handler('2', 'N')">승인대기</li>
                <li class="tab03" onclick="tab_handler('3', 'P')">승인중</li>
                <li class="tab04" onclick="tab_handler('4', 'E')">승인완료</li>
            </ul>
            <div class="content_box_wrap ver2">
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? ''; ?>";
tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let building_id = "<?php echo $building_id; ?>";

    $.ajax({

    url : "/expense_report_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id}, //파라미터 값
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