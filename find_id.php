<?php
include_once("_common.php");
include_once(G5_PATH."/_head.php");
?>
<form name="find_frm" id="find_frm" method="post" autocomplete="off">
	<div class="find_id_view sub_box">
		<div class="inner">
			<p class="regi_title"><?php echo $title?> 아이디 찾기</p>
            <input type="hidden" name="mtype" value="<?php echo $type; ?>">
			<ul class="regi_list mgt30">
				<li>
					<p class="regi_list_title"><?php echo $type == "sm" ? "담당자" : "이름";?> <span>*</span></p>
					<div class="ipt_box">
						<input type="text" name="mb_name" id="mb_name" class="bansang_ipt" placeholder="이름을 입력하세요.">
					</div>
				</li>
                <li>
                    <p class="regi_list_title">연락처 <span>*</span></p>
					<div class="ipt_box">
						<input type="tel" name="mb_hp" id="mb_hp" class="bansang_ipt phone" placeholder="연락처를 입력하세요. (-제외)" maxlength="13">
					</div>
                </li>
			</ul>
		</div>
	</div>

	<div class="fix_btn_back_box"></div>
	<div class="fix_btn_box">
		<button type="button" class="fix_btn on" id="fix_btn" onClick="find_info();">확인</button>
	</div>
</form>
<script>
//연락처 하이픈
$(".phone").keyup(function () {
  // 숫자 이외의 모든 문자 제거
  var value = this.value.replace(/[^0-9]/g, "");

  // 길이에 따라 하이픈 삽입
  if (value.length <= 3) {
    // 3자리까지는 아무것도 하지 않음
    this.value = value;
  } else if (value.length <= 7) {
    // 4자리까지는 '010-XXXX' 형태
    this.value = value.replace(/(\d{3})(\d{0,4})/, "$1-$2");
  } else if (value.length <= 11) {
    // 11자리까지는 '010-XXXX-YYYY' 형태
    this.value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  } else {
    // 11자리를 초과하는 경우는 잘라서 처리
    this.value = value
      .substring(0, 11)
      .replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  }
});

function find_info(){

    //location.href = "./find_id_res.php";
    let types = "<?php echo $types; ?>";

    var formData = $("#find_frm").serialize();

    $.ajax({
        type: "POST",
        url: "/find_id_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                $("#" + data.data).focus();
                return false;
            }else{
               
                location.href = "./find_id_res.php?types="+types+"&hp=" + data.data;
            }
        },
    });
}
</script>
<?php
include_once(G5_PATH."/_tail.php");
?>