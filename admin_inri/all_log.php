<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once(NX_PATH.'iladmin/lib/class.Log.php');

$pager = array(
  'perPage' => 100,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'module', 'date', 'dump_data');

$carisel = new Log('all_log', false, $pager);

$carisel->setHeader('Лог');
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
/*
if(isset($_GET['ajx'])){
  if (isset($_SESSION["WA_USER"])){
    
    if(isset($_GET['act'])){
      if($_GET['act'] == 'star_check'){
        $carisel->star_check();
      }elseif($_GET['act'] == 'sort_item'){
        echo "sort_item";
        $carisel->sort_item();
      }elseif($_GET['act'] == 'ajx_pager'){
        echo $carisel->ajx_pager();
      }
    }

  }
  
}else{
  $output='<div style="padding:20px 0;">';
    
  if (isset($_SESSION["WA_USER"])){
    
    
    if(isset($_GET['adds'])){
      $output .= $carisel->add_slide();
    }elseif(isset($_GET['creates'])){
      $output .= $carisel->create_slide();  
    }elseif(isset($_GET["edits"])){
      $output .= $carisel->edit_slide(intval($_GET["edits"]));  
    }elseif(isset($_GET["updates"])){
      $output .= $carisel->update_slide(intval($_GET["updates"]));  
    }elseif(isset($_GET["delete_picture"])&&isset($_GET['id'])){
      $output .= $carisel->delete_picture(intval($_GET['id']));  
    }elseif(isset($_GET["deletes"])){
      $output .= $carisel->delete_slide(intval($_GET['deletes']));  
    }else{
      $output .= $carisel->show_table();
    }
 
  }else{
    $output .=  '<div style ="text-align: center;"><h3> Кончилость время ссесии <a href = "/'.ADM_DIR.'">Повторите авторизацию</a></h3>';
    
  }
  $output .= "</div>";
  
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}*/