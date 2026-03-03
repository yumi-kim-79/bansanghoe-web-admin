<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$bbs_setting_sql = "SELECT * FROM a_bbs_setting WHERE is_view = 1 ORDER BY bbs_id asc";
$bbs_setting_res = sql_query($bbs_setting_sql);
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="request_write_wrap">
                <a href="/board_write.php" class="approval_btn request_write">게시글 작성</a>
            </div>
            <ul class="tab_lnb">
                <li class="tab00" onclick="tab_handler('0', 'all')">전체</li>
                <?php for($i=0;$bbs_setting_row = sql_fetch_array($bbs_setting_res);$i++){?>
                    <li class="tab0<?php echo $i + 1;?>" onclick="tab_handler('<?php echo $i + 1;?>', '<?php echo $bbs_setting_row['bbs_code']; ?>')"><?php echo $bbs_setting_row['bbs_title']; ?></li>
                <?php }?>
            </ul>
            <div class="content_box_wrap ver2 ver3">
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '0'; ?>";
// let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'all'; ?>";
// let tabCode = "<?php echo $tabCode ?? 'notice'; ?>";
tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    $.ajax({

    url : "/board_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "index":index}, //파라미터 값
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