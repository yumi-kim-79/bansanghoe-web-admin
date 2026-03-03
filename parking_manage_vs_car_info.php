<?php
require_once "./_common.php";

//print_r2($_POST);

$sql = "SELECT * FROM a_building_visit_car WHERE car_id = '{$car_id}'";
//echo $sql;
$row = sql_fetch($sql);

?>
<li>
    <input type="hidden" name="car_id" id="car_id" value="<?php echo $car_id; ?>">
    <p class="regi_list_title">방문자 차량 차종 <span>*</span></p>
    <div class="ipt_box">
        <input type="text" name="visit_car_name" id="visit_car_name2" class="bansang_ipt ver2" placeholder="차종을 입력해주세요." value="<?php echo $row['visit_car_name'];?>">
    </div>
</li>
<li>
    <p class="regi_list_title">방문자 차량 번호 <span>*</span></p>
    <div class="ipt_box">
        <input type="text" name="visit_car_number" id="visit_car_number2" class="bansang_ipt ver2" placeholder="차량번호를 입력해주세요." value="<?php echo $row['visit_car_number'];?>">
    </div>
</li>
<li>
    <p class="regi_list_title">방문자 연락처 <span>*</span></p>
    <div class="ipt_box">
        <input type="tel" name="visit_hp" id="visit_hp2" class="bansang_ipt ver2 phone" placeholder="연락처를 입력해주세요." maxLength="13" value="<?php echo $row['visit_hp'];?>">
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
        </script>
    </div>
</li>