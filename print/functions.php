<?php
function forceDownload($dir,$fileName) {
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

function saveFile($tmpdir, $pdf) {
	$fileName = "pdfPrint".gmdate('YmdHis').".pdf";
	$file = $tmpdir.$fileName;
	$pdf->Output($file, 'F');
	$result["url"] = $fileName;
	$result["error"] = 0;
	print_r(json_encode($result));	
}
?>