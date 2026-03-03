<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$bdi_data = explode("|", $bdi);

$year = date("Y");
$month = date("m");
$months = date("n");

$building_infos = get_builiding_info($bdi_data[0]);

// print_r2($building_infos);

//단지와 업종으로 계약된 업체가 있는지 확인
$contract_info = sql_fetch("SELECT * FROM a_contract WHERE building_id = '{$bdi_data[0]}' and industry_idx = '{$bdi_data[1]}'");

//단지 업종 현재 연도와 월에 등록된 점검일지가 있는지
$inspection_confirm = "SELECT * FROM a_inspection WHERE building_id = '{$bdi_data[0]}' and inspection_category = '{$bdi_data[1]}' and inspection_year = '{$year}' and inspection_month = '{$months}' and (inspection_status != 'Y' and inspection_status != 'H') ORDER BY inspection_idx desc limit 0,1 ";

// echo $inspection_confirm;
$inspection_confirm_row = sql_fetch($inspection_confirm);

//점검일지에 이미지 파일 등록했는지
$inspection_file = "SELECT * FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = '{$inspection_confirm_row['inspection_idx']}' ORDER BY bf_no asc ";
$inspection_file_res = sql_query($inspection_file); 

if($inspection_confirm_row){
    //등록된 점검일지가 있다면

    //수정상태로
    $status = "u";
    
    $inspection_idx = $inspection_confirm_row['inspection_idx'];

    //등록된 점검일지가 재점검요청 상태가 아니면
    if($inspection_confirm_row['inspection_status'] != 'R'){
        $readonly = "readonly"; //input readonly
        $disabled = "disabled"; //파일첨부 비활성
        $cl = "";
    }else{
        //재점검요청 아니면 초기화
        $readonly = "";
        $disabled = "";
        $cl = "ver2";
    }
}else{
    //초기화
    $status = "";
    $inspection_idx = "";
    $readonly = "";
    $disabled = "";
    $cl = "ver2";
}

//print_r2($contract_info);
?>
<?php if($contract_info){ //계약된 업체가 있다면 ?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="insepecton_form_t parking_sc parking_sc1">
            <div class="inner">
                <p><?php echo $building_infos['building_name']; ?></p>
                <p class="inspection_txt"><?php echo $contract_info['company_name']; ?> - <?php echo $contract_info['industry_name']; ?></p>
                <p class="inspection_txt">점검기간 : <?php echo $year; ?>.<?php echo $month;?></p>
            </div>
        </div>
        <div class="inner" style="padding-bottom:20px;">
            <div class="inspection_form_wrap">
                <ul class="regi_list">
                    <?php if($status == 'u'){?>
                    <li>
						<p class="regi_list_title">상태</p>
						<div class="ipt_box">
                            <?php
                             switch($inspection_confirm_row['inspection_status']){
                                case "N":
                                    $ins_status = "승인대기";
                                    break;
                                case "Y":
                                    $ins_status = "승인";
                                    break;
                                case "R":
                                    $ins_status = "재점검";
                                    break;
                                case "H":
                                    $ins_status = "보류";
                                    break;
                            }
                            ?>
							<input type="text" name="inspection_status" id="inspection_status" class="bansang_ipt <?php echo $cl; ?>" placeholder="상태" value="<?php echo $ins_status; ?>" readonly>
						</div>
					</li>
                    <?php }?>
					<li>
						<p class="regi_list_title">작성자</p>
						<div class="ipt_box">
							<input type="text" name="ins_name" id="ins_name" class="bansang_ipt <?php echo $cl; ?>" placeholder="이름을 입력해주세요." value="<?php echo $inspection_confirm_row['inspection_name']; ?>" <?php echo $readonly; ?>>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">연락처</p>
						<div class="ipt_box">
							<input type="tel" name="ins_hp" id="ins_hp" class="bansang_ipt <?php echo $cl; ?> phone" placeholder="- 없이 숫자만 입력해 주세요." value="<?php echo $inspection_confirm_row['inspection_hp']; ?>" <?php echo $readonly; ?>>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">사진첨부 <span class="pic_ver">*jpg, jpeg,png만 등록 가능합니다.</span></p>
						<div class="ipt_box">
                            <div class="img_upload_wrap">
                                <div class="img_upload_box ver1">
                                    <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*" <?php echo $disabled; ?>>
                                    <label for="img_up">
                                        <img src="/images/file_plus.svg" alt="">
                                    </label>
                                </div>
                                <?php if($status == "u" && $inspection_file_res){?>
                                    <?php for($i=0;$inspection_file_row = sql_fetch_array($inspection_file_res);$i++){?>
                                        <div class="img_upload_box_wrapper4">
                                            <div class="img_upload_box ver4 filebox">
                                                <input type="file" name="img_up<?php echo $i; ?>" id="img_up<?php echo $i + 1; ?>" accept="image/*" onchange="fileUp(this, 'img_up<?php echo $i + 1; ?>', <?php echo $i; ?>, 'before')"  <?php echo $disabled; ?>>
                                                
                                                <img src="/data/file/inspection/<?php echo $inspection_file_row['bf_file']; ?>" class="img_up<?php echo $i + 1; ?>" alt="" onclick="bigSize('/data/file/inspection/<?php echo $inspection_file_row['bf_file']; ?>')">

                                                <?php 
                                                if($inspection_confirm_row['inspection_status'] == 'R'){
                                                    //재점검 요청일때만 삭제
                                                ?>
                                                <div class="file_del">
                                                    <input type="checkbox" name="inspection_file_del[<?php echo $inspection_file_row['bf_no'];?>]" id="inspection_file_del<?php echo $i+1;?>" class="inspection_file_del" value="1">
                                                    <label for="inspection_file_del<?php echo $i+1;?>">삭제</label>
                                                </div>
                                                <?php }?>
                                            </div>
                                            <?php 
                                            if($inspection_confirm_row['inspection_status'] == 'R'){
                                                //재점검 요청일때만 삭제
                                            ?>
                                            <label class="img_labels" for="img_up<?php echo $i + 1;?>">
                                            이미지 변경
                                            </label>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">제목</p>
						<div class="ipt_box">
                            <input type="text" name="inspection_title" id="inspection_title" class="bansang_ipt <?php echo $cl; ?>" value="<?php echo $inspection_confirm_row['inspection_title']; ?>" placeholder="제목을 입력해주세요." <?php echo $readonly; ?>>
						</div>
					</li>
                    <li>
						<p class="regi_list_title">특이사항</p>
						<div class="ipt_box">
                            <textarea name="ins_memo" id="ins_memo" class="bansang_ipt <?php echo $cl; ?> ta" placeholder="특이사항을 입력해주세요." <?php echo $readonly; ?>><?php echo $inspection_confirm_row['inspection_memo']; ?></textarea>
						</div>
					</li>
                </ul>
            </div>
        </div>
        <?php if($status == ""){ //점검일지 처음 등록시 ?>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box">
            <button type="button" onclick="inspection_submit();" class="fix_btn on" id="fix_btn" >저장</button>
        </div>
        <?php }else{ //점검일지 등록 후 재점검 요청일 때 수정버튼 생성 ?>
        <?php if($inspection_confirm_row['inspection_status'] == 'R'){?>
            <div class="fix_btn_back_box"></div>
            <div class="fix_btn_box">
                <button type="button" onclick="inspection_submit();" class="fix_btn on" id="fix_btn" >수정</button>
            </div>
        <?php }?>
        <?php }?>
    </div>
</div>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>점검일지를 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<?php }else{ //계약된 업체가 없으면 ?>
<div id="wrappers">
    <div class="wrap_container">
        
        <div class="empty_inspection">
        <p class="login_logo"><img src="/images/bansang_logos.svg" alt="이맥스 로고"></p>
        단지에 해당 업종으로 등록된 업체가 없습니다.
        </div>
    </div>
</div>
<?php }?>

<!-- 이미지 팝업 -->
<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>
<!-- heic 파일용 -->
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script>
function bigSize(url){
	const windowHeight = window.innerHeight;
	$("#big_size_pop .od_cancel_cont").css("height", `${windowHeight}px`);
	$("#big_img").attr("src", url);
	$("#big_size_pop").show();
}

function bigSizeOff(){
	$("#big_size_pop").hide();
	$("#big_img").attr("src", "");
}

function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
}


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

var fileNo = 0;
var filesArr = new Array();
let fileImg = new Array();
var imgdatas = '';
var attFileCnt1 = 0;
var attFileCnt2 = document.querySelectorAll('.filebox').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCnt2;j++){

    //console.log("fileArr index", j);
    filesArr.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile(obj){
    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        showToast("첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                        <div class="img_upload_box_wrapper4">
                            <div class="img_upload_box ver4 filebox">
                                <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_up${cnt + 1}" alt="">
                                </label>
                            </div>
                             <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt22 = document.querySelectorAll('.filebox').length + curFileCnt;

                // console.log('attFileCnt2', attFileCnt2 + curFileCnt);

                // if(attFileCnt22 == maxFileCnt){
                //     $(".work_img_up1").hide();
                // }
            } else {
                continue;
            }

            
        }
    }
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}

function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper4');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper4'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper4');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `img_up${i}`;
            fileInput.id = `img_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp(this, 'img_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `img_up${i + 1}`);
            img.className = `img_up${i + 1}`;
        });

        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }
    }
}

function fileUp(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr[index] = input.files[0];

        console.log('file up file arr', filesArr)
      
    }
}

/* 첨부파일 검증 */
function validation(obj){
    const fileTypes = ['application/pdf', 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif', 'image/heic'];
    //'application/haansofthwp', 'application/x-hwp'
    if (obj.name.length > 100) {
        alert("파일명이 100자 이상인 파일은 제외되었습니다.");
        return false;
    } else if (obj.size > (100 * 1024 * 1024)) {
        alert("최대 파일 용량인 100MB를 초과한 파일은 제외되었습니다.");
        return false;
    } else if (obj.name.lastIndexOf('.') == -1) {
        alert("확장자가 없는 파일은 제외되었습니다.");
        return false;
    } else if (!fileTypes.includes(obj.type)) {
        alert("첨부가 불가능한 파일은 제외되었습니다.");
        return false;
    } else {
        return true;
    }
}

function inspection_submit(){

    buildingInfoPopOpen();

    let w = "<?php echo $status; ?>";
    let inspection_idx = "<?php echo $inspection_idx;?>";
    let building_id = '<?php echo $bdi_data[0]; ?>';
    // let dong_id = '<?php echo $bdi_data[1]; ?>';
    let inspection_category = '<?php echo $bdi_data[1]; ?>';
    let inspection_cmp = '<?php echo $contract_info['company_idx'];?>';
    let inspection_name = $("#ins_name").val();
    let inspection_hp = $("#ins_hp").val();
    let inspection_year = '<?php echo date("Y"); ?>';
    let inspection_month = '<?php echo date("n"); ?>';
    let inspection_title = $("#inspection_title").val();
    let inspection_memo = $("#ins_memo").val();

    var formData = new FormData();
    formData.append('w', w);
    formData.append('inspection_idx', inspection_idx);
    formData.append('building_id', building_id);
    // formData.append('dong_id', dong_id);
    formData.append('inspection_category', inspection_category);
    formData.append('inspection_cmp', inspection_cmp);
    formData.append('inspection_name', inspection_name);
    formData.append('inspection_hp', inspection_hp);
    formData.append('inspection_year', inspection_year);
    formData.append('inspection_month', inspection_month);
    formData.append('inspection_title', inspection_title);
    formData.append('inspection_memo', inspection_memo);

    //파일첨부
    for (var i = 0; i < filesArr.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("inspection_file[]", filesArr[i]);
    }

    // 파일삭제 체크된 삭제 항목 추가
    $("input[name^=inspection_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("inspection_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("inspection_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });


    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/inspection_form_update.php",
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                if(data.result == false) { 
                    showToast(data.msg);

                    buildingInfoPopClose();
                    return false;
                }else{
                    //showToast(data.msg);
                    showToast(data.msg);

                    setTimeout(() => {
                        location.replace('/inspection_end.php?w=' + w);
                    }, 700);
                }
            },
            error:function(e){
                console.log(e);
            }
        });
    }, 50);
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>