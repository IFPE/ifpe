<?php
// 用途:PHP取得智慧財產局的Opendata(JSON格式)
// https://www.ifpe.tw/

// 宣告內容類型為text/html、編碼語系為utf-8
    header('Content-Type: text/html; charset=utf-8');

//HTML表單，action的值留白是為了在同一頁顯示結果
    echo "<FORM METHOD=\"post\" ACTION=\"\">
	<input type=\"text\" name=\"input_tk\" value=\"輸入識別碼\">
	<input type=\"text\" name=\"input_applname\" value=\"輸入申請人名稱\">
	<input type=\"submit\" name=\"Send\" value=\"送出\">
	</FORM>";

//當按鍵被按下時才執行
    if(isset($_POST['Send'])) {

//變數tk儲存鍵入的識別碼(向智慧財產局申請)、applname儲存鍵入的申請人名稱
	$tk = $_POST['input_tk'];
	$applname = $_POST['input_applname'];

//判斷鍵入的申請人名稱為中文或英文，以進行相應處理
//Opendata資料的網址(專利權狀態異動資料PatentRights)中，applnamee用於查詢英文，若值為中文可能查無資料；applnamec用於查詢中文，值為英文可能查無資料
        if (mb_strlen($applname, mb_detect_encoding($applname)) == strlen($applname)) {
//輸入為英文
            $applnamee = $_POST['input_applname'];
            $url = "https://tiponet.tipo.gov.tw/OpenDataApi/OpenData/API/PatentRights?tk=$tk&applclass=1&format=json&applnamee=$applnamee";
        } else {
//輸入為中文
            $applnamec = $_POST['input_applname'];
            $url = "https://tiponet.tipo.gov.tw/OpenDataApi/OpenData/API/PatentRights?tk=$tk&applclass=1&format=json&applnamec=$applnamec";
        }

//取JSON資料
        $json = file_get_contents($url);

//利用函數json_decode解析JSON格式資料，若有「true」則轉換為陣列，沒設定就轉換為物件
//例如: $json_data_array = json_decode($json, true); ===>轉為陣列
        $json_data_obj = json_decode($json);

//輸出資料
        if ($json_data_obj->{'total-count'} == 0) {
            echo "查無資料";
            exit;
        }
//驗證碼錯誤時
        else if($json_data_obj->{'status'} == "sampledata"){
            echo "<h2>". $json_data_obj->{'message'} . "</h2><br>";
        }

        echo "總數量: ". $json_data_obj->{'total-count'} . "<br>";

        echo "建立日期: ". $json_data_obj->{'tw-patent-rightsI'}->{'-create-date'} . "<br>";

        for($i = 0; $i <25; $i++) {

            echo "<br>序號: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'-sequence'} . "<br>";

	    echo "專利名稱: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'patent-title'}->{'patent-name-chinese'} . "<br>";

            echo "專利證書號: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'patent-right'}->{'patent-no'} . "<br>";

            echo "申請日: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'application-reference'}->{'appl-date'} . "<br>";

            echo "專利權止日: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'patent-right'}->{'patent-edate'} . "<br>";

            echo "消滅日期: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'patent-right'}->{'cancel-date'} . "<br>";

            echo "消滅原因: ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'patent-right'}->{'cancel-desc'} . "<br>";

            echo "申請人(中文): ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'parties'}->applicants[0]->{'chinese-name'} . "<br>";

            echo "申請人(英文): ". $json_data_obj->{'tw-patent-rightsI'}->patentcontent[$i]->{'parties'}->applicants[0]->{'english-name'} . "<br>";
        }
    }
?>
