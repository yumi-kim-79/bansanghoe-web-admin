<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

// 로그인 체크
if(!$is_users){
    alert('로그인이 필요합니다.', '/bbs/login.php');
}

$ho_id = $user_building['ho_id'];
$building_name = $user_building['building_name'];
$dong_name = $user_building['dong_name'];
$ho_name = $user_building['ho_name'];

// 등록된 카드 정보 조회
$card_sql = "SELECT * FROM a_billing_card WHERE ho_id = '{$ho_id}' AND is_del = 0 ORDER BY card_idx DESC LIMIT 1";
$card_row = sql_fetch($card_sql);
$has_card = ($card_row['card_idx'] > 0);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="card_register_wrap">
                <!-- 안내 문구 -->
                <div class="card_info_box">
                    <p class="card_info_title">자동결제 카드 관리</p>
                    <p class="card_info_desc">카드를 등록하면 매월 관리비가 자동으로 결제됩니다.</p>
                    <p class="card_info_building"><?php echo $building_name.' '.$dong_name.'동 '.$ho_name.'호'; ?></p>
                </div>

                <?php if($has_card){ ?>
                <!-- 등록된 카드 정보 -->
                <div class="card_registered_box">
                    <div class="card_visual">
                        <div class="card_chip"></div>
                        <p class="card_number"><?php echo $card_row['card_number']; ?></p>
                        <p class="card_company"><?php echo $card_row['card_company']; ?></p>
                        <p class="card_date">등록일: <?php echo substr($card_row['created_at'], 0, 10); ?></p>
                    </div>
                    <div class="card_btn_wrap">
                        <button type="button" class="card_btn card_btn_change" onclick="requestBillingKey();">카드 변경</button>
                        <button type="button" class="card_btn card_btn_delete" onclick="deleteCard();">카드 삭제</button>
                    </div>
                </div>
                <?php }else{ ?>
                <!-- 카드 미등록 -->
                <div class="card_empty_box">
                    <div class="card_empty_icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#B8B8B8" stroke-width="1.5">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                    </div>
                    <p class="card_empty_text">등록된 카드가 없습니다.</p>
                    <button type="button" class="card_register_btn" onclick="requestBillingKey();">카드 등록하기</button>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- 삭제 확인 팝업 -->
<div class="cm_pop" id="card_del_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <p class="cm_pop_desc2">등록된 카드를 삭제하시겠습니까?<br><span style="font-size:13px;color:#999;">삭제 시 자동결제가 중단됩니다.</span></p>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('card_del_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="confirmDeleteCard();">삭제</button>
        </div>
    </div>
</div>

<style>
.card_register_wrap {padding-top:20px;}
.card_info_box {margin-bottom:24px;}
.card_info_title {font-size:20px;font-weight:700;color:var(--fontColor);margin-bottom:6px;}
.card_info_desc {font-size:14px;color:var(--fontColor2);margin-bottom:8px;}
.card_info_building {font-size:13px;color:var(--fontColor6);font-weight:500;}

/* 등록된 카드 */
.card_registered_box {margin-top:10px;}
.card_visual {
    background:linear-gradient(135deg, #1a2e4a 0%, #2d4a7a 100%);
    border-radius:14px;
    padding:24px 20px;
    color:#fff;
    position:relative;
    min-height:160px;
}
.card_chip {
    width:36px;height:28px;
    background:linear-gradient(135deg, #e8c547 0%, #d4a832 100%);
    border-radius:5px;
    margin-bottom:20px;
}
.card_number {font-size:18px;font-weight:600;letter-spacing:2px;margin-bottom:12px;font-family:'Courier New',monospace;}
.card_company {font-size:14px;font-weight:500;opacity:0.9;}
.card_date {font-size:12px;opacity:0.6;position:absolute;bottom:20px;right:20px;}

.card_btn_wrap {display:flex;gap:10px;margin-top:16px;}
.card_btn {
    flex:1;
    height:48px;
    border-radius:10px;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    border:none;
}
.card_btn_change {background:var(--colorMain);color:#fff;}
.card_btn_delete {background:#F1F3F4;color:var(--fontColor2);}

/* 카드 미등록 */
.card_empty_box {
    text-align:center;
    padding:60px 20px;
    background:var(--boxColor);
    border-radius:14px;
    border:2px dashed var(--borderColor);
}
.card_empty_icon {margin-bottom:16px;}
.card_empty_text {font-size:15px;color:var(--fontColor6);margin-bottom:24px;}
.card_register_btn {
    width:100%;
    max-width:280px;
    height:52px;
    background:var(--colorMain);
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
</style>

<script src="https://js.tosspayments.com/v1/payment"></script>
<script>
var tossPayments = TossPayments('test_ck_placeholder');

function requestBillingKey(){
    var hoId = "<?php echo $ho_id; ?>";
    var customerKey = "bansang_ho_" + hoId;
    var orderId = "CARD_" + hoId + "_" + Date.now();

    tossPayments.requestBillingKeyAuth('카드', {
        customerKey: customerKey,
        successUrl: window.location.origin + '/card_register_callback.php?ho_id=' + hoId,
        failUrl: window.location.origin + '/card_register.php?fail=1',
    });
}

function deleteCard(){
    popOpen('card_del_pop');
}

function confirmDeleteCard(){
    popClose('card_del_pop');

    $.ajax({
        type: "POST",
        url: "/api/billing_api.php",
        data: {action: 'delete_card', ho_id: '<?php echo $ho_id; ?>'},
        dataType: "json",
        success: function(data){
            if(data.result){
                alert('카드가 삭제되었습니다.');
                location.reload();
            } else {
                alert(data.msg || '삭제 중 오류가 발생했습니다.');
            }
        },
        error: function(){
            alert('통신 오류가 발생했습니다.');
        }
    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
