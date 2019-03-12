<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.CatCarusel.php');
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


class Article extends CatCarusel{
  
  function show_cat_table_rows($item, $i = 0){
    $output = '';
              extract($item);
              $output .= '
                <tr class="r'.($i % 2).'" id="tr_'.$id.'" style="cursor: move;">			 
                  <td style="width: 20px;">'.$id.'<input type="hidden" value="'.$id.'" name="itCatSort[]"></td>
                  
                  <td style="width: 30px;" class="img-act"><div title="Скрыть" onclick="star_cat_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>
              	  
                  <td style="width: 50px;">
              ';
              if($img){
                $output .= '
                  <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/cat/slide/'.$img.'"></div>  
                ';
              }else if($color){
                $output .= '
                  <div class="zoomImg" style = "background-color: '.$color.'">
                ';
              }
              $output .= '
                  </td>
              	  
                  <td style="text-align: left;">
                    <a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
                        class = "btn btn-info btn-sm"
                        title = "Редактировать"
                        style = "color: #fff;">
                        <i class="fa fa-pencil"></i>
                    </a> &nbsp;
                    <a href="'.IA_URL.$this->carusel_name.'.php?c_id='.$id.'" title="редактировать"><b>'.$title.'</b></a>';
              if($link){
                $output .= '
                    <br>Ссылка: '.trim(strip_tags($link)).'</span>';
              }
              if($longtxt1){
                $output .= '
                    <br><span>'.trim(strip_tags($longtxt1)).'</span>';
              }
              $output .= '
                  </td>';

              $output .= '
              	  <td style="" class="img-act">
                    
                    <a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
                        class = "btn btn-info btn-sm"
                        title = "Редактировать">
                        <i class="fa fa-pencil"></i>
                    </a>';
/*<a href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'"><img src="..'.IA_URL.'images/icons/b_props.png" width="16" height="16" border="0"></a>&nbsp;*/                    
                    
              $val_is_cildren = $val_is_items = '';
              #$val_is_cildren = db::value('id', '`'.$this->prefix.$this->carusel_name.'_cat'.'`', "parent_id = ".$id);
              #$val_is_items = db::value('id', '`'.$this->prefix.$this->carusel_name.'`', "cat_id = ".$id);
              
              $s = "SELECT id FROM `".$this->prefix.$this->carusel_name.'_cat'."` WHERE parent_id = $id";
              $q = $this->pdo->query($s);
              if($q->rowCount()) {
                $r = $q->fetch();
                $val_is_cildren = $r['id'];
              }
              $s = "SELECT id FROM `".$this->prefix.$this->carusel_name."` WHERE cat_id = $id";
              $q = $this->pdo->query($s);
              if($q->rowCount()) {
                $r = $q->fetch();
                $val_is_items = $r['id'];
              }
              
              if(!$val_is_cildren && !$val_is_items){
                $output .= '
                    <a href="..'.IA_URL.$this->carusel_name.'.php?deletec='.$id.'" onclick="javascript: if (confirm(\'Удалить?\')) { return true;} else { return false;}"
                          class="btn btn-danger btn-sm" 
                          title="удалить" 
                          onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                      <i class="fa fa-trash-o"></i>
                    </a>
                ';
                /*<a href="..'.IA_URL.$this->carusel_name.'.php?deletec='.$id.'" onclick="javascript: if (confirm(\'Удалить?\')) { return true;} else { return false;}">
                      <img src="..'.IA_URL.'images/icons/b_drop.png" width="16" height="16" border="0">
                    </a>*/
              }
              $output .= '
                  </td>
        			  </tr>
              ';
    
    return $output;
  }
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 50px;">На главной</td>
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
            <td class="img-act"><div title="На главной" onclick="star_check('.$id.', \'fl_show_mine\')" class="star_check '.$this->getStarValStyle($fl_show_mine).'" id="fl_show_mine_'.$id.'"></div></td>
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
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'"  
                  class = "btn btn-info btn-sm"
                  title = "Редактировать"
                  style = "color: #fff;">
                  <i class="fa fa-pencil"></i>
              </a> &nbsp;
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a><br />
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$longtxt1.'</a>
              
            </td>

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
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    $i=0;
    foreach($this->date_arr as $key=>$val){
      if($key == 'is_enlarge_photos'){
        if ( isset($_POST[$key]) && $_POST[$key] ) {
          $_POST[$key] = 1;
        }else{
          $_POST[$key] = 0;
        }
      }
      ($i) ? $prefix = ', ' : $prefix = ''; $i++;
      /*if($key == 'date'){
        if (($timestamp = strtotime($_POST[$key])) === false) {
          $_POST[$key] = time();
        }else{
          $_POST[$key] = strtotime($_POST[$key]);
        }
      }*/
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      
    };
  }
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; 
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    $i=0;
    foreach($this->date_arr as $key=>$val){
      if($key == 'is_enlarge_photos'){
        if ( isset($_POST[$key]) && $_POST[$key] ) {
          $_POST[$key] = 1;
        }else{
          $_POST[$key] = 0;
        }
      }
      ($i) ? $prefix = ', ' : $prefix = ''; $i++;
      /*if($key == 'date'){
        if (($timestamp = strtotime($_POST[$key])) === false) {
          $_POST[$key] = time();
        }else{
          $_POST[$key] = strtotime($_POST[$key]);
        }
      }*/
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      
    }
    return $sql_vals;
  }
  
  function getCreateCatSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    $i=0;
      
    foreach($this->date_cat_arr as $key=>$val){
      if($key == 'is_enlarge_photos'){
        if ( isset($_POST[$key]) && $_POST[$key] ) {
          $_POST[$key] = 1;
        }else{
          $_POST[$key] = 0;
        }
      }
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function getUpdateCatSlide_SqlVals(){
    $sql_vals = ''; 
    
    if($_POST['title']){
      $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    }
    
    if($_POST['longtxt2']){
      $_POST['orm_search'] = get_phpmorphy($_POST['longtxt2']);
    }
    
    $i=0;
    
    foreach($this->date_cat_arr as $key=>$val){
      if($key == 'is_enlarge_photos'){
        if ( isset($_POST[$key]) && $_POST[$key] ) {
          $_POST[$key] = 1;
        }else{
          $_POST[$key] = 0;
        }
      }
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
  
  function show_cat_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    
    /*$output .= '
      <div class="panel panel-default"> 
        <div class="panel-heading"> 
          <h3 class="panel-title">Основное</h3>
        </div> 
        <div class="panel-body"> 
    ';*/
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix."cat_".$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      } 
        
       $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix."cat_".$this->carusel_name, $id, $item['title']));  
      
    }
    
    
       
    foreach($this->date_cat_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      
      if($key == 'is_enlarge_photos'){
        ($item && $item[$key]) ? $coldate = 'checked' : $coldate = ''; #pri($coldate);
        
        $output .= $this->show_form_row( 
          $val.$this->getErrorForKey($key), 
            '<input type="checkbox" class="col_checkbox" name="'.$key.'" '.$coldate.'>'
          );
          
        $output .= '
            <script type="text/javascript">
              $(document).ready(function(){
                $(".col_checkbox").iCheck({
                  checkboxClass: "icheckbox_flat-red",
                  radioClass: "iradio_flat-red"
                });
              });
            </script>';
        
        continue;  
      }
      
      if($key == 'parent_id'){
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if(!$item_cat_id) $item_cat_id = 0;
        
        $tmp  = '<select name="parent_id" class="form-control">';
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        
        #$output .= '<input type = "hidden" name = "parent_id" value = "'.$item_cat_id.'">';
        $output .= $this->show_form_row( $val, $tmp); 
        continue;
      }
      
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
            <div class="panel-heading"> 
              <h3 class="panel-title">SEO</h3>
            </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;  
      
        //$output .= '<div class = "col-xs-12"><h2></h2></div>';
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
            <div class="panel-heading"> 
              <h3 class="panel-title">Атрибуты основого изображения</h3>
            </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;         
        //$output .= '<div class = "col-xs-12"><b>Атрибуты основого изображения</b></div>';
      }
      
      if($item){
        if($type){
          
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'">'
          );
          
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
         
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="#FFFFFF">'
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
    
    $output .= ' </div> ';
    
    $output .= ' Изображение  (Иделальный размер '.$this->img_cat_ideal_width.' x '.$this->img_cat_ideal_height.'):';
    $output .= '<BR/><INPUT type="file" name="picture" value="" class="w100"><BR/>';
    
    return $output;
    
  }
  
  function show_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    
    /*$output .= '
      <div class="panel panel-default"> 
        <div class="panel-heading"> 
          <h3 class="panel-title">Основное</h3>
        </div> 
        <div class="panel-body"> 
    ';*/
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a href="/'.$url.'" class="btn btn-info pull-right" style="color:#fff"><i class="icon-eye-open icon-white"></i> Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }
      
      $output .= $this->show_form_row( 'ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']) );
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; 
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      
      if($key == 'cat_id'){
        $tmp  = '<select name="cat_id" class="form-control">';
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if(!$item_cat_id) $item_cat_id = 0;
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }
      
      if($key == 'is_enlarge_photos'){
        ($item && $item[$key]) ? $coldate = 'checked' : $coldate = ''; #pri($coldate);
        
        $output .= $this->show_form_row( 
          $val.$this->getErrorForKey($key), 
            '<input type="checkbox" class="col_checkbox" name="'.$key.'" '.$coldate.'>'
          );
          
        $output .= '
            <script type="text/javascript">
              $(document).ready(function(){
                $(".col_checkbox").iCheck({
                  checkboxClass: "icheckbox_flat-red",
                  radioClass: "iradio_flat-red"
                });
              });
            </script>';
        
        continue;  
      }
      
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
            <div class="panel-heading"> 
              <h3 class="panel-title">SEO</h3>
            </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;  
      
        //$output .= '<div class = "col-xs-12"><h2></h2></div>';
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
            <div class="panel-heading"> 
              <h3 class="panel-title">Атрибуты основого изображения</h3>
            </div> 
            <div class="panel-body"> 
        ';
        $is_open_panel_div = true;         
        //$output .= '<div class = "col-xs-12"><b>Атрибуты основого изображения</b></div>';
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
            '<input type="'.$type.'" name="'.$key.'"  value="#FFFFFF">'
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
      
    $output .= ' </div> ';
    
    return $output;
    
  }
  
  function full_tree(){
    $output = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    $output .= '
		  <script>
		  $(function(){
			  $("#article").keyup(function(){
			    var q=$(this).val();
			    $.post("'.$this->carusel_name.'.php?ajx&act=search", {que:q}).done(function( data ) 
				  {
				  	$("#exists").html(data);
				  });
		    });
		  });
		  </script>

      <div class="container-fluid">
        <div class="row-fluid">
          <div class="span6">
            <div class="row-fluid">
              <div class="span12 ">
    ';
	  $output .= $this->show_entrie_catalog();
    $output .= '
              </div>
            </div>
          </div>
          <div class="span6">
            <div class="row-fluid">
              <div class="span12">
                <input type="text" class="text span12" name="article" id="article" placeholder="Поиск...">
              </div>
            </div>
            
            <div class="row-fluid">
              <div class="span12" id="exists"></div>
            </div>
          </div>
        </div>
      </div>
      
      <script>
      $(function(){
        $(".posit").popover({
	        html:true,
	        trigger:"hover",
	        placement:"top"
        })
      });
      </script>
      
      <style>
      .listingb li
      {
	      padding: 5px 0 0 0;
      }
      .listingb{
        padding:5px 0;
        margin: 0 0 0 15px;
      }
      .listingb a{
        color: #265c88;
      }
      .container{
        width: 100%;
      }
      .listingb .label{
        color: #000000;
      }
      
      </style>
    ';
    
    return $output;
  }
  
}


$date_arr = array(
    'title'             => 'Название',
    'cat_id'            => 'Категория',
    #'substring'         => 'Подстрочник',
    'date'              => 'Дата',
    'longtxt1'          => 'Краткий текст',
    'longtxt2'          => 'Полный текст (для отдельной страницы)<br><small style = "color:#28a745">Для вставки слайдера: %slider%</small>',
    #'is_enlarge_photos' => 'Увеличивать фото<br>в описании',
    'seo_h1'            => 'SEO H1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
  );

$date_cat_arr = array(
    'title'             => 'Название',
    'parent_id'         => 'Категория',
    'link'              => 'Ссылка',
    #'substring'         => 'Подстрочник',
    'longtxt1'          => 'Краткий текст',
    'longtxt2'          => 'Полный текст (для отдельной страницы)',#<br><small style = "color:#28a745">Для вставки слайдера: %slider%</small>',
    #'is_enlarge_photos' => 'Увеличивать фото<br>в описании',
    'seo_h1'            => 'SEO H1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
  );
$pager = array(
  'perPage' => 10,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'longtxt1', 'longtxt2');
  
$carisel = new Article('articles', $date_arr, $date_cat_arr, false, false, $pager);

$carisel->setHeader('КАТАЛОГ СТАТЕЙ');
$carisel->setIsUrl(true);
$carisel->setIsImages(true);
$carisel->setIsFiles(true);
$carisel->setIsLog(true);
$carisel->setIsPager(true);
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 
$carisel->setImg_ideal_width(650);  
$carisel->setImg_ideal_height(650); 
  
#$carisel->setDate_arr($date_arr);


if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
