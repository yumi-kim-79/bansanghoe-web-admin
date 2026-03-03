<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="request_write_wrap">
                <a href="/holiday_reqeust_form.php?types=<?php echo $types;?>" class="approval_btn request_write">서류 작성</a>
            </div>
            <ul class="tab_lnb">
                <li class="tab01 on" onclick="tab_handler('1', 'all')">전체</li>
                <li class="tab02" onclick="tab_handler('2', 'N')">승인대기</li>
                <li class="tab03" onclick="tab_handler('3', 'P')">승인중</li>
                <li class="tab04" onclick="tab_handler('4', 'E')">승인완료</li>
                <li class="tab05" onclick="tab_handler('5', 'R')">반려</li>
            </ul>
            <div class="content_box_wrap nm ver2 ver3">
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'all'; ?>";

tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabIdx = index;
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let mb_id = "<?php echo $member['mb_id']; ?>";
    let types = "<?php echo $types; ?>";

    $.ajax({

    url : "/holiday_reqeust_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "mb_id":mb_id, "types":types, "tabIdx":tabIdx, "tabCode":tabCode}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        //console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>