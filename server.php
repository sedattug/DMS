<?php
header('Content-Type: text/html; charset=ISO-8859-9');
include "../../security/db.php";
if(isset($_POST["table"]) && !empty($_POST["table"])){
		if(isset($_POST["pafta250"])){
			if(!empty($_POST["pafta250"])){
				$pafta250 = $_POST["pafta250"];
				$pafta250 = " PAF250 LIKE '%$pafta250%'";
			}else {
				$pafta250 = " PAF250 is not null";
			}
		}else{
			$pafta250 = " PAF250 is not null";
		}
		
		if(isset($_POST["pafta100"])){
			if(!empty($_POST["pafta100"])){
				$pafta100 = $_POST["pafta100"];
				$pafta100 = "  PAF100 LIKE '%$pafta100%'";
			}else {
				$pafta100 = " PAF100 is not null";
			}
		}else{
			$pafta100 = " PAF100 is not null";
		}
		
		if(isset($_POST["pafta50"])){
			if(!empty($_POST["pafta50"])){
				$pafta50 = $_POST["pafta50"];
				$pafta50 = " PAF50 LIKE '%$pafta50%'";
			}else {
				$pafta50 = " PAF50 is not null";
			}
		}else{
			$pafta50 = " PAF50 is not null";
		}
		if(isset($_POST["pafta25"])){
			if(!empty($_POST["pafta25"])){
				$pafta25 = $_POST["pafta25"];
				$pafta25 = " PAF25 LIKE '%$pafta25%'";
			}else {
				$pafta25 = " PAF25 is not null";
			}
		}else{
			$pafta25 = " PAF25 is not null";
		}
		
		if(isset($_POST["postedDistance"])){
			if(!empty($_POST["postedDistance"])){
				$postedDistance = $_POST["postedDistance"];
			}else {
				$postedDistance = 10;
			}
		}else{
			$postedDistance = 10;
		}
		
		if(isset($_POST["postedEnlem"])){
			if(!empty($_POST["postedEnlem"])){
				$postedEnlem = str_replace(",",".",$_POST["postedEnlem"]);
				$postedEnlemQuery = " ENLEM >=  $postedEnlem -($postedDistance/111.045) AND ENLEM <=  $postedEnlem +($postedDistance/111.045) ";
			}else {
				$postedEnlemQuery = " ENLEM is not null";
			}
		}else{
			$postedEnlemQuery = " ENLEM is not null";
		}
		
		if(isset($_POST["postedBoylam"])){
			if(!empty($_POST["postedBoylam"])){
				$postedBoylam = str_replace(",",".",$_POST["postedBoylam"]);
				$postedBoylamQuery = " BOYLAM >= $postedBoylam -($postedDistance/(111.045 * COS($postedEnlem/57.2957795))) AND BOYLAM <= $postedBoylam +($postedDistance/(111.045 * COS($postedEnlem/57.2957795))) ";
			}else {
				$postedBoylamQuery = " BOYLAM is not null";
			}
		}else{
			$postedBoylamQuery = " BOYLAM is not null";
		}
		
		if(isset($_POST["hazirlayan"])){
			if(!empty($_POST["hazirlayan"]) && !empty($_POST["kontrol_eden"])){
				$hazirlayan = $_POST["hazirlayan"];
				$kontrol_eden = $_POST["kontrol_eden"];
				$firma_adi = $_POST["firma_adi"];
				$arama_tipi = $_POST["arama_tipi"];
					if($hazirlayan == $kontrol_eden) {
						echo 1; // Hazırlayan ve Kontrol Eden Alanları aynı olamaz.
						exit;
					}
				$sql = $db->prepare("SELECT * FROM PERSONEL_TBL where ADISOYADI = :ADISOYADI");
				$sql->execute(array("ADISOYADI" => $hazirlayan));	
					$row = $sql->fetch(PDO::FETCH_OBJ);
					$hazirlayan_rütbe = $row->RUTBESI;
					$hazirlayan_adsoyad = $row->ADISOYADI;
					
				$sql = $db->prepare("SELECT * FROM PERSONEL_TBL where ADISOYADI = :ADISOYADI");
				$sql->execute(array("ADISOYADI" => $kontrol_eden));	
					$row = $sql->fetch(PDO::FETCH_OBJ);
					$kontrol_eden_rütbe = $row->RUTBESI;
					$kontrol_eden_adsoyad = $row->ADISOYADI;
					
						$protokol_onay = array(
											"HAZIRLAYAN" => array(
															"RUTBESI" => $hazirlayan_rütbe, 
															"ADISOYADI" => $hazirlayan_adsoyad
															),
											"KONTROL_EDEN" => array(
															"RUTBESI" => $kontrol_eden_rütbe, 
															"ADISOYADI" => $kontrol_eden_adsoyad
															),
											"FIRMA" => array(
															"ADI" => $firma_adi
															),
											"ARAMA_TIPI" => $arama_tipi
										);
						$_SESSION["PROTOKOL_ONAY"] = $protokol_onay;
						
						if($_SESSION["PROTOKOL_ONAY"]["ARAMA_TIPI"] == "HASSAS"){
							if(strlen(trim($firma_adi))<1) {
								echo 3;// hassas ise firma adını boş geçme.
								exit;								
							}
						}
					
			}else {
				echo 0;//Hazırlayan ve kontrol eden alanları boş bırakılamaz.
				exit;
			}
		}
		$table = $_POST["table"];
		if($table == "NIRKOOR_TBL"){
			$noktaNoNirengi = $_POST["noktaNoNirengi"];
			$noktaAdiNirengi = strtoupper($_POST["noktaAdiNirengi"]);
			/*  $sql = "SELECT * FROM $table 
								WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 
								and NOK_NO LIKE '$noktaNoNirengi%' AND NOK_ADI LIKE '%$noktaAdiNirengi%'
								and $postedEnlemQuery and $postedBoylamQuery";
			echo $sql; exit;  */
			
			$sql = $db->prepare("SELECT * FROM $table 
								WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 
								and NOK_NO LIKE '$noktaNoNirengi%' AND NOK_ADI LIKE '%$noktaAdiNirengi%'
								and $postedEnlemQuery and $postedBoylamQuery");
			$sql->execute();	
				while ($row = $sql->fetch(PDO::FETCH_ASSOC))
					{
					$rows[] = $row;
					}
				$adet = sizeof($rows);
				print json_encode(["veriler" => $rows, "adet" => $adet]);
		}
		elseif($table == "NIVDEG_TBL"){
			$hatNoNivelman = strtoupper($_POST["hatNoNivelman"]);
			$noktaNoNivelman = strtoupper($_POST["noktaNoNivelman"]);
			$noktaAdiNivelman = strtoupper($_POST["noktaAdiNivelman"]);
			//$sql = "SELECT * FROM $table WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 and NIVHAT_NO LIKE '$hatNoNivelman%' AND NIVNOK_NO LIKE '%$noktaNoNivelman%'";
			//echo $sql; exit;
			$sql = $db->prepare("SELECT * FROM $table 
								WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 and 
								NIVHAT_NO LIKE '$hatNoNivelman%' AND NIVNOK_NO LIKE '%$noktaNoNivelman%' 
								AND NIVNOK_ADI LIKE '$noktaAdiNivelman%'
								and $postedEnlemQuery and $postedBoylamQuery");
			$sql->execute();	
				while ($row = $sql->fetch(PDO::FETCH_ASSOC))
					{
					$rows[] = $row;
					}
				$adet = sizeof($rows);
				print json_encode(["veriler" => $rows, "adet" => $adet]);
		}
		elseif($table == "GPSKOOR_TBL"){
			$noktaNoGps = strtoupper($_POST["noktaNoGps"]);
			$kisaAdiGps = strtoupper($_POST["kisaAdiGps"]);
			//$sql = "SELECT * FROM $table WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 and GPSNOK_NO LIKE '$noktaNoGps%' AND GPSKISA_ADI LIKE '%$kisaAdiGps%'";
			//echo $sql; exit;
			$sql = $db->prepare("SELECT * FROM $table WHERE $pafta250 and $pafta100 and $pafta50 and $pafta25 
			and GPSNOK_NO LIKE '%$noktaNoGps%' AND GPSKISA_ADI LIKE '%$kisaAdiGps%'
			and $postedEnlemQuery and $postedBoylamQuery");
			$sql->execute();	
				while ($row = $sql->fetch(PDO::FETCH_ASSOC))
					{
					$rows[] = $row;
					}
				print json_encode($rows);
		}
		else{
			$msg = "Tablo Adı Belirtilmedi!";
			exit();
		}
}else {
	$msg = "Tablo Adı Belirtilmedi!";
	exit();
}

?>