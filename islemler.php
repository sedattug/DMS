<?php
header('Content-Type: text/html; charset=ISO-8859-9');
    include "../../security/db.php";
	if(isset($_GET["table"]) && !empty($_GET["table"])){
		$table = $_GET["table"];
		
	}else {
		echo "<center><h2>Tablo Adı Belirtilmeli !</h2></center>";
		exit;
	}
	if(isset($_POST["islem"])){
		$islem = $_POST["islem"];
		$hat_no = $_POST["hat_no"];
		$nok_no = $_POST["nok_no"];
		if($islem == "edit"){
			$sql = $db->prepare("SELECT * FROM $table where NIVHAT_NO = :hat_no AND NIVNOK_NO = :nok_no");
			$sql->execute(array("hat_no" => $hat_no, "nok_no" => $nok_no));	
				while ($row = $sql->fetch(PDO::FETCH_ASSOC))
					{
					$rows[] = $row;
					}
				print json_encode($rows);
			
		}
	}elseif(isset($_GET["editConfirm"])){
		$hat_no = $_GET["hat_no"];
		$nok_no = $_GET["nok_no"];
		$arr=$_POST; 
		//echo "$table-- $hat_no-- $nok_no<br>";
		//print_r($arr);
		
		foreach($arr as $key => $val)
			{
				//echo $key."-".$val;
			$stmt = $db->prepare("UPDATE $table
					SET $key=:$key 
					WHERE NIVHAT_NO = '$hat_no' AND NIVNOK_NO = '$nok_no'");
			$stmt->execute(array(
								"$key"    => $val
							)); 
			} 
			//$sql = "SELECT * FROM $table where NIVHAT_NO LIKE '%$hat_no%' AND NIVNOK_NO LIKE '%$nok_no%'";
		$sql = $db->prepare("SELECT * FROM $table where NIVHAT_NO = '$hat_no' AND NIVNOK_NO = '$nok_no'");
						$sql->execute();
		
			while ($row = $sql->fetch(PDO::FETCH_ASSOC))
				{
				$rows[] = $row;
				}
			print json_encode($rows); 
	}elseif(isset($_POST["deleteConfirm"])){
		$p = $_POST;
		$hat_no = $p["hatno"];
		$nok_no = $p["nokno"];
		//$sql = "DELETE FROM $table WHERE NIVHAT_NO = '$hat_no' AND NIVNOK_NO = '$nok_no'";
		$stmt = $db->prepare("DELETE FROM $table WHERE NIVHAT_NO = '$hat_no' AND NIVNOK_NO = '$nok_no'");
		$stmt->execute();
		//echo $sql;
		echo "$hat_no$nok_no";
	}elseif(isset($_GET["addConfirm"])){
		$arr = $_POST;
		$datafields = array();
		$insert_values = array();
		 foreach($arr as $key => $val)
			{
				array_push($datafields, $key);
				array_push($insert_values, $val);
			} 
		function placeholders($text, $count=0, $separator=","){
			$result = array();
			if($count > 0){
				for($x=0; $x<$count; $x++){
					$result[] = $text;
				}
			}
			return implode($separator, $result);
		}

		$question_marks[] = '('  . placeholders('?', sizeof($datafields)) . ')';
		
		$sql = "INSERT INTO $table (" . implode(",", $datafields ) . ") VALUES " . implode(',', $question_marks);
		$stmt = $db->prepare ($sql);
		$stmt->execute($insert_values);
		echo 1;
		
	}
	$db = null;
?>