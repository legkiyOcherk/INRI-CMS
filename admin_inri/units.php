<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php');

class Units extends Carusel{
  
}

$date_arr = array(
    'title' => 'Название',
    'reduction' => 'Сокращение',
    #'link' => 'Ссылка',
    'longtxt1' => 'Описание',
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение',
  );

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'reduction', 'longtxt1');

$carisel = new Units('units', $date_arr, false, false, $pager);

$carisel->setHeader('Ед. измерения');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsFiles(false);
$carisel->setIsPager(true);
$carisel->setIsFilter(false);
$carisel->setIsLog(true);
$carisel->setFilterField($arrfilterfield); 
$carisel->setImg_ideal_width(750);  
$carisel->setImg_ideal_height(750); 

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
