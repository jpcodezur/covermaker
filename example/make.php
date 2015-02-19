<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_USER_NOTICE);
ini_set('display_errors','On');
ini_set('display_startup_errors', true);
set_time_limit(60);

require_once "../canvas.php";
require_once '../cls/simple_html_dom.php';
require_once '../cls/GoogleImages.php';


$gi = new GoogleImages();

if(!isset($_POST["pelicula"])){
    $name = "SpongeBob";
}else{
    $name = $_POST["pelicula"];
}

$name = str_replace(" ", "+", $name);

$results = $gi->getFront(/*"Fifty Shades of Grey"*//*"Malefica"*/$name);
$resultsBack = $gi->getBack(/*"Fifty Shades of Grey"*//*"Malefica"*/$name);

if(!$results || !$resultsBack){
    echo "<div>No se encontraron peliculas</div>";
    echo "<a href='/canvas/example'>Volver</a>";
    die();
}

if(!isset($results["url"]) || !isset($resultsBack["url"])){
    echo "<div>No se encontraron peliculas</div>";
    echo "<a href='/canvas/example'>Volver</a>";
    die();
}

if(!$results["url"] || !$resultsBack["url"]){
    echo "<div>No se encontraron peliculas</div>";
    echo "<a href='/canvas/example'>Volver</a>";
    die();
}

$file = $results["url"];
$fileBack = $resultsBack["url"];

$url = $file;
$img = '/temp/temp.jpg';
$res = file_put_contents($img, file_get_contents($url));

$urlBack = $fileBack;
$imgBack = '/temp/tempBack.jpg';
$resBack = file_put_contents($imgBack, file_get_contents($urlBack));

//$canvas = new canvas('/temp/temp.jpg');
$canvas = new canvas('../templates/basico.jpg');

/* $image_name - Name of the image which is uploaded
      $new_width - Width of the resized photo (maximum)
      $new_height - Height of the resized photo (maximum)
      $uploadDir - Directory of the original image
      $moveToDir - Directory to save the resized image */

$backNew = $gi->createThumbnail("temp.jpg", "1530", "2175", "/temp/", "../temp_resize/");
$logoLomo = $gi->createThumbnail("temp.jpg", "136", "216", "/temp/", "../temp_logo_lomo/");
$backCoverNew = $gi->createThumbnail("tempBack.jpg", "1708", "2175", "/temp/", "../temp_resize/");
// merge($image, $position, $alpha = 100, $positionTrue=null)

$canvas->set_rgb('#df0d32')
       ->merge("../temp_resize/temp.jpg", array("right", "bottom"))
       ->merge("../templates/blanck_back.jpg", array("left", "bottom"))
       ->merge("../temp_resize/tempBack.jpg", array("left", "bottom"))
       //->filter("blur", 23)
       ->merge("../temp_logo_lomo/temp.jpg", array("left", "top"),100,array(1553,262))
       ->text("Esto es un texto de prueba", 
               array(
                "color" => "#fff",
                "size" => 36,
                "x" => 100,
                "y" => 1700,
                "truetype" => true,
                "font" => "../fonts/SteelTongs.ttf"))
       //->resize("1530", "2175", "crop")
       ->merge("../templates/movie_simple.png", array("left", "top"));

$saveName = str_replace("+", "_", $name);       
$canvas->save('../save/movie_'.$saveName.'.jpg');

$canvas->show();


//$canvas = new canvas('../templates/movie_'.$saveName.'.jpg');

/*$canvas
        ->merge('/temp/temp.jpg', array("right"))
        ->show();*/

/*
 * Posibles errores
 * Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 32396 bytes) in C:\xampp\htdocs\canvas\cls\GoogleImages.php on line 248
 **/