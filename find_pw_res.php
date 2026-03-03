<?php
include_once("_common.php");
include_once(G5_PATH."/_head.php");

if($id == ""){
    alert("잘못된 접근입니다.", "/");
}
?>
<form name="pw_change_frm" id="pw_change_frm" method="post" autocomplete="off">
    <input type="hidden" name="types" id="types" value="<?php echo $types; ?>">
    <input type="hidden" name="mb_id" id="mb_id" value="<?php echo $id; ?>">
	<div class="find_id_view sub_box">
		<div class="inner">
			<p class="regi_title">비밀번호 설정</p>
			<ul class="regi_list mgt30">
                <li>
					<p class="regi_list_title">새 비밀번호</p>
					<div class="ipt_box">
						<input type="password" name="mb_password" id="mb_password" class="bansang_ipt" placeholder="영문, 숫자 6자리 이상 16자리 미만" maxLength="16">
					</div>
                    <div class="ipt_box">
						<input type="password" name="mb_password_re" id="mb_password_re" class="bansang_ipt" placeholder="비밀번호 확인" maxLength="16">
					</div>
				</li>
            </ul>
        </div>
    </div>
    <div class="fix_btn_back_box"></div>
	<div class="fix_btn_box">
		<button type="button" class="fix_btn on" id="fix_btn" onClick="pw_update();">확인</button>
	</div>
</form>

<script>
function pw_update(){
    //location.href = "./find_pw_res.php";
    var types = "<?php echo $types; ?>";
    var formData = $("#pw_change_frm").serialize();

    $.ajax({
        type: "POST",
        url: "/find_pw_res_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
                if(data.data != ""){
                    $("#" + data.data).focus();
                }
                return false;
            }else{
                //$(".timer").show();
                //startTimer(180);
                //startTimer(60)
                showToast(data.msg);
                
                setTimeout(() => {
                    if(types == 'sm'){
                        location.href = "/bbs/login_sm.php";
                    }else{  
                        location.href = "/bbs/login.php";
                    }
                }, 700);
                
            }
        }
    });  
}
</script>
<?php
include_once(G5_PATH."/_tail.php");
?>