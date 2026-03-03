<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

//faq category
$faq_cate_sql = "SELECT * FROM a_faq_category ORDER BY fc_idx desc";
$faq_cate_res = sql_query($faq_cate_sql);

$complain_sql = "SELECT * FROM a_online_complain WHERE complain_type = 'user' and complain_id = '{$user_info['mb_id']}' and ho_id = '{$user_building['ho_id']}' and is_del = 0 ORDER BY complain_idx desc";

// echo $complain_sql.'<br>';
$complain_res = sql_query($complain_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="tab_lnb">
                <li class="tab01 on" onclick="tab_handler('1')">FAQ</li>
                <li class="tab02" onclick="tab_handler('2')">온라인 민원</li>
            </ul>
            <div class="faq_info_wrapper">
                <div class="tab_btn_wrap">
                    <?php for($i=0;$faq_cate_row = sql_fetch_array($faq_cate_res);$i++){?>
                    <div class="tab_btn <?php echo $i == 0 ? 'on' : ''?>" data-code="<?php echo $faq_cate_row['fc_code']; ?>"><?php echo $faq_cate_row['fc_name']; ?></div>
                    <?php }?>
                </div>
                <div class="faq_info_wrap mgt20"></div>
            </div>
            <div class="online_complain_wrap">
                <div class="content_box_wrap">
                    <?php for($i=0;$complain_row = sql_fetch_array($complain_res);$i++){

                        switch($complain_row['complain_status']){
                            case "CA":
                                $cate = "접수대기";
                                $status = "on";
                                break;
                            case "CB":
                                $cate = "할당대기";
                                $status = "on";
                                break;
                            case "CC":
                                $cate = "진행중";
                                $status = "on";
                                break;
                            case "CD":
                                $cate = "완료";
                                $status = "off";
                                break;
                        }
                    ?>
                    <a href="/online_complain_info.php?complain_idx=<?php echo $complain_row['complain_idx']; ?>&cstatus=<?php echo $complain_row['complain_status']; ?>" class="content_box">
                        <div class="content_box_icons">
                            <img src="/images/online_complain_icon_<?=$status; ?>.svg" alt="">
                        </div>
                        <div class="content_box_ct">
                            <div class="content_box_ct1">
                                <span><?php echo $cate; ?></span> <?php echo date("Y.m.d",strtotime($complain_row['wdate']));?>
                            </div>
                            <div class="content_box_ct2">
                                <?php echo $complain_row['complain_title']; ?>
                            </div>
                        </div>
                    </a>
                    <?php }?>
                    <?php if($i==0){?>
                        <div class="complain_empty">등록된 민원이 없습니다.</div>
                    <?php }?>
                </div>
            </div>
        </div>
        <div class="fix_btn_back_box online_complain_wrap"></div>
        <div class="fix_btn_box ver3 online_complain_wrap">
            <a href="/online_complain_form.php" class="fix_btn on" id="fix_btn" onClick="register();">민원 접수하기</a>
        </div>
    </div>
</div>
<script>

let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";

tab_handler(tabIdx);

function tab_handler(index){
    if(index == 1){
        $(".faq_info_wrapper").show();
        $(".online_complain_wrap").hide();
    }else if(index == 2){
        $(".faq_info_wrapper").hide();
        $(".online_complain_wrap").show();
    }

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");
}

function faq_content_handler(fc_code){
    $.ajax({

    url : "/fac_content_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "fc_code":fc_code}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        //console.log(msg);
        $(".faq_info_wrap").html(msg);
    }

    });
}

faq_content_handler('mng_price');
$(".tab_btn_wrap .tab_btn").on("click", function(){
    $(".tab_btn_wrap .tab_btn").removeClass("on");
    $(this).addClass("on");

    let fc_code = $(this).data('code');

    faq_content_handler(fc_code);
});

$(document).on("click", ".faq_info_question", function(){
    $(this).next(".faq_info_answer").toggle();
    $(this).children(".faq_question").toggleClass("up");
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>