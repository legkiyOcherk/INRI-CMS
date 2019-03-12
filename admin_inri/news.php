<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php');
require_once('../vendors/phpmorphy/phpmorphy_init.php'); // Морфология


function get_phpmorphy($descr_str) {
    global $morphy;
    
    $descr_str = strip_tags($descr_str);
    
    $descrs = str_word_count($descr_str, 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
    $orm_search = '';
    foreach($descrs as $descr){
      
      $des = mb_strtoupper($descr, 'UTF-8');
      //echo "des = $des<br>";
      $collection = $morphy->findWord($des);
      if(false === $collection) { 
        #echo $des, " NOT FOUND\n<br>";
        $orm_search .= $des." ";
        continue;
      } else {
        
      }
    
      foreach($collection as $paradigm) {
        #echo "lemma: ", $paradigm[0]->getWord(), "\n<br>";
        $orm_search .= $paradigm[0]->getWord()." ";
        break;
      }
    }
    
    $orm_search = trim($orm_search);
    
    return $orm_search;
}

class News extends Carusel{  
  
  function star_check(){
    
    if (!isset($_POST['id']) or !intval($_POST['id']) or !$_POST['field']) return;
		
    $fields = array('hide', 'flShowMine', 'fl_show_mine', 'fl_mine_slider');
		$id = intval($_POST['id']);
		$field = str_replace(' ', '', $_POST['field']);
		if (array_search($field, $fields) === false) return;
     
		$q = $this->pdo->query("SELECT `$field` FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = $id");
    $r = $q->fetch();
    $state = $r[$field];
    
    $new_state = ($state == 1) ? 0 : 1;

    $sql = "
      UPDATE `".$this->prefix.$this->carusel_name."` 
      SET `$field`=:$field
      WHERE `".$this->prefix.$this->carusel_name."`.`id` = $id
    ";
    $values = array($field=>$new_state);
    
    $stm = $this->pdo->prepare($sql);
    $res = $stm->execute($values);
		
    if (!$res) return;
		
    echo $new_state;
    
  }
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 50px;">На главной</td>
            <td style="width: 115px;">Дата</td>
            <td style="width: 60px;">Картинка</td>
      		  <td>Название</td>
      		  <td style="width: 80px">Действие</td>
          </tr>';
    
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
          <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>
            
          <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($fl_show_mine).'" id="fl_show_mine_'.$id.'"></div></td>';
            
    $date_str = '';
    if($date){
      $dArr = explode("-", $date);
      $year = $dArr[0];
      $day = $dArr[2];
      $month = $dArr[1];
      switch($dArr[1]){      
        case "01": $month = ' января ';  break;       
        case "02": $month = ' февраля '; break;       
        case "03": $month = ' марта ';   break;       
        case "04": $month = ' апреля ';  break;       
        case "05": $month = ' мая ';     break;       
        case "06": $month = ' июня ';    break;       
        case "07": $month = ' июля ';    break;       
        case "08": $month = ' августа '; break;       
        case "09": $month = ' сентября ';break;       
        case "10": $month = ' октября '; break;       
        case "11": $month = ' ноября ';  break;       
        case "12": $month = ' декобря '; break; 
      }
      
      $date_str = $day.' '.$month.' '.$year;
    }
          
    $output .= '
            <td>'.$date_str.'</td>';
    $output .= '
            <td style="max-width: 60px;">';
            
    if($img){
      $output .= '
            <div class="zoomImg" ><img style="width:50px;" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>        ';
    }elseif($color){
      $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">';
    }
    $output .= '
            </td>
        	  
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать"><b>'.$title.'</b> '.$longtxt1.'</a>
            </td>';
            
    $output .= '
        	  <td style="" class="img-act">
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать">
                <i class="fa fa-pencil"></i>
              </a>
              
              <span >
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="fa fa-trash-o"></i>
              </span>
            </td>
  			  </tr>
  			  </tr>';
    
    return $output;
  }

    function show_table(){
    $output = "";
   
    $output .= $this->getFormStyleAndScript(); 
    
    $header = '<h1>'.$this->header.'</h1>';
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    #$this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php'; 
    $this->title  = ucfirst_utf8($this->header);
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
    ";
    
    $q = $this->pdo->query($s);
    $r = $q->fetch();
    $count_items = $r['count'];

    $s_filter = $s_sorting = $s_limit = $strPager = $groupOperationsCont = '';
    $s_order = " ORDER BY `date` DESC ";
    
    if(!$count_items) $output .= "<p>Раздел пуст</p>";
    if($this->is_filter &&  $count_items) $output .= $this->getFilterTable($s_filter);
    if( $count_items) $groupOperationsCont = $this->getGroupOperations();
    if($this->is_pager && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
   
    $output .= $strPager;
    
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      $s_filter
      $s_sorting
      $s_order
      $s_limit
    ";
    #echo $s;
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">
    ';
    if($q = $this->pdo->query($s))
      if($q->rowCount()){
        
        
    #if($items){
      
      $output .= '
  	    <table id="sortabler" class="table sortab table-condensed table-striped ">
          '.$this->show_table_header_rows();
      
      while($item = $q->fetch()){
        
        $output .= $this->show_table_rows($item);

        
      }
      
      $output .= '
        </table>
      ';
      
      
    }
    $output .= $groupOperationsCont;
    $output .= '
    <br>
  	<center><a class="btn btn-success " href="?adds" id="submit">Добавить</a></center>
    </form>';

    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
  
  function show_form($item = null, $output = '', $id = null){ 
    
    $output .= '<div class = "c_form_box">';
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }  
      
      $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']));
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3", "longtxt4"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      if( in_array($key, array("datetime"))) $type = 'datetime';
      
      // Вспомогательные поля для храниения поискового индекса
      if(($key == 'orm_search_name') || ($key == 'orm_search')) continue;
      
      // Отступы SEO
      if($key == 'seo_h1'){
        if($is_open_panel_div){
          $output .= '
            </div>
          </div>  
          ';
        }
        $output .= ' 
          <div class="panel panel-default"> 
            <div class="panel-heading"> <h3 class="panel-title">SEO</h3> </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;  
      }
      
      if($key == 'img_alt') {
        if($is_open_panel_div){
          $output .= '
            </div>
          </div>  
          ';
        }
        $output .= ' 
          <div class="panel panel-default"> 
            <div class="panel-heading"> <h3 class="panel-title">Атрибуты основого изображения</h3> </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;         
      }
      if($item){
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'" >'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="">'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50></textarea>'
          );
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false;
      $output .= '
        </div>
      </div>
      ';
    }
    
    $output .= $this->getFormPicture($id, $item); 
    
    return $output;
    
  }
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
}

$date_arr = array(
    'title'            => 'Название',
    'date'             => 'Дата',
    'longtxt1'         => 'Краткий текст',
    'longtxt2'         => 'Полный текст (для отдельной страницы)',
    'fl_show_mine'     => 'На главной',

    'orm_search_name'  => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'       => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса    
    'seo_h1'           => 'SEO H1',
    'seo_title'        => 'SEO Title',
    'seo_description'  => 'SEO Description',
    'seo_keywords'     => 'SEO Keywords',
    'img_alt'          => 'Alt изображение',
    'img_title'        => 'Title изображение', 
  );
$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'date', 'longtxt1', 'longtxt2');

$carisel = new News('news', $date_arr, false, false, $pager);

$carisel->setHeader('Новости');
$carisel->setIsUrl(true);
$carisel->setIsImages(true);
$carisel->setIsFiles(true);
$carisel->setIsPager(true);
$carisel->setIsFilter(true);
$carisel->setIsLog(true);
$carisel->setFilterField($arrfilterfield); 

$carisel->setImg_ideal_width(750);  
$carisel->setImg_ideal_height(750); 
#$carisel->setDate_arr($date_arr);

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}