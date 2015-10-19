<?php

class GoogleImages {

    //google gives 4 images per request
    private $count = 4;
    //enter your key here
    private $key = 'your-key-here';

    private function multi_curl($urls) {
        // for curl handlers
        $curl_handlers = array();
        $images = array();

        //for storing contents
        $content = array();
        //setting curl handlers
        foreach ($urls as $url) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $curl_handlers[] = $curl;
        }
        //initiating multi handler
        $multi_curl_handler = curl_multi_init();

        // adding all the single handler to a multi handler
        foreach ($curl_handlers as $key => $curl) {
            curl_multi_add_handle($multi_curl_handler, $curl);
        }

        // executing the multi handler
        do {
            $multi_curl = curl_multi_exec($multi_curl_handler, $active);
        } while ($multi_curl == CURLM_CALL_MULTI_PERFORM || $active);

        foreach ($curl_handlers as $curl) {
            //checking for errors
            if (curl_errno($curl) == CURLE_OK) {
                //if no error then getting content
                $content = curl_multi_getcontent($curl);
                $result = json_decode($content, true);
                foreach ($result['responseData']['results'] as $img) {
                    $images[] = $img;
                }
            } else {
                $images[] = curl_error($curl);
            }
        }
        curl_multi_close($multi_curl_handler);
        return $images;
    }

    private function output($images, $cols = 4, $rows = 5) {
        //creating table
        echo "<table border='1'>";
        for ($i = 0; $i < $rows; $i++) {
            //outputting text with search criteries found
            echo "<tr>";
            for ($j = 0; $j < $cols; $j++) {
                echo "<td>" . $images[($i * $this->count) + $j]['content'] . "</td>";
            }
            echo "</tr>";
            //outputting thumbnail with link to real size image
            echo "<tr>";
            for ($j = 0; $j < $cols; $j++) {
                echo "<td><a href='" . $images[($i * $this->count) + $j]['url'] .
                "' target='blank'><img src='" . $images[($i * $this->count) + $j]['tbUrl'] .
                "' /></a></td>";
            }
            echo "</tr>";
            //outputtin link to webpage where image is found
            echo "<tr>";
            for ($j = 0; $j < $cols; $j++) {
                echo "<td><a href='" . $images[($i * $this->count) + $j]['originalContextUrl'] .
                "' target='blank'>View page</a></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

    public function get_images($query, $cols = 4, $rows = 5) {
        //calculating amount of requests
        $requests = floor(($cols * $rows) / $this->count);
        //creating array with urls
        $urls = array();
        for ($i = 0; $i < $requests; $i++) {
            $urls[$i] = 'http://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=';
            $urls[$i] .= urlencode($query) . '&start=' . ($i * $this->count) . '&key=' . $this->key;
        }
        //performing multiple requests
        $images = $this->multi_curl($urls);
        //outputting results
        $this->output($images, $cols, $rows);
    }

    public function getFront($busqueda) {
        $url = 'http://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=' . $busqueda . "movie+cover";
        $crl = curl_init();

        curl_setopt($crl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5);

        $ret = curl_exec($crl);
        curl_close($crl);
        return $this->getBestImage($ret);
    }
    
    public function getBack($busqueda) {
        $url = 'http://ajax.googleapis.com/ajax/services/search/images?v=1.0&q=' . $busqueda . "movie+background";
        $crl = curl_init();

        curl_setopt($crl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5);

        $ret = curl_exec($crl);
        curl_close($crl);
        return $this->getBestImageLandscape($ret);
    }
    
    public function getBestImageLandscape($json) {
        $data = json_decode($json);
        if($data){
        /*$results = array();

        $maxSize = 0;
        $count = 0;*/
        $posiblesImages = array();
        foreach ($data->responseData->results as $result) {
            $s = getimagesize($result->url);
            if($s){
                if($s[0]<$s[1]){
                    $result->size = $s;
                    $posiblesImages[] = $result;
                }
            }
        }
        
        $maximo = 0;
        $count = 0;
        foreach($posiblesImages as $result){
            if($result->size[0]>$maximo){
                $maximo = $result->size[0];
            }
            $count++;
        }
        
        if($count>0){
            $count--;
        }
        
        $result = $posiblesImages[$count];
        
        return array(
                    'url' => $result->url,
                    'alt' => $result->title);
        
            /*
            $s = getimagesize(rtrim(trim($result->url)));
            if ($s[1] > $maxSize) {
                $maxSize = $s[1];
                $count++;
            }
        }
        
        if ($count > 0) {
            $count -= 1;
        }
        
        $obj = $result[$count];*/
        }
        return false;
    }
    
    public function getBestImage($json) {
        $data = json_decode($json);
        if($data){
        $results = array();

        $verticales = array();

        foreach ($data->responseData->results as $result) {
            $u = $result->url;
            $posJpg = strpos($u, ".jpg");
            $posPng = strpos($u, ".png");
            if($posJpg !== false || strpos($posPng, ".png") !== false ){
                $s = getimagesize($u);
                if($s){
                    $w = $s[0];
                    $h = $s[1];

                    if ($h > $w) {
                        $percent = $h / 100;
                        if ($h + ($percent * 20) > $w) {
                            $verticales[] = $result;
                        }
                    }

                    $results[] = array(
                        'url' => $result->url,
                        'alt' => $result->title);
                }
            }
        }

        if (isset($verticales[0])) {
            $maxSize = 0;
            $count = 0;
            foreach ($verticales as $vertical) {
                $s = getimagesize($vertical->url);
                if ($s[1] > $maxSize) {
                    $maxSize = $s[1];
                    $count++;
                }
            }

            if ($count > 0) {
                $count -= 1;
            }

            $obj = $verticales[$count];
            return array(
                'url' => $obj->url,
                'alt' => $obj->title);
        }

        return $results;
        }
        return false;
    }

    /* $image_name - Name of the image which is uploaded
      $new_width - Width of the resized photo (maximum)
      $new_height - Height of the resized photo (maximum)
      $uploadDir - Directory of the original image
      $moveToDir - Directory to save the resized image */

    public function createThumbnail($image_name, $new_width, $new_height, $uploadDir, $moveToDir) {
        $path = $uploadDir . '/' . $image_name;

        $mime = getimagesize($path);

        if ($mime['mime'] == 'image/png') {
            $src_img = imagecreatefrompng($path);
        }
        if ($mime['mime'] == 'image/jpg') {
            $src_img = imagecreatefromjpeg($path);
        }
        if ($mime['mime'] == 'image/jpeg') {
            $src_img = imagecreatefromjpeg($path);
        }
        if ($mime['mime'] == 'image/pjpeg') {
            $src_img = imagecreatefromjpeg($path);
        }

        $old_x = imageSX($src_img);
        $old_y = imageSY($src_img);

        if ($old_x > $old_y) {
            $thumb_w = $new_width;
            $thumb_h = $old_y * ($new_height / $old_x);
        }

        if ($old_x < $old_y) {
            $thumb_w = $old_x * ($new_width / $old_y);
            $thumb_h = $new_height;
        }

        if ($old_x == $old_y) {
            $thumb_w = $new_width;
            $thumb_h = $new_height;
        }

        $thumb_w = $new_width;
        $thumb_h = $new_height;
        
        $dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);


        // New save location
        $new_thumb_loc = $moveToDir . $image_name;

        if ($mime['mime'] == 'image/png') {
            $result = imagepng($dst_img, $new_thumb_loc, 8);
        }
        if ($mime['mime'] == 'image/jpg') {
            $result = imagejpeg($dst_img, $new_thumb_loc, 80);
        }
        if ($mime['mime'] == 'image/jpeg') {
            $result = imagejpeg($dst_img, $new_thumb_loc, 80);
        }
        if ($mime['mime'] == 'image/pjpeg') {
            $result = imagejpeg($dst_img, $new_thumb_loc, 80);
        }

        imagedestroy($dst_img);
        imagedestroy($src_img);

        return $result;
    }

}
