<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
?>
<div id="wrappers">
    <div class="sm_register_agree">
        <div class="agree_wraps">
            <div class="inner">
                <p class="prv_all">
                    <input type="checkbox" id="chk_all">
                    <label for="chk_all">약관 전체 동의</label>
                </p>
                <ul class="prv_list">
                    <li>
                        <p class="regi_prv">
                            <input type="checkbox" name="chk1" id="chk1" class="chk_box" value="1">
                            <label for="chk1">[필수] SM인트라앱 서비스 이용약관</label>
                        </p>
                        <button type="button" onClick="prvPopOn('provision_sm');"></button>
                    </li>
                    <li>
                        <p class="regi_prv">
                            <input type="checkbox" name="chk2" id="chk2" class="chk_box" value="1">
                            <label for="chk2">[필수] 개인정보 수집 및 이용동의</label>
                        </p>
                        <button type="button" onClick="prvPopOn('privacy_sm');"></button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="agree_text_wrap mgt5">
            <div class="inner">
                <div class="agree_text">로그인 최초 1회 약관 동의 입니다.</div>
                <div class="agree_text">약관 동의 후 SM인트라 앱 사용 가능합니다.</div>
            </div>
        </div>
    </div>
    <div class="fix_btn_back_box"></div>
	<div class="fix_btn_box">
		<button type="button" class="fix_btn on" id="fix_btn" onClick="sm_agree_login();">확인</button>
	</div>
</div>
<div class="cm_pop" id="privacy_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont cm_pop_cont2">
		<p class="cm_pop_title cm_pop_title_privacy">개인정보처리방침</p>
        <div class="close_box">
            <div class="close_box_line close_box_line1"></div>
            <div class="close_box_line close_box_line2"></div>
        </div>
		<div class="cm_pop_desc cm_pop_desc_pop black ver2 mgt20">
      
		</div>
		<div class="cm_pop_btn_box">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('privacy_pop');">닫기</button>
		</div>
	</div>
</div>
<script>
function prvPopOn(privacy){
    
    if(privacy == "provision_sm"){
        $(".cm_pop_title_privacy").text("SM인트라앱 서비스 이용약관");
    }else{
        $(".cm_pop_title_privacy").text("개인정보 수집 및 이용약관");
    }

    $.ajax({

        url : "/privacy_ajax.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "co_id":privacy}, //파라미터 값
        success: function(msg){ //성공시 이벤트
            console.log(msg);
            $(".cm_pop_desc").html(msg); 
            popOpen('privacy_pop');

            
        }

    });
}

function sm_agree_login(){
    let mb_id = "<?php echo $id; ?>";
    let agree1 = $('input[name=chk1]:checked').val() ?? "0";
    let agree2 = $('input[name=chk2]:checked').val() ?? "0";

    let sendData = {'mb_id':mb_id, 'agree1':agree1, 'agree2':agree2};

    $.ajax({
        type: "POST",
        url: "/sm_login_agree_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                if(data.data != ""){
                    $("#" + data.data).focus();
                }
                return false;
            }else{
                showToast(data.msg);
                
                // popClose('visit_car_info');

                setTimeout(() => {
                    //window.location.reload();
                    location.replace("/sm_index.php");
                }, 700);
            }
        },
    });
}

$("#chk_all").click(function () {
  console.log($("#chk_all").is(":checked"));
  if ($("#chk_all").is(":checked")) {
    $(".chk_box").prop("checked", true);
  } else {
    $(".chk_box").prop("checked", false);
  }
  $(".chk_box").change();
});
$(".chk_box").click(function () {
  var total = $(".chk_box").length;
  var checked = $(".chk_box:checked").length;

  if (total != checked) $("#chk_all").prop("checked", false);
  else $("#chk_all").prop("checked", true);
});

</script>
<?php
include_once(G5_PATH.'/tail.php');