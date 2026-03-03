<?php
require_once "./_common.php";

$ho_sql = "SELECT * FROM a_building_ho WHERE is_del = 0 and building_id = '{$building_id}'";
$ho_res = sql_query($ho_sql);

$ho_sql2 = "SELECT * FROM a_building_ho WHERE is_del = 0 and building_id = '{$building_id}'";
$ho_res2 = sql_query($ho_sql2);
?>
<div class="tbl_frm01_wrap">
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">전기</h2>
        
        
        <p class="h2_frm_sub_text mgt20">메인 검침</p>
        <table class="sub_table mgt10">
            <thead>
                <tr>
                    <!-- <th></th> -->
                    <th>전월(8/9) 검침값</th>
                    <th>당월 검침값</th>
                    <th>사용량(kWh)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- <td>1001</td> -->
                    <td>9562</td>
                    <td><input type="number" name="" class="meter_input" min="0"></td>
                    <td>709</td>
                </tr>
            </tbody>
        </table>
        <p class="h2_frm_sub_text mgt20">세대 검침</p>
        <p class="h2_frm_text mgt20">검침날짜</p>
        <div class="meter_reading_dates_wrap">
            <div class="meter_reading_dates">
                <input type="text" name="elec_date" id="elec_date" class="bansang_ipt ver2 ipt_date mgt10" readonly>
            </div>
            <a href="./meter_reading_excel.php?building_id=<?php echo $building_id; ?>&mr_year=<?php echo $mr_year;?>&mr_month=<?php echo $mr_month;?>&type=electro&mr_department=<?php echo $mr_department;?>&mr_id=<?php echo $mr_id;?>" onclick="return excelform(this.href);" class="btn btn_04">전기 엑셀 업로드</a>
        </div>
        <table class="sub_table mgt10">
            <thead>
                <tr>
                    <th>호</th>
                    <th>전월(8/9) 검침값</th>
                    <th>당월 검침값</th>
                    <th>사용량(kWh)</th>
                </tr>
            </thead>
            <tbody>
                <?php while($ho_row = sql_fetch_array($ho_res)){?>
                <tr>
                    <td>
                        <input type="hidden" name="ho_id" value="<?php echo $ho_row['ho_id']; ?>">
                        <?php echo $ho_row['ho_name']; ?>
                    </td>
                    <td></td>
                    <td><input type="number" name="electro_value[]" class="meter_input" min="0"></td>
                    <td></td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">수도</h2>
        
        <p class="h2_frm_sub_text mgt20">메인 검침</p>
        <table class="sub_table mgt10">
            <thead>
                <tr>
                    <!-- <th></th> -->
                    <th>전월(8/9) 검침값</th>
                    <th>당월 검침값</th>
                    <th>사용량(t)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- <td>0</td> -->
                    <td>9562</td>
                    <td><input type="number" name="" class="meter_input" min="0"></td>
                    <td>709</td>
                </tr>
            </tbody>
        </table>
        <p class="h2_frm_sub_text mgt20">세대 검침</p>
        <p class="h2_frm_text mgt20">검침날짜</p>
        <div class="meter_reading_dates_wrap">
            <div class="meter_reading_dates">
                <input type="text" name="water_date" id="water_date" class="bansang_ipt ver2 ipt_date mgt10" readonly>
            </div>
            <a href="./meter_reading_excel.php?building_id=<?php echo $building_id; ?>&mr_year=<?php echo $mr_year;?>&mr_month=<?php echo $mr_month;?>&type=water" onclick="return excelform(this.href);" class="btn btn_04">수도 엑셀 업로드</a>
        </div>
        <table class="sub_table mgt10">
            <thead>
                <tr>
                    <th>호</th>
                    <th>전월(8/9) 검침값</th>
                    <th>당월 검침값</th>
                    <th>사용량(t)</th>
                </tr>
            </thead>
            <tbody>
                <?php while($ho_row2 = sql_fetch_array($ho_res2)){?>
                <tr>
                    <td>
                        <input type="hidden" name="ho_id" value="<?php echo $ho_row2['ho_id']; ?>">
                        <?php echo $ho_row2['ho_name']; ?>
                    </td>
                    <td></td>
                    <td><input type="number" name="water_value[]" class="meter_input" min="0"></td>
                    <td></td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
</div>