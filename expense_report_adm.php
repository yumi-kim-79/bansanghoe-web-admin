<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb ver3">
                <li class="tab01 on" onclick="tab_handler('1', 'N')">승인대기</li>
                <li class="tab02" onclick="tab_handler('2', 'E')">승인완료</li>
            </ul>
            <div class="content_box_wrap">
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'N'; ?>";
tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let building_id = "<?php echo $user_building['building_id']; ?>";
    let dong_id = "<?php echo $user_building['dong_id']; ?>";

    $.ajax({

    url : "/expense_report_adm_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id, "dong_id":dong_id}, //파라미터 값
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