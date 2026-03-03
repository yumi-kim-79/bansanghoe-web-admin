<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            
            <div class="content_box_wrap">
                <?php for($i=0;$i<10;$i++){?>
                <a href="/building_new_info.php" class="content_box">
                    <div class="content_box_icons">
                        <img src="/images/build_icons.svg" alt="">
                    </div>
                    <div class="content_box_ct">
                        <div class="content_box_ct1">
                            <span>공문</span> 2024.10.08
                        </div>
                        <div class="content_box_ct2">
                            공문 제목입니다.
                        </div>
                    </div>
                </a>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<script>
    $(".tab_lnb li").on("click", function(){
        $(".tab_lnb li").removeClass("on");
        $(this).addClass("on");
    })
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>