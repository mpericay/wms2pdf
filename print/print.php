<?php
//************************************************************************
//
// OGC Client
// ============================================
//
// Copyright (c) 2008 by Geodata Sistemas S.L.
// http://www.geodata.es
//
// Info output parser file
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
//************************************************************************

//ini_set('display_errors','on');
//error_reporting(E_ALL);

require_once("lib/functions.php");
require_once("lib/class.imgmerger.php");
require_once("lib/class.ezpdf.php");

//look for enviroment variable or use /tmp
//if(isset($_ENV["TEMP"])) $tmpdir = $_ENV["TEMP"];
//if (!$tmpdir) if(isset($_ENV["TMP"])) $tmpdir = $_ENV["TMP"];
if (!$tmpdir) $tmpdir = "C:\Windows\Temp\wms2pdf\\";

// Output buffer
$buffer = Array();

// reset feature count
$count = 0;


//delete the old data
//_clearCache($tmpdir);

//force download the PDF
if (isset($_REQUEST["pdfUrl"])) {
    _forceDownload($tmpdir,$_REQUEST["pdfUrl"]);
    die();
}

// nothing recieved?
if (isset($_REQUEST['printData'])) {

    $content=str_replace("\\", "", $_REQUEST['printData']);
    $printData = json_decode($content);
    $printData = $printData->map;
    _createpdf();
}else {
    $result = array("error" => "-1");
    print_r(json_encode($result));
    die();
}

function _createpdf() {

    global $printData;
    global $tmpdir;

    $result = array();

    // get layers and print images

    $servers = $printData->servers;
 
    $title = $printData->title;
    $fixed_scale = (isset($_REQUEST['fixedscale'])) ? $_REQUEST['fixedscale'] : false;

    // layout parameters
    $page_factor = 3;
    $page_margin = 30;
    $page_height = 535;
    $page_width = 782;
    $scalebar_width = 100;
    $scalebar_height = 20;
    $padding = 10;
    $margin = 5;
    $legend_min_width = $printData->printoptions->legendwidth;
    //print_r($printData->printoptions->legendwidth);print_r("<br>");


    ////////////////////////////////////////////
    $services = Array();

    // gets 'services' as url from servers
    for ($i=0; $i<count($servers); $i++) {
        $currentServer = $servers[$i];
        $url = $currentServer->url;
        $services[]=$url;
    }

    // check GD support
    $im = new ImageMerger($tmpdir);

    if ($im->ready) {
    // get smallest image size
        $tmp = 0;
        $iconn = 0;
        $first = true;
        $maxsize = $printData->maxsize;

        for ($i=0; $i < count($services); $i++) {
            if ($tmp = get_url_parameter($services[$i], "maxsize")) {
                if ($maxsize > $tmp) {
                    $maxsize = $tmp;
                    $iconn = $i;
                }
                $first = false;
            }
        }

        // get images size
        $map_image_width = get_url_parameter($services[$iconn], "WIDTH");
        $map_image_height = get_url_parameter($services[$iconn], "HEIGHT");

        $image_factor_width = ($page_width - $page_margin - $legend_min_width - $margin) / $map_image_width;
        $image_factor_height = $page_height / $map_image_height;

        if($map_image_height<= $map_image_width) {
            $image_factor = min($image_factor_width, $image_factor_height);
        }else {
            $image_factor = max($image_factor_width, $image_factor_height);
            $image_factor= min(1.18,$image_factor); // more than 1.18 should make a memory overflow on server
        }

        $map_image_width *= $image_factor;
        $map_image_height *= $image_factor;

        // get coordinates
        $bbox = split(",", get_url_parameter($services[0], "BBOX"));

        if ($fixed_scale) {
            $cenX = ($bbox[0]+$bbox[2])/2;
            $cenY = ($bbox[1]+$bbox[3])/2;
            $difX = (($fixed_scale/1000)*($map_image_width/$page_factor))/2;
            $difY = (($fixed_scale/1000)*($map_image_height/$page_factor))/2;

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
        $map_window_ratio = $map_image_width / $page_height;
        $bbox_h = $bbox_w / $map_window_ratio;

        $bbox[0] = $bbox_cx-$bbox_w;
        $bbox[1] = $bbox_cy-$bbox_h;
        $bbox[2] = $bbox_cx+$bbox_w;
        $bbox[3] = $bbox_cy+$bbox_h;

        $sbbox = join(",",$bbox);
        $map_image_height = $map_image_width / $map_window_ratio;

        // load resized images
        $qf1 = $maxsize / $map_image_width;
        $qf2 = $maxsize / $map_image_height;
        $qf3 = $printData->printoptions->qfactor;

        if ($qf3) {
            $qf = min($qf1,$qf2,$qf3);
        } else {
            $qf = min($qf1,$qf2);
        }

        for ($i=0; $i < count($services); $i++) {

            $image = $services[$i];

            if ($servers[$i]->maxSize) {
            //create the grid if there is a maxSize param defined
                $totalWidth = $map_image_width * $qf;
                $totalHeight = $map_image_height * $qf;
                $gridCellsWidth = ceil($totalWidth / $servers[$i]->maxSize);
                $gridCellsHeight = ceil($totalHeight / $servers[$i]->maxSize);
                $cells = max($gridCellsWidth,$gridCellsHeight);

                $accumulateBBOXX = 0;

                //first the X part of the GRID
                for ($x = 0; $x < $cells; $x++ ) {
                    $accumulateBBOXY = 0;
                    for ($y = 0; $y < $cells; $y++) {

                    //recalculate the BBOX
                    //get the bbox distance x and y
                        $bboxWidth = $bbox[2] - $bbox[0];
                        $bboxHeight = $bbox[3] - $bbox[1];

                        $cellWidth = $servers[$i]->maxSize;
                        $cellHeight = $servers[$i]->maxSize;
                        //recalculate the size of the cells if they are the last
                        if (($x+1) * $cellWidth > $totalWidth) $cellWidth = ($totalWidth - $cellWidth * ($x));
                        if (($y+1) * $cellHeight > $totalHeight) $cellHeight = ($totalHeight - $cellHeight * ($y));

                        //apply Cross-multiplication to get the new values of the BBOX
                        $gridBboxWidth = $bboxWidth * $cellWidth / $totalWidth;
                        $gridBboxHeight = $bboxHeight * $cellHeight / $totalHeight;


                        $cellBBOX = $bbox;
                        $cellBBOX[0] = $bbox[0] + $accumulateBBOXX;
                        $cellBBOX[2] = $bbox[0] + $accumulateBBOXX + $gridBboxWidth;
                        $cellBBOX[1] = $bbox[3] - ($accumulateBBOXY + $gridBboxHeight);
                        $cellBBOX[3] = $bbox[3] - ($accumulateBBOXY);

                        $accumulateBBOXY += $gridBboxHeight;

                        $cellsbbox = join(",",$cellBBOX);

                        $image = set_url_parameter($image, "WIDTH", (int) ($cellWidth ));
                        $image = set_url_parameter($image, "HEIGHT", (int) ($cellHeight ));

                        $image = set_url_parameter($image, "BBOX", $cellsbbox);
                        $im->load_image($image,$x*$servers[$i]->maxSize,$y*$servers[$i]->maxSize,$totalWidth,$totalHeight);
                    }
                    $accumulateBBOXX += $gridBboxWidth;
                }

            } else {
				$image = set_url_parameter($image, "WIDTH", ($map_image_width * $qf ));
                $image = set_url_parameter($image, "HEIGHT", ($map_image_height * $qf ));
                $image = set_url_parameter($image, "BBOX", $sbbox);
                if(strrpos($image, "barraques")>0) {
                    set_url_parameter($image, "FORMAT", "image/png");
                }

                $im->load_image($image);

            }

        }

        //print_r($printData);die();



        if ($printData->printFeature) {

            $scale_x = ($bbox[2] - $bbox[0]) / 1070;
            $scale_y = ($bbox[3] - $bbox[1]) / 1070;
            //var_dump($scale_x);die();
            $feature = $printData->printFeature;
            // transform to image coordinates
            $x = (int) ( ($feature->x - $bbox[0]) / $scale_x );
            $y = (int) ( ($bbox[3] - $feature->y) / $scale_y );
            $icon = "../img/iconPedraseca.png";
            $im->load_image($icon,$x-39,$y-34,29,49);
        }



        // get final JPG
        //var_dump($im->load_image($image));
        //if ($file = $im->get_image(90)) {
        $file = $im->get_image(90);

        // create PDF writer
        $pdf =& new Cezpdf("a4", "landscape");
        $pdf->selectFont('../fonts/Helvetica.afm');
        $pdf->ezSetMargins($page_margin, $page_margin, $page_margin, $page_margin);
        $pdf->setLineStyle(1);

        // map frame coordinates
        $map_frame_width = $map_image_width;
        $map_frame_height = $page_height;
        $map_frame_bottom = $page_margin;
        $map_frame_left = $page_margin;

        // legend frame coordinates
        $legend_frame_width = $page_width - $map_frame_left - $map_frame_width - $margin;
        $legend_frame_height = $page_height;
        $legend_frame_left = $page_width - $legend_frame_width;
        $legend_frame_bottom = $page_margin;
        $legend_frame_top = $legend_frame_bottom + $legend_frame_height;

        // map image coordinates
        $map_image_bottom = $map_frame_bottom + ($map_frame_height - $map_image_height) / 2;
        $map_image_left = $map_frame_left + ($map_frame_width - $map_image_width) / 2;

        // title
        $x = $legend_frame_left + $padding;
        $y = $legend_frame_top - $padding - $pdf->getFontHeight(18);
        $max_width = $legend_frame_width - 3 * $padding;
        //print_r($x."-".$y."-".$max_width."-".$title);die();
        $y = _addTextWrapper($pdf, $x, $y, $max_width, 14, $title, 'center');
        $legend_image_start = $y;




        // escut obpservatori
        $north_image_width = 115;
        $north_image_height = 132;
        $footerheight =($printData->printoptions->footerheight ? $printData->printoptions->footerheight : $north_image_height);
        $north_image_left = $legend_frame_left + $padding+40;
        $north_image_bottom = $legend_frame_bottom + $padding + 60;
        $pdf->addJpegFromFile("../img/op.jpg", $north_image_left, $north_image_bottom, $north_image_width, $north_image_height);
        $legend_frame_stop = $north_image_bottom + $footerheight + $padding;


        // north image
        $north_image_width = 18;
        $north_image_height = 40;
        $footerheight =($printData->printoptions->footerheight ? $printData->printoptions->footerheight : $north_image_height);
        $north_image_left = $legend_frame_left + $padding;
        $north_image_bottom = $legend_frame_bottom + $padding;
        $pdf->addJpegFromFile("../img/north2.jpg", $north_image_left, $north_image_bottom, $north_image_width, $north_image_height);
        $legend_frame_stop = $north_image_bottom + $footerheight + $padding;

        // coordinates
        $x = $north_image_left + $north_image_width + $padding;
        $y = $north_image_bottom + $footerheight - $padding;
        if (($printData->printoptions->scalebar) == "graphic")
            $y = $y + $scalebar_height;
        $w = $max_width - $north_image_width;

        if ($printData->printoptions->personaltext) {
        //print_r($printData->printoptions->personaltext);die();
            $y = _addTextWrapper($pdf, $x-20, $y+160, $w+150, 10, $printData->printoptions->personaltext, 'left');
        }
        if ($printData->printoptions->showcoordinates) {
            $text = $printData->locale->coordinates.': '.intval($bbox[0]).", ".intval($bbox[1]);
            $y = _addTextWrapper($pdf, $x, $y, $w, 8, $text, 'left');
        }
        //if ($printData->printoptions->showreferencesystem) {
        $text ='Sistema de referència  EPSG : 23031 ';
        $y = _addTextWrapper($pdf, $x, $y-170, $w, 8, $text, 'left');
        //}
        if (($printData->printoptions->scalebar) == "numeric") {
            $scale = intval(1000 * ($bbox[2]-$bbox[0]) / ($map_image_width / $page_factor));
            $text = $printData->locale->mapscale.' ~ 1:'._significant($scale,2);
            $y = _addTextWrapper($pdf, $x, $y, $w, 8, $text, 'left');
        } else if (($printData->printoptions->scalebar) == "graphic") {
                $scale = intval(1000 * ($bbox[2]-$bbox[0]) / ($map_image_width / $page_factor));
                $sign_scale = _significant($scale / $map_image_width * $scalebar_width,2);
                $scalebar_x = round($x + $w/2 - $scalebar_width/2 - $padding);

                $pdf->addPngFromFile(_create_graphic_scalebar($sign_scale,$scalebar_width,$scalebar_height),$scalebar_x,$y-$scalebar_height,$scalebar_width,$scalebar_height);
                $y = $y + $scalebar_height;
            }


        // dynamic legend
        $continue_legend = Array();
        //print_r($printData->legendstyle);die();
        if ($printData->legendstyle=="none") {
            $printData->legendstyle="embedded";
        }
        
        if (($printData->legendstyle == "embedded") || ($printData->legendstyle == "auto") || ($printData->legendstyle == "inline")) {

            $layers = Array();
            //$layers = $_REQUEST['layers'];
            $legends = Array();
            //$legends = $_REQUEST['legends'];

            for($i=0; $i<count($servers); $i++) {
                $currentServer = $servers[$i];
                for($j=0; $j < sizeof($servers[$i]->layers); $j++) {
                    $layers[]=$servers[$i]->layers[$j]->title;
                    $legends[]=$servers[$i]->layers[$j]->legend;

                }

            }

            $x = $legend_frame_left + $padding;
            $y = $legend_image_start;

            //var_dump($servers);

            //print_r(count($layers));
            for ($j=count($layers)-1; $j>=0; $j--) {
                list($legend, $legend_image_width, $legend_image_height) = _import_image($legends[$j]);

                if ($legend) {

                // correct sizes
                    $legend_image_width = (int) ($legend_image_width * $printData->printoptions->legendqfactor);
                    $legend_image_height = (int) ($legend_image_height * $printData->printoptions->legendqfactor);

                    // check height
                    $dump = true;
                    $max_height = $y - $legend_frame_stop - 3 * $pdf->getFontHeight(10);
                    if ($max_height < $legend_image_height) {
                    // if first layer then make it fit
                        if ($j == count($layers)-1) {
                            $legend_image_width = $legend_image_width * ($max_height / $legend_image_height);
                            $legend_image_height = $max_height;
                        // else skip it
                        } else {
                            $dump = false;
                        }
                    }

                    // dump it
                    if ($dump) {

                    // check width
                        if ($legend_image_width > $max_width) {
                            $legend_image_height = $legend_image_height * ($max_width / $legend_image_width);
                            $legend_image_width = $max_width;
                        }

                        if ($map->legend_style == "inline") {

                        // title
                            _addTextWrapper($pdf, $x + $legend_image_width + (int) ($padding / 2), $y - (int) ($legend_image_height / 2), $max_width, 10, $layers[$j], 'left');

                            // image
                            $pdf->addJpegFromFile($legend, $x, $y - $legend_image_height, $legend_image_width, $legend_image_height);
                            $y -= ($legend_image_height + (int) ($padding / 2));

                        } else {

                        // title
                            $y = _addTextWrapper($pdf, $x, $y, $max_width, 10, $layers[$j], 'left');

                            // abstract
                            if ($abstexts[$j] != "") $y = _addTextWrapper($pdf, $x, $y, $max_width, 8, $abstexts[$j], 'left');

                            // image
                            $y += (int) ($padding / 3);
                            $pdf->addJpegFromFile($legend, $x, $y - $legend_image_height, $legend_image_width, $legend_image_height);
                            $y -= ($legend_image_height + (int) (3*$padding/2));

                        }

                        // destroy
                        @unlink($legend);

                    } else {

                    // add to legend array
                        array_push ($continue_legend, Array($layers[$j], $abstexts[$j], $legend, $legend_image_width, $legend_image_height, false));

                    }
                }
            }

        }

        // static legend
        if ($printData->legendstyle == "nomal") {

            $legend = $printData->legend->file;

            list($legend, $legend_image_width, $legend_image_height) = _import_image($legend);

            if ($legend) {

            // check legend don't oversize
                $legend_image_maxheight = $legend_image_start - $legend_frame_stop;
                if ($legend_image_maxheight < $legend_image_height) {
                    $legend_image_width = $legend_image_width * ($legend_image_maxheight / $legend_image_height);
                    $legend_image_height = $legend_image_maxheight;
                }
                if ($legend_frame_width < $legend_image_width) {
                    $legend_image_height = $legend_image_height * ($legend_frame_width / $legend_image_width);
                    $legend_image_width = $legend_frame_width;
                }

                $legend_image_bottom = $legend_image_start - $legend_image_height;
                $legend_image_left = $legend_frame_left + ($legend_frame_width - $legend_image_width) / 2;
                $pdf->addJpegFromFile($legend, $legend_image_left, $legend_image_bottom, $legend_image_width, $legend_image_height);

                @unlink($legend);

            }

        }

        // output elements
        $pdf->addJpegFromFile($file, $map_image_left, $map_image_bottom, $map_image_width, $map_image_height);
        @unlink($file);
        $pdf->rectangle($map_frame_left, $map_frame_bottom, $map_frame_width, $map_frame_height);
        $pdf->rectangle($legend_frame_left, $legend_frame_bottom, $legend_frame_width, $legend_frame_height);

        // continue legend
        if (count($continue_legend)>0) {

            $legend_frame_height = $page_height;
            $legend_frame_width = (int) (($page_width - 2 * $margin - $page_margin) / 3);
            $legend_frame_bottom = $page_margin;
            $legend_frame_top = $legend_frame_bottom + $legend_frame_height;
            $legend_frame_left = $page_margin;

            $legends_left = count($continue_legend);
            $column = -1;
            $max_width = $legend_frame_width - 3 * $padding;

            while ($legends_left > 0) {

                $column++;
                if (fmod($column, 3) == 0) {
                    $column = 0;
                    $pdf->ezNewPage();
                    for ($i=0; $i<3; $i++)
                        $pdf->rectangle($legend_frame_left + $i * ($legend_frame_width + $margin),
                            $legend_frame_bottom, $legend_frame_width, $legend_frame_height);
                }

                $row = 0;
                $x = (int) ($legend_frame_left + $column * ($legend_frame_width + $margin) + $padding);
                $y = (int) ($legend_frame_top - $padding - $pdf->getFontHeight(10));

                $legends_left = 0;
                reset($continue_legend);

                for ($i=0; $i<count($continue_legend); $i++) {

                // get layer legend
                    $legend =& $continue_legend[$i];
                    list($title, $abstract, $image, $width, $height, $dumped) = $legend;

                    // check if legend has been already dumped
                    if (!$dumped) {

                    // check if legend fits into available height
                        $max_height = (int) ($y - $legend_frame_bottom - 3 * $pdf->getFontHeight(10));

                        if ($width > $max_width) {
                            $height = $height * ($max_width / $width);
                            $width = $max_width;
                        }

                        if ($height > $max_height) {

                        // make it fit if it is the first legend of the columns
                            if ($row==0) {

                                $width = $width * ($max_height / $height);
                                $height = $max_height;

                            // otherwise skip it by now
                            } else {

                                $legends_left++;
                                continue;

                            }
                        }

                        // title
                        $y = _addTextWrapper($pdf, $x, $y, $max_width, 10, $title, 'left');

                        // abstract
                        if ($abstract != "") $y = _addTextWrapper($pdf, $x, $y, $max_width, 8, $abstract, 'left');

                        // image
                        $y += (int) ($padding / 3);
                        $pdf->addJpegFromFile($image, $x, $y - $height, $width, $height);
                        $y -= ($height + (int) (3*$padding/2));
                        @unlink($image);

                        // add legend row
                        $row++;

                        // mark legend as already dumped
                        $legend[5] = true;

                    }
                }
            }
        }
        $data = $pdf->ezOutput();

        //write file in the tmp dir
        $pdfFileName = "pdfPrint".gmdate('YmdHis').".pdf";
        $pdfFile = $tmpdir.$pdfFileName;

        if (!$file = fopen($pdfFile, 'a')) {
            echo "Error creating the file ($pdfFileName).";
            $result["error"] = "-2";
        } else {

        // Escribir $contenido a nuestro arcivo abierto.
            if (fwrite($file, $data) === FALSE) {
                echo "Error writing in the file ($pdfFileName)";
                $result["error"] = "-3";
            } else {

                fclose($file);
                $result["url"] = $pdfFileName;
                $result["error"] = "0";
            }
        }

/*
        }else{
            $result["error"] = "-4";
        }
*/
        print_r(json_encode($result));
        die();
    }

}

function _import_image($file) {

    global $tmpdir;

    if (list($width, $height, $type, $atr) = @getimagesize($file)) {

    // get image
        if ($type == 1) { // GIF
            $tmp = imagecreatefromgif($file);
        } else if ($type == 2) { // JPEG
                $tmp = imagecreatefromjpeg($file);
            } else if ($type == 3) { // PNG
                    $tmp = imagecreatefrompng($file);
                } else {
                    $tmp = false;
                }

        if ($tmp) {

        // create local copy
            $file = secure_tmpname('.jpg','tmp',$tmpdir);
            if (!imagejpeg($tmp, $file, 100)) $file="";
            imagedestroy($tmp);

            return Array($file, $width, $height);

        }

    }

    return false;

}  

function _reference_system($srs) {

    $datums = Array(
        "EPSG:4326" => Array("GCS", "WGS84"),
        "EPSG:326" => Array("UTM", "WGS84"),
        "EPSG:4230" => Array("GCS", "ED50"),
        "EPSG:230" => Array("UTM", "ED50")
    );

    // find datum
    $srs = strtoupper($srs);
    while (list($datum, $params) = each($datums)) {
        if (strpos($srs, $datum) === 0) {
            $output = $params[1].", ".$params[0];
            if ($params[0] == "UTM") $output .= substr($srs, -2);
            return $output;
        }
    }

}

function _get_file_type($file) {
    $pos = strrpos($file,".")+1;
    $type = substr($file,$pos,strlen($file)-$pos);
    switch($type) {
        case "png":
            return "png";
        case "gif":
            return "gif";
        case "jpg":
        case "jpeg":
            return "jpg";
    }
}

function _create_refmap_image($refmap_file,$refmap_bbox,$view_bbox,$output_format) {
    global $tmpdir;

    $file = $refmap_file;

    $type = _get_file_type($file);

    $refmap_bbox = explode(",",$refmap_bbox);
    $view_bbox = explode(",",$view_bbox);

    $view_minx = $view_bbox[0] - $refmap_bbox[0];
    $view_miny = $view_bbox[3] - $refmap_bbox[3];

    $view_maxx = $view_bbox[2] - $refmap_bbox[0];
    $view_maxy = $view_bbox[1] - $refmap_bbox[3];

    $distx_refmap = $refmap_bbox[2] - $refmap_bbox[0];
    $disty_refmap = $refmap_bbox[1] - $refmap_bbox[3];

    list($width, $height) = getimagesize($file);

    switch($type) {
        case "gif":
            $tmp = @imagecreatefromgif($file);
            break;
        case "jpg":
            $tmp = @imagecreatefromjpeg($file);
            break;
        case "png":
            $tmp = @imagecreatefrompng($file);
            break;
    }



    if ($tmp) {
    //add the rectangle of the view
        $minx = $view_minx * $width / $distx_refmap;
        $miny = $height - $view_miny * $height / $disty_refmap;
        $maxx = $view_maxx * $width / $distx_refmap;
        $maxy = $height - $view_maxy * $height / $disty_refmap;

        $color = imagecolorallocate($tmp, 255, 0, 0);
        imagerectangle($tmp,$minx,$miny,$maxx,$maxy,$color);

        // create local copy
        switch($output_format) {
            case "png":
                $file = secure_tmpname('.png','tmp',$tmpdir);
                if (!imagepng($tmp, $file)) $file="";
                imagedestroy($tmp);
                break;
            case "jpg":
                $file = secure_tmpname('.jpg','tmp',$tmpdir);
                if (!imagejpeg($tmp, $file)) $file="";
                imagedestroy($tmp);
                break;
        }

        return $file;

    }
}


function _create_graphic_scalebar($scale,$scalebar_width,$scalebar_height) {
    global $tmpdir;

    $tmp = @imagecreate($scalebar_width, $scalebar_height);
    $background_color = imagecolorallocate($tmp, 254, 254, 254);
    $color = imagecolorallocate($tmp, 0, 0, 0);
    $font = 'fonts/arial.ttf';
    $font_size = 6;

    imagettftext($tmp, $font_size, 0, 0, $scalebar_height/2-$font_size/2, $color, $font, '0');
    imagettftext($tmp, $font_size, 0, round($scalebar_width/2)-ceil(log10($scale/2))*$font_size/2, $scalebar_height/2-$font_size/2, $color, $font, round($scale/2));
    imagettftext($tmp, $font_size, 0, $scalebar_width-ceil(log10($scale))*$font_size+6, $scalebar_height/2-$font_size/2, $color, $font, $scale);

    imagefilledrectangle($tmp,1,$scalebar_height-9,$scalebar_width-2,$scalebar_height-2,$color);
    imagefilledrectangle($tmp,2,$scalebar_height-5,$scalebar_width/2,$scalebar_height-3,$background_color);
    imagefilledrectangle($tmp,$scalebar_width/2+1,$scalebar_height-8,$scalebar_width-3,$scalebar_height-6,$background_color);

    imagefilledrectangle($tmp,1,$scalebar_height-12,2,$scalebar_height-2,$color);
    imagefilledrectangle($tmp,$scalebar_width/2-1,$scalebar_height-12,$scalebar_width/2,$scalebar_height-2,$color);
    imagefilledrectangle($tmp,$scalebar_width-1,$scalebar_height-12,$scalebar_width-2,$scalebar_height-2,$color);

    if ($tmp) {
    // create local copy
        $file = secure_tmpname('.png','tmp',$tmpdir);
        if (!imagepng($tmp, $file)) $file="";
        imagedestroy($tmp);

        return $file;

    }
}

function _addTextWrapper($pdf, $x, $y, $width, $size, $text, $justification='left') {

// parse text for multiple lines


//Geodata specific decoding, because the viewer is in UTF8

//Enhanment, the profile has to habe the coding for the text.
    $text = utf8_decode($text);
    $text = explode("\n", $text);

    // get text height
    $height = $pdf->getFontHeight($size);

    // output each element of the array
    for ($i=0; $i<count($text); $i++) {

    // get text row
        $row = $text[$i];

        // pretty text
        $row = _pretty_text($row);

        // output rows of text until it ends
        while ($row) {
            $row = $pdf->addTextWrap($x, $y, $width, $size, $row, $justification, 0);
            $y -= $height;
        }

    }

    return $y;

}

function _pretty_text($text) {
    $text = html_entity_decode($text);
    $from = Array("\'");
    $to = Array("'");
    return str_replace($from, $to, $text);
}

function _significant($num, $digits=2) {
    $multiplier = pow(10, intval(log10($num)) - $digits + 1);
    return round($num / $multiplier, 0) * $multiplier;
}


function _clearCache($dir) {
    $limitHour = time() - 3 * 60 * 60;
    if (is_dir($dir)) {
        if(!$dh = @opendir($dir)) return false;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            if (is_dir($dir.$obj)) continue;
            $info = pathinfo($dir.$obj);
            if ($info["extension"] != "pdf") continue;
            if (filemtime($dir."/".$obj) < $limitHour) {
                if (!@unlink($dir.'/'.$obj)) return false;
            }
        }
    } return true;
}

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












?>