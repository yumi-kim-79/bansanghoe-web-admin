<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">

            <div class="mng_bill_list">
                <ul>
                    <li>
                        <div class="mng_bill_cont">회사명</div>
                        <div class="mng_bill_price"><?php echo $config['cf_title']; ?></div>
                    </li>
                    <li>
                        <div class="mng_bill_cont">사업자번호</div>
                        <div class="mng_bill_price"><?php echo $config['cf_2']; ?></div>
                    </li>
                    <li>
                        <div class="mng_bill_cont">앱버전</div>
                        <div class="mng_bill_price">1.0</div>
                    </li>
                   
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>