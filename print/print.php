<?
/**
 * Endpoint of the WMS2PDF print service
 *
 * @author     Marti Pericay <marti@pericay.com>
 * @author     Mcrit <catala@mcrit.com>
 * @copyright  (c) 2014 by Marti Pericay and MCRIT
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 * 
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License.
 */

// Rutas donde tendremos la libreria y el fichero de idiomas.
require_once('tcpdf/tcpdf.php');
require_once('geolocation/geolocation.php');
require_once('functions.php');
require_once('class.wms2pdf.php');

/* tmp directory */
$tmpdir = "C:\Windows\Temp\wms2pdf\\";
//look for enviroment variable or use /tmp
//if(isset($_ENV["TEMP"])) $tmpdir = $_ENV["TEMP"];
//if (!$tmpdir) if(isset($_ENV["TMP"])) $tmpdir = $_ENV["TMP"];

//force download the PDF if we created it
if (isset($_REQUEST["pdfUrl"])) {
    forceDownload($tmpdir,$_REQUEST["pdfUrl"]);
    die();
}

//layout parameter
if (isset($_REQUEST['layout'])) {
	//Require class file
	$layout = $_REQUEST['layout'];
	if($layout != "default") {
		$file = "layouts/layout.".$layout.".php";
		if(file_exists($file)) {
			require_once($file);
			$pdf = new $layout(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		}
		// if we couldn't instantiate layout class (file didn't exist) we will use default layout
	}
}

// create new PDF document if no or unexistant layout was passed
if(!$pdf) $pdf = new wms2PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// get JSON data
if (isset($_REQUEST['printData'])) {
    $content=str_replace("\\", "", $_REQUEST['printData']);
    $printData = json_decode($content);
    //TODO: instead of die, output error
    if(!$printData->map) die("No JSON provided");
    else $pdf->loadConfig($printData->map);
} else {
	outputError("No POST data sent (printData)");
}

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('WMS2PDF');
$pdf->SetTitle('');
$pdf->SetSubject('WMS2PDF Map print');
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

$pdf->SetFont('helvetica', '', 11);

$pdf->buildPage();

//Close and output PDF document
if($pdf->config["directOutput"]) { // default is false
	$pdf->Output('pdfPrint.pdf', 'I');
//save PDF document in tmp directory and return its link	
} else {
	saveFile($tmpdir, $pdf);
}
die();

?>