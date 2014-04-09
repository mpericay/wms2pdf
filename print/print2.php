<?

// Rutas donde tendremos la libreria y el fichero de idiomas.
require_once('tcpdf/tcpdf.php');
require_once('config.php');

function _forceDownload($dir,$fileName) {
    $file = $dir.$fileName;
    $gestor = @fopen($file, "r");
    if (!$gestor) die("error opening file");
    $data = fread($gestor,filesize($file));
    fclose($gestor);
    // stream
    header('Cache-control: private, must-revalidate');
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename="map_'.gmdate('YmdHis').'.pdf"');
    header('Content-Length: '.strlen($data));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    echo $data;
}

class mapPDF extends TCPDF {
	public function getRemainingHeight() {
		return ($this->getPageHeight() - $this->getBreakMargin() - $this->GetY());
	}

}

$tmpdir = _TMP_DIR;
//look for enviroment variable or use /tmp
//if(isset($_ENV["TEMP"])) $tmpdir = $_ENV["TEMP"];
//if (!$tmpdir) if(isset($_ENV["TMP"])) $tmpdir = $_ENV["TMP"];

//force download the PDF
if (isset($_REQUEST["pdfUrl"])) {
    _forceDownload($tmpdir,$_REQUEST["pdfUrl"]);
    die();
}
 
// create new PDF document
$pdf = new mapPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('WMS2PDF');
$pdf->SetTitle('');
$pdf->SetSubject('WMS Map print');
$pdf->SetKeywords('PDF, PHP, WMS, map, print');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set JPEG quality
//$pdf->setJPEGQuality(75);

//$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

$pdf->SetFont('helvetica', '', 18);

// add a page
$pdf->AddPage('L', 'A4');
//die($pdf->getRemainingHeight());

//only for landscape
$height = $pdf->getRemainingHeight();
$width = $height; //only for landscape!!!

// the Image() method recognizes the alpha channel embedded on the image:
$pdf->Image('http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
$pdf->Image('http://si.progess.com:8008/geoserver/wms?LAYERS=mediacions_obertes&FORMAT=image%2Fpng&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A4326&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
$pdf->setPageMark();

//write the boxes
$pdf->SetLineStyle(array("color"=>array(50, 50, 50)));
$pdf->SetLineWidth(0.5);
// write the first cell (Map cell)
$pdf->MultiCell($width, $height, "", 1, 'J', 0, 0, '', '', true, 0, false, true, 0);

// write the splitter
$pdf->MultiCell(_BOX_GAP, $height, "", 0, 'J', 0, 0, '', '', false, 0, false, true, 0);

// write the second column
$pdf->SetFillColor(215, 235, 255);
$pdf->MultiCell(0, $height, "legend", 1, 'C', 1, 1, '', '', true, 0, false, true, 0);

// write the first column
//$pdf->MultiCell(80, 0, $left_column, 1, 'J', 1, 0, '', '', true, 0, false, true, 0);
// write the second column
//$pdf->MultiCell(0, 0, $right_column, 1, 'J', 1, 1, '', '', true, 0, false, true, 0);



// ---------------------------------------------------------

//Close and output PDF document
if(_DIRECT_OUTPUT) {
	$pdf->Output('pdfPrint.pdf', 'I');
	die();
}

//save PDF document in tmp directory and return its name
$fileName = "pdfPrint".gmdate('YmdHis').".pdf";
$file = $tmpdir.$fileName;
$pdf->Output($file, 'F');
$result["url"] = $fileName;
$result["error"] = 0;
print_r(json_encode($result));
die();

?>