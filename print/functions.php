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

function outputError($txt) {
	$result = array("error" => "-1", "text" => $txt);
    print_r(json_encode($result));
    die();
}

function saveFile($tmpdir, $pdf) {
	$fileName = "pdfPrint".gmdate('YmdHis').".pdf";
	$file = $tmpdir.$fileName;
	$pdf->Output($file, 'F');
	$result["url"] = $fileName;
	$result["error"] = 0;
	print_r(json_encode($result));	
}

function correctBbox($bbox, $imageHeight, $imageWidth, $fixedScale = false) {
	$windowRatio = $imageWidth / $imageHeight;
    if ($fixedScale) {
            $cenX = ($bbox[0]+$bbox[2])/2;
            $cenY = ($bbox[1]+$bbox[3])/2;
            //half the image in milimeters, multiplied by scale conversion factor
            $difX = ($imageWidth/2)*($fixedScale/1000);
            $difY = ($imageHeight/2)*($fixedScale/1000);

            $bbox[0] = $cenX - $difX;
            $bbox[1] = $cenY - $difY;
            $bbox[2] = $cenX + $difX;
            $bbox[3] = $cenY + $difY;
    }

    $bbox_cx = ($bbox[0]+$bbox[2])/2;
    $bbox_cy = ($bbox[1]+$bbox[3])/2;
    $bbox_w = ($bbox[2]-$bbox[0])/2;
    $bbox_h = ($bbox[3]-$bbox[1])/2;

    // correct map ratio
    //$map_window_ratio = $map_image_width / $page_height;
    $bbox_h = $bbox_w / $windowRatio;

    $bbox[0] = $bbox_cx-$bbox_w;
    $bbox[1] = $bbox_cy-$bbox_h;
    $bbox[2] = $bbox_cx+$bbox_w;
    $bbox[3] = $bbox_cy+$bbox_h;

    return $bbox;	
}

function get_url_parameter($url, $name, $default = null) {
  $parsed_url = parse_url($url);
  parse_str($parsed_url['query'],$parameters);
  if (isset($parameters[$name])) return $parameters[$name];
  return $default;
}

function set_url_parameter($url, $name, $value) {
  $parsed_url = parse_url($url);
  parse_str($parsed_url['query'],$parameters);
  $parameters[$name] = $value;
  $parsed_url['query'] = http_implode($parameters);
  $url = glue_url($parsed_url);
  return $url;
}

function http_implode($input) {
  if (!is_array($input)) return false;
  $url_query="";
  foreach ($input as $key=>$value) {
    $url_query .=(strlen($url_query)>1)?'&':"";
    $url_query .= urlencode($key).'='.urlencode($value);
  }
  return $url_query;
}

function glue_url($parsed) {
  if (! is_array($parsed)) return false;
  if(array_key_exists('scheme', $parsed)) $uri = $parsed['scheme'] ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
  if(array_key_exists('user', $parsed)) $uri .= $parsed['user'] ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
  if(array_key_exists('host', $parsed)) $uri .= $parsed['host'] ? $parsed['host'] : '';
  if(array_key_exists('port', $parsed)) $uri .= $parsed['port'] ? ':'.$parsed['port'] : '';
  if(array_key_exists('path', $parsed)) $uri .= $parsed['path'] ? $parsed['path'] : '';
  if(array_key_exists('query', $parsed)) $uri .= $parsed['query'] ? '?'.$parsed['query'] : '';
  if(array_key_exists('fragment', $parsed)) $uri .= $parsed['fragment'] ? '#'.$parsed['fragment'] : '';
  return $uri;
}

function significant($num, $digits=2) {
    $multiplier = pow(10, intval(log10($num)) - $digits + 1);
    return round($num / $multiplier, 0) * $multiplier;
}
?>