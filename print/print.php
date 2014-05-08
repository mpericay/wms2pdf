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

// create new PDF document
$pdf = new wms2PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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

$pdf->SetFont('helvetica', '', 11);
// add a page
$pdf->AddPage('L', 'A4');

//only for landscape (legend on right)
$pageHeight = $pdf->getRemainingHeight();
$imageHeight = $pageHeight; 
$imageWidth = $pageHeight * $pdf->getRatio();

//we need to pass image width and height to recalculate bbox
$pdf->recalculateBbox($imageHeight, $imageWidth);

// the Image() method recognizes the alpha channel embedded on the image:
$pdf->writeMap($imageHeight, $imageWidth);
$pdf->setPageMark();

//write the boxes
$pdf->SetLineStyle(array('color'=>array(50, 50, 50)));
$pdf->SetLineWidth(0.3);
// write the first cell (Map cell)
$pdf->MultiCell($imageWidth, $pageHeight, '', 1, 'J', 0, 0, '', '', true, 0, false, true, 0);

// write the splitter
$pdf->MultiCell($pdf->config["boxGap"], $pageHeight, '', 0, 'J', 0, 0, '', '', false, 0, false, true, 0);

//Get current write position: we will draw the legend from here
$x = $pdf->GetX();
$y = $pdf->GetY();

// write the second cell
$pdf->MultiCell(0, $pageHeight, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 0);

/* --- START LEGEND BLOCK ---*/
//fixed elements: write north and texts 
$fixedSpaceUsed = 0;
if($pdf->config["showNorth"]) {
	$northHeight = 13;
	$pdf->writeNorth($x + 5, $pageHeight - $northHeight, $imageWidth);
	$fixedSpaceUsed += $northHeight;
}

//fixed elements: write logo (46pt above bottom)
if($pdf->config["showLogo"]) {
	$logoHeight = 16;
	$pdf->writeLogo('img/stacoloma.jpg', $x + 10, $pageHeight - $logoHeight - $fixedSpaceUsed);
	$fixedSpaceUsed += $logoHeight;
}

//reduce the page break by the 46pt (if the legend doesn't fit, we must not write over logo and north)
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM + $fixedSpaceUsed * PDF_IMAGE_SCALE_RATIO);
//dynamic elements: write Legend and Title
$pdf->SetXY($x,$y); //return to the beginning of the legend to start writing dynamically
if($title = $printData->map->title) $pdf->writeTitle($title, 15);
if($pdf->config["showLegend"]) $pdf->writeLegend();
/* --- END LEGEND BLOCK ---*/

//Close and output PDF document
if($pdf->config["directOutput"]) { // default is false
	$pdf->Output('pdfPrint.pdf', 'I');
//save PDF document in tmp directory and return its link	
} else {
	saveFile($tmpdir, $pdf);
}
die();

?>