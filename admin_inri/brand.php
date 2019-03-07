<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once(NX_PATH.'iladmin/lib/class.Carusel.php');
require_once(NX_PATH.'iladmin/lib/class.Image.php');

class Brand extends Carusel{
  
}

$date_arr = array(
    'title' => 'Название',
    'link' => 'Ссылка',
    'longtxt1' => 'Краткий текст',
    'longtxt2' => 'Полный текст (для отдельной страницы)',
    'seo_title' => 'SEO Title',
    'seo_description' => 'SEO Description',
    'seo_keywords' => 'SEO Keywords',
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение',
  );

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'longtxt1', 'longtxt2');

$carisel = new Brand('brand', $date_arr, true, true, $pager);

$carisel->setHeader('Бренд');
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
