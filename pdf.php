<?php
include "../../security/db.php";
header('Content-Type: text/html; charset=utf-8');
function replacepoint($deger){
	return ceil(str_replace(',', '.', $deger));
}
function replacewhitespaces($url){
	return str_replace(' ', '', $url);
}


function hassasMi($value){
	$arama_tipi = $_SESSION["PROTOKOL_ONAY"]["ARAMA_TIPI"];
	//return $arama_tipi;
	if($arama_tipi == "HASSAS"){
		if($value[0]==","){
			return "0".$value;
		}elseif($value[0]=="-" && $value[1]==","){
			return "-0".str_replace("-","",$value);
		}else {
			return $value;
		}
	}else return round(str_replace(",",".",$value));
}
require_once('tcpdf_include.php');

// create new PDF document
//$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information

class MYPDF extends TCPDF {
	public function Header() {
		$headerData = $this->getHeaderData();
		$this->SetFont('dejavusans', 'B', 10);
		$this->writeHTML($headerData['string']);
	}
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);




$pdf->SetAuthor('Jeodezi Dairesi Başkanlığı');
$pdf->SetTitle('Harita Genel Komutanlığı');
$pdf->SetSubject('Protokol');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));


// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}
// set font
$pdf->SetFont('dejavusans', '', 10);
// ---------------------------------------------------------



if(isset($_SESSION['PROTOCOL']))
{
	if($_SESSION["KULLANICI_YETKILERI"]){
		if($_SESSION["KULLANICI_YETKILERI"]["PROTOKOL_GORUNTULEME"] != "true"){
			echo "<div style='background-color:#f4f4f4; color:red; border:1px solid#000; padding:25px; text-align:center;'>
					<b>PROTOKOL VE DEĞER CETVELİ GÖRÜNTÜLEME YETKİNİZ BULUNMAMAKTADIR!</b>
				</div>";
			exit();
		}
	}
	
	$type = $_SESSION["PROTOCOL"]["TYPE"];
	
	$firma_text = !empty($_SESSION["PROTOKOL_ONAY"]["FIRMA"]["ADI"]) 
						? date('d/m/Y').' tarihli talep yazısı gereği <u><strong>'.$_SESSION["PROTOKOL_ONAY"]["FIRMA"]["ADI"].'</strong></u> için hazırlanmıştır.'
						: "";
	$footer_html = '<div style="font-size:11px !important;"><table>
						<tr>
							<td style="width:25%;"><b>TARİH</b> : '.date('d-m-Y H:i').'</td>
							<td style="width:75%; text-align:right;">'.$firma_text .'</td>
						</tr>
						</table>
						<div style="margin-top:0px;"></div>
						<table>
						<tr>
							<td style="width:75%;">
								<div><b style="text-decoration:underline;">HAZIRLAYAN</b><br/>
									<div style="margin-top:25px;"></div>
									'.$_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["ADISOYADI"].'<br/>'.$_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["RUTBESI"].'
								</div>
							</td>
							<td style="width:25%;">
								<div><b style="text-decoration:underline;">KONTROL EDEN</b><br/>
									<div style="text-align:right;"></div>
									'.$_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["ADISOYADI"].'<br/>'.$_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["RUTBESI"].'
								</div>
							</td>
						</tr>
						</table>
						<table>
						<tr>
							<td style="width:25%;"><b style="text-decoration:underline;"><br/>TASNİF DIŞI</b></td>
							<td style="width:75%; text-align:right;"><b><br />Bu değerler Harita Genel Komutanlığı\'nca hazırlanmıştır.Her Hakkı Saklıdır.</b><br/>
							</td>
						</tr>
					</table></div>';
	switch($type){
			case "NIVELMAN":
				$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
								FROM NIVDEG_TBL A 
								LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25
								where NIVHAT_NO = :NIVHAT_NO AND NIVNOK_NO = :NIVNOK_NO");
				$sql->execute(array("NIVHAT_NO" => $_SESSION["PROTOCOL"]["NIVHAT_NO"], "NIVNOK_NO" => $_SESSION["PROTOCOL"]["NIVNOK_NO"]));	
					$row = $sql->fetch(PDO::FETCH_OBJ);
							switch($row->NIVNOK_TIPI){
								case "D": $NIVNOK_TIPI = "DUVAR NOK.";
								break;
								case "Y": $NIVNOK_TIPI = "YER NOK.";
							}
							$NIVHAT_NO = $row->NIVHAT_NO;
							$HEL_ORT_YUK = $row->HEL_ORT_YUK;
							$HEL_ORT_YUK_KOH = $row->HEL_ORT_YUK_KOH;
							$MOL_NOR_YUK = $row->MOL_NOR_YUK;
							$BOLGESI = $row->BOLGESI;
							$NIVNOK_NO = $row->NIVNOK_NO;
							$OLCUM_YILI = $row->OLCUM_YILI;
							$NIVNOK_ADI = $row->NIVNOK_ADI;
							$NIVNOK_TARIFI = $row->NIVNOK_TARIFI;
							$NOKDER = $row->NOKDER;
							$ENLEM = $row->ENLDER."°".$row->ENLDAK."'".replacepoint($row->ENL_SAN)."\"";
							$BOYLAM = $row->BOYDER."°".$row->BOYDAK."'".replacepoint($row->BOY_SAN)."\"";
							$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
							$YUKSEKLIK = $row->NOR_ORT_YUK;
							$FILENAME = "$NIVHAT_NO";
							
							
							switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
							
							$imgurl = "../../data/niv/$NIVHAT_NO/$NIVHAT_NO$NIVNOK_NO.jpg";
							$imgurl = replacewhitespaces($imgurl);
							$page_1 = '<div style="text-align:center;" ><h2>NİVELMAN NOKTA PROTOKOLÜ</h2></div>
											<table>
												<tr><td><b>HAT NO</b></td><td>'.$NIVHAT_NO.'</td><td><b>BÖLGESİ</b></td><td>'.$BOLGESI.'</td></tr>
												<tr><td><b>NOKTA NO</b></td><td>'.$NIVNOK_NO.'</td><td><b>ÖLÇÜ YILI</b></td><td>'.$OLCUM_YILI.'</td></tr>
												<tr><td><b>NOKTA ADI</b></td><td>'.$NIVNOK_ADI.'</td><td><b>NOKTA TANIMI</b></td><td>'.$NIVNOK_TARIFI.'</td></tr>
												<tr><td><b>DERECESİ</b></td><td>'.$NOKDER.'</td><td><b>ENLEM</b></td><td>'.$ENLEM.'</td></tr>
												<tr><td><b>TESİS TÜRÜ</b></td><td>'.$NIVNOK_TIPI.'</td><td><b>BOYLAM</b></td><td>'.$BOYLAM.'</td></tr>
												<tr><td><b>PAFTASI</b></td><td>'.$PAFTASI.'</td><td><b>YÜKSEKLİK</b></td><td>'.hassasMi($YUKSEKLIK).' m.</td></tr>
											</table>
											<div style="height:25px;"></div>
											<table>
												<tr><td style="text-align:center;"><img src="'.$imgurl.'" style="width:500px;"/></td></tr>
											</table><div style="height:25px;"></div>'.$footer_html;
							$page_2 = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>NİVELMAN DEĞER CETVELİ</h2></div>
									  <table border="1" cellpadding="5" width="1000px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td><b>Hat No<br/>Nok No</b></td>
											<td><b>Nokta Adı</b></td>
											<td style="width:50px;"><b>Der.</b></td>
											<td><b>Pafta Adı</b></td>
											<td><b>Enlemi</b></td>
											<td><b>Boylamı</b></td>
											<td><b>Hel.Ort(m)</b></td>
											<td><b><sup>σ</sup>Hel.(m)</b></td>
											<td style="width:95px;"><b>Mol.Nor.(m)</b></td>
											<td style="width:95px;"><b>Nor.Ort.(m)</b></td>
											<td style="width:60px;"><b>Ölç.Yıl.</b></td>
											<td><b>Tesis Türü</b></td>
										</tr>
										<tr style="'.$style.'">
											<td><strong>1</strong></td>
											<td>'.$NIVHAT_NO.'<br/>'.$NIVNOK_NO.'</td>
											<td>'.$NIVNOK_ADI.'</td>
											<td>'.$NOKDER.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.$ENLEM.'</td>
											<td>'.$BOYLAM.'</td>
											<td>'.hassasMi($HEL_ORT_YUK).'</td>
											<td>'.hassasMi($HEL_ORT_YUK_KOH).'</td>
											<td>'.hassasMi($MOL_NOR_YUK).'</td>
											<td>'.hassasMi($YUKSEKLIK).'</td>
											<td>'.$OLCUM_YILI.'</td>
											<td>'.$NIVNOK_TIPI.'</td>
										</tr>
									  </table><div style="height:25px;"></div>'.$footer_html;
				$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner_old.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage();
				$pdf->writeHTML($page_1, true, false, true, false, '');
				$pdf->lastPage();
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage('L', 'A4');
				$pdf->writeHTML($page_2, true, false, true, false, '');
				$pdf->lastPage();
			break;
											
		case "GPS":
			$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
								FROM GPSKOOR_TBL A 
								LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25  
								where GPSNOK_NO = :GPSNOK_NO");
			$sql->execute(array("GPSNOK_NO" => $_SESSION["PROTOCOL"]["GPSNOK_NO"]));	
				$row = $sql->fetch(PDO::FETCH_OBJ);
					$GPSNOK_NO = $row->GPSNOK_NO;
					$GPSNOK_ADI = $row->GPSNOK_ADI;
					$GPSKISA_ADI = $row->GPSKISA_ADI;
					$JEODEZIK_NO = $row->JEODEZIK_NO;
					$ENLEM = $row->ENLDER."° ".$row->ENLDAK."' ".replacepoint($row->ENL_SAN)."\"";
					$BOYLAM = $row->BOYDER."° ".$row->BOYDAK."' ".replacepoint($row->BOY_SAN)."\"";
					$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
					$TESIS_TURU = $row->TESIS_TURU; 
					$CINSI = $row->CINSI;
					$NITELIGI = $row->NITELIGI;
					$TESISI_YAPAN = $row->TESISI_YAPAN;
					if(strlen($TESISI_YAPAN)==0) $TESISI_YAPAN = '---';
					$SON_KESIF_TRH = $row->SON_KESIF_TRH;
					$TESIS_TARIHI = $row->TESIS_TARIHI;
					if(strlen($TESIS_TARIHI)==0) $TESIS_TARIHI = '---';
					$YUKSEKLIK = $row->ORT_YUK;
					$ILI = $row->ILI;
					$ILCESI = $row->ILCESI;
					$KOYU = $row->KOYU;
					$X = $row->X;
					$Y = $row->Y;
					$Z = $row->Z;
					$XSIG = $row->XSIG;
					$YSIG = $row->YSIG;
					$ZSIG = $row->ZSIG;
					$VX = $row->VX;
					$VY = $row->VY;
					$VZ = $row->VZ;
					$VXSIG = $row->VXSIG;
					$VYSIG = $row->VYSIG;
					$VZSIG = $row->VZSIG;
					$ELP_YUK = $row->ELP_YUK;
					$ENL_SIG = $row->ENL_SIG;
					$BOY_SIG = $row->BOY_SIG;
					$YUK_SIG = $row->YUK_SIG;
					switch($row->TAHRIPMI){
						case "T": $TAHRIPMI = "TAHRİP";
							break;
						case "F": $TAHRIPMI = "SAĞLAM";
							break;
						case "0": $TAHRIPMI = "---";
							break;
					}
					
					switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
					
					$EK_BILGI_ILK = "<b>REFERANS EPOĞU :</b>".$row->BAS_EPOGU ." | <b>DATUM :</b> ".$row->DATUM ." | <b>ELİPSOİD :</b> ".$row->ELIPSOID;
					
					$sql_tektonik = $db->prepare("SELECT * FROM GPSTEKTONIK_TBL where GPSNOK_NO = :GPSNOK_NO");
					$sql_tektonik->execute(array("GPSNOK_NO" => $_SESSION["PROTOCOL"]["GPSNOK_NO"]));	
						$row_tektonik = $sql_tektonik->fetch(PDO::FETCH_OBJ);
					    $GPSNOK_NO_TEKTO = $row->GPSNOK_NO;
						if($row_tektonik){
							$TEK_ETKI_BAS = $row_tektonik->TEK_ETKI_BAS;
							$BAS_EPOGU = $row_tektonik->BAS_EPOGU;
							$TEK_DX = $row_tektonik->TEK_DX;
							$TEK_DY = $row_tektonik->TEK_DY;
							$TEK_DZ = $row_tektonik->TEK_DZ;
							$EK_BILGI = "<b>REFERANS EPOĞU :</b>".$BAS_EPOGU." | <b>DATUM :</b> ".$row_tektonik->DATUM ." | <b>ELİPSOİD :</b> ".$row_tektonik->ELIPSOID . " | 
										 <b>Depremler ve Tarihleri :</b> ".$row_tektonik->DEPREM1 ." / ".$row_tektonik->DEPREM2 ." / ".$row_tektonik->DEPREM3;
								$page_4 = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>DEPREM SONRASI GPS KOORDİNATLARI VE HIZLARI</h2></div>
									  <table border="1" cellpadding="5" width="1100px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td><b>Nokta No</b></td>
											<td><b>Kısa Adı<br />Jeo. No</b></td>
											<td><b>Pafta Adı</b></td>
											<td style="width:90px"><b>X(m)<br/>Y(m) <br />Z(m)</b></td>
											<td style="width:60px"><b>σ<sub>x</sub>(m)<br />σ<sub>y</sub>(m)<br />σ<sub>z</sub>(m)</b></td>
											<td style="width:60px">Tektonik Etki<br/>Periyodu</td>
											<td style="width:90px">Ko-Sismik Yer<br/>Değitirmeler<br/>(dx dy dz)(m)</td>
											<td style="width:60px"><b>V<sub>x</sub>(m/y)<br />V<sub>y</sub>(m/y)<br />V<sub>z</sub>(m/y)</b></td>
											<td style="width:60px"><b>σ<sub>vx</sub>(m/y)<br />σ<sub>vy</sub>(m/y)<br />σ<sub>vz</sub>(m/y)</b></td>
											<td><b>Enl. (° \' ")<br />Boyl. (° \' ") <br />Elip. Y.(m)</b></td>
											<td style="width:60px"><b>σ<sub>φ</sub>(m)<br />σ<sub>λ</sub>(m)<br />σ<sub>h</sub>(m)</b></td>
											<td style="width:110px"><b>Tesis Türü <br /> Nokta Niteliği</b></td>
										</tr>
										<tr style="'.$style.'">
											<td><strong>1</strong></td>
											<td>'.$GPSNOK_NO.'</td>
											<td>'.$GPSKISA_ADI.'<br/>'.$JEODEZIK_NO.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.hassasMi($X).'<br/>'.hassasMi($Y).'<br/>'.hassasMi($Z).'</td>
											<td>'.hassasMi($XSIG).'<br/>'.hassasMi($YSIG).'<br/>'.hassasMi($ZSIG).'</td>
											<td>'.$TEK_ETKI_BAS.'<br/>'.$BAS_EPOGU.'</td>
											<td>'.hassasMi($TEK_DX).'<br/>'.hassasMi($TEK_DY).'<br/>'.hassasMi($TEK_DZ).'</td>
											<td>'.hassasMi($VX).'<br/>'.hassasMi($VY).'<br/>'.hassasMi($VZ).'</td>
											<td>'.hassasMi($VXSIG).'<br/>'.hassasMi($VYSIG).'<br/>'.hassasMi($VZSIG).'</td>
											<td>'.$ENLEM.'<br/>'.$BOYLAM.'<br/>'.hassasMi($ELP_YUK).'</td>
											<td>'.hassasMi($ENL_SIG).'<br/>'.hassasMi($BOY_SIG).'<br/>'.hassasMi($YUK_SIG).'</td>
											<td>'.$TESIS_TURU.'<br/>'.$NITELIGI.'</td>
										</tr>
									  </table><div style="height:25px;"></div>'.$EK_BILGI."<br/>".$footer_html;
						}

					$FILENAME = "$GPSNOK_NO";
					$tarif_url = replacewhitespaces("../../data/GPS/tarif/".$GPSNOK_NO.".txt");
					$kroki_url = replacewhitespaces("../../data/GPS/kroki/".$GPSNOK_NO."k.jpg");
					$tarif = file_get_contents($tarif_url);
							$page_1 = '<div style="text-align:center;" ><h2>GPS NOKTA PROTOKOLÜ</h2></div>
											<table>
												<tr><td><b>NOKTA NO</b></td><td>'.$GPSNOK_NO.'</td><td><b>KISA ADI</b></td><td>'.$GPSKISA_ADI.'</td></tr>
												<tr><td><b>NOKTA ADI</b></td><td>'.$GPSNOK_ADI.'</td><td><b>ENLEM</b></td><td>'.$ENLEM.'</td></tr>
												<tr><td><b>JEODEZİK NO</b></td><td>'.$JEODEZIK_NO.'</td><td><b>BOYLAM</b></td><td>'.$BOYLAM.'</td></tr>
												<tr><td><b>PAFTASI</b></td><td>'.$PAFTASI.'</td><td><b>YÜKSEKLİK</b></td><td>'.$YUKSEKLIK.' m.</td></tr>
												<tr><td><b>TESİS TÜRÜ</b></td><td>'.$TESIS_TURU.'</td><td><b>CİNSİ</b></td><td>'.$CINSI.'</td></tr>
												<tr><td><b>NİTELİĞİ</b></td><td>'.$NITELIGI.'</td><td><b>TESİSİ YAPAN</b></td><td>'.$TESISI_YAPAN.'</td></tr>
												<tr><td><b>TESİS TARİHİ</b></td><td>'.$TESIS_TARIHI.'</td><td><b>SON KEŞİF TARİHİ</b></td><td>'.$SON_KESIF_TRH.'</td></tr>
												<tr><td><b>TAHRİP DURUMU</b></td><td>'.$TAHRIPMI.'</td><td></td><td></td></tr>
											</table>
											<div style="height:5px;"></div>
											<div style="text-align:center;" ><h2>YER TARİFİ - ULAŞIM ŞARTLARI</h2></div>
											<table>
												<tr><td><b>EN YAKIN İL:</b> '.$ILI.'</td><td><b>İLÇESİ:</b> '.$ILCESI.'</td><td><b>KÖYÜ:</b> '.$KOYU.'</td></tr>
											</table>
											<h4>AYRINTILI AÇIKLAMA</h4>'.$tarif.'
											<div style="height:25px;"></div>'.$footer_html;
							$page_2 = '<br /><br /><br /><br />
									  <table>
										<tr><td><b>NOKTA NO. : </b>'.$GPSNOK_NO.'</td><td><b>4 KAR. KISA ADI : </b>'.$GPSKISA_ADI.'</td><td><B>PAFTASI : </B>'.$PAFTASI.'</td></tr>
									  </table>
									  <div style="height:25px;"></div>
									  <table>
										<tr><td style="text-align:center;"><img src="'.$kroki_url.'" style="width:580px;"/></td></tr>
									  </table></div>';
							$page_3 = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>GPS DEĞER CETVELİ</h2></div>
									  <table border="1" cellpadding="5" width="1100px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td><b>Nokta No</b></td>
											<td><b>Kısa Adı<br />Jeo. No</b></td>
											<td><b>Pafta Adı</b></td>
											<td style="width:90px"><b>X(m)<br/>Y(m) <br />Z(m)</b></td>
											<td style="width:60px"><b>σ<sub>x</sub>(m)<br />σ<sub>y</sub>(m)<br />σ<sub>z</sub>(m)</b></td>
											<td><b>V<sub>x</sub>(m/y)<br />V<sub>y</sub>(m/y)<br />V<sub>z</sub>(m/y)</b></td>
											<td ><b>σ<sub>vx</sub>(m/y)<br />σ<sub>vy</sub>(m/y)<br />σ<sub>vz</sub>(m/y)</b></td>
											<td><b>Enl. (° \' ")<br />Boyl. (° \' ") <br />Elip. Y.(m)</b></td>
											<td style="width:60px"><b>σ<sub>φ</sub>(m)<br />σ<sub>λ</sub>(m)<br />σ<sub>h</sub>(m)</b></td>
											<td style="width:110px"><b>Tesis Türü <br /> Nokta Niteliği</b></td>
										</tr>
										<tr style="'.$style.'">
											<td><strong>1</strong></td>
											<td>'.$GPSNOK_NO.'</td>
											<td>'.$GPSKISA_ADI.'<br/>'.$JEODEZIK_NO.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.hassasMi($X).'<br/>'.hassasMi($Y).'<br/>'.hassasMi($Z).'</td>
											<td>'.hassasMi($XSIG).'<br/>'.hassasMi($YSIG).'<br/>'.hassasMi($ZSIG).'</td>
											<td>'.hassasMi($VX).'<br/>'.hassasMi($VY).'<br/>'.hassasMi($VZ).'</td>
											<td>'.hassasMi($VXSIG).'<br/>'.hassasMi($VYSIG).'<br/>'.hassasMi($VZSIG).'</td>
											<td>'.$ENLEM.'<br/>'.$BOYLAM.'<br/>'.hassasMi($ELP_YUK).'</td>
											<td>'.hassasMi($ENL_SIG).'<br/>'.hassasMi($BOY_SIG).'<br/>'.hassasMi($YUK_SIG).'</td>
											<td>'.$TESIS_TURU.'<br/>'.$NITELIGI.'</td>
										</tr>
									  </table><div style="height:25px;"></div>'.$EK_BILGI_ILK."<BR/>".$footer_html;
									  
				$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner_old.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage();
				$pdf->writeHTML($page_1, true, false, true, false, '');
				$pdf->lastPage();
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage();
				$pdf->writeHTML($page_2, true, false, true, false, '');
				$pdf->lastPage();
				
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage('L', 'A4');
				$pdf->writeHTML($page_3, true, false, true, false, '');
				$pdf->lastPage(); 
				if($row_tektonik){
					$pdf->AddPage('L', 'A4');
					$pdf->writeHTML($page_4, true, false, true, false, '');
					$pdf->lastPage();
				}

			break;
		
		case "NIRENGI":
			$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
								FROM NIRKOOR_TBL A 
								LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25
								where NOK_NO = :NOK_NO AND PAF100 = :PAF100");
			$sql->execute(array("NOK_NO" => $_SESSION["PROTOCOL"]["NOK_NO"], "PAF100" => $_SESSION["PROTOCOL"]["PAF100"]));	
				$row = $sql->fetch(PDO::FETCH_OBJ);
					$NOK_NO = $row->NOK_NO;
					$NOK_ADI = $row->NOK_ADI;
					$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
					$TESIS_TURU = $row->TESIS_TURU;
					$TESIS_YILI = $row->TESIS_YILI;
					$KOT_TURU = $row->KOT_TURU;
					$KOT = $row->KOT;
					$YU6 = $row->YU6;
					$SA6 = $row->SA6;
					$DOM6 = $row->DOM6;
					$NOKDER = $row->NOKDER;
					$ENLEM = $row->ENLDER."° ".$row->ENLDAK."' ".replacepoint($row->ENLSAN)."\"";
					$BOYLAM = $row->BOYDER."° ".$row->BOYDAK."' ".replacepoint($row->BOYSAN)."\"";
					$YUKSEKLIK = $row->KOT;
					$FILENAME = "$NOK_NO";
					
					switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
					
					$tarif_url = replacewhitespaces("../../data/nir/tarif/".$row->PAF100.$NOK_NO."t.jpg");
					$kroki_url = replacewhitespaces("../../data/nir/kroki/".$row->PAF100.$NOK_NO."k.jpg");
					if(file_exists($tarif_url)){$tarif_url = $tarif_url;}
					else {$tarif_url = "../../protokol/examples/images/image_not_found.jpg";}
					if(file_exists($kroki_url)){$kroki_url = $kroki_url;}
					else {$kroki_url = "../../protokol/examples/images/kroki_not_found.jpg";}
							$page_1 = '<div style="text-align:center;" ><h2>NİRENGİ NOKTA PROTOKOLÜ</h2></div>
											<table>
												<tr><td><b>NOKTA NUMARASI</b></td><td>'.$NOK_NO.'</td><td><b>ENLEM</b></td><td>'.$ENLEM.'</td></tr>
												<tr><td><b>NOKTA ADI</b></td><td>'.$NOK_ADI.'</td><td><b>BOYLAM</b></td><td>'.$BOYLAM.'</td></tr>
												<tr><td><b>PAFTASI</b></td><td>'.$PAFTASI.'</td><td><b>YÜKSEKLİK</b></td><td>'.$YUKSEKLIK.' m.</td></tr>
												<tr><td><b>TESİS TÜRÜ</b></td><td>'.$TESIS_TURU.'</td><td><b>TESİS YILI</b></td><td>'.$TESIS_YILI.'</td></tr>
											</table>
											<div style="height:25px;"></div>
											<div style="text-align:center;" ><h2>YER TARİFİ - ULAŞIM ŞARTLARI - NOKTA KROKİSİ</h2></div>
											<table>
												<tr><td style="text-align:center;"><img src="'.$tarif_url.'" style="width:400px;"/></td></tr>
											</table><div style="height:25px;"></div>'.$footer_html;
							$page_2 = '<br /><br /><br /><br /><br />
									  <table>
										<tr><td><b>NOKTA NO. : </b>'.$NOK_NO.'</td><td><B>PAFTASI : </B>'.$PAFTASI.'</td></tr>
									  </table>
									  <div style="height:25px;"></div>
									  <table>
										<tr><td style="text-align:center;"><img src="'.$kroki_url.'" style="width:580px;"/></td></tr>
									  </table></div>';
							$page_3 = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>NİRENGİ DEĞER CETVELİ<BR />(ED-50 DATUMU)</h2></div>
									  <table border="1" cellpadding="5" width="1100px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td style="width:80px"><b>Nokta</b></td>
											<td><b>Nokta Adı</b></td>
											<td><b>Pafta Adı</b></td>
											<td style="width:50px"><b>DOM°</b></td>
											<td style="width:50px"><b>D.G°</b></td>
											<td style="width:80px"><b>Sağa Değ.(m)</b></td>
											<td style="width:80px"><b>Yukarı Değ.(m)</b></td>
											<td style="width:80px"><b>Kot(m)</b></td>
											<td style="width:50px"><b>Der.</b></td>
											<td style="width:50px"><b>Ts.Y.</b></td>
											<td style="width:80px"><b>Tesis Türü</b></td>
											<td style="width:120px"><b>Kot Türü</b></td>
										</tr>
										<tr style="'.$style.'">
											<td><strong>1</strong></td>
											<td>'.$NOK_NO.'</td>
											<td>'.$NOK_ADI.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.$DOM6.'</td>
											<td>D.G</td>
											<td>'.hassasMi($SA6).'</td>
											<td>'.hassasMi($YU6).'</td>
											<td>'.hassasMi($KOT).'</td>
											<td>'.$NOKDER.'</td>
											<td>'.$TESIS_YILI.'</td>
											<td>'.$TESIS_TURU.'</td>
											<td>'.$KOT_TURU.'</td>
										</tr>
									  </table><div style="height:25px;"></div>'.$footer_html;
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, 25, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner_old.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage();
				$pdf->writeHTML($page_1, true, false, true, false, '');
				$pdf->lastPage();
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage();
				$pdf->writeHTML($page_2, true, false, true, false, '');
				$pdf->lastPage();
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
				
				$pdf->AddPage('L', 'A4');
				$pdf->writeHTML($page_3, true, false, true, false, '');
				$pdf->lastPage();
				
			break;
			
		case "MULTI_GPS" :	
			function placeholders($text, $count=0, $separator=","){
				$result = array();
				if($count > 0){
					for($x=0; $x<$count; $x++){
						$result[] = $text;
					}
				}
				return implode($separator, $result);
			}
			$question_marks[] = '('  . placeholders('?', sizeof($_SESSION["PROTOCOL"]["GPSNOK_NO_ARRAY"])) . ')';
			$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
										FROM GPSKOOR_TBL A 
										LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25 
										WHERE TRIM(A.GPSNOK_NO) IN ".implode(',', $question_marks));
			$sql->execute($_SESSION["PROTOCOL"]["GPSNOK_NO_ARRAY"]);
			$page1_header = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>GPS DEĞER CETVELİ</h2></div>
							  <table border="1" cellpadding="5" width="1100px" style="font-size:10px !important;">
								<tr>
									<td style="width:35px;"><b>SN</b></td>
									<td><b>Nokta No</b></td>
									<td><b>Kısa Adı<br />Jeo. No</b></td>
									<td><b>Pafta Adı</b></td>
									<td style="width:90px"><b>X(m)<br/>Y(m) <br />Z(m)</b></td>
									<td style="width:60px"><b>σ<sub>x</sub>(m)<br />σ<sub>y</sub>(m)<br />σ<sub>z</sub>(m)</b></td>
									<td><b>V<sub>x</sub>(m/y)<br />V<sub>y</sub>(m/y)<br />V<sub>z</sub>(m/y)</b></td>
									<td ><b>σ<sub>vx</sub>(m/y)<br />σ<sub>vy</sub>(m/y)<br />σ<sub>vz</sub>(m/y)</b></td>
									<td><b>Enl. (° \' ")<br />Boyl. (° \' ") <br />Elip. Y.(m)</b></td>
									<td style="width:60px"><b>σ<sub>φ</sub>(m)<br />σ<sub>λ</sub>(m)<br />σ<sub>h</sub>(m)</b></td>
									<td style="width:110px"><b>Tesis Türü <br /> Nokta Niteliği</b></td>
								</tr>';
				$page1_body = "";
				$sira_no = 1;
				while ($row = $sql->fetch(PDO::FETCH_OBJ))
				{
					$GPSNOK_NO = $row->GPSNOK_NO;
					$GPSNOK_ADI = $row->GPSNOK_ADI;
					$GPSKISA_ADI = $row->GPSKISA_ADI;
					$JEODEZIK_NO = $row->JEODEZIK_NO;
					$ENLEM = $row->ENLDER."° ".$row->ENLDAK."' ".replacepoint($row->ENL_SAN)."\"";
					$BOYLAM = $row->BOYDER."° ".$row->BOYDAK."' ".replacepoint($row->BOY_SAN)."\"";
					$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
					$TESIS_TURU = $row->TESIS_TURU; 
					$CINSI = $row->CINSI;
					$NITELIGI = $row->NITELIGI;
					$TESISI_YAPAN = $row->TESISI_YAPAN;
					if(strlen($TESISI_YAPAN)==0) $TESISI_YAPAN = '---';
					$SON_KESIF_TRH = $row->SON_KESIF_TRH;
					$TESIS_TARIHI = $row->TESIS_TARIHI;
					if(strlen($TESIS_TARIHI)==0) $TESIS_TARIHI = '---';
					$YUKSEKLIK = $row->ORT_YUK;
					$ILI = $row->ILI;
					$ILCESI = $row->ILCESI;
					$KOYU = $row->KOYU;
					$X = $row->X;
					$Y = $row->Y;
					$Z = $row->Z;
					$XSIG = $row->XSIG;
					$YSIG = $row->YSIG;
					$ZSIG = $row->ZSIG;
					$VX = $row->VX;
					$VY = $row->VY;
					$VZ = $row->VZ;
					$VXSIG = $row->VXSIG;
					$VYSIG = $row->VYSIG;
					$VZSIG = $row->VZSIG;
					$ELP_YUK = $row->ELP_YUK;
					$ENL_SIG = $row->ENL_SIG;
					$BOY_SIG = $row->BOY_SIG;
					$YUK_SIG = $row->YUK_SIG;
					$FILENAME = "$GPSNOK_NO";
					
					switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
					
						
					if($sira_no % 7 == 0){
						$pagebreak = $footer_html.'</table><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'.$page1_header;
					}else $pagebreak = ""; 
						$page1_body = $page1_body.'<tr nobr="true" style="'.$style.'">
													<td><strong>'.$sira_no.'</strong></td>
													<td>'.$GPSNOK_NO.'</td>
													<td>'.$GPSKISA_ADI.'<br/>'.$JEODEZIK_NO.'</td>
													<td>'.$PAFTASI.'</td>
													<td>'.hassasMi($X).'<br/>'.hassasMi($Y).'<br/>'.hassasMi($Z).'</td>
													<td>'.hassasMi($XSIG).'<br/>'.hassasMi($YSIG).'<br/>'.hassasMi($ZSIG).'</td>
													<td>'.hassasMi($VX).'<br/>'.hassasMi($VY).'<br/>'.hassasMi($VZ).'</td>
													<td>'.hassasMi($VXSIG).'<br/>'.hassasMi($VYSIG).'<br/>'.hassasMi($VZSIG).'</td>
													<td>'.$ENLEM.'<br/>'.$BOYLAM.'<br/>'.hassasMi($ELP_YUK).'</td>
													<td>'.hassasMi($ENL_SIG).'<br/>'.hassasMi($BOY_SIG).'<br/>'.hassasMi($YUK_SIG).'</td>
													<td>'.$TESIS_TURU.'<br/>'.$NITELIGI.'</td>
												</tr>'.$pagebreak;
											
					$sira_no++;
				}
				$page1_footer = '</table><div style="height:25px;"></div>'.$footer_html;
				$page_1 = $page1_header.$page1_body.$page1_footer;
				
				$pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
				$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
			
				
				$pdf->AddPage('L', 'A4');
				$pdf->writeHTML($page_1, true, false, true, false, '');
				$pdf->lastPage();	
			break;
			
		case "MULTI_NIVELMAN":
				  function placeholders($text, $count=0, $separator=","){
						$result = array();
						if($count > 0){
							for($x=0; $x<$count; $x++){
								$result[] = $text;
							}
						}
						return implode($separator, $result);
					}
					$question_marks[] = '('  . placeholders('?', sizeof($_SESSION["PROTOCOL"]["NIVHAT_NO_NIVNOK_NO_ARRAY"])) . ')';
					$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
										FROM NIVDEG_TBL A 
										LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25 
										WHERE CONCAT(TRIM(A.NIVHAT_NO),CONCAT('#',TRIM(A.NIVNOK_NO))) IN ".implode(',', $question_marks));
					$sql->execute($_SESSION["PROTOCOL"]["NIVHAT_NO_NIVNOK_NO_ARRAY"]);
					
					/* print_r($_SESSION["PROTOCOL"]["NIVHAT_NO_NIVNOK_NO_ARRAY"]);
					exit; */
					
					$page1_header = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>NİVELMAN DEĞER CETVELİ</h2></div>
									  <table border="1" cellpadding="5" width="1000px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td><b>Hat No<br/>Nok No</b></td>
											<td><b>Nokta Adı</b></td>
											<td style="width:50px;"><b>Der.</b></td>
											<td><b>Pafta Adı</b></td>
											<td><b>Enlemi</b></td>
											<td><b>Boylamı</b></td>
											<td><b>Hel.Ort(m)</b></td>
											<td><b><sup>σ</sup>Hel.(m)</b></td>
											<td style="width:95px;"><b>Mol.Nor.(m)</b></td>
											<td style="width:95px;"><b>Nor.Ort.(m)</b></td>
											<td style="width:60px;"><b>Ölç.Yıl.</b></td>
											<td><b>Tesis Türü</b></td>
										</tr>';
						$page1_body = "";
						$sira_no = 1;
						while ($row = $sql->fetch(PDO::FETCH_OBJ))
						{
							switch($row->NIVNOK_TIPI){
								case "D": $NIVNOK_TIPI = "DUVAR NOKTASI";
								break;
								case "Y": $NIVNOK_TIPI = "YER NOKTASI";
							}
							
							switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
							$NIVHAT_NO = $row->NIVHAT_NO;
							$HEL_ORT_YUK = $row->HEL_ORT_YUK;
							$HEL_ORT_YUK_KOH = $row->HEL_ORT_YUK_KOH;
							$MOL_NOR_YUK = $row->MOL_NOR_YUK;
							$BOLGESI = $row->BOLGESI;
							$NIVNOK_NO = $row->NIVNOK_NO;
							$OLCUM_YILI = $row->OLCUM_YILI;
							$NIVNOK_ADI = $row->NIVNOK_ADI;
							$NIVNOK_TARIFI = $row->NIVNOK_TARIFI;
							$NOKDER = $row->NOKDER;
							$ENLEM = $row->ENLDER."° ".$row->ENLDAK."' ".replacepoint($row->ENL_SAN)."\"";
							$BOYLAM = $row->BOYDER."° ".$row->BOYDAK."' ".replacepoint($row->BOY_SAN)."\"";
							$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
							$YUKSEKLIK = $row->NOR_ORT_YUK;
							$FILENAME = "$NIVHAT_NO";
								if($sira_no % 10 == 0){
									$pagebreak = $footer_html.'</table><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'.$page1_header;
								}else $pagebreak = ""; 
							$page1_body = $page1_body.'<tr nobr="true" style="'.$style.'">
											<td><strong>'.$sira_no.'</strong></td>
											<td>'.$NIVHAT_NO.'<br/>'.$NIVNOK_NO.'</td>
											<td>'.$NIVNOK_ADI.'</td>
											<td>'.$NOKDER.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.$ENLEM.'</td>
											<td>'.$BOYLAM.'</td>
											<td>'.hassasMi($HEL_ORT_YUK).'</td>
											<td>'.hassasMi($HEL_ORT_YUK_KOH).'</td>
											<td>'.hassasMi($MOL_NOR_YUK).'</td>
											<td>'.hassasMi($YUKSEKLIK).'</td>
											<td>'.$OLCUM_YILI.'</td>
											<td>'.$NIVNOK_TIPI.'</td>
										</tr>'.$pagebreak;
							$sira_no++;
						}
						$page1_footer = '</table><div style="height:25px;"></div>'.$footer_html;
						$page_1 = $page1_header.$page1_body.$page1_footer;
						
						$pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
						$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
						
						$pdf->AddPage('L', 'A4');
						$pdf->writeHTML($page_1, true, false, true, false, '');
						$pdf->lastPage();
				break;
				
			case "MULTI_NIRENGI":
					function placeholders($text, $count=0, $separator=","){
						$result = array();
						if($count > 0){
							for($x=0; $x<$count; $x++){
								$result[] = $text;
							}
						}
						return implode($separator, $result);
					}
					
					$question_marks[] = '('  . placeholders('?', sizeof($_SESSION["PROTOCOL"]["NOK_NO_PAF100_ARRAY"])) . ')';
					$sql = $db->prepare("SELECT A.*, DECODE(B.PAF250,NULL,'H','E') ASKERIMI 
										FROM NIRKOOR_TBL A 
										LEFT OUTER JOIN ASYASBLG_TBL B ON A.PAF250=B.PAF250 AND A.PAF100=B.PAF100 AND A.PAF50=B.PAF50 AND A.PAF25=B.PAF25 
										WHERE CONCAT(TRIM(A.NOK_NO),CONCAT('#',TRIM(A.PAF100))) IN ".implode(',', $question_marks));
					$sql->execute($_SESSION["PROTOCOL"]["NOK_NO_PAF100_ARRAY"]);	
					$page1_header = '<br /><br /><br /><br /><div style="text-align:center;" ><h2>NİRENGİ DEĞER CETVELİ<BR />(ED-50 DATUMU)</h2></div>
									  <table border="1" cellpadding="5" width="1100px" style="font-size:10px !important;">
										<tr>
											<td style="width:35px;"><b>SN</b></td>
											<td style="width:80px"><b>Nokta</b></td>
											<td><b>Nokta Adı</b></td>
											<td><b>Pafta Adı</b></td>
											<td style="width:50px"><b>DOM°</b></td>
											<td style="width:50px"><b>D.G°</b></td>
											<td style="width:80px"><b>Sağa Değ.(m)</b></td>
											<td style="width:80px"><b>Yukarı Değ.(m)</b></td>
											<td style="width:80px"><b>Kot(m)</b></td>
											<td style="width:50px"><b>Der.</b></td>
											<td style="width:50px"><b>Ts.Y.</b></td>
											<td style="width:80px"><b>Tesis Türü</b></td>
											<td style="width:120px"><b>Kot Türü</b></td>
										</tr>';
						$page1_body = "";
						$sira_no = 1;
						while ($row = $sql->fetch(PDO::FETCH_OBJ))
						{
							$NOK_NO = $row->NOK_NO;
							$NOK_ADI = $row->NOK_ADI;
							$PAFTASI = "$row->PAF250 $row->PAF100-$row->PAF50$row->PAF25";
							$TESIS_TURU = $row->TESIS_TURU;
							$TESIS_YILI = $row->TESIS_YILI;
							$KOT_TURU = $row->KOT_TURU;
							$KOT = $row->KOT;
							$YU6 = $row->YU6;
							$SA6 = $row->SA6;
							$DOM6 = $row->DOM6;
							$NOKDER = $row->NOKDER;
							$ENLEM = $row->ENLDER."° ".$row->ENLDAK."' ".replacepoint($row->ENLSAN)."\"";
							$BOYLAM = $row->BOYDER."° ".$row->BOYDAK."' ".replacepoint($row->BOYSAN)."\"";
							$YUKSEKLIK = $row->KOT;
							$FILENAME = "$NOK_NO";
							
							switch($row->ASKERIMI){
								case "E": $style = "background-color:yellow";
								break;
								case "H": $style = "";
							}
							
							if($sira_no % 9 == 0){
									$pagebreak = $footer_html.'</table><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'.$page1_header;
								}else $pagebreak = ""; 
							$page1_body = $page1_body.'<tr nobr="true" style="'.$style.'">
											<td><strong>'.$sira_no.'</strong></td>
											<td>'.$NOK_NO.'</td>
											<td>'.$NOK_ADI.'</td>
											<td>'.$PAFTASI.'</td>
											<td>'.$DOM6.'</td>
											<td>D.G</td>
											<td>'.hassasMi($SA6).'</td>
											<td>'.hassasMi($YU6).'</td>
											<td>'.hassasMi($KOT).'</td>
											<td>'.$NOKDER.'</td>
											<td>'.$TESIS_YILI.'</td>
											<td>'.$TESIS_TURU.'</td>
											<td>'.$KOT_TURU.'</td>
										</tr>'.$pagebreak;
							$sira_no++;
						}
						$page1_footer = '</table><div style="height:25px;"></div>'.$footer_html;
						$page_1 = $page1_header.$page1_body.$page1_footer;
						
						$pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
						$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs='<img src="images/protokol_banner.jpg" />', $tc=array(0,0,0), $lc=array(0,0,0));
						
						$pdf->AddPage('L', 'A4');
						$pdf->writeHTML($page_1, true, false, true, false, '');
						$pdf->lastPage();
				break;
	}

	
if(strlen($_SESSION["PROTOKOL_ONAY"]["FIRMA"]["ADI"]) > 0){
	$firma_adi = $_SESSION["PROTOKOL_ONAY"]["FIRMA"]["ADI"];
}else{
	$firma_adi = "Firma Adı Girilmedi.";
}
$sql = "INSERT INTO KULLANICI_LOG (id,
								   firma_adi,
								   kullanici,
								   arama_tipi,
								   hazirlayan,
								   tarih,
								   kontrol_eden,
								   nokta_bilgisi) 
								VALUES (seq_log.nextval,
										'".$firma_adi."',
										'".$_SESSION['adSoyad']."',
										'".$_SESSION["PROTOKOL_ONAY"]["ARAMA_TIPI"]."',
										'".$_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["RUTBESI"].' '.$_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["ADISOYADI"]."',
										to_date('".date('d-m-Y H:i')."','DD-MM-YYYY HH24:MI'),
										'".$_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["RUTBESI"].' '.$_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["ADISOYADI"]."',
										'".json_encode($_SESSION['PROTOCOL'],JSON_PRETTY_PRINT)."')";
   /*  echo $sql;
	exit; */
	$stmt = $db->prepare ($sql);
	$stmt->execute();

}  





/* $pdf->AddPage();
$html = '<h1>Sayfa 2</h1>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage(); */


//Close and output PDF document
$pdf->Output("$FILENAME.pdf", "I");

//============================================================+
// END OF FILE
//============================================================+
