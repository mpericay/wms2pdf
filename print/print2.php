<?

// Rutas donde tendremos la libreria y el fichero de idiomas.
require_once('tcpdf/tcpdf.php');
require_once('config.php');
require_once('functions.php');
require_once('class.wms2pdf.php');

$tmpdir = _TMP_DIR;
//look for enviroment variable or use /tmp
//if(isset($_ENV["TEMP"])) $tmpdir = $_ENV["TEMP"];
//if (!$tmpdir) if(isset($_ENV["TMP"])) $tmpdir = $_ENV["TMP"];

//force download the PDF if we created it
if (isset($_REQUEST["pdfUrl"])) {
    forceDownload($tmpdir,$_REQUEST["pdfUrl"]);
    die();
}

// create new PDF document
$pdf = new wms2PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// get JSON data
if (isset($_REQUEST['printData'])) {
    $content=str_replace("\\", "", $_REQUEST['printData']);
    $printData = json_decode($content);
    //TODO: instead of die, output error
    if(!$printData->map) die("No JSON provided");
    else $pdf->setServers($printData->map->servers);
} else {
    $result = array("error" => "-1");
    print_r(json_encode($result));
    die();
}

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
/*if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}*/

// ---------------------------------------------------------

// set JPEG quality
$pdf->setJPEGQuality(90);

$pdf->SetFont('helvetica', '', 12);
// add a page
$pdf->AddPage('L', 'A4');

//only for landscape (legend on right)
$height = $pdf->getRemainingHeight();
$width = $height; //only for landscape!!!

// the Image() method recognizes the alpha channel embedded on the image:
//$pdf->Image('http://mapcache.icc.cat/map/bases_noutm/service?FORMAT=image%2Fjpeg&EXCEPTIONS=application%2Fvnd.ogc.se_xml&SRS=EPSG%3A4326&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&LAYERS=topo&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
//$pdf->Image('http://si.progess.com:8008/geoserver/wms?LAYERS=mediacions_obertes&FORMAT=image%2Fpng&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A4326&BBOX=2.17462439453%2C41.4216758916%2C2.23401560547%2C41.4810671025&WIDTH=1024&HEIGHT=1024', PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $height, $width, '', '', '', false, 1024);
$pdf->writeMap($height, $width);
$pdf->setPageMark();

//write the boxes
$pdf->SetLineStyle(array('color'=>array(50, 50, 50)));
$pdf->SetLineWidth(0.3);
// write the first cell (Map cell)
$pdf->MultiCell($width, $height, '', 1, 'J', 0, 0, '', '', true, 0, false, true, 0);

// write the splitter
$pdf->MultiCell(_BOX_GAP, $height, '', 0, 'J', 0, 0, '', '', false, 0, false, true, 0);

//Get current write position: we will draw the legend from here
$x = $pdf->GetX();
$y = $pdf->GetY();

// write the second cell
$pdf->MultiCell(0, $height, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 0);

/* --- START LEGEND BLOCK ---*/
//fixed elements: write Logo (46pt above bottom)
$pdf->Image('img/stacoloma.jpg', $x + 10, $height - 30, 58, 16);
//fixed elements: write north and texts 
$pdf->writeNorth($x + 5, $height - 13);
//reduce the page break by the 46pt (if the legend doesn't fit, we mustn't write over logo and north)
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM + 46);
//dynamic elements: write Legend and Title
$pdf->SetXY($x,$y); //return to the beginning of the legend to start writing dynamically
if($title = $printData->map->title) $pdf->writeTitle($title, 15);
$pdf->writeLegend();
/* --- END LEGEND BLOCK ---*/

//Close and output PDF document
if(_DIRECT_OUTPUT) { // default is false
	$pdf->Output('pdfPrint.pdf', 'I');
//save PDF document in tmp directory and return its link	
} else {
	saveFile($tmpdir, $pdf);
}
die();

?>