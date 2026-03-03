<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');




$complain_sql = "SELECT * FROM a_online_complain WHERE complain_idx = '{$complain_idx}'";
$complain_row = sql_fetch($complain_sql);

//print_r2($complain_row);
$complain_file = "SELECT * FROM g5_board_file WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' ORDER BY bf_no asc ";
$complain_file_res = sql_query($complain_file);

//echo $_SERVER['HTTP_USER_AGENT'];
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <form action="">
                <ul class="regi_list">
                    <li>
                        <div class="ipt_box">
                            <input type="text" name="complain_title" id="complain_title" class="bansang_ipt ver2" placeholder="제목을 입력하세요." value="<?php echo $complain_row['complain_title'];?>">
                        </div>
                    </li>
                    <li>
                        <div class="ipt_box">
                            <textarea name="complain_content" id="complain_content" class="bansang_ipt ver2 ta" placeholder="민원 내용을 입력하세요."><?php echo $complain_row['complain_content'];?></textarea>
                        </div>
                    </li>
                </ul>
                <div class="img_upload_wrap mgt20">
                    <div class="img_upload_box ver1">
                        <!-- accept="image/*" -->
                        <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                        <label for="img_up">
                            <img src="/images/file_plus.svg" alt="">
                        </label>
                    </div>
                    <?php if($w == "u" && $complain_file_res){?>
                        <?php for($i=0;$complain_file_row = sql_fetch_array($complain_file_res);$i++){?>
                            <!-- 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif' -->
                            <div class="img_upload_box filebox">
                                <input type="file" name="img_up<?php echo $i; ?>" id="img_up<?php echo $i + 1; ?>" accept=".jpg,.jpeg,.png,.gif,.tmp,.bmp" onchange="fileUp(this, 'img_up<?php echo $i + 1; ?>', <?php echo $i; ?>, 'before')">
                                <label for="img_up<?php echo $i + 1; ?>">
                                    <img src="/data/file/complain/<?php echo $complain_file_row['bf_file']; ?>" class="img_up<?php echo $i + 1; ?>" alt="">
                                </label>

                                <div class="file_del">
                                    <input type="checkbox" name="complain_file_del[<?php echo $complain_file_row['bf_no'];?>]" id="complain_file_del<?php echo $i+1;?>" class="complain_file_del" value="1">
                                    <label for="complain_file_del<?php echo $i+1;?>">삭제</label>
                                </div>
                            </div>
                        <?php }?>
                    <?php }?>
                </div>
            </form>
        </div>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box ver3">
            <button type="button" onclick="complain_submit();" class="fix_btn on" id="fix_btn" onClick="register();">민원 <?php echo $w == 'u' ? '수정' : '접수';?>하기</button>
        </div>
    </div>
</div>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<div class="cm_pop" id="complain_form_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">온라인 민원 접수가 완료되었습니다.<br>
        담당자가 확인 후 순차적으로 답변 드릴 예정입니다.</p>
		<div class="cm_pop_btn_box">
            <button type="button" class="cm_pop_btn ver2" onClick="go_page();">확인</button>
		</div>
	</div>
</div>
<!-- heic 파일용 -->
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script>
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
                reader.onload = async function (e) {
                    // filesArr.push(file);
                    let processedFile = file;
                    // const files = e.target.files;

                   
                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    let previewHTML;
                    console.log('cnt', cnt);

                    //heic 파일이라면 변환
                    if (file.type === "image/heic" || file.name.endsWith(".heic")) {

                        try {

                            const blob = await heic2any({ blob: file, toType: "image/jpeg" });
                            const url = URL.createObjectURL(blob);

                             console.log(url);

                            // 새 File 객체로 교체
                            processedFile = new File([blob], file.name.replace(/\.heic$/, '.jpg'), {
                                type: 'image/jpeg'
                            });

                             previewHTML = `
                                <div class="img_upload_box_wrapper4">
                                    <div class="img_upload_box ver4 filebox">
                                        <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                        <label for="img_up${cnt + 1}">
                                            <img src="${url}" class="img_up${cnt + 1}" alt="">
                                        </label>
                                        
                                    </div>
                                    <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                        삭제
                                    </button>
                                </div>
                            `;
                        } catch (err) {
                            console.log('err', err);
                        }
                        
                    }else{
                         previewHTML = `
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
                    }

                    
                    filesArr.push(processedFile);

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

    console.log('filesArr', filesArr);
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

        console.log('fire arr after delete', filesArr);
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
    const fileTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif', 'image/heic'];
    //'image/heic', 'application/pdf'
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
        alert("첨부가 불가능한 파일은 제외되었습니다. gif, jpg, png만 가능");
        return false;
    } else {
        return true;
    }
}

function complain_submit(){

    let w = "<?php echo $w; ?>";
    let complain_idx = "<?php echo $complain_idx; ?>";
    let mb_id = "<?php echo $user_info['mb_id']; ?>";
    let complain_title = $("#complain_title").val();
    let complain_content = $("#complain_content").val();
    let cstatus = "<?php echo $cstatus; ?>";
    let ho_id = "<?php echo $user_building['ho_id']; ?>";
    let dong_id = "<?php echo $user_building['dong_id']; ?>";
    let post_id = "<?php echo $user_building['post_id']; ?>";
    let building_id = "<?php echo $user_building['building_id']; ?>";

    var formData = new FormData();
    formData.append('w', w);
    formData.append('complain_idx', complain_idx);
    formData.append('mb_id', mb_id);
    formData.append('complain_title', complain_title);
    formData.append('complain_content', complain_content);

    formData.append('post_id', post_id);
    formData.append('building_id', building_id);
    formData.append('dong_id', dong_id);
    formData.append('ho_id', ho_id);

    for (var i = 0; i < filesArr.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("complain_file[]", filesArr[i]);
    }

    // 파일삭제 체크된 삭제 항목 추가
    $("input[name^=complain_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });


    popOpen('building_info_pop');


    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/online_complain_form_update.php",
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
                    //$(".btn_submit").attr('disabled', false);
                    popClose('building_info_pop');
                    return false;
                }else{
                    //showToast(data.msg);
                    if(w == 'u'){
                        showToast(data.msg);

                        setTimeout(() => {
                            location.replace('/online_complain_info.php?complain_idx=' + complain_idx + '&cstatus=' + cstatus); 
                        }, 300);
                    }else{
                        popClose('building_info_pop');
                        popOpen('complain_form_pop');
                    }
                }
            },
            error:function(e){
                alert("관리자에게 문의해주세요.");
            }
        });
    }, 50);

    
}

function go_page(){
    window.location.href = '/online_complain.php?tabIdx=2'; 
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>