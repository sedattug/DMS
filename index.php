<?php
$activePage = "jvtHarita";
include "../../security/db.php";
if(!isset($_SESSION['giris_durum']))
{
 header("Location: ../../giris/giris/");
} 
if($_SESSION["KULLANICI_YETKILERI"]["JVT_HARITA"] != true){
	header("Location: ../../");
}
if(isset($_POST["islem"])){
	$type = $_POST["type"];
	switch($type)
	{
		case "GPS": 
			$POSTED_GPSNOK_NO = strtoupper($_POST["parametre_1"]);
			$session_array = array("TYPE" => "GPS", "GPSNOK_NO" => $POSTED_GPSNOK_NO);
			$_SESSION["PROTOCOL"] = $session_array;
			break;
		case "NIVELMAN":
			$POSTED_NIVHAT_NO = strtoupper($_POST["parametre_1"]);
			$POSTED_NIVNOK_NO = strtoupper($_POST["parametre_2"]);
			$session_array = array("TYPE" => "NIVELMAN", "NIVHAT_NO" => $POSTED_NIVHAT_NO, "NIVNOK_NO" => $POSTED_NIVNOK_NO);
			$_SESSION["PROTOCOL"] = $session_array;
			break;
		case "NIRENGI":
			$POSTED_NOK_NO = strtoupper($_POST["parametre_1"]);
			$POSTED_PAF100 = strtoupper($_POST["parametre_2"]);
			$session_array = array("TYPE" => "NIRENGI", "NOK_NO" => $POSTED_NOK_NO, "PAF100" => $POSTED_PAF100);
			$_SESSION["PROTOCOL"] = $session_array;
			break;
	}
	//print_r($_SESSION);
	//header("Location: ../../protokol/examples/pdf.php");
	echo 1;
	exit;
}
if(isset($_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["ADISOYADI"])){
	$selected_hazirlayan = $_SESSION["PROTOKOL_ONAY"]["HAZIRLAYAN"]["ADISOYADI"];
}

if(isset($_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["ADISOYADI"])){
	$selected_kontrol_eden = $_SESSION["PROTOKOL_ONAY"]["KONTROL_EDEN"]["ADISOYADI"];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Jeodezik Veri Tabani</title>
  <!-- Bootstrap core CSS-->
  <link href="../../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="../../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
  <link href="../../assets/css/sb-admin.css" rel="stylesheet">
  
  <!--Sweetalert-->
  <script src="../../assets/js/sweetalert2.min.js"></script>
  <link rel="stylesheet" href="../../assets/css/sweetalert2.min.css">
  
    <script src="docs/examples/libs/leaflet-src.js"></script>
	<link rel="stylesheet" href="docs/examples/libs/leaflet.css" />
	
	<!--<link rel="stylesheet" type="text/css" href="../css/MarkerCluster/MarkerCluster.css" />
    <link rel="stylesheet" type="text/css" href="../css/MarkerCluster/MarkerCluster.Default.css" />-->

	<!--<link rel="stylesheet" href="measure/leaflet.draw.css" />
	<link rel="stylesheet" href="measure/leaflet.measurecontrol.css" />
	<script src="measure/leaflet.draw.js"></script>
	<script src="measure/leaflet.measurecontrol.js"></script>-->


    <script src="src/Leaflet.draw.js"></script>
    <script src="src/Leaflet.Draw.Event.js"></script>
    <link rel="stylesheet" href="src/leaflet.draw.css" />

    <script src="src/Toolbar.js"></script>
    <script src="src/Tooltip.js"></script>

    <script src="src/ext/GeometryUtil.js"></script>
    <script src="src/ext/LatLngUtil.js"></script>
    <script src="src/ext/LineUtil.Intersect.js"></script>
    <script src="src/ext/Polygon.Intersect.js"></script>
    <script src="src/ext/Polyline.Intersect.js"></script>
    <script src="src/ext/TouchEvents.js"></script>

    <script src="src/draw/DrawToolbar.js"></script>
    <script src="src/draw/handler/Draw.Feature.js"></script>
    <script src="src/draw/handler/Draw.SimpleShape.js"></script>
    <script src="src/draw/handler/Draw.Polyline.js"></script>
    <script src="src/draw/handler/Draw.Marker.js"></script>
    <script src="src/draw/handler/Draw.CircleMarker.js"></script>
    <script src="src/draw/handler/Draw.Circle.js"></script>
    <script src="src/draw/handler/Draw.Polygon.js"></script>
    <script src="src/draw/handler/Draw.Rectangle.js"></script>


    <script src="src/edit/EditToolbar.js"></script>
    <script src="src/edit/handler/EditToolbar.Edit.js"></script>
    <script src="src/edit/handler/EditToolbar.Delete.js"></script>

    <script src="src/Control.Draw.js"></script>

    <script src="src/edit/handler/Edit.Poly.js"></script>
    <script src="src/edit/handler/Edit.SimpleShape.js"></script>
    <script src="src/edit/handler/Edit.Marker.js"></script>
    <script src="src/edit/handler/Edit.CircleMarker.js"></script>
    <script src="src/edit/handler/Edit.Circle.js"></script>
    <script src="src/edit/handler/Edit.Rectangle.js"></script>



	<style>
	.custom-controls-stacked{
		background-color:#FFF;
		padding:10px;

	}
	.custom-controls-stacked .custom-control-description{
		margin-top:4px !important;
	}
	.custom_button {
		background-color:#FFF;
		width:33px;
		height:33px;
	}
	.custom_button:hover, .custom-controls-stacked>label:hover{
		background-color:#f4f4f4;
		cursor:pointer;
	}
	.info_win td {
		text-align:right;
		padding:1px;
	}
	.info_win tr {
		border-bottom:1px solid #f4f4f4;
		padding:1px;
	}
	
	#myForm .custom-radio {
		margin-left:5px;
	}
	#myForm .custom-radio:hover {
		cursor:pointer;
	}
	
	.layer_name:hover {
		cursor:pointer;
	}
	</style>
</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">

<div class="modal fade fade bd-example-modal-lg" id="protokolModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title">Protokol Önizleme</h5>
		<button class="close" type="button" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">×</span>
		</button>
	  </div>
	  <div class="modal-body">
		<iframe id="protokolModaliFrame" src='' width="100%" height="750px" frameborder="0"></iframe>
	  </div>
	  <div class="modal-footer">
		<button class="btn btn-secondary" type="button" data-dismiss="modal">Kapat</button>
	  </div>
	</div>
  </div>
</div>

  <!-- Navigation-->
  <?php
	include "../../includes/left_menu.php";
  ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="#">Anasayfa</a>
        </li>
        <li class="breadcrumb-item active">Harita</li>
      </ol>
      <!-- Icon Cards-->
				<div class="row">
					<div class="col-12">
						<!-- Modal -->
						<div class="modal fade fade bd-example-modal-lg" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
						  <div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
							  <div class="modal-header" style="background-color:#e9ecef;">
								<h5 class="modal-title" id="exampleModalLongTitle">Verileri Filtrele</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								  <span aria-hidden="true">&times;</span>
								</button>
							  </div>
							  <div class="modal-body">
								  <form id="myForm">
									  <div class="form-row">
										<div class="form-group col-md-12">
										<label class="custom-control custom-radio">
										  <input id="radio1" data-form="nirengi_search" name="table" type="radio" class="custom-control-input table_radio" value="NIRKOOR_TBL">
										  <span class="custom-control-indicator"></span>
										  <span class="custom-control-description">Nirengi</span>
										</label>
										<label class="custom-control custom-radio">
										  <input id="radio2" data-form="gps_search" name="table" type="radio" class="custom-control-input table_radio" checked value="GPSKOOR_TBL">
										  <span class="custom-control-indicator"></span>
										  <span class="custom-control-description">Gps</span>
										</label>
										<label class="custom-control custom-radio">
										  <input id="radio2" data-form="nivelman_search" name="table" type="radio" class="custom-control-input table_radio" value="NIVDEG_TBL">
										  <span class="custom-control-indicator"></span>
										  <span class="custom-control-description">Nivelman</span>
										</label>
										</div>
									  </div>
									  <div class="form-row">
										<div class="form-group col-md-6">
										  <label for="inputState">1 / 250 000 liğin adı</label>
										  <select id="pafta250" class="form-control" name="pafta250">
											<option selected value="">Seçiniz...</option>
										  </select>
										</div>
										<div class="form-group col-md-6">
										  <label for="inputState">1 / 100 000 liğin adı</label>
										  <select id="pafta100" class="form-control" name="pafta100">
											<option selected value="">Seçiniz...</option>
										  </select>
										</div>
									  </div>
									  <div class="form-row">
										<div class="form-group col-md-6">
										  <label for="inputState">1 / 50 000 liğin adı</label>
										  <select id="pafta50" class="form-control" name="pafta50">
											<option selected value="">Seçiniz...</option>
											<option value="a">a</option>
											<option value="b">b</option>
											<option value="c">c</option>
											<option value="d">d</option>
										  </select>
										</div>
										<div class="form-group col-md-6">
										  <label for="inputState">1 / 25 000 liğin adı</label>
										  <select id="pafta25" class="form-control" name="pafta25">
											<option selected value="">Seçiniz...</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
										  </select>
										</div>
									  </div>
									 <a data-toggle="collapse" href="#latLonSearch"><i class="fa fa-angle-down"></i> Girilen Koordinatın Çevresinde Ara</a>
									 <div class="collapse" id="latLonSearch">
									 <div class="form-row">
											<div class="form-group col-md-4">
											  <label for="inputEmail4">Enlem</label>
											  <input type="text" class="form-control" id="inputEmail4" placeholder="Enlem" name="postedEnlem">
											  <small class="form-text text-muted">Örn: 38.72</small>
											</div>
											<div class="form-group col-md-4">
											  <label for="inputPassword4">Boylam</label>
											  <input type="text" class="form-control" id="inputPassword4" placeholder="Boylam" name="postedBoylam">
											  <small class="form-text text-muted">Örn: 38.72</small>
											</div>
											<div class="form-group col-md-4">
											  <label for="inputPassword4">Mesafe</label>
											  <input type="text" class="form-control" id="inputPassword4" placeholder="Mesafe [km]" name="postedDistance">
											  <small class="form-text text-muted">Örn: 10</small>
											</div>
										</div>
									</div>
									  <div class="form-row nivelman_search changeable" style="display:none;">
										<div class="form-group col-md-6">
										  <label for="inputEmail4">Hat No</label>
										  <input type="text" class="form-control" id="inputEmail4" placeholder="Hat No" name="hatNoNivelman">
										</div>
										<div class="form-group col-md-6">
										  <label for="inputPassword4">Nokta No</label>
										  <input type="text" class="form-control" id="inputPassword4" placeholder="Nokta No" name="noktaNoNivelman">
										</div>
									  </div>
									  <div class="form-row nivelman_search changeable" style="display:none;">
										<div class="form-group col-md-12">
										  <label for="inputEmail4">Nokta Adı</label>
										  <input type="text" class="form-control" id="inputEmail4" placeholder="Nokta Adı" name="noktaAdiNivelman">
										</div>
									  </div>
									  <div class="form-row nirengi_search changeable" style="display:none;">
										<div class="form-group col-md-6">
										  <label for="inputEmail4">Nokta No</label>
										  <input type="text" class="form-control" id="inputEmail4" placeholder="Nokta No" name="noktaNoNirengi">
										</div>
										<div class="form-group col-md-6">
										  <label for="inputPassword4">Nokta Adı</label>
										  <input type="text" class="form-control" id="inputPassword4" placeholder="Nokta Adı" name="noktaAdiNirengi">
										</div>
									  </div>
									  <div class="form-row gps_search changeable">
										<div class="form-group col-md-6">
										  <label for="inputEmail4">Nokta No</label>
										  <input type="text" class="form-control" id="inputEmail4" placeholder="Nokta No" name="noktaNoGps">
										</div>
										<div class="form-group col-md-6">
										  <label for="inputPassword4">Kısa Adı</label>
										  <input type="text" class="form-control" id="inputPassword4" placeholder="Kısa Adı" name="kisaAdiGps">
										</div>
									  </div>
									  <div class="form-row">
										  <div class="col-md-6">
											<label for="exampleInputName">Hazırlayan</label>
											<select class="form-control" name="hazirlayan">
											<option value="" selected>--Seçiniz--</option>
											<?php
												$sql = $db->prepare("SELECT  RUTBESI, ADISOYADI FROM PERSONEL_TBL");
												$sql->execute();					
												while ($row = $sql->fetch(PDO::FETCH_OBJ))
												{
													if(isset($selected_hazirlayan) && $selected_hazirlayan == $row->ADISOYADI){
														$isSelected = "selected";
													}else $isSelected = "";
													echo "<option $isSelected value='$row->ADISOYADI'>$row->RUTBESI $row->ADISOYADI</option>";
												}
											?>
											</select>
										  </div>
										  <div class="col-md-6">
											<label for="exampleInputLastName">Kontrol Eden</label>
											<select class="form-control" name="kontrol_eden">
											<option value="" selected>--Seçiniz--</option>
											<?php
												$sql = $db->prepare("SELECT  RUTBESI, ADISOYADI FROM PERSONEL_TBL");
												$sql->execute();					
												while ($row = $sql->fetch(PDO::FETCH_OBJ))
													{
														if(isset($selected_kontrol_eden) && $selected_kontrol_eden == $row->ADISOYADI){
															$isSelected = "selected";
														}else $isSelected = "";
														echo "<option $isSelected value='$row->ADISOYADI'>$row->RUTBESI $row->ADISOYADI</option>";
													}
											?>
											</select>
										  </div>
										</div>
										<hr/>
										<div class="form-row">
										    <div class="form-group col-md-12">
											<label class="custom-control custom-radio hassas">
											  <input id="radio1_" data-form="nirengi_search" name="arama_tipi" type="radio" class="custom-control-input" value="HASSAS">
											  <span class="custom-control-indicator"></span>
											  <span class="custom-control-description">Hassas</span>
											</label>
											<label class="custom-control custom-radio kaba">
											  <input id="radio2_" data-form="gps_search" name="arama_tipi" type="radio" class="custom-control-input" checked value="KABA">
											  <span class="custom-control-indicator"></span>
											  <span class="custom-control-description">Kaba</span>
											</label>
											</div>
										</div>
										<div class="form-row collapse" style="margin-top:10px;" id="companyName">
											<div class="form-group col-md-12">
											  <label for="firma_adi">Firma Adı</label>
											  <input type="text" class="form-control"  placeholder="Firma Adı" name="firma_adi">
											</div>
										</div>
										<div class="alert alert-danger err" role="alert" style="display:none;">
										  <strong>Hata!</strong> Kayıt Bulunamadı...
										</div>
								</form>
								</label>
							  </div>
							  <div class="modal-footer" style="background-color:#e9ecef;">
								<button type="button" class="btn btn-danger reset_form">Formu Temizle</button>
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
								<button type="button" class="btn btn-success" id="smt">Uygula</button>
							  </div>
							</div>
						  </div>
						</div>
						<div id="map" style="width: 100%; height: 750px; border: 1px solid #ccc">
						</div>
					</div>
				</div>
				</div>
    <!-- /.container-fluid-->
    <!-- /.content-wrapper-->
    <?php
		include "../../includes/footer.php";
	?>
    <!--<script src="../js/leaflet.js"></script>-->
	<!--<script type='text/javascript' src='../js/MarkerCluster/leaflet.markercluster.js'></script>-->
	<script>
		var rectangleArray = [];
		var markerArray = [];
		//var topograf = "http://www.hgkatlas.hgk.tsk/topografik/9/304/191.jpg";
    /*     var hgkUrl = 'http://www.hgkatlas.hgk.tsk/siyasi/{z}/{x}/{y}.jpeg',
            hgkAttrib = '&copy; <a href="http://www.hgk.tsk/" target="_blank">Harita Genel Komutanlığı</a>',
            map = new L.Map('map', {center: new L.LatLng(39.15, 35.5), zoom: 6}),
			hgk = L.tileLayer(hgkUrl, {maxZoom: 11, minZoom: 2, attribution: hgkAttrib}).addTo(map),
			crs:L.CRS.EPSG4326
            drawnItems = L.featureGroup().addTo(map); */
		

		
        var hgkLink = '<a href="http://www.hgk.tsk/" target="_blank">Harita Genel Komutanlığı</a>';
        
        var siyasiUrl = 'http://www.hgkatlas.hgk.tsk/siyasi/{z}/{x}/{y}.jpeg', siyasiAttrib = '&copy; ' + hgkLink,
            fizikiUrl = 'http://www.hgkatlas.hgk.tsk/atlas/{z}/{x}/{y}.jpeg', fizikiAttrib = '&copy; '+hgkLink,
			topografikUrl = 'http://www.hgkatlas.hgk.tsk/topografik/{z}/{x}/{y}.jpg', fizikiAttrib = '&copy; '+hgkLink;

        var siyasiMap = L.tileLayer(siyasiUrl, {minZoom: 2, maxZoom: 11, attribution: siyasiAttrib}),
            fizikiMap = L.tileLayer(fizikiUrl, {minZoom: 2, maxZoom: 11, attribution: fizikiAttrib}),
            topografikMap = L.tileLayer(topografikUrl, {minZoom: 9, maxZoom: 13, attribution: fizikiAttrib});
		
		
		
		$.ajax({
			type : "GET",
			url : "http://haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?",
			async : false,
			beforeSend : function(xhr){
				xhr.setRequestHeader("Authorization","Basic ",btoa("hgk:hgk"));
			},
			success: function(){
				console.log("Aldı.");
			}
		});
		var ortoLayer = L.tileLayer.wms('http://hgk:hgk@haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?', {
			crs:L.CRS.EPSG4326,
			layers: 'Dunya,HGK_Ortofoto_SB,HGK_Ortofoto_DN34,HGK_Ortofoto_DN35,HGK_Ortofoto_DN36,HGK_Ortofoto_DN37,HGK_Ortofoto_DN38,HGK_Ortofoto_DN39,HGK_ASuriye,HGK_Merkezler',
			minZoom: 2,
			maxZoom: 20
		});
		
		var k_100Layer = L.tileLayer.wms('http://hgk:hgk@haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?', {
			crs:L.CRS.EPSG4326,
			layers: 'HGK_100K_Raster_Harita',
			minZoom: 12,
			maxZoom: 13
		});
		
		var k_50Layer = L.tileLayer.wms('http://hgk:hgk@haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?', {
			crs:L.CRS.EPSG4326,
			layers: 'HGK_50K_Raster_Harita',
			minZoom: 14,
            maxZoom: 15
		});
		
		var k_25Layer = L.tileLayer.wms('http://hgk:hgk@haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?', {
			crs:L.CRS.EPSG4326,
			layers: 'HGK_25K_Raster_Harita',
			minZoom: 16,
            maxZoom: 17
		});
		
        var map = L.map('map', {
			    layers: [siyasiMap] // only add one!
		    })
		    .setView([39.15, 35.5], 6);

		var baseLayers = {
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Vektör Tematik Harita (Siyasi)'>Vektör Tematik (Siyasi)</span>": siyasiMap,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Vektör Tematik Harita (Fiziki)'>Vektör Tematik (Fiziki)</span>": fizikiMap,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Raster Topoğrafik'>Raster Topoğrafik</span>": topografikMap,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Orto Görüntü (Hava Fotoğrafı - Uydu Görüntüsü)'>Orto Görüntü</span>": ortoLayer,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Raster Harita (100K)'>Raster Harita (100K)</span>": k_100Layer,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Raster Harita (50K)'>Raster Harita (50K)</span>": k_50Layer,
			"<span class='layer_name' data-toggle='tooltip' data-placement='left' title='Raster Harita (25K)'>Raster Harita (25K)</span>": k_25Layer 
		};

		L.control.layers(baseLayers,null,{collapsed:false}).addTo(map);
		
		
		
		
		
		
		
		//http://haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?
		//K.adı:hgk
		//Şifre : hgk

		
	    /* var map = new L.Map('map', {center: new L.LatLng(39.15, 35.5), zoom: 6});	
		var basemaps = {
			hgkOrto: L.tileLayer.wms('http://haritasunucu4.hgk.tsk/cshgksisgel/csogc.dll/wms?', {
				layers: 'HGK_Ortofoto_SB',
				srs
			})
		}; 
		
		L.control.layers(basemaps).addTo(map);

		basemaps.hgkOrto.addTo(map);*/
		
		//L.Control.measureControl().addTo(map);
		$(".leaflet-control-draw-measure").attr({ 
												"title" : "Mesafe Ölç", 
												"data-toggle" : "tooltip", 
												"data-placement" : "left"
												});
		$(".leaflet-control-zoom-in").attr({ 
										"title" : "Yakınlaş", 
										"data-toggle" : "tooltip", 
										"data-placement" : "right"
										});
		$(".leaflet-control-zoom-out").attr({ 
									"title" : "Uzaklaş", 
									"data-toggle" : "tooltip", 
									"data-placement" : "right"
									});
		$(".leaflet-control-draw-measure").click(function(){
			if($(this).hasClass("active")){
				$(".leaflet-control-draw-measure").removeClass("active").css("background-color","#FFF");
			}else $(".leaflet-control-draw-measure").addClass("active").css("background-color","#ccc");
		})
		
		/*PAFTA GRİDLERİNİ OLUŞTURDUĞUMUZ FONKSİYON.*/
		function createRectangle(data_ad, time){
			 swal({
			  title: 'Lütfen Bekleyin.',
			  text: 'İndeksler Oluşturuluyor...',
			  showConfirmButton:false,
			  timer: time,
			  onOpen: () => {
				//swal.showLoading()
			  }
			}).then((result) => {
			  if (result.dismiss === 'timer') {
				  //$("#protokolModal").modal("show");
			  }
			})
				clearRectangles();
				$(".indeksTemizle").attr("disabled", false);
				var url = "katmanlar/"+data_ad+"/json/pafta_"+data_ad+".json";
				$.getJSON(url,function(data){
					$.each(data, function(key, val){
					var pafta = val.pafta;
					var north = val.north;
					var east = val.east;
					var south = val.south;
					var west = val.west;
						var bounds = [[north, east], [south, west]];
						var rect = L.rectangle(bounds, {color: "red", weight: 0.5, fillOpacity:0.0,pafta : pafta  });
						rect.on('click',function(e){
						changecolor();
						
						map.eachLayer(function(l) {
						  if (l.getTooltip) {
							var toolTip = l.getTooltip();
							if (toolTip) {
							  this.map.closeTooltip(toolTip);
							}
						  }
						});
						
						//Click Function
						var pafta = e.target.options.pafta;
						rect.bindTooltip(pafta,
						{
							permanent: true, 
							direction:"center"
						}
						).openTooltip()
							map.fitBounds(bounds);
							rect.setStyle({color: "#4B1BDE",fillOpacity:0.2});
						});
						rectangleArray.push(rect);
						rect.addTo(map);
					});
				});
			}
			
			/*PAFTA GRİDLERİNİ HARİTADAN SİLER.*/
			function clearRectangles(){
				for(var i = 0; i < rectangleArray.length; i++){
						rectangleArray[i].remove();
				}
				rectangleArray = [];
			}
			
			function changecolor() {
			  for (var i = 0; i < rectangleArray.length; i++) {
				rectangleArray[i].setStyle({color: "red", weight: 0.5, fillOpacity:0.0});
			  }
			}
		
		/*HARİTA ÜZERİNE CUSTOM BUTON EKLER.HOME ICON*/
		var customControl = L.Control.extend({
		options: {
		position: 'topleft' //control position - allowed: 'topleft', 'topright', 'bottomleft', 'bottomright'
		},
		onAdd: function (map) {
		var refresh = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom custom_button');
		refresh.innerHTML = '<img data-toggle="tooltip" data-placement="right" style="width:24px; height:24px; margin:3px;" title="Haritayı Yenile" src="../images/icons/refresh_icon.png"/>';
		refresh.onclick = function(){
		location.reload();
		}
		return refresh;
		},
		});
		map.addControl(new customControl());
		
		/*HARİTA ÜZERİNE CUSTOM BUTON EKLER.SEARCH ICON*/
		var customControl = L.Control.extend({
		options: {
		position: 'topleft' //control position - allowed: 'topleft', 'topright', 'bottomleft', 'bottomright'
		},
		onAdd: function (map) {
		var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom custom_button');
		container.innerHTML = '<img data-toggle="tooltip" data-placement="right" title="Arama Seçenekleri" src="../images/icons/search.png"/>';
		container.onclick = function(){
		$("#exampleModalLong").modal("toggle");
		}
		return container;
		},
		});
		map.addControl(new customControl());
		
		/* HARİTA ÜZERİNE CUSTOM İNDEKS DİVİ EKLEDİK */
		var command = L.control({position: 'bottomright'});

		command.onAdd = function (map) {
			var div = L.DomUtil.create('div', 'command');

			div.innerHTML = '<div class="custom-controls-stacked leaflet-bar leaflet-control leaflet-control-custom">'+
								'<h6 class="text-center">İndeksler</h6>'+
							  '<label class="custom-control custom-radio">'+
								'<input id="index25" name="indeks_radio" onclick="createRectangle(this.id, 3000);" type="radio"  class="custom-control-input">'+
								'<span class="custom-control-indicator"></span>'+
								'<span class="custom-control-description">İndeks 25 000</span>'+
							  '</label>'+
							 '<label class="custom-control custom-radio">'+
								'<input id="index50" name="indeks_radio" type="radio" onclick="createRectangle(this.id, 2000);" class="custom-control-input">'+
								'<span class="custom-control-indicator"></span>'+
								'<span class="custom-control-description">İndeks 50 000</span>'+
							  '</label>'+
							  '<label class="custom-control custom-radio">'+
								'<input id="index100" name="indeks_radio" type="radio" onclick="createRectangle(this.id, 1000);" class="custom-control-input">'+
								'<span class="custom-control-indicator"></span>'+
								'<span class="custom-control-description">İndeks 100 000</span>'+
							  '</label>'+
							  '<label class="custom-control custom-radio">'+
								'<input id="index250" name="indeks_radio" type="radio" onclick="createRectangle(this.id, 750);" class="custom-control-input">'+
								'<span class="custom-control-indicator"></span>'+
								'<span class="custom-control-description">İndeks 250 000</span>'+
							  '</label>'+
							  '<button class="btn btn-danger btn-sm btn-block indeksTemizle" disabled="true" onclick="indeksTemizle();">İndeksi Temizle</button>'
							'</div>'; 
			return div;
		};

		command.addTo(map);
		
		/* HGK LOGO */
		var hgkLogo = L.control({position: 'bottomleft'});

		hgkLogo.onAdd = function (map) {
			var div = L.DomUtil.create('div', 'hgkLogo');

			div.innerHTML = '<img src="../images/icons/hgk_logo.png" />'; 
			return div;
		};

		hgkLogo.addTo(map);
		
		
		function indeksTemizle(){
			clearRectangles();
			$(".indeksTemizle").attr("disabled", true);
			$(".command input").each(function(){
				$(this).prop("checked", false);
			});
		}
		

		$(".table_radio").click(function(){
			pafta250Doldur();
			var data_form = $(this).attr("data-form");
			$(".changeable").hide("slow");
			$("."+data_form).show("slow");
		});
		
		/* ARAMA SEÇENEKLERİ FORMU AJAX POST İŞLEMİ. MARKER ARRAY DOLDURMA İŞLEMLERİ. */
			$("#smt").click(function(){
				//e.preventDefault();
				var form=$("#myForm");
				var checked_input = $("#myForm input[name='table']:checked").val();
				clearMarkers();
				$.ajax({
						type:"POST",
						url:"server.php",
						data:form.serialize(),

						success: function(response){
							if(response == 0){
								swal(
								  'Uyarı',
								  'Hazırlayan ve Kontrol Eden Alanları BOŞ BIRAKILAMAZ!',
								  'warning'
								)
								return;
							}
							if(response == 1){
								swal(
								  'Uyarı',
								  'Hazırlayan ve Kontrol Eden Alanları AYNI OLAMAZ!',
								  'warning'
								)
								return;
							}
							if(response == 3){
								swal(
								  'Uyarı',
								  'Firma Adı Alanı BOŞ BIRAKILAMAZ!',
								  'warning'
								)
								return;
							}
							var data;
							//data = JSON.parse(response);
							try {
								data = JSON.parse(response);
								$("#exampleModalLong").modal("toggle");
								$(".err").hide();
							} catch(err) {
								$(".err").fadeIn("slow");
								console.log(err);
								return;
							} 
							var len = data.length;
							if(checked_input=="GPSKOOR_TBL"){
								$.each( data, function( key, val ) {
									var	lng = val.BOYLAM.replace(",",".");
									var	lat = val.ENLEM.replace(",",".");
									  contentString = '<h5 class="text-center">Gps Noktası</h5><table class="info_win">'+
														  '<tr><th>NOKTA NO</th><td>'+val.GPSNOK_NO+'</td></tr>'+
														  '<tr><th>NOKTA ADI</th><td>'+val.GPSNOK_ADI+'</td></tr>'+
														  '<tr><th>KISA ADI</th><td>'+val.GPSKISA_ADI+'</td></tr>'+
														  '<tr><th>ENLEM</th><td>'+lat.toString().slice(0,8)+'</td></tr>'+
														  '<tr><th>BOYLAM</th><td>'+lng.toString().slice(0,8)+'</td></tr>'+
														  '<tr><th>TESİS TÜRÜ</th><td>'+val.TESIS_TURU+'</td></tr>'+
														  '<tr><th>İLÇESİ / İLİ</th><td>'+val.ILCESI+' / '+val.ILI+'</td></tr>'+
													  '</table>'+
													  '<button class="btn btn-sm btn-info btn-block" onclick="goToProtocol(\'GPS\',\''+val.GPSNOK_NO+'\',\'\')">Protokol</button>';
										var gps_icon = L.icon({
											iconUrl: '../images/icons/tutga_12_12.png',
											popupAnchor:  [0, -12],
											iconAnchor:  [7, 10],
										});
									var marker = L.marker([lat, lng],{icon: gps_icon}).addTo(map).bindPopup(contentString);
									markerArray.push(marker);
								});
							}else if(checked_input=="NIVDEG_TBL"){
								if(data.adet > 1000) {
									swal({
										  title: 'Dikkat!',
										  text: 'Filtreleme sonucunuz [ '+data.adet+' ] adet veri döndürdü. Lütfen filtrenizi daraltınız!.',
										  showConfirmButton:false,
										  timer: 5000,
										  onOpen: () => {
											//swal.showLoading()
										  }
										});
									return false;
								}
								$.each( data.veriler, function( key, val ) {
									var	lng = val.BOYLAM.replace(",",".");
									var	lat = val.ENLEM.replace(",",".");
									var tipi;
									switch(val.NIVNOK_TIPI){
										case 'Y' :  tipi = 'YER'; break;
										case 'D' :  tipi = 'DUVAR'; break;
									}
										contentString = '<h5 class="text-center">Nivelman Noktası</h5><table class="info_win">'+
														  '<tr><th>HAT NO</th><td>'+val.NIVHAT_NO+'</td></tr>'+
														  '<tr><th>NOKTA NO</th><td>'+val.NIVNOK_NO+'</td></tr>'+
														  '<tr><th>NOKTA ADI</th><td>'+val.NIVNOK_ADI+'</td></tr>'+
														  '<tr><th>DERECESİ</th><td>'+val.NOKDER+'</td></tr>'+
														  '<tr><th>TİPİ</th><td>'+tipi+'</td></tr>'+
														  '<tr><th>ENLEM</th><td>'+lat.toString().slice(0,8)+'</td></tr>'+
														  '<tr><th>BOYLAM</th><td>'+lng.toString().slice(0,8)+'</td></tr>'+
														  '<tr><th>PAFTASI</th><td>'+val.PAF250+' '+val.PAF100+'-'+val.PAF50+val.PAF25+'</td></tr>'+
														  '<tr><th>ÖLÇÜM YILI</th><td>'+val.OLCUM_YILI+'</td></tr>'+
													  '</table>'+
													  '<button class="btn btn-sm btn-info btn-block" onclick="goToProtocol(\'NIVELMAN\',\''+val.NIVHAT_NO+'\',\''+val.NIVNOK_NO+'\')">Protokol</button>';
									var nivelman_icon = L.icon({
											iconUrl: '../images/icons/nivelman.png',
											popupAnchor:  [4, -20],
											iconAnchor:  [8, 20],
										});
									var marker = L.marker([lat, lng], {icon: nivelman_icon}).addTo(map).bindPopup(contentString);
									markerArray.push(marker);
								});
							}else if(checked_input=="NIRKOOR_TBL"){
								if(data.adet > 1000) {
									swal({
										  title: 'Dikkat!',
										  text: 'Filtreleme sonucunuz [ '+data.adet+' ] adet veri döndürdü. Lütfen filtrenizi daraltınız!.',
										  showConfirmButton:false,
										  timer: 5000,
										  onOpen: () => {
											//swal.showLoading()
										  }
										});
									return false;
								}
								$.each( data.veriler, function( key, val ) {
									var	lng = val.BOYLAM.replace(",",".");
									var	lat = val.ENLEM.replace(",",".");
										contentString = '<h5 class="text-center">Nirengi Noktası</h5><table class="info_win">'+
															  '<tr><th>NOKTA NO</th><td>'+val.NOK_NO+'</td></tr>'+
															  '<tr><th>NOKTA ADI</th><td>'+val.NOK_ADI+'</td></tr>'+
															  '<tr><th>ENLEM</th><td>'+lat.toString().slice(0,8)+'</td></tr>'+
															  '<tr><th>BOYLAM</th><td>'+lng.toString().slice(0,8)+'</td></tr>'+
															  '<tr><th>PAFTASI</th><td>'+val.PAF250+' '+val.PAF100+'-'+val.PAF50+val.PAF25+'</td></tr>'+
															  '<tr><th>DERECESİ</th><td>'+val.NOKDER+'</td></tr>'+
															  '<tr><th>YÜKSEKLİK</th><td>'+val.KOT+' m.</td></tr>'+
															  '<tr><th>TESİS TÜRÜ</th><td>'+val.TESIS_TURU+'</td></tr>'+
															  '<tr><th>TESİS YILI</th><td>'+val.TESIS_YILI+'</td></tr>'+
													  '</table>'+
													  '<button class="btn btn-sm btn-info btn-block" onclick="goToProtocol(\'NIRENGI\',\''+val.NOK_NO+'\',\''+val.PAF100+'\')">Protokol</button>';
									var nirengi_icon = L.icon({
											iconUrl: '../images/icons/nirengi.png',
											popupAnchor:  [4, -20],
											iconAnchor:  [8, 20],
										});
									var marker = L.marker([lat, lng], {icon: nirengi_icon}).addTo(map).bindPopup(contentString);
									markerArray.push(marker);
								});
							}
							/* MARKER GRUBUNA ODAKLAN.*/
							var markerGroup = L.featureGroup(markerArray);
							map.fitBounds(markerGroup.getBounds());
							markerGroup = null;
							
							var centerIcon = L.icon({
											iconUrl: '../images/icons/marker-icon.png',
											iconAnchor:  [8, 20],
										});
							var postedEnlem = ($('input[name="postedEnlem"]').val()).replace(",",".");
							var postedBoylam = $('input[name="postedBoylam"]').val().replace(",",".");
							var markerCenter = L.marker([postedEnlem, postedBoylam], {icon: centerIcon}).addTo(map).bindPopup("Belirttiğiniz koordinat.");
							markerArray.push(markerCenter);					
						}
					});
			});
		
		/* HARİTADAN MARKERLARI SİLER */
		function clearMarkers(){
				for(var i = 0; i < markerArray.length; i++){
						markerArray[i].remove();
				}
				markerArray = [];
			}
		
		/* ZOOM SEVİYESİ BİLGİSİNİ VERİR.*/
		map.on("zoomstart", function(){
			var zoomLev = map.getZoom();
		});
		
		/* 250 000 PAFTA SEÇENEĞİNİ DOLDURUR. */
		function pafta250Doldur(){
			$("#pafta250").html("<option value='' selected>Seçiniz...</option>");
			var checked_input = $("#myForm input[name='table']:checked").val();
			var url = "pafta_finder.php?bul250&table="+checked_input;
			
			$.getJSON( url, function( data ) {
				$.each( data, function( key, val ) {
					$("#pafta250").append("<option value='"+val.PAF250+"'>"+val.PAF250+"</option>");
				});
			});
		}
		
		pafta250Doldur();
		
		/* 250 000 PAFTA SEÇENEĞİNE GÖRE 100 000 LİK SEÇENEĞİ DOLDURUR. */
		$("#pafta250").change(function(){
			$("#pafta100").html("<option value='' selected>Seçiniz...</option>");
			var checked_input = $("#myForm input[name='table']:checked").val();
			var pafta250 = $(this).val();
			var url = "pafta_finder.php?bul100&table="+checked_input+"&pafta250="+pafta250;
			$.getJSON( url, function( data ) {
				$.each( data, function( key, val ) {
					$("#pafta100").append("<option value='"+val.PAF100+"'>"+val.PAF100+"</option>");
				});
			});
		});
	
		function goToProtocol(type, parametre_1, parametre_2){
		 $.ajax({
			  type: "POST",
			  //url: url,
			  data: {islem: "protokol", type:type, parametre_1: parametre_1, parametre_2:parametre_2},
			  success: function(response)
			  {		
				  if(response == 1) {
					  $("#protokolModaliFrame").attr("src", "../../protokol/examples/pdf.php");
					  swal({
						  title: 'Lütfen Bekleyin.',
						  text: 'PDF Dosyanız Hazırlanıyor...',
						  timer: 3000,
						  onOpen: () => {
							swal.showLoading()
						  }
						}).then((result) => {
						  if (result.dismiss === 'timer') {
							  $("#protokolModal").modal("show");
						  }
						})
				  }
			  }
		  }); 
	}
	$(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
	
	$(".reset_form").click(function(){
		document.getElementById("myForm").reset();
	});
    </script>
  </div>
</body>

</html>
