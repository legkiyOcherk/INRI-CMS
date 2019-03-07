<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once(NX_PATH.'iladmin/lib/class.Carusel.php');
require_once(NX_PATH.'iladmin/lib/class.Image.php');

class Availability extends Carusel{
  
}

$date_arr = array(
    'title' => 'Название',
    #'img_alt' => 'Alt изображение',
    #'img_title' => 'Title изображение',
  );

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title');

$carisel = new Availability('availability', $date_arr, true, true, $pager);

$carisel->setHeader('Варианты наличия');
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
