<?php
class Goods {
  
  function __construct (){
    
  }
  
  static function show_path_link($category_id, $title = false, $separator = "/", $line = false) {
    $output = '';
		$path = self::get_path_link($category_id, '/', $separator, $category_id);
		if ($title) $path = "$path <span>$title</span>";
		$path = "<a href=\"/\">Главная</a> $separator $path";
		$output .= '
    <div class="bread_crumbs_box ">
      <div class="bread_crumbs
    ';
    if($line) $output .= ' border_top_button_line';
    $output .= ' ">'.$path.'</div></div>';
    
    return $output;
	}
  
  static function get_path_link($cid, $str = '', $separator = "/", $sid = null) {
		$row = db::row('title, parent_id', DB_PFX.'goods_cat', "id = $cid");
		$title = $row['title'];
		$url = $href = Url::getStaticUrlForModuleAndModuleId(DB_PFX.'url', DB_PFX.'goods_cat', $cid);
		$parent_id = intval($row['parent_id']);
		if ($cid==$sid) $str = "<span>$title</span> ";
		else $str = "<a href=\"/$url\">$title</a> ".$str;
		if ($parent_id > 0) {
			$str = " $separator $str";
			$str = self::get_path_link($parent_id, $str, $separator, $sid);
		}
		return $str;
	}
   
  static function get_arr_act_cat_items($sid, $arr){
    $item = db::row('*',  DB_PFX.'goods_cat', 'id = '.$sid);
    
    if($item['parent_id']){
      $arr[] = $item['parent_id'];
      return self::get_arr_act_cat_items($item['parent_id'] ,$arr);
    }else{
      return $arr;
    }
  }
 
  static function get_left_menu(&$site){
  	
    $output = '';
    
    /*$output .= '
      <script type="text/javascript">
        $(document).ready(function() {
  	  	
        $(".list-group-item").click(function(e) {
  	  	  //e.preventDefault();
          if(!$(this).children("a").is(":focus")){
            if( $(this).next(".sub_item").is(":visible") ){
              if($(this).next(".sub_item").length){
              $(this).next(".sub_item").slideUp();
              $(this).next(".sub_item").find(".sub_item").slideUp();
              $(this).css("background", "url(\'css/img/lm_open.png\') no-repeat 0px 14px ");
              }
    	  	  }else{
              if($(this).next(".sub_item").length){
                
              $(this).next(".sub_item").slideDown();
              $(this).css("background", "url(\'css/img/lm_close.png\') no-repeat 0px 14px ");  
              
              }
    		    }
          }
  	  	
  	  	});
  	  	

  	  });
      </script>
    ';*/
    
    $arr_act_cat_items =array();
    
    if($site->getModule() == 'il_cat_goods' && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == 'il_goods' && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', 'il_goods', 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    
    #  <div class = "left_menu_header">Каталог товаров</div>
    $output .= '
      <div class="list-group cat-menu goods_cats">
        <div class="left_menu_header">Каталог</div>
    ';
    $c_t = 'il_cat_goods'; 
    $s = "
    SELECT `$c_t`.*,  `il_url`.`url`
    FROM `$c_t` 
    LEFT JOIN `il_url`
    ON (`il_url`.`module` = '$c_t') AND (`il_url`.`module_id` = `$c_t`.`id`)
    WHERE `$c_t`.`hide` = 0
    AND `parent_id` = 4
    ORDER BY `$c_t`.`ord`
    ";
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
          
          $s_sub = "
            SELECT `$c_t`.*,  `il_url`.`url`
            FROM `$c_t` 
            LEFT JOIN `il_url`
            ON (`il_url`.`module` = '$c_t') AND (`il_url`.`module_id` = `$c_t`.`id`)
            WHERE `$c_t`.`hide` = 0
            AND `parent_id` = ".$r['id']."
            ORDER BY `$c_t`.`ord`
          ";
          $count = mysql_num_rows(mysql_query($s_sub));
          
          $glyphicon = $display = $style = '';
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 14px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'css/img/lm_open.png\') 0px 14px no-repeat;" ';
              $display = "none";
            }
          }
          
          
          $glyphicon = '';
          $output .= '
            <div class="list-group-item" '.$style.'><a href="/'.$r['url'].'" >'.$glyphicon.' '.$r['title'].'</a></div>
          ';  
          
          //if($active){
            $output .= self::get_left_sub_menu($s_sub, $arr_act_cat_items, $display);  
          //}

        }
      }
    }
      

    
    $output .= '
      </div><!--/span-->
    ';
    
    
    
    return $output;
  }
  
  static function get_left_sub_menu($s_sub, $arr_act_cat_items, $display = "none"){
    $output = '';
    $c_t = 'il_cat_goods'; 
    //echo "s_sub = $s_sub";
    
    //<span class="glyphicon glyphicon-plus"></span>
    
    if($q = mysql_query($s_sub)){
      if(mysql_num_rows($q)){
        $output .= '<div class = "sub_item" style = "display: '.$display.'; ">';
        while($r = mysql_fetch_assoc($q)){
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
            
          
          $s_sub = "
            SELECT `$c_t`.*,  `il_url`.`url`
            FROM `$c_t` 
            LEFT JOIN `il_url`
            ON (`il_url`.`module` = '$c_t') AND (`il_url`.`module_id` = `$c_t`.`id`)
            WHERE `$c_t`.`hide` = 0
            AND `parent_id` = ".$r['id']."
            ORDER BY `$c_t`.`ord`
          ";
          
          $count = mysql_num_rows(mysql_query($s_sub));
          $glyphicon = $style = '';
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_close.png\') 0px 14px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_open.png\') 0px 14px no-repeat;" ';
              $display = "none";
            }
          }
          $glyphicon = '';
          $output .= '
            <div class="list-group-item  " '.$style.'><a href="/'.$r['url'].'" >'.$glyphicon.' '.$r['title'].'</a></div>
          ';
          
          //if($active){
            $output .= self::get_left_sub_menu($s_sub, $arr_act_cat_items, $display);
          //}

        }
        $output .= '</div>';
      }
    }
    
    return $output;
    
  }
  
  static function show_mine_menu($site){
    
    $output = '';
    
    $arr_act_cat_items =array();
    
    if($site->getModule() == 'il_cat_goods' && $site->getModuleId()){
      $arr_act_cat_items[] = $site->getModuleId();
      $arr_act_cat_items = self::get_arr_act_cat_items($site->getModuleId(), $arr_act_cat_items); 
    }elseif($site->getModule() == 'il_goods' && $site->getModuleId()){
      $act_cat_id = db::value('cat_id', 'il_goods', 'id = '.$site->getModuleId());
      $arr_act_cat_items[] = $act_cat_id;
      $arr_act_cat_items = self::get_arr_act_cat_items($act_cat_id, $arr_act_cat_items); 
    }
    
    $s = "
      SELECT  `il_cat_goods` . * ,  `il_url`.`url` 
      FROM  `il_cat_goods` 
      LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_cat_goods' ) 
      AND (
      `il_url`.`module_id` =  `il_cat_goods`.`id`
      )
      WHERE  `il_cat_goods`.`parent_id` = 1
      ORDER BY  `il_cat_goods`.`ord` 
    ";
    
    if($q = mysql_query($s)){
      if( mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $sub_menu = '';
          
          (in_array( $r['id'], $arr_act_cat_items)) ? $active = ' active ' : $active = ''; 
          
          if($sub_menu = self::show_sub_mine_menu($id)){
            $output .= '
              <li class="dropdown '.$active.'">
                <a href="'.$url.'" class="dropdown-toggle" data-toggle="dropdown">'.$title.' <b class="caret"></b></a>
              '.$sub_menu.'
              </li>
            ';
          }else{
            $output .= '
              <li class="'.$active.'" ><a href="'.$url.'">'.$title.'</a></li>
            ';
          }
          
        }
      }
    }
    
    return $output;
    
  }
  
  static function get_chaild_cat_arr($cat_id, &$arr){
    $s = "
      SELECT `il_cat_goods`.*
      FROM `il_cat_goods`
      WHERE `il_cat_goods`.`hide` = 0
      AND `il_cat_goods`.`parent_id` = $cat_id
      ORDER BY `ord`
    ";
    
    if($q = mysql_query($s))
      if(mysql_num_rows($q))
        while($r = mysql_fetch_assoc($q)){
          $arr[] = $r['id'];
          self::get_chaild_cat_arr($r['id'], $arr);
        }
      
  }
  
  static function view_left_menu(&$site){
    $output = '';
    
    $output .= '
      <div class="list-group cat-menu">
    ';
    
    $s_view = "
      SELECT  `il_view_granite` . * ,  `il_url`.`url` 
      FROM  `il_view_granite` 
      LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_view_granite' ) 
      AND (
      `il_url`.`module_id` =  `il_view_granite`.`id`
      )
      WHERE  `il_view_granite`.`hide` = 0
      ORDER BY  `il_view_granite`.`ord`
    ";
    
    if($q_view = mysql_query($s_view)){
      if( mysql_num_rows($q_view)){
        while($r_view = mysql_fetch_assoc($q_view)){
          extract($r_view);
          
          ($site->arr_urls[0] == $url) ? $active = ' active ' : $active = ''; 
          
          $s_texture = "
            SELECT  `il_cat_goods` . * , 
                    `il_texture`.`id` as `texture_id`, 
                    `il_texture`.`title` as `texture_title`,  
                    `il_url`.`url` 
            FROM  `il_cat_goods` 
            
            LEFT JOIN  `il_texture` ON (  `il_cat_goods`.`texture_id` =  `il_texture`.`id` ) 
            LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_texture' ) 
            AND (
            `il_url`.`module_id` =  `il_texture`.`id`
            )
            WHERE  `il_cat_goods`.`hide` = 0
            AND `il_cat_goods`.`view_granite_id` = $id
            AND `il_cat_goods`.`texture_id` > 0
            AND `il_texture`.`hide` = 0
            GROUP BY `il_cat_goods`.`texture_id`
            ORDER BY  `il_texture`.`ord`
          ";
          
          //
            
          //$s_texture
          
          //echo "<pre>$s_texture</pre>";
          
          $q_texture = mysql_query($s_texture);
          $count = mysql_num_rows($q_texture);
          
          $glyphicon = $display = $style = '';
          
          if($count){
            if($active){
              //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
              $style = ' style="background: url(&quot;/css/img/lm_close.png&quot;) 0px 14px no-repeat;" ';
              $display = "block";
            }else{
              //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
              $style = ' style="background: url(\'/css/img/lm_open.png\') 0px 14px no-repeat;" ';
              $display = "none";
            }
          }
          
          $output .= '
            <div class="list-group-item " '.$style.'>
              <a href="/'.$url.'" class = "'.$active.'"> '.$title.' </a>
            </div>
          ';

          
          if($q_texture){
            if( mysql_num_rows($q_texture)){
              $output .= '
                <div class="sub_item" style="display: '.$display.' ">
              ';
              while($r_texture = mysql_fetch_assoc($q_texture)){
                $active = '';
                if(isset($site->arr_urls[1]))
                  ($site->arr_urls[1] == $r_texture['url']) ? $active = ' active ' : $active = ''; 
                
                $s_cat = "
                  SELECT  `il_cat_goods` . * ,  `il_url`.`url` 
                  FROM  `il_cat_goods` 
                  LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_cat_goods' ) 
                  AND (
                  `il_url`.`module_id` =  `il_cat_goods`.`id`
                  )
                  WHERE  `il_cat_goods`.`view_granite_id` = $id
                  AND `il_cat_goods`.`texture_id` = {$r_texture['texture_id']}
                  AND `il_cat_goods`.`hide` = 0
                  ORDER BY  `il_cat_goods`.`ord` 
                ";
                
                $q_cat = mysql_query($s_cat);
                $count_cat = mysql_num_rows($q_cat);
                
                $glyphicon = $display = $style = '';
                
                if($count){
                  if($active){
                    //$glyphicon = '<span class="glyphicon glyphicon-minus left-menu-move"></span>';
                    $style = ' style="background: url(&quot;/css/img/lm_close.png&quot;) 0px 14px no-repeat;" ';
                    $display = "block";
                  }else{
                    //$glyphicon = '<span class="glyphicon glyphicon-plus left-menu-move"></span>';
                    $style = ' style="background: url(\'/css/img/lm_open.png\') 0px 14px no-repeat;" ';
                    $display = "none";
                  }
                }
                
                $output .= '
                  <div class="list-group-item" '.$style.'>
                    <a href="/'.$url.'/'.$r_texture['url'].'" class = "'.$active.'" > '.$r_texture['texture_title'].' </a>
                  </div>
                ';
                
                
                
                //echo "<pre>$s_cat</pre>";
                
                if($q_cat){
                  if( mysql_num_rows($q_cat)){
                    $output .= '
                      <div class="sub_item" style="display: '.$display.' ">
                    ';
                    while($r_cat = mysql_fetch_assoc($q_cat)){
                      $active = '';
                      if(isset($site->arr_urls[2]))
                        ($site->arr_urls[2] == $r_cat['url']) ? $active = ' active ' : $active = ''; 
                      $output .= '
                        <div class="list-group-item">
                          <a href="/'.$url.'/'.$r_texture['url'].'/'.$r_cat['url'].'" class = "'.$active.'"> '.$r_cat['title'].' </a>
                        </div>
                      ';
                    }
                    $output .= '
                      </div>
                    ';
                  }
                }   
                
              }
              $output .= '
                </div>
              ';
            }
          }
          
        
        }
      }
    }
    
    $output .= '
      </div>
    ';
    
    return $output;
  }
  
  static function show_filter_form(&$site){
    
    $output = '';
    
    $surface_id = $color_id = $texture_id = 0;
    
    if(isset ($_SESSION['surface_id']) )
      $surface_id = $_SESSION['surface_id'];
    
    if(isset ($_SESSION['color_id']) )
      $color_id = $_SESSION['color_id'];
    
    if(isset ($_SESSION['texture_id']) )
      $texture_id = $_SESSION['texture_id'];
    
    
    $output .= '
      <div class="filter_box lf_box">
        <form action="rezultatyi-poiska-plitki.html" method="get">
          <input type="hidden" name="kind" value="65">
          <div class="f_poverh">
            <div class="filter_box_header"><a href = "/poverhnost">Выбрать по поверхности</a></div>
            <div class="row">
              <select name="surface" id = "surface_id">
                <option value="0">- любая -</option>
    ';
    $surface_items = db::select("*", "il_surface", "`hide` = 0", "`ord`");
    foreach($surface_items as $surface_item){
      
      $selected = '';
      
      if($surface_id == $surface_item['id'])
        $selected = ' selected="selected" ';
        
      $output .= '
        <option value="'.$surface_item['id'].'" '.$selected.' >'.$surface_item['title'].'</option>
      ';
    }
    $output .= '
              </select>
            </div>
          </div>
            
          <div class="f_color">
            <div class="filter_box_header"><a href="/tsvet">Выбрать по цвету</a></div>
            <div class="row last">
              <select name="color" id = "color_id">
                <option value="0">- любой -</option>
    ';
    $color_items = db::select("*", "il_color", "`hide` = 0", "`ord`");
    foreach($color_items as $color_item){
      
      $selected = '';
      
      if($color_id == $color_item['id'])
        $selected = ' selected="selected" ';
        
      $output .= '
        <option value="'.$color_item['id'].'" '.$selected.' >'.$color_item['title'].'</option>
      ';
    }
    $output .= '
              </select>
            </div>
          </div>
            
          <div class="f_factura">
            <div class="filter_box_header"><a href = "/faktura">Выбрать по фактуре</a></div>
            <div class="row last">
              <select name="texture" id = "texture_id">
                <option value="0">- любая -</option>
    ';
    $texture_items = db::select("*", "il_texture", "`hide` = 0", "`ord`");
    foreach($texture_items as $texture_item){
      
      $selected = '';
      
      if($texture_id == $texture_item['id'])
        $selected = ' selected="selected" ';
        
      $output .= '
        <option value="'.$texture_item['id'].'" '.$selected.' >'.$texture_item['title'].'</option>
      ';
    }
    $output .= '
              </select>
            </div>
          </div>
            
          <!-- <input class="apply" type="submit" value="Подобрать"> -->
          <div class = "apply">Подобрать</div>
        </form>
      </div>
    ';
    
    $output .= '
      <script type="text/javascript">
      
      //Страница фильтра
      $(document).ready(function() {
        $(".apply", this).click(function(){
          var surface_id = $("#surface_id option:selected").val();
          var color_id = $("#color_id option:selected").val();
          var texture_id = $("#texture_id option:selected").val();
          $.ajax({
            type: "POST",
            url: "/ajax.php",
            data: "show_filter=1&surface_id="+surface_id+"&color_id="+color_id+"&texture_id="+texture_id,
            success: function(msg){
              if(msg == "ok"){
                window.location = "/filter";
              }else{
                alert(msg);
              }
             }
          });
        });
      });
      </script>
    ';
    
    return $output;
    
    
  }
  
  static function show_sub_filter(&$site){
    
    $output = '';
    
    $s_where = '
      `il_goods`.`hide` = 0
    ';
    
    $s_left = '';
    $surface_id = 0; $color_id = 0; $texture_id = 0;
    
    if(isset ($_SESSION['surface_id']) )
      if($_SESSION['surface_id']){
        $surface_id = $_SESSION['surface_id'];
        $s_left = "
          INNER JOIN `il_goods_surface`
          ON (`il_goods_surface`.`good_id` = `il_goods`.`id`) AND (`il_goods_surface`.`surface_id` = ".$_SESSION['surface_id']." )
        ";
      }
    
    if(isset ($_SESSION['color_id']) )
      if($_SESSION['color_id']){
        $color_id = $_SESSION['color_id'];
        $s_where .= "AND `il_goods`.`color_id` = ".$_SESSION['color_id']." ";
      }
    
    if(isset ($_SESSION['texture_id']) )
      if($_SESSION['texture_id']){
        $texture_id = $_SESSION['texture_id'];
        $s_where .= "AND `il_goods`.`texture_id` = ".$_SESSION['texture_id']." ";
      }
    
    /*
    $output .= '
      <p> <b>Параметры фильтра</b> </p>
    ';
    
    $output .= '
      <p>ПОВЕРХНОСТЬ:';
    
    if($surface_id){
      $output .= db::value("title", "il_surface", "id = ".$surface_id)."</p>";
    }else{
      $output .= ' Любая </p> ';
    }
    
    $output .= '
      <p>ЦВЕТ:';
    
    if($color_id){
      $output .= db::value("title", "il_color", "id = ".$color_id)."</p>";
    }else{
      $output .= ' Любой </p> ';
    }
    
    $output .= '
      <p>ФАКТУРA:';
    
    if($texture_id){
      $output .= db::value("title", "il_texture", "id = ".$texture_id)."</p>";
    }else{
      $output .= ' Любая </p> ';
    }
    */
    
    $s = "
    SELECT *
    FROM `il_goods`
    $s_left
    WHERE $s_where
    GROUP BY `il_goods`.`id`
    ";
    //echo "s = <pre>$s</pre><br>";
    //$r = mysql_fetch_assoc(mysql_query($s));
    //$cat_count = $r['count'];
    $cat_count = mysql_num_rows(mysql_query($s));
    
    $pagerPage = 0;
    if(isset($_GET['page'])){
      if($_GET['page']){
        $pagerPage = intval($_GET['page']);
        //echo "pagerPage = $pagerPage<br>";
      }
    }
    
    
    $s_filter = $limit = $s_cat_sorting = '';
    //$s_cat_sorting = " ORDER BY `img` DESC ";
    //$limit = 'LIMIT 20';
    
    $strPager = Article::getPager($site, $pagerPage, $cat_count, 20, $limit);
    
    //echo "limit = $limit<br>";
    
    
    
    $s = "
    SELECT `il_goods`.*, `il_url`.`url` 
    FROM `il_goods`
    LEFT JOIN `il_url`
    ON (`il_url`.`module` = 'il_goods') AND (`il_url`.`module_id` = `il_goods`.`id`)
    $s_left
    WHERE $s_where
    $s_filter
    $s_cat_sorting
    GROUP BY `il_goods`.`id`
    $limit
    ";
    
    if($cat_count){
      $output .= '
        <div class = "model_col_header">Модели коллекции</div>
      ';
    }
    
    //echo "<pre>s = $s</pre><br>";
    $output .= $strPager;
    
    $output .= '
      <div class="catalog_box">
        <div class="row">
    ';
    $output .= self::show_catalog_items($site, $s, $cat_count, $filter );

    $output .= '
        </div>
      </div>';
    $output .= $strPager;
    
    
    
    return $output;
  }
  
  static function show_filter(&$site){
    
    $output = '';
    
    $site->siteTitle = 'Фильтр';
    
    $site->setSiteDescription = 'Фильтр';
      
    $site->setSiteKeywords = 'Фильтр';
    
    $site->bread = '
      <div class="bread_crumbs_box ">
        <div class="bread_crumbs">
          <a href="/">Главная</a> → <span>Фильтр</span> 
        </div>
      </div>
    ';
    
    $output .= $site->bread;
    
    $output .= '
      <h1 class="cat_h1">Фильтр</h1>
    ';
    
    $s_where = '
      `il_goods`.`hide` = 0
    ';
    
    $s_left = '';
    $surface_id = 0; $color_id = 0; $texture_id = 0;
    
    if(isset ($_SESSION['surface_id']) )
      if($_SESSION['surface_id']){
        $surface_id = $_SESSION['surface_id'];
        $s_left = "
          INNER JOIN `il_goods_surface`
          ON (`il_goods_surface`.`good_id` = `il_goods`.`id`) AND (`il_goods_surface`.`surface_id` = ".$_SESSION['surface_id']." )
        ";
      }
    
    if(isset ($_SESSION['color_id']) )
      if($_SESSION['color_id']){
        $color_id = $_SESSION['color_id'];
        $s_where .= "AND `il_goods`.`color_id` = ".$_SESSION['color_id']." ";
      }
    
    if(isset ($_SESSION['texture_id']) )
      if($_SESSION['texture_id']){
        $texture_id = $_SESSION['texture_id'];
        $s_where .= "AND `il_goods`.`texture_id` = ".$_SESSION['texture_id']." ";
      }
      
    
    $output .= '
      <p> <b>Параметры фильтра</b> </p>
    ';
    
    $output .= '
      <p>ПОВЕРХНОСТЬ: ';
    
    if($surface_id){
      $output .= db::value("title", "il_surface", "id = ".$surface_id)."</p>";
    }else{
      $output .= ' Любая </p> ';
    }
    
    $output .= '
      <p>ЦВЕТ: ';
    
    if($color_id){
      $output .= db::value("title", "il_color", "id = ".$color_id)."</p>";
    }else{
      $output .= ' Любой </p> ';
    }
    
    $output .= '
      <p>ФАКТУРA: ';
    
    if($texture_id){
      $output .= db::value("title", "il_texture", "id = ".$texture_id)."</p>";
    }else{
      $output .= ' Любая </p> ';
    }
    
    
    $s = "
    SELECT *
    FROM `il_goods`
    $s_left
    WHERE $s_where
    GROUP BY `il_goods`.`id`
    ";
    //echo "s = <pre>$s</pre><br>";
    //$r = mysql_fetch_assoc(mysql_query($s));
    //$cat_count = $r['count'];
    $cat_count = mysql_num_rows(mysql_query($s));
    
    $pagerPage = 0;
    if(isset($_GET['page'])){
      if($_GET['page']){
        $pagerPage = intval($_GET['page']);
        //echo "pagerPage = $pagerPage<br>";
      }
    }
    
    
    $s_filter = $limit = $s_cat_sorting = '';
    //$s_cat_sorting = " ORDER BY `img` DESC ";
    //$limit = 'LIMIT 20';
    
    $strPager = Article::getPager($site, $pagerPage, $cat_count, 20, $limit);
    
    //echo "limit = $limit<br>";
    
    
    
    $s = "
    SELECT `il_goods`.*, `il_url`.`url` 
    FROM `il_goods`
    LEFT JOIN `il_url`
    ON (`il_url`.`module` = 'il_goods') AND (`il_url`.`module_id` = `il_goods`.`id`)
    $s_left
    WHERE $s_where
    $s_filter
    $s_cat_sorting
    GROUP BY `il_goods`.`id`
    $limit
    ";
    
    if($cat_count){
      $output .= '
        <div class = "model_col_header">Модели коллекции</div>
      ';
    }
    
    //echo "<pre>s = $s</pre><br>";
    $output .= $strPager;
    
    $output .= '
      <div class="catalog_box">
        <div class="row">
    ';
    $output .= self::show_catalog_items($site, $s, $cat_count, $filter );

    $output .= '
        </div>
      </div>';
    $output .= $strPager;
    
    
    
    return $output;
  }
  
  static function show_mine_goods_cat(&$site ){
    $output = '';
    
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_url = DB_PFX.'url';
    $s = "
      SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`fl_show_mine` = 1
      AND `$tbl_goods_cat`.`hide` = 0
      ORDER BY `$tbl_goods_cat`.`ord`
    "; #pri($s);
    
    $output .= self::show_sub_cats($site, null, $s);
    
    return $output;
  }
  
  
  static function show_mine_goods(&$site, $parent_id ){
    $output = '';
    
    $tbl_goods_cat = DB_PFX.'goods_cat';
    $tbl_goods = DB_PFX.'goods';
    $tbl_url = DB_PFX.'url';
    $s = "
      SELECT `$tbl_goods_cat`.*,  `$tbl_url`.`url` 
      FROM `$tbl_goods_cat`
      LEFT JOIN `$tbl_url`
      ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
      WHERE `$tbl_goods_cat`.`parent_id` = $parent_id
      AND `$tbl_goods_cat`.`hide` = 0
      ORDER BY `$tbl_goods_cat`.`ord`
    "; #pri($s);
    
    
    if($q = $site->pdo->query($s)){
      if ( $q->rowCount() ){
        
      	while ($r = $q->fetch()){
          $s_g  = "
            SELECT `$tbl_goods`.*, `$tbl_url`.`url` 
            FROM `$tbl_goods`
            LEFT JOIN `$tbl_url`
            ON (`$tbl_url`.`module` = '$tbl_goods') AND (`$tbl_url`.`module_id` = `$tbl_goods`.`id`)
            WHERE `$tbl_goods`.`cat_id` = {$r['id']}
            AND `$tbl_goods`.`fl_show_mine`  = 1 
            AND `$tbl_goods`.`hide`  = 0 
            ORDER BY `ord`
          "; #pri( $s_g );
          
          $q_g = $site->pdo->query($s_g);
          $cat_count = $q_g->rowCount();
          
          if($cat_count){
            $output .= '<p class="c_h1">'.$r['title'].'</p>';
            $output .= Goods::show_catalog_items($site, $s_g, $cat_count, $filter );
          }
          
          $output .= Goods::show_mine_goods( $site, $r['id'] );
          
          
        }
      }
    }
    
    return $output;
  }
  
  static function show_sub_mine_menu($parent_id){
    $output = '';
    
    $s = "
      SELECT  `il_cat_goods` . * ,  `il_url`.`url` 
      FROM  `il_cat_goods` 
      LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_cat_goods' ) 
      AND (
      `il_url`.`module_id` =  `il_cat_goods`.`id`
      )
      WHERE  `il_cat_goods`.`parent_id` = $parent_id
      ORDER BY  `il_cat_goods`.`ord` 
    ";
    
    if($q = mysql_query($s)){
      if( mysql_num_rows($q)){
        $output .= '
          <ul class="dropdown-menu navmenu-nav">
        ';
        
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $output .= '
            <li><a href="'.$url.'">'.$title.'</a></li>
          ';
        }
        $output .= '
          </ul>
        ';
    
      }
    }
    return $output;
  }
  
  static function get_arr_sub_cat_items($sid, &$cat_arr, &$arr){
    
    foreach($cat_arr as $item){
      if($item['parent_id'] == $sid){
        $arr[] = $item['id'];
        self::get_arr_sub_cat_items($item['id'], $cat_arr, $arr);
      }
    }
    
  }
  
  static function get_arr_parent_cat_items($sid, &$cat_arr, &$arr){
    
    foreach($cat_arr as $item){
      if($item['id'] == $sid){
        $arr[] = $item['id'];
        self::get_arr_parent_cat_items($item['parent_id'], $cat_arr, $arr);
      }
    }
    
  }
 
  static function show_sub_cats(&$site, $sid, $s = null){
    
    $output = '';
    
    if(!$s){
      $s = "
        SELECT  `".DB_PFX."goods_cat` . * , 
                `".DB_PFX."url`.`url` 
        FROM  `".DB_PFX."goods_cat` 
        
        LEFT JOIN  `".DB_PFX."url` ON (  `".DB_PFX."url`.`module` =  '".DB_PFX."goods_cat' ) 
        AND (
        `".DB_PFX."url`.`module_id` =  `".DB_PFX."goods_cat`.`id`
        )
        WHERE `".DB_PFX."goods_cat`.`parent_id` =  $sid 
        AND `".DB_PFX."goods_cat`.`hide` = 0
        ORDER BY  `".DB_PFX."goods_cat`.`ord`
      "; #pri($s);
    }
    
    if($q = $site->pdo->query($s)){
      if( $q->rowCount()){
        $output .= '
          <div class = "m_catalog">
            <div class="catalog_dir card-deck">
        '; 
        while($r = $q->fetch()){
          $output .= self::show_goods_cat_card($r);
        }
        $output .= '
            </div>
          </div>
        ';
      }
    }
    
    return $output;
    
  }
  
  static function show_goods_cat_card($goods_cat_arr){
    $output = '';
    $r = $goods_cat_arr;
    ($r['img']) ? $image = '/images/goods/cat/slide/'.$r['img'] : $image = '/css/img/nofoto.gif' ;
    $output .= '
      <div class="card">
        <a href="/'.$r['url'].'">
          <div class="card_img_box">  
            <img class="" src = "'.$image.'" alt = "Изображение - '.$r['title'].'"   title = "'.$r['title'].'">
            <div class="card_img_shadow"></div>
          </div>
        </a> 
        <div class="card-body">
        </div>
        <div class="card-footer">
          <p class="card-title"><a href="/'.$r['url'].'">'.$r['title'].'</a></p> 
        </div>
      </div>';
      
    return $output;
  }
  
  static function show_сat_of_factories(&$site){
    $output = '';
    $cat_item = db::select("*", "il_cat_goods", "id = 4", null, null, 1, 0 );
    
    if($cat_item['seo_title']){
      $site->siteTitle = $cat_item['seo_title'];
    }else{
      if($seo_title =  db::value('value', 'il_seo', "type = 'goods_cat_title'" )){
        $site->siteTitle = str_replace("*h1*", $cat_item['title'], $seo_title);
      }else{
        $site->siteTitle = $cat_item['title'];
      }
    }
    if($cat_item['seo_description']) $site->setSiteDescription($cat_item['seo_description']);
      
    if($cat_item['seo_keywords'])    $site->setSiteKeywords($cat_item['seo_keywords']);
    
    $output .= '
    <div class="bread_crumbs_box ">
      <div class="bread_crumbs">
        <a href="/">Главная</a> → <span>Каталог по фабрикам</span>
      </div>
    </div>
    <h1 class="cat_h1">Каталог по фабрикам</h1>
    ';
    
    /*echo "<pre>";
    print_r($site);
    echo "</pre>";*/
    
    $s = "
      SELECT `il_cat_goods` . *, `il_url`.`url`
      FROM  `il_cat_goods` 
      LEFT JOIN  `il_url` ON (  `il_url`.`module` =  'il_cat_goods' ) 
      AND (
      `il_url`.`module_id` =  `il_cat_goods`.`id`
      )
      WHERE  `il_cat_goods`.`parent_id` = 4
      AND `il_cat_goods`.`hide` = 0
      ORDER BY `il_cat_goods`.`view_granite_id`, `il_cat_goods`.`country_items_id`
    ";
    
    $view_granite = $country_items = '';
    
    if($q = mysql_query($s)){
      if( mysql_num_rows($q)){
        $i = 0;
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          
          if( ($view_granite != $view_granite_id) || ($country_items != $country_items_id) ){
            $view_granite = $view_granite_id;
            $country_items = $country_items_id;
            if($i++){
              $output .= '
                </ul>
              ';
            }
            $output .= '
              <p class = "fabrics_title" >'.$site->view_granite[$view_granite_id].' / '.$site->countrys[$country_items_id].'</p>
            ';
            $output .= '
              <ul class="fabrics">
            ';
          }
          
          $output .= '<li><a href = "/'.$url.'">'.$title.'</a></li> ';
          
          //$output .= '<p>'.$title.' '.$site->view_granite[$view_granite_id].' '.$site->countrys[$country_items_id].'</p>';
          
        }
        
        $output .= '
          </ul>
        ';
        
      }
    }
    
    $test = '
    <ul class="fabrics">
      <li><a href="katalog-po-fabrikam/coem/" title="COEM">COEM</a></li>
      <li><a href="katalog-po-fabrikam/leonardo/" title="LEONARDO">LEONARDO</a></li>
      <li><a href="katalog-po-fabrikam/rex/" title="REX">REX</a></li>
      <li><a href="katalog-po-fabrikam/fioraneze/" title="FIORANEZE">FIORANEZE</a></li>
      <li><a href="katalog-po-fabrikam/rondine/" title="RONDINE">RONDINE</a></li>
      <li><a href="katalog-po-fabrikam/caesar1/" title="CAESAR">CAESAR</a></li>
      <li><a href="katalog-po-fabrikam/santagostino/" title="SANT\'AGOSTINO">SANT\'AGOSTINO</a></li>
    </ul>
    ';
    
    return $output;
  }
  
  static function show_no_photo_goods(&$site){
    $output = '';
    
    $s = "
      SELECT `il_goods`.*, `il_url`.`url` 
      FROM `il_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_goods') AND (`il_url`.`module_id` = `il_goods`.`id`)
      WHERE 1
      ORDER BY `il_goods`.`id` DESC
    ";
    
    $output .= "<h1>Товары</h1>";
    
    $i = 1;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          if(!$img){
            $output .=  $i++.'. Фото нет в карточке товара <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            continue;
          }
          
           
          $filename = 'images/goods/orig/'.$img;
          
          // пробуем открыть файл для чтения
          if (@fopen($filename, "r")) {
            //$output .= "Файл $filename существует<br>";
          } else {
            $output .= $i++.'. Фото отсутствует на сервере, но есть карточке товара <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
          }

                      
          
        }
      }
    }
    
    $s = "
      SELECT `il_cat_goods`.*, `il_url`.`url` 
      FROM `il_cat_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_cat_goods') AND (`il_url`.`module_id` = `il_cat_goods`.`id`)
      ORDER BY `il_cat_goods`.`id` DESC
    ";
    
    $output .= "<h1>Разделы</h1>";
    
    $i = 1;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          if(!$img){
            $output .=  $i++.'. Фото нет в карточке раздела <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            continue;
          }
          
          $filename = 'images/goods/cat/orig/'.$img;

            if (@fopen($filename, "r")) {
              //$output .= "Файл $filename существует<br>";
            } else {
              $output .= $i++.'. Фото отсутствует на сервере, но есть карточке раздела <a href = "/'.$url.'" target = "_blank">'.$title.'</a> <br>';  
            }
          
        }
      }
    }
    
    
    return $output;
    
  }
  
  static function show_cat_all_goods(&$site, $cat_id = 4, $indent = '' ){
    $output = "";
    
    $s = "
      SELECT `il_cat_goods`.*, `il_url`.`url`
      FROM `il_cat_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_cat_goods') AND (`il_url`.`module_id` = `il_cat_goods`.`id`)
      WHERE `il_cat_goods`.`parent_id` = $cat_id
      ORDER BY `il_cat_goods`.`title`
      
    ";
    #$output .= "<pre>$s</pre>";
    $indent .= "-&nbsp;".$indent;
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          
          $bread_crumbs = '
          <div class="bread_crumbs_box ">
            <div class="bread_crumbs">
              <a href="\">Главная</a> →
          ';
          #$bread_crumbs .= self::get_path_link($cat_id, $str = '', "→",  null);
          $bread_crumbs .= ' → '.$title.'
            </div>
          </div>
          ';
          #$output .= $bread_crumbs;
          
          $output .= '<h2> '.$indent.' <a href = "/'.$url.'" > '.$title.'</a> </h2>';
          $output .= self::show_all_goods($site, $id);
          $output .= self::show_cat_all_goods($site, $id, $indent);
          $output .= '<br><br>';
        }
      }
    }
    
    
    return $output;
  }
  
  static function rename_url(&$site){
    $output = '';
    
    
    require_once('iladmin/lib/class.Url.php');
    $url_item = new Url('url');
    
    /*$s = "
      SELECT `il_cat_goods`.*, `il_url`.`url`
      FROM `il_cat_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_cat_goods') AND (`il_url`.`module_id` = `il_cat_goods`.`id`)
      ORDER BY `il_cat_goods`.`title`
    ";
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $new_url = '';
          $new_url = $url_item->set_url($new_url, 'il_cat_goods', $id, $title);
          $output .= 'il_cat_goods '.$id.' '.$title.' '.$url.' '.$new_url.'<br>';
        }
      }
    }*/
    
    
    /*$s = "
      SELECT `il_goods`.*, `il_url`.`url`
      FROM `il_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_goods') AND (`il_url`.`module_id` = `il_goods`.`id`)
      ORDER BY `il_goods`.`title`
    ";
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $new_url = '';
          $new_url = $url_item->set_url($new_url, 'il_goods', $id, $title);
          $output .= 'il_goods '.$id.' '.$title.' '.$url.' '.$new_url.'<br>';
        }
      }
    }*/
    
    return $output;
  }
  
  static function update_surface(&$site){
    $output = '';
    $s_turn = "
      TRUNCATE TABLE  `il_articul`
    ";
    mysql_query($s_turn);
    
    $s = "
      SELECT *
      FROM `il_goods_surface`
      WHERE `il_goods_surface`.`surface_id` > 10
      ORDER BY `il_goods_surface`.`good_id`, `il_goods_surface`.`surface_id`
      

    ";
    #LIMIT 4
    #echo "<pre>$s</pre>";
    
    
    
    
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        $i = 0;
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          //$output .= "$text<br>";
          $format = $price = $unit = '';
          $arr = explode("||",$text);
          foreach($arr as $line){
            $output .= "$line<br>";  
            if(strpos($line, "€/")){
              $output .= "Евро<br>";  
              
              $arr2 = explode("::", $line);
              if(isset($arr2[0]))
                $format = trim($arr2[0]);
              $output .= "Формат - $format<br>";  
              
              if(isset($arr2[1]))
                $arr3 = explode("€/", $arr2[1]);
              
              if(isset($arr3[0])){
                $price = str_replace(",", ".", $arr3[0]);
                $price = trim($price);
              }
              $output .= "Цена - $price<br>";
              
              if(isset($arr3[1])) 
                $unit = trim($arr3[1]);
              $output .= "Ед.изм. - $unit<br>";
                
              
              
              if($unit)
                if( ($unit != "шт.") && ($unit != "м2") )
                  $output .= "Зрада<br>";
              
              $unit_add = "NULL";  
              ($unit == "шт.")? $unit_add = 2 : $unit_add = 1;
              $i++;
              $s_add = "
                INSERT INTO `il_articul` 
                  (`id`, `goods_id`, `surface_id`, `title`, `img`, `longtxt1`, `longtxt2`, `price`, `price_eur`, `price_dol`, `units_id`, `hide`, `ord`) 
                VALUES
                  (NULL, $good_id, $surface_id, '$format', '', NULL, NULL,  '0', '$price', '0', $unit_add, 0, $i)
              ";
              mysql_query($s_add);
              
              $output .=  "<pre>$s_add</pre>";
              $output .= "<br>";
            }elseif(strpos($line, "$/")){
              $output .= "Доллары<br>";  
              
               $arr2 = explode("::", $line);
              if(isset($arr2[0]))
                $format = trim($arr2[0]);
              $output .= "Формат - $format<br>";  
              
              if(isset($arr2[1]))
                $arr3 = explode("$/", $arr2[1]);
              
              if(isset($arr3[0])){
                $price = str_replace(",", ".", $arr3[0]);
                $price = trim($price);
              }
              $output .= "Цена - $price<br>";
              
              
              if(isset($arr3[1])) 
                $unit = trim($arr3[1]);
              $output .= "Ед.изм. - $unit<br>";
                
              
              if($unit)
                if( ($unit != "шт.") && ($unit != "м2") )
                  $output .= "Зрада<br>";
              
              $unit_add = "NULL";  
              ($unit == "шт.")? $unit_add = 2 : $unit_add = 1;
              $i++;
              $s_add = "
                INSERT INTO `il_articul` 
                  (`id`, `goods_id`, `surface_id`, `title`, `img`, `longtxt1`, `longtxt2`, `price`, `price_eur`, `price_dol`, `units_id`, `hide`, `ord`) 
                VALUES
                  (NULL, $good_id, $surface_id, '$format', '', NULL, NULL,  '0', '0', '$price', $unit_add, 0, $i)
              ";
              mysql_query($s_add);
              
              $output .=  "<pre>$s_add</pre>";
              
              $output .= "<br>";
            }elseif(strpos($line, "/")){
              $output .= "Рубли<br>";    
              
               $arr2 = explode("::", $line);
              if(isset($arr2[0]))
                $format = trim($arr2[0]);
              $output .= "Формат - $format<br>";  
              
              if(isset($arr2[1]))
                $arr3 = explode("/", $arr2[1]);
              
              if(isset($arr3[0])) 
                $price = trim($arr3[0]);
              $output .= "Цена - $price<br>";
              
              if(isset($arr3[1])) 
                $unit = trim($arr3[1]);
              $output .= "Ед.изм. - $unit<br>";
              
              
              if($unit)
                if( ($unit != "шт.") && ($unit != "м2") )
                  $output .= "Зрада<br>";
              
              $unit_add = "NULL";  
              ($unit == "шт.")? $unit_add = 2 : $unit_add = 1;
              $i++;
              $s_add = "
                INSERT INTO `il_articul` 
                  (`id`, `goods_id`, `surface_id`, `title`, `img`, `longtxt1`, `longtxt2`, `price`, `price_eur`, `price_dol`, `units_id`, `hide`, `ord`) 
                VALUES
                  (NULL, $good_id, $surface_id, '$format', '', NULL, NULL,  '$price', '0', '0', $unit_add, 0, $i)
              ";
              mysql_query($s_add);
              
              $output .=  "<pre>$s_add</pre>";
                
              $output .= "<br>";
            }else{
              $output .= "Баг<br>";    
            }
          }
          
          $output .= "<br>";  
        }
        
      }
    }
    
    return $output;  
  }
  
  static function show_all_goods(&$site, $cat_id){
    $output = "";
    
    $s = "
      SELECT `il_goods`.*, `il_url`.`url`, `il_cat_goods`.`title` as `cat_title`
      FROM `il_goods`
      LEFT JOIN `il_url`
      ON (`il_url`.`module` = 'il_goods') AND (`il_url`.`module_id` = `il_goods`.`id`)
      LEFT JOIN `il_cat_goods`
      ON (`il_cat_goods`.`id` = `il_goods`.`cat_id`)
      WHERE `il_goods`.`cat_id` = $cat_id
      ORDER BY `il_cat_goods`.`title`, `il_goods`.`title`
      
    ";
    #$output .= "<pre>$s</pre>";
    
    $i = 1;
    $tmp_cat_id = 0; $brand_col = $brand = '';
    if($q = mysql_query($s)){
      if(mysql_num_rows($q)){
        while($r = mysql_fetch_assoc($q)){
          extract($r);
          $item_full = $r;
          
          $item_full['bread_crumbs'] = '';
          #$item_full['bread_crumbs'] = '<a href="\">Главная</a> → '.self::get_path_link($cat_id, $str = '', "→",  null) ; // Хлебные крошки 
          $bread_crumbs = '
          <div class="bread_crumbs_box ">
            <div class="bread_crumbs">
              '.$item_full['bread_crumbs'].' → '.$item_full['title'].'
            </div>
          </div>
          ';
          
          
          #$output .= $bread_crumbs;
          $output .= '<br><div><a href = "/iladmin/goods.php?edits='.$item_full['id'].'">Админка</a> <a href = "/'.$item_full['url'].'">На сайте</a>'.'</div>';// Коллекция: '.$item_full['cat_title'].'</div>';
          
          $item_full['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/500x500',  500, null, 0xFFFFFF, 100).'"  class="slide_img" style = "    max-width: 100%;"/>
          ';
          
        // Короч если для передидущего товара категория та же,
        // то не нужно по новой мучать базу данных
        // Очень полезно для товаров одной категории
        // P.S. Сайт перенесен поэтому такой вывод
        
        if( $tmp_cat_id != $item_full['cat_id']){
          $brand_row = db::row("*", "il_cat_goods", "id = ".$item_full['cat_id']);
          $brand_col = $brand_row['title'];
          $brand = db::value("title", "il_cat_goods", "id = ".$brand_row['parent_id']);  
          $tmp_cat_id = $item_full['cat_id'];
        }
          
          $item_full['parameters'] = '
            <ul class="parameters-list">
          ';
          $item_full['parameters'] .= '
              <li><div class="l">Название:</div><div class="r">'.$item_full['title'].'</div></li>
          ';
          if($item_full['texture_id']){
            $item_full['parameters'] .= '
              <li><div class="l">Фактура:</div><div class="r">'.$site->textures[$item_full['texture_id']].'</div></li>
            ';
          }
          if($item_full['color_id']){
            $item_full['parameters'] .= '
              <li><div class="l">Цвет:</div><div class="r">'.$site->colors[$item_full['color_id']].'</div></li>
            ';
          }
            
          if($brand){
            $item_full['parameters'] .= '
              <li><div class="l">Фабрика:</div><div class="r">'.$brand.'</div></li>
            ';
          }
          if($brand_col){
            $item_full['parameters'] .= '
              <li><div class="l">Коллекция:</div><div class="r">'.$brand_col.'</div></li>
            ';
          }
          
          $s_good_surface = "
            SELECT * 
            FROM `il_goods_surface`
            WHERE `good_id` = {$item_full['id']}
          ";
          
          $div_sp = '</td>
                        <td width="25%" align="center">
          ';
          
          $item_full['parameters_table'] = '';
          
          if($q_good_surface = mysql_query($s_good_surface)){
            if( mysql_num_rows($q_good_surface) ){
              
              while($r_good_surface = mysql_fetch_assoc($q_good_surface)){
                if($r_good_surface['text']){
                  $spArr = explode("||", $r_good_surface['text']);
                  
                  $size_price = '';$cout_sizes = 1;
                  foreach ($spArr as $sp){
                    if($sp){
                      $sp = str_replace("::", $div_sp, $sp);
                      $size_price .= '
                        <tr>
                          <td width="25%" align="center">'.$sp.'</td>
                        </tr>
                      ';
                    }
                    
                      $cout_sizes++;
                      
                  } 
                  $item_full['parameters_table'] .= '
                    <tr>
                      <td width="50%" rowspan="'.$cout_sizes.'">'.$site->surfaces[$r_good_surface['surface_id']].'</td>
                    </tr>
                      '.$size_price.'
                  ';
                }
                

              }
              
            }
          }
          if($item_full['parameters_table']){
            
            $item_full['parameters'] .= '

                <li>
                        
                  <table width="100%" border="0">
                    <tr>
                      <th>поверхность</th>
                      <th>формат</th>
                      <th nowrap="nowrap">цена, руб.</th>
                    </tr>
                      
                    <tr>
                      <td width="50%" rowspan="2"></td>
                    </tr>
                    <tr>
                      <td width="25%" align="center"></td>
                      <td width="25%" align="center">расчет производится в рублях по курсу на день оплаты</td>
                    </tr>
            '.$item_full['parameters_table'].'
                  </table>
                </li>
              </ul>
            ';
          
          }
      
          $output .= '
            <div class="good_item_box">
              <div class="good_item" style = "    overflow: hidden;">
              
                <div class="col-xs-12 col-sm-5 good_img_box">
                  <div class = "row">
                    '.$item_full['image'].'
                  </div>
                </div>
                
                <div class="col-xs-12 col-sm-7">
                  <div class="good_item_header">
                    <h1>'.$item_full['title'].'</h1>  
                  </div>

                  <div class="parameters-box" style = "display: block;">
                    '.$item_full['parameters'].'
                  </div>
                </div>
                
              </div>
            </div>
            <br>    
          ';
          
        }
      }
    }
     
    return $output;
  }
  
  static function show_cat_items(&$site, $cid, $cat_table, $table){
    
    $goods_tbl = DB_PFX.'goods';
    $goods_cat_tbl = DB_PFX.'goods_cat';
    $all_images_tbl = DB_PFX.'all_images';
    $all_files_tbl = DB_PFX.'all_files';
    
    $cat_item = db::select("*", $goods_cat_tbl, "id = ".$cid, null, null, 1, 0 );
    $cat_full = array();
    $cat_full = $cat_item;
    #pri($cat_item);
    
    // Вывод товаров включая подразделы
    /*$arr_sub_cat_id = array($cid); $str_sub_cat_id = '';
    self::get_arr_sub_cat_items( $cid, $site->cat_arr, $arr_sub_cat_id);
    #pri($arr_sub_cat_id);
    
    $i = 0;
    foreach($arr_sub_cat_id as $c_id){
      if( $i++ ) $str_sub_cat_id .= ' ,';
      $str_sub_cat_id .= $c_id;
    }
    
        
    #echo "str_sub_cat_id = $str_sub_cat_id";
    $s_where = ' `il_goods`.`cat_id` IN ( '.$str_sub_cat_id.' ) ';
    */
    $s_where = ' `'.$goods_tbl.'`.`cat_id` = '.$cid.' ';
    
    // ------------- SEO -------------
    {

      if($cat_item['seo_title']){
        $site->siteTitle = $cat_item['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'goods_cat_title'" )){
          $site->siteTitle = str_replace("*h1*", $cat_item['title'], $seo_title);
        }else{
          $site->siteTitle = $cat_item['title'];
        }
      }
      
      if($cat_item['seo_description']) $site->setSiteDescription($cat_item['seo_description']);
      
      if($cat_item['seo_keywords'])    $site->setSiteKeywords($cat_item['seo_keywords']);
    }
    // ------------- END SEO -------------
    
    
    // ------------- SEO Images -------------
    {   
      $count_seo_img_alt = 0;
      $img_alt = $cat_item['img_alt'];
      $title = $cat_item['title'];
      if($img_alt){
        $seo_img_alt_str = $img_alt;
      }else{
        if($seo_img_alt_str =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
          #$img_alt_txt= str_replace("*h1*", $title.' '.$article, $seo_img_alt);
        }else{
          $seo_img_alt_str = $title;
          #$img_alt_txt[0] = $title.' '.$article;
        }
      
      }
      $seo_img_alt_arr = explode("/", $seo_img_alt_str);
      $i = 0;
      foreach($seo_img_alt_arr as $seo_img_alt_item){
        $img_alt_txt[$i] = trim(str_replace("*h1*", $title, $seo_img_alt_item));
        $count_seo_img_alt = $i;
        $i++;
      }
      
      $img_title = $cat_item['img_title'];
      if($img_title){
        $seo_img_title_str = $img_title;
        #$img_title_txt = $img_title;
      }else{
        if($seo_img_title_str =  db::value('value', DB_PFX.'seo', "type = 'img_title'" )){
          #$img_title_txt= str_replace("*h1*", $title.' '.$article, $seo_img_title);
        }else{
          $seo_img_title_str = $title;
          #$img_title_txt = $title.' '.$article;
        }
      }
      
      $seo_img_title_arr = explode("/", $seo_img_title_str);
      $i = 0;
      foreach($seo_img_title_arr as $seo_img_title_item){
        $img_title_txt[$i] = trim(str_replace("*h1*", $title, $seo_img_title_item));
        $count_seo_img_title = $i;
        $i++;
      }
    }
    // ------------- END SEO -------------
    
    
    $output = '';
    
    
    $site->bread = self::show_path_link($cid, '', "→", false); // Хлебные крошки 
    //$cat_full['bread'] = $site->bread;
    
    // Добавить в просмотренные страницы
    $v_img = '';
    if($cat_item['img']) $v_img = Images::static_get_img_link("images/goods/cat/orig", $cat_item['img'],  'images/goods/cat/variations/140x120',  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $cat_item['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    $carousel = $carousel_fierst_img = $carousel2 = $carousel_img = '';
    
    $img = $cat_item['img'];
    
    $img_exists = file_exists ("images/goods/cat/orig/$img");
    
    if (0 && $img && $img_exists){ 
      $item_full['image'] = '
      <section class="slider">
        <div id="slider1" class="flexslider">
          <ul class="slides">
            <li>
              <a href="/images/goods/cat/orig/'.$img.'" title="" rel="prettyPhoto[item_big_img]">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig/", $img,  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>
            </li>
      ';
      
      $s  = "
        SELECT * 
        FROM `il_all_images`
        WHERE `module` = 'il_cat_goods'
        AND `module_id` = {$cat_item['id']}
        AND `hide` = 0
        ORDER BY  `il_all_images`.`module_ord` 
      ";  #pri($s);
      
      if($q = $site->pdo->query($s)){
        if($q->rowCount()){
          
          $carousel = '<div id="carousel1" class="flexslider" >
            <ul class="slides">
          ';
          $carousel_fierst_img = '
            <li>
              <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $img,  'images/goods/cat/variations/90x90',  90, null, 0xFFFFFF, 90).'"  class="slide_img_mini" alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' "  style = ""/>
            </li>
          ';
          $carousel2 = '
            </ul>
          </div>
          ';
          $i = $p = 0;
          while($r = $q->fetch()){
            if($i > $count_seo_img_alt) $i = 0;
            if($p > $count_seo_img_title) $p = 0;
            $item_full['image'] .= '
              <li>
                <a href="/images/all_images/orig/'.$r['img'].'" title="" rel="prettyPhoto[item_big_img]">
                  <img src = "'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/500x500',  500, null, 0xFFFFFF, 100).'" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].' " class="slide_img" style = ""/>
                </a>
              </li>
            ';
            
            $carousel_img .= '
              <li>
                <img src="'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/90x90',  90, null, 0xFFFFFF, 90).'" class="slide_img_mini" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].'"/>
              </li>
            ';
            
            
            $i++; $p++;
          }
          
        $item_full['image'] .= '
            </ul>
          </div>
        ';
        $item_full['image'] .= $carousel.$carousel_fierst_img.$carousel_img.$carousel2;
        $item_full['image'] .= '
          </section>
        ';
        }else{
          
          $item_full['image'] =  '
                      <a href="/images/goods/cat/orig/'.$img.'" title="" rel="prettyPhoto[item_big_img]">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $img,  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = "/*float: left;*/ padding: 0 15px 15px 0;"/>
              </a>
          ';
        }
        
        
      }else{

      }

      
    /*$item_full['image'] .=  '
      <script src="/flexslider/jquery.flexslider.js"></script>
      <script type="text/javascript" charset="utf-8">
        $(window).load(function() {
          $("#slider1").flexslider({
            animation: "slide",
            controlNav: false,
            prevText: "",
            nextText: "",
            animationLoop: false,
            slideshow: false
          });
        });
      </script>  
    ';*/ 
    
    $site->js_scripts .=  '
    <!-- FlexSlider -->
    <script defer src="/vendors/flexslider/jquery.flexslider.js"></script>
    <script type="text/javascript">
      $(window).ready(function(){
        $("#carousel1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          itemWidth: 90,
          itemMargin: 5,
          asNavFor: "#slider1"
        });

        $("#slider1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          sync: "#carousel1",
          start: function(slider){
            $("body").removeClass("loading");
            $(".slide_img").each(function(){
              $(this).css("margin-top", ($("#slider1").height() - $(this).height())/2+"px");
            });
            $(".slide_img_mini").each(function(){
              $(this).css("margin-top", ($("#carousel1").height() - $(this).height())/2+"px");
            });
          }
        });
      });
    </script>
    ';
      
    }else{
      /*$item_full['image'] ='
            <img src = "/css/img/no_photo.jpg" style = "float: left; padding: 0 15px 15px 0;"/>
      ';*/
      $item_full['image'] = '';
    }
    
    
    $cat_full['image'] = $item_full['image'];
    
    $cat_full['sub_cats'] = self::show_sub_cats($site, $cid);
    
    //$s_where = ' `il_goods`.`cat_id` IN ( '.$str_sub_cat_id.' ) ';
       
    $s = "
    SELECT COUNT( * ) AS count
    FROM `$goods_tbl`
    WHERE $s_where
    AND hide = 0 
    ";
    //echo "s = $s<br>";
    #$r = mysql_fetch_assoc(mysql_query($s));
    $q = $site->pdo->query($s);
    $r = $q->fetch();
    $cat_count = $r['count'];
    
    $pagerPage = 0;
    if(isset($_GET['page'])){
      if($_GET['page']){
        $pagerPage = intval($_GET['page']);
        //echo "pagerPage = $pagerPage<br>";
      }
    }
    
    
    $s_filter = $limit = $s_cat_sorting = '';
    $s_cat_sorting = " ORDER BY `ord` ";
    //$s_cat_sorting = " ORDER BY `img` DESC ";
    //$limit = 'LIMIT 20';
    
    $strPager = Article::getPager($site, $pagerPage, $cat_count, 20, $limit);
    
    //echo "limit = $limit<br>";
    
    
    
    $s = "
    SELECT `$goods_tbl`.*, `".DB_PFX."url`.`url` 
    FROM `$goods_tbl`
    LEFT JOIN `".DB_PFX."url`
    ON (`".DB_PFX."url`.`module` = '$goods_tbl') AND (`".DB_PFX."url`.`module_id` = `$goods_tbl`.`id`)
    WHERE $s_where
    AND `$goods_tbl`.`hide`  = 0 
    $s_filter
    $s_cat_sorting
    $limit
    "; #pri($s);
    
    $cat_full['cat_items'] = '';
    
    if($cat_count){
      $cat_full['cat_items'] .= '';
    }
    
    //echo "<pre>s = $s</pre><br>";
    $cat_full['cat_items'] .= $strPager;
    
    $cat_full['cat_items'] .= '
      <div class="catalog_box">
    ';
    $cat_full['cat_items'] .= self::show_catalog_items($site, $s, $cat_count, $filter );

    $cat_full['cat_items'] .= '
      </div>';
    $cat_full['cat_items'] .= $strPager;
    
    $output = self::tmp_cat_page($cat_full);

    return $output;
    
  }
  
  static function show_catalog_items(&$site, $s, $filter_count, &$filter = null, $prefix_url = ''){
    $output = "";
    #echo "<pre>s = $s</pre>";
    $q = $site->pdo->query($s);
    if($filter_count){
      
      $filter_item = '';
      
      $seo_img_alt_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_alt'");
      $seo_img_title_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_title'" );
      
      $output .= '
        <div class="cat_box">
          <div class="catalog_items card-deck">';
      
      $availability_arr = db::select('*', DB_PFX."availability", "hide = 0");
      $site->goods_availability = array();
      foreach($availability_arr as $k=>$v){
        $site->goods_availability[$v['id']] = $v['title'];
      } #pri($availability_arr);
      
      $j = 0;
      while($r = $q->fetch()){
        extract($r);

        // ------------- SEO -------------
        {
          $count_seo_img_alt = 0;
          if($img_alt){
            $seo_img_alt_str = $img_alt;
          }else{
            if($seo_img_alt_str_wed =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
              $seo_img_alt_str = $seo_img_alt_str_wed;
            }else{
              $seo_img_alt_str = $title.' '.$article;
            }
          
          }
          $seo_img_alt_arr = explode("/", $seo_img_alt_str);
          $i = 0;
          foreach($seo_img_alt_arr as $seo_img_alt_item){
            $img_alt_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_alt_item));
            if($i++) break;
          }
          
          
          if($img_title){
            $seo_img_title_str = $img_title;
          }else{
            if($seo_img_title_str_wed){
              $seo_img_title_str = $seo_img_title_str_wed;
            }else{
              $seo_img_title_str = $title.' '.$article;
            }
          }
          
          $seo_img_title_arr = explode("/", $seo_img_title_str);
          $i = 0;
          foreach($seo_img_title_arr as $seo_img_title_item){
            $img_title_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_title_item));
            if($i++) break;
          }
        }
        // ------------- END SEO -------------
        $cat_item = array();
        $cat_item = $r;
        $cat_item['url'] =  $prefix_url.$cat_item['url'];
        $cat_item['img_alt_txt'] = $img_alt_txt[0];
        $cat_item['img_title_txt'] = $img_title_txt[0];
        $cat_item['availability'] = $site->goods_availability[$r['availability_id']];
        //$cat_item['price'] = number_format(Goods::getPrice( $site, $cat_item['price'], $cat_item['price_ye'] ), 2, ',', ' ')." KZT";
        
        $img_exists = file_exists ("images/goods/orig/$img");
        
        if ($img && $img_exists){
          $cat_item['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/450x450',  450, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          ';
        }elseif( $cat_item['cat_image'] = db::value("img", "il_cat_goods", "id = ".$cat_id) ){
          $cat_item['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/100x100',  100, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          ';  
        }else{
          $cat_item['image'] ='
            <img src = "/css/img/no_photo.jpg">
          ';
        }
        $output .= self::tmp_cat_item($cat_item, ++$j);
        
        
      }
      $output .= '
          </div>
        </div>
      ';

    }else{
      #$output .= "Нет таких товаров";
      $output .= "";
    }
    
    return $output;
  }
  
  static function getPrice(&$site, $rub, $ye){
    if($rub > 0){
      $output = $rub;
    }else{
      $output = $ye * $site->ye;
    }
    return $output;
  }
  
  static function show_item_full(&$site, $item, $bread_crumbs = null) {
		if (!$item) return;
		extract($item);
    
    $output = '';
    $goods_tbl = DB_PFX.'goods';
    $goods_cat_tbl = DB_PFX.'goods_cat';
    $all_images_tbl = DB_PFX.'all_images';
    $all_files_tbl = DB_PFX.'all_files';
      
    $item_full = array();
    $item_full = $item;
    $item_full['url'] = Url::getStaticUrlForModuleAndModuleId(DB_PFX."url", DB_PFX."goods", $id);
    /*Добавление id товара в массив просмотренных товаров*/{
    
    if(!isset($_SESSION['item_history'])){
      $_SESSION['item_history'] = array();
    }
    
    $historyArr = $_SESSION['item_history'];

    $newHistoriArr[] = $id;
    foreach ($historyArr as $his) {
        if ($his != $id) {
            $newHistoriArr[] = $his;
        }
    }

    $_SESSION['item_history'] = $newHistoriArr;
      
      //print_r($_SESSION['item_history']);

    }/* End Добавление id товара в массив просмотренных товаров*/
    
    /*
    $q = $site->pdo->query("SELECT * FROM `".DB_PFX."basket_price` WHERE `item_id` = $id ORDER BY `from`") or die(mysql_error()); 
    while ($rows = $q->fetch()) {
      $prices[] = $rows;
    }*/
    
    // ------------- SEO -------------
    {
      if($item['seo_title']){
        $site->siteTitle = $item['seo_title'];
      }else{
        if($seo_title =  db::value('value', DB_PFX.'seo', "type = 'goods_title'" )){
          $site->siteTitle = str_replace("*h1*", $item['title'], $seo_title);
        }else{
          $site->siteTitle = $item['title'];
        }
      }

      if($item['seo_description']) $site->setSiteDescription($item['seo_description']);
      
      if($item['seo_keywords'])    $site->setSiteKeywords($item['seo_keywords']);
        
      $count_seo_img_alt = 0;
      if($img_alt){
        $seo_img_alt_str = $img_alt;
      }else{
        if($seo_img_alt_str =  db::value('value', DB_PFX.'seo', "type = 'img_alt'" )){
        }else{
          $seo_img_alt_str = $title.' '.$article;
        }
      
      }
      $seo_img_alt_arr = explode("/", $seo_img_alt_str);
      $i = 0;
      foreach($seo_img_alt_arr as $seo_img_alt_item){
        $img_alt_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_alt_item));
        $count_seo_img_alt = $i;
        $i++;
      }
      
      
      if($img_title){
        $seo_img_title_str = $img_title;
      }else{
        if($seo_img_title_str =  db::value('value', DB_PFX.'seo', "type = 'img_title'" )){
        }else{
          $seo_img_title_str = $title.' '.$article;
        }
      }
      
      $seo_img_title_arr = explode("/", $seo_img_title_str);
      $i = 0;
      foreach($seo_img_title_arr as $seo_img_title_item){
        $img_title_txt[$i] = trim(str_replace("*h1*", $title.' '.$article, $seo_img_title_item));
        $count_seo_img_title = $i;
        $i++;
      }
    }
    // ------------- END SEO -------------
    
    if($bread_crumbs){
      $site->bread = $bread_crumbs;
    }else{
      $item_full['bread_crumbs'] = '<a href="\">Главная</a> → '.self::get_path_link($cat_id, $str = '', "→",  null) ; // Хлебные крошки 
      $site->bread = '
      <div class="bread_crumbs_box ">
        <div class="bread_crumbs">
          '.$item_full['bread_crumbs'].' → '.$item_full['title'].'
        </div>
      </div>
      ';
    }
    //$output .= $site->bread ;
    //$output .= $site->search->showSearchLine(); 
    
    // Добавить в просмотренные страницы
    $v_img = '';
    if($item_full['img']) $v_img = Images::static_get_img_link("images/goods/orig", $item_full['img'],  'images/goods/variations/140x120',  140, null, 0xFFFFFF, 90);
    $v_cont = $site->getVisitedPage( $_SERVER['REQUEST_URI'], $v_img, $item_full['title']);
    $site->addVisitedPage($site->module, $site->module_id, $v_cont );
    
    $carousel = $carousel_fierst_img = $carousel2 = $carousel_img = '';
    
    $img_exists = file_exists ("images/goods/orig/$img");
    if ($img && $img_exists){ 
      $item_full['image'] = '
      <section class="slider">
        <div id="slider1" class="flexslider">
          <ul class="slides">
            <li>
              <a class="fancyfoto fancybox.iframe" href="/images/goods/orig/'.$img.'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>
            </li>
      ';
      
      
      $s  = "
        SELECT * 
        FROM `$all_images_tbl`
        WHERE `module` = '$goods_tbl'
        AND `module_id` = $id
        AND `hide` = 0
        ORDER BY  `$all_images_tbl`.`module_ord` 
      "; 
      
      if($q = $site->pdo->query($s)){
        if($q->rowCount()){
          
          $carousel = '<div id="carousel1" class="flexslider" >
            <ul class="slides">
          ';
          $carousel_fierst_img = '
            <li>
              <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/90x90',  90, null, 0xFFFFFF, 90).'"  class="slide_img_mini" alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' "  style = ""/>
            </li>
          ';
          $carousel2 = '
            </ul>
          </div>
          ';
          $i = $p = 0;
          while($r = $q->fetch()){
            if($i > $count_seo_img_alt) $i = 0;
            if($p > $count_seo_img_title) $p = 0;
            $item_full['image'] .= '
              <li>
                <a class="fancyfoto fancybox.iframe" href="/images/all_images/orig/'.$r['img'].'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                  <img src = "'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/500x500',  500, null, 0xFFFFFF, 100).'" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].' " class="slide_img" style = ""/>
                </a>
              </li>
            ';
            
            $carousel_img .= '
              <li>
                <img src="'.Images::static_get_img_link("images/all_images/orig", $r['img'],  'images/all_images/variations/90x90',  90, null, 0xFFFFFF, 90).'" class="slide_img_mini" alt = "'.$img_alt_txt[$i].'" title = "'.$img_title_txt[$p].'"/>
              </li>
            ';
            
            
            $i++; $p++;
          }
          
        $item_full['image'] .= '
            </ul>
          </div>
        ';
        $item_full['image'] .= $carousel.$carousel_fierst_img.$carousel_img.$carousel2;
        $item_full['image'] .= '
          </section>
        ';
        }else{
          
          $item_full['image'] =  '
            <div class="full_img_box">
              <a class="fancyfoto fancybox.iframe" href="/images/goods/orig/'.$img.'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = "max-width: 100%;"/>
              </a>
            </div> 
          ';
        }
        
      }else{

      }
      
    /*$item_full['image'] .=  '
      <script src="/flexslider/jquery.flexslider.js"></script>
      <script type="text/javascript" charset="utf-8">
        $(window).load(function() {
          $("#slider1").flexslider({
            animation: "slide",
            controlNav: false,
            prevText: "",
            nextText: "",
            animationLoop: false,
            slideshow: false
          });
        });
      </script>  
    ';*/
    
    $site->js_scripts .=  '
    <!-- FlexSlider -->
    <script defer src="/vendors/flexslider/jquery.flexslider.js"></script>
    <link href="/vendors/flexslider/flexslider.css" rel="stylesheet" >
    <script type="text/javascript">
      $(window).ready(function(){
        $("#carousel1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          itemWidth: 90,
          itemMargin: 5,
          asNavFor: "#slider1"
        });

        $("#slider1").flexslider({
          animation: "slide",
          controlNav: false,
          prevText: "",
          nextText: "",
          animationLoop: false,
          slideshow: false,
          sync: "#carousel1",
          start: function(slider){
            $("body").removeClass("loading");
            $(".slide_img").each(function(){
              $(this).css("margin-top", ($("#slider1").height() - $(this).height())/2+"px");
            });
            $(".slide_img_mini").each(function(){
              $(this).css("margin-top", ($("#carousel1").height() - $(this).height())/2+"px");
            });
          }
        });
      });
    </script>
    ';
      
    }elseif( $cat_item['cat_image'] = db::value("img", $goods_cat_tbl, "id = ".$cat_id) ){
          /*$item_full['image'] = '
            <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/130x130',  130, null, 0xFFFFFF, 95).'" title = "'.$img_title_txt[0].'" alt = "'.$img_alt_txt[0].'">
          '; */
          $item_full['image'] = '
          <a class="fancyfoto fancybox.iframe" href="/images/goods/cat/orig/'.$cat_item['cat_image'].'" data-fancybox="groupfoto" data-caption="'.$item_full['title'].'">
                <img src = "'.Images::static_get_img_link("images/goods/cat/orig", $cat_item['cat_image'],  'images/goods/cat/variations/500x500',  500, null, 0xFFFFFF, 100).'"  alt = "'.$img_alt_txt[0].'" title = "'.$img_title_txt[0].' " class="slide_img" style = ""/>
              </a>';
    }else{
      $item_full['image'] ='
            <img src = "/css/img/no_photo.jpg" style = "max-width: 100%"/>
      ';
    }

    $i = 0;
    $s_files  = "
    SELECT * 
    FROM `$all_files_tbl`
    WHERE `module` = '$goods_tbl'
    AND `module_id` = $id
    AND `hide` = 0
    "; #pri($s_files)";
    
    $item_full['files'] = '';
    if($q_files = $site->pdo->query($s_files)){
      if($q_files->rowCount()){
        $item_full['files'] .= '
                            <div class = "doc_box">
                              <ul class = "doc">
        ';
        while($r_files = $q_files->fetch()){
          if(file_exists('images/all_files/files/'.$r_files['file'])){
            $item_full['files'] .= '
              <li><a href = "/images/all_files/files/'.$r_files['file'].'" target = "_blank">'.$r_files['title'].'</a></li>
            ';
          }

 
        }
        $item_full['files'] .= '
                              </ul>
                            </div>
        ';
      }
    }
    
      
    if($item_full['seo_h1']){
      $item_full['title'] = $item_full['seo_h1'];
    }
    
    
    if(!isset($site->units) || !$site->units){
      $unit_items =  db::select("*", DB_PFX."units" );
      
      foreach($unit_items as $unit_item){
        $site->units[ $unit_item['id'] ] = $unit_item['reduction'];
      }
    }
    
    if( isset($site->units[ $item_full['units_id'] ]) && $site->units[ $item_full['units_id'] ] ){
      $item_full['units'] = $site->units[ $item_full['units_id'] ];   
    }else{
      $item_full['units'] = "шт";
    }
    
    ($site->whatsap_phone) ? $item_full['whatsap_phone'] = $site->whatsap_phone : $item_full['whatsap_phone'] = '';
    
    $output .= self::tmp_full_item($item_full);
    
    return $output;
	}
  
  static function show_renter(&$site){
    $output = '';
    $fio = $email = $phone = $company = $comment = $error = $is_send = '';
    $error_arr = array();
    
    if (isset ($_POST['email'])) {
      $mail = EMail::Factory();
      //$to = "1@in-ri.ru"; // поменять на свой адрес
      $email_order = db::value('val', 'config', "name = 'email_order'");
      $fio = substr(htmlspecialchars(trim($_POST['fio'])), 0, 1000);
      $email = substr(htmlspecialchars(trim($_POST['email'])), 0, 1000);
      $phone = substr(htmlspecialchars(trim($_POST['phone'])), 0, 1000);
      $company = substr(htmlspecialchars(trim($_POST['company'])), 0, 1000);
      $comment = substr(htmlspecialchars(trim($_POST['comment'])), 0, 10000);
      if(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)){
        $error = 1;
        $error_arr['email'] = 'Не верно введен email';
      }
      if (empty($fio)){
        $error_arr['fio'] = 'Не введенно имя'; 
        $error = 1;
      }
        
         


      $filesize = 0;
      

      for($i=0;$i<count($_FILES['fileFF']['name']);$i++) {
          if(is_uploaded_file($_FILES['fileFF']['tmp_name'][$i])) {
             $filesize += $_FILES['fileFF']['size'][$i];
         }
       }

      if ($filesize > 10000000) { // проверка на общий размер всех файлов. Многие почтовые сервисы не принимают вложения больше 10 МБ
        //mail($to, $subject, $message, $headers);
        $error_arr['filesize'] = "Извините, письмо не отправлено. Размер всех файлов превышает 10 МБ.";
        $error = 1;
      } else {
        //$output = '<script>alert("Извините, письмо не отправлено. Размер всех файлов превышает 10 МБ.");</script>';
        
      }
      
      if($error != 1){
        
        $date = date("Y-m-d");
        $ip = $_SERVER['REMOTE_ADDR'];
        $s = "
            INSERT INTO `il_renter` 
                    (`title`, `txt2`,     `txt3`, `date`,  `longtxt1`, `phone`,  `email`,  `txt1`, `hide`) 
            VALUES  ('$fio',  '$company', 'Новая', '$date', '$comment', '$phone', '$email', '$ip',  0);
        ";
        //echo "s = $s";
        
        mysql_query($s);
        $nuber = mysql_insert_id();
        $subject = "Заполнена контактная форма с ".$_SERVER['HTTP_REFERER'];
        $message = "
        № заявки: ".$nuber."<br>
        Дата: ".date("d.m.Y h:i:s")."<br>
        Имя: ".$fio."<br>
        Email: ".$email."<br>
        Телефон: ".$phone."<br>
        Компания: ".$company."<br>
        IP: ".$_SERVER['REMOTE_ADDR']."<br>
        Сообщение: <br>".$comment.'<br><br>
        <a href = "http://'.$_SERVER["HTTP_HOST"].'/iladmin/renter.php?edits='.$nuber.'">Перейти в админку</a><br><br>';
        
        $tosend = $message;
        
  		  $res = $mail->send($email_order, $subject, $tosend, null, $_FILES);
        
        if($res){
          //$output = '<script>alert("Ваше сообщение получено, спасибо!");</script>';  
          $is_send = true;
          

        }else{
          //$output = '<script>alert("Ошибка!");</script>';
          $error_arr['send'] = "Ошибка при отправке сообщения";
        }
      }
      
      

    }

    $output .= '
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header">
              <h1 class="md_content_h1" style="padding-top: 30px;">&nbsp; АРЕНДАТОРАМ &nbsp;</h1>
              <div class="line_header">&nbsp;</div>
            </div>
          </div>
          

        </div>
    ';
    if($is_send){
      $output .= '
        <div class="row">
          <div class="col-xs-12" style = "min-height: 500px;">
            <h2>Благодарим за заявку!</h2>
            <p>Ваша заявка успешно отправленна. Наши менеджер скоро с вами свяжется.</p>
          </div>
        </div>
      ';
    }else{
      /*$output .= '
          <div class="col-xs-12">
            <p>мебельный центр КОСМОС — это 6 этажЕЙ эффективных торговых площадей, организованных таким образом, чтобы гарантировать максимально комфортные рабочие условия для арендаторов и обеспечить высокий уровень проходимости каждого магазина, вне зависимости от места его расположения! </p>

            <p>Расположение мебельного центра в непосредственной близости от метро позволяет охватить широчайший круг потребителей, предпочитающих общественный транспорт, а просторная парковка на 1 000 машиномест обеспечивает удобство клиентов-автолюбителей. Кроме того, обилие офисных центров, жилых объектов и магазинов вблизи нового мебельного комплекса гарантирует постоянный поток посетителей не только в выходные и праздничные дни, но и в будни.</p>
          </div>
      ';*/
      $output .= db::value('longtxt1', 'il_cat_articles', "id = 6");
    if($error_arr){
      $output .= '
        <div class="row">
          <div class="col-xs-12">
      ';
      foreach($error_arr as $err){
        $output .= '<p style = "color: red; text-align: right;">'.$err.'</p>';  
      }
      $output .= '
          </div>
        </div>
      ';
      
    }

    $output .= '    
        <div class="col-xs-12 application_rent_box">
          <div class="col-xs-12 col-sm-5 application_rent_info">
    ';/*
            <p>По вопросам аренды, пожалуйста, обращайтесь<br>
            по телефонам:</br>
            &nbsp;&nbsp; - <a href = "tel:89827551567" class="rent_info_phone_link">8 (982) 755-15-67</a> Алексей Анатольевич<br>
            &nbsp;&nbsp; - <a href = "tel:89028783864" class="rent_info_phone_link">8 (902) 878-38-64</a> Александр Васильевич<br>
            e-mail:  <a href = "mailto:kosmos.mc@mail.ru">kosmos.mc@mail.ru</a></p>

            <div class="rent_info_check">Презентация Вашей компании или предлагаемых товаров, прикрепленная к заявке, станет Вашим конкурентным преимуществом при определении претендентов на свободную площадь.</div>
            <div class="rent_info_requisition"><a href = "#">Заявка на аренду</a></div>
    */
    $output .= db::value('longtxt2', 'il_cat_articles', "id = 6");
    $output .= '   
          </div>
          <div class="col-xs-12 col-sm-7 application_rent_form">
            <form id="rent_form" class="form-horizontal rent_form" role="form" method="post" enctype="multipart/form-data" >
              
              <div class="form-group">
                <label for="company" class="col-sm-4 control-label">Название организации</label>
                <div class="col-sm-8">
                  <input type="text" name="company" class="form-control" id="company" 
    ';
      if($company) $output .= ' value="'.$company.'"';
      $output .= '>
                </div>
              </div>
              
              <div class="form-group">
                <label for="fio" class="col-sm-4 control-label">Имя <span class="z">*</span></label>
                <div class="col-sm-8">
                  <input type="text" class="text form-control" name="fio" id="fio" placeholder="Фамилия Имя Отчество" required="" 
    ';
      if($fio) $output .= ' value="'.$fio.'"';
      $output .= '>
                </div>
              </div>
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js"></script>
              <script type="text/javascript">
              $(document).ready(function() {
                 $("#phone").mask("+7 (999) 999-9999");
              });
              </script>
              <div class="form-group">
                <label for="phone" class="col-sm-4 control-label">Телефон <span class="z">*</span></label>
                <div class="col-sm-8">
                  <input type="text" class="text form-control" name="phone" id="phone" required="" ';
      if($phone) $output .= ' value="'.$phone.'"';
      $output .= ' placeholder="+7 (___) ___-____">
                </div>
              </div>
              
              <div class="form-group">
                <label for="email" class="col-sm-4 control-label">E-mail</label>
                <div class="col-sm-8">
                  <input type="email" placeholder="your@email.com" class="text form-control" name="email" id="email" ';
      if($email) $output .= ' value="'.$email.'"';
      $output .= '>
                </div>
              </div>

              <div class="form-group">
                <label for="comment" class="col-sm-4 control-label">Комментарий</label>
                <div class="col-sm-8">
                  <textarea name="comment" class="text form-control" id="comment">';
      if($comment) $output .= $comment;
      $output .= '</textarea>
                </div>
              </div>
              
              <div class="form-group">
                <label for="fileFF" class="col-sm-4 control-label">Вложить файл</label>
                <div class="col-sm-8">
                  <input type="file" name="fileFF[]" multiple id="fileFF"  class="form-control" >
                  
                </div>
              </div>
              
              <div class="form-group">
                <div class="col-sm-12 text-right">
                  <button type="submit" class="order_submit btn btn-default btn_backed_remove" id="order_submit" value="Оформить заказ" >Отправить</button>
                </div>
              </div>
            </form>
          </div>
        </div>
       
      ';
    
    }
    
    return  $output;
  }
  
  static function show_feedback(&$site){
    $output = '';
    
    $fio = $email = $phone = $comment = $error = $is_send = '';
    $error_arr = array();
    
    if (isset ($_POST['email'])) {
      $mail = EMail::Factory();
      //$to = "1@in-ri.ru"; // поменять на свой адрес
      $email_order = db::value('val', 'config', "name = 'email_order'");
      $fio = substr(htmlspecialchars(trim($_POST['fio'])), 0, 1000);
      $email = substr(htmlspecialchars(trim($_POST['email'])), 0, 1000);
      $phone = substr(htmlspecialchars(trim($_POST['phone'])), 0, 1000);
      $comment = substr(htmlspecialchars(trim($_POST['comment'])), 0, 10000);
      if(!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)){
        $error = 1;
        $error_arr['email'] = 'Не верно введен email';
      }
      if (empty($fio)){
        $error_arr['fio'] = 'Не введенно имя'; 
        $error = 1;
      }
        
         
      $subject = "Добавлен новый коментарий в разделе `Обратная связь` ".$_SERVER['HTTP_REFERER'];
      $message = "
      Имя: ".$fio."<br>
      Email: ".$email."<br>
      Телефон: ".$phone."<br>
      IP: ".$_SERVER['REMOTE_ADDR']."<br>
      Сообщение: <br>".$comment.'<br><br>';

       
      if($error != 1){
        $tosend = $message;
  		  $res = $mail->send($email_order, $subject, $tosend);
        
        if($res){
          //$output = '<script>alert("Ваше сообщение получено, спасибо!");</script>';  
          $is_send = true;
          $date = date("Y-m-d");
          $ip = $_SERVER['REMOTE_ADDR'];
          $s = "
            INSERT INTO `il_feedback` 
                    (`title`, `date`,  `longtxt1`, `phone`,  `email`,  `txt1`, `hide`) 
            VALUES  ('$fio',  '$date', '$comment', '$phone', '$email', '$ip',  1);
          ";
          mysql_query($s);
          
        }else{
          //$output = '<script>alert("Ошибка!");</script>';
          $error_arr['send'] = "Ошибка при отправке сообщения";
        }
      }
      
      

    }
    if($is_send){
      $output .= '
        <div class="row">
          <div class="col-xs-12" style = "min-height: 200px;">
            <h2>Благодарим за ваш отзыв!</h2>
            <p>Ваша отзыв успешно отправлен. После того как мы сформируем ответ он появиться в разделе.</p>
          </div>
        </div>
      ';
    }else{
    
      $output .= '
        <div class="row">
    
          
          <div class="col-xs-12">
          
            <p>Мы не останавливаемся в развитии и постоянно стремимся сделать наш Торговый Центр удобнее для посетителей. Ваши отзывы, советы, комментарии  здорово помогают нам в этом. Спасибо! </p>
            <p><b>ТЦ «КОСМОС»</b></p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp; - <a href = "tel:83432394386"> 239-43-86 </a> (общий)</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp; - <a href = "tel:83432394396">239-43-96</a>  (Деж. администратор)</p><br><br>
            
          </div>

        </div>
        
        
        <div class="col-xs-12 obr_line"></div>
        
        <form id="obr_form" class="obr_form" role="form" method="post" enctype="multipart/form-data" >
        <div class="row">
          <div class="col-xs-12 col-sm-5">
          
          <h1><span class = "sosa">C</span> Обратаная связь: </h1>
          
          <div class="form-group">
            <label for="fio" class="control-label">Имя <span class="z">*</span></label>
            <input type="text" class="text form-control" name="fio" id="fio" placeholder="Имя Фамилия Отчество" required="" value="">
          </div>
          
          <div class="form-group">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function() {
               $("#phone").mask("+7 (999) 999-9999");
            });
            </script>
            <label for="phone" class="control-label">Телефон <span class="z">*</span></label>
            <input type="text" class="text form-control" name="phone" id="phone" required="" value="" placeholder="+7 (___) ___-____">
          </div>
          
          <div class="form-group">
            <label for="email" class="control-label">E-mail <span class="z">*</span></label>
            <input type="email" placeholder="your@email.com" class="text form-control" name="email" id="email" value="">
          </div>
          <div class="form-group" style = "    margin-bottom: 0px;">
                <label for="comment" class="control-label">Сообщение</label>
          </div>

          </div>
          
          <div class="col-sm-7 hidden-xs feedback_dog" ></div>
          
          <div class="col-xs-12" >
              <div class="form-group">
                <textarea name="comment" class="text form-control" id="comment" style = "min-height: 90px;"></textarea>
              </div>
              
              <div class="form-group">
                  <button type="submit" class="obr_submit btn btn-default btn_backed_remove" id="obr_submit" value="" >Отправить</button>
              </div>
          </div>
        </div>
        </form>
          
       
        <div class="col-xs-12 obr_line"></div>
        
        <br><br>
    ';
    }
    
    $output .= '
        <div class="row">
          <div class="col-xs-12">
            <h2>Популярные вопросы</h2>
          </div>
    ';
    $obr_items = db::select("*", "il_feedback", "hide = 0", "`date` DESC  ",  null, null, 0);
    
    foreach ($obr_items as $item){
      extract($item);
      $str_date = date("d", strtotime($date))." ";
      $month = date("m", strtotime($date));
      switch( $month ){
        case  1: $month = "Янаваря"; break;
        case  2: $month = "Февраля"; break;
        case  3: $month = "Марта"; break;
        case  4: $month = "Апреля"; break;
        case  5: $month = "Майя"; break;
        case  6: $month = "Июня"; break;
        case  7: $month = "Июля"; break;
        case  8: $month = "Августа"; break;
        case  9: $month = "Сентября"; break;
        case 10: $month = "Октября"; break;
        case 11: $month = "Ноября"; break;
        case 12: $month = "Декабря"; break;

      }
      
      $str_date .=  $month." ".date("Y", strtotime($date))." г.";
      
      $output .= '
          <div class="col-xs-12 obr_question_box">
            <div class="col-xs-12 obr_question">
              <div class="col-xs-12 obr_date"> '.$str_date.' </div>
              <div class="col-xs-12 obr_user"> '.$title.' </div>
              <div class="col-xs-12 obr_quest_txt">
                <p> '.$longtxt1.' </p>
              </div>
      ';
      if($longtxt2){
        $output .= '
              <div class="col-xs-12 obr_answer">
                <p><span class = "sosa obr_answer_sosa">C</span> Команда сайта<br>
                <span class = "obr_answer_indent" >&nbsp;</span>'.$longtxt2.'</p>
              </div> 
        ';
      }

      $output .= '
            </div>
          </div>
      ';
      
    }
    
    $output .= '
        </div>
    ';
    
    return $output;
  }

  static function show_calculator_armature(&$site, $cid){
    $output = '';
    
    $site->bread = self::show_path_link($cid, '', "→", false); // Хлебные крошки 
    
    $output .= '
                  <div class = "content_box full_page" >
                    <h1 class = "c_h1">Калькулятор арматуры</h1>     
                  </div>  
    ';
    
    $s = "
    SELECT `il_goods`.*
    FROM `il_goods`
    WHERE  `il_goods`.`cat_id` = $cid 
    AND `il_goods`.`hide`  = 0 
    ORDER BY `ord` 
    ";
    
    if($q = $site->pdo->query($s)){
      if($q->rowCount()){
        $site->js_scripts .= '
          <!-- iCheck for checkboxes and radio inputs -->
          <link rel="stylesheet" href="/vendors/iCheck/skins/all.css">
          
          <!-- iCheck 1.0.1 -->
          <script src="/vendors/iCheck/icheck.min.js"></script>
          <script>
          $(document).ready(function(){
            $(".group_radio").iCheck({
              checkboxClass: "icheckbox_flat-blue",
              radioClass: "iradio_flat-blue"
            });
          });
          </script>
        ';
        $output .= '
        <table class="table table-sm table-striped" style = "max-width: 650px;">
          <thead>
            <tr>
              <th scope="col" style = "text-align: center;">#</th>
              <th scope="col" style = "text-align: center;">Диаметр арматры</th>
              <th scope="col" style = "text-align: center;">Длина хлыста </th>
              <th scope="col" style = "text-align: center;">Вес 1 м</th>
            </tr>
          </thead>
          <tbody>
        ';
        $i = 1;
        while($r = $q->fetch()){
          ($i == 1 ) ? $checked = "checked" : $checked = "";
          $output .= '
            <tr>
              <td style = "text-align: center;"> <input type="radio" class="group_radio" name="group_radio" data-id="'.$i++.'" '.$checked.' data-o1="'.$r['opt1'].'" data-o2="'.$r['opt2'].'" data-o3="'.$r['opt3'].'" value = "'.$r['opt1'].'"></td>
              <td style = "text-align: center;">'.$r['opt1'].'</td>
              <td style = "text-align: center;">'.$r['opt2'].'</td>
              <td style = "text-align: center;">'.$r['opt3'].'</td>
            </tr>';
        }
        $output .= '
            <tr>
              <th style = "text-align: center;"></th>
              <th style = "text-align: center;">метры</th>
              <th style = "text-align: center;">кол/стержней</th>
              <th style = "text-align: center;">кг</th>
            </tr>
            <tr>
              <th style = "text-align: center;">Итого:</th>
              <th style = "text-align: center;"> <input style = "text-align: center;" type="number" class = "cacl_input form-control"  min="0" max="1000000" value = "300"> </th>
              
              <th style = "text-align: center;"><span id = "number_rods">0</span></th>
              <th style = "text-align: center;"><span id = "kg">0</span></th>
            </tr>
          </tbody>
        </table>
        <br><br><br>
        ';
        
        $site->js_scripts .= '
        <script type="text/javascript">
          $(function() {  
            cacl_arm();
            
            $(".group_radio").on("ifChecked", function(event){
              cacl_arm();
            });
            
            $(".cacl_input").bind("change paste keyup", function() {
              cacl_arm();
            });
          });
          
          
          function cacl_arm() {
            var active_item = $("input[name=group_radio]:checked");
            var o1 = active_item.data( "o1" );
            var o2 = active_item.data( "o2" );
            var o3 = active_item.data( "o3" );
            var meters = parseFloat($(".cacl_input").val());
            
            if(meters < 0) meters = 0;
            if(meters > 1000000) meters = 1000000;
            
            var kg = Math.ceil(o3 * meters * 1000) / 1000;
            var number_rods = Math.ceil( (meters / o2) * 1000) / 1000;
            
            $("#kg").html(kg);
            $("#number_rods").html(number_rods);
            
            console.log( "o1 = " + o1 + "; o2 = " + o2 +"; o3 = " + o3 + "; meters = " + meters + ";");
            console.log( "kg = " + kg + "; number_rods = " + number_rods +";");
          }
        </script>
        ';
      }
    }
    
    return $output;
  }
  
  static function show_calculator_rabitsy_quantity_rolls(&$site, $cid){
    $output = '';
    
    $site->bread = self::show_path_link($cid, '', "→", false); // Хлебные крошки 
    
    $output .= '
                  <div class = "content_box full_page" >
                    <h1 class = "c_h1">Калькулятор рабицы количества рулонов</h1>     
                  </div>  
    ';
    $output .= '
      <br><br>
      <table class="table table-sm table-striped" style = "max-width: 650px;">
        <thead>
          <tr>
            <th scope="col" style = "text-align: center;">Метры</th>
            <th scope="col" style = "text-align: center;">Ед. Измерения</th>
            <th scope="col" style = "text-align: center;">Ширина рулона м</th>
            <th scope="col" style = "text-align: center;">Длина рулона м</th>
          </tr>
        </thead>
        <tbody>
    ';
    #Кол-во рулонов понадобиться
    $output .= '
          <tr>
            <th style = "text-align: center;">
              <input 
                     id = "roll_metrs"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     value = "3000"> 
            </th>
            <th style = "text-align: center;">
              <select class="form-control" id = "roll_ed_izm" name = "roll_ed_izm">
                <option value = "running_meters">м/погоные</option>
                <option value = "square_meters">м/кв<sub>2</sub></option>
              </select>
            </th>
            <th style = "text-align: center;">
              <select class="form-control" id = "roll_width" name = "roll_width">
                <option value = "1.5" >1,5 м</option>
                <option value = "2">2 м</option>
              </select>
            </th>
            <th style = "text-align: center;"><span class="form-control" style = "background-color: rgba(0,0,0,.05);">10</span><th>
          </tr>
          <tr>
            <th style = "text-align: center;">Итого:</th>
            <th style = "text-align: center;"> <span id = "calc_rez">0</span> </th>
            <th style = "text-align: center;"> <span id = "calc_rez_ed_izm"></span> </th>
          </tr>
        </tbody>
      </table>
      <br><br><br>
    ';
    
    $site->js_scripts .= '
        <script type="text/javascript">
          $(function() {  
            
            cacl_rull();
            
            $("#roll_metrs").bind("change paste keyup", function() {
              cacl_rull();
            });
            
            $("#roll_ed_izm").bind("change",
              function(){
                cacl_rull();
              }
            );
            
            $("#roll_width").bind("change",
              function(){
                cacl_rull();
              }
            );
         
          });
          
          
          
          
          function cacl_rull() {
            var meters = parseFloat($("#roll_metrs").val());
            var roll_ed_izm = $("#roll_ed_izm option:selected").val();
            var roll_width  = $("#roll_width option:selected").val();
            var rez = 0;
            var rez_ed_izm = "рулон";
            /*var rez_ed_izm = "м/кв2";*/
            
            if(meters < 0) meters = 0;
            if(meters > 1000000) meters = 1000000;
            
            if(roll_ed_izm == "running_meters"){
              rez = meters/10;
            }else{
              rez = Math.ceil( meters/(roll_width * 10) * 1000) / 1000;
              /*rez_ed_izm = "м/погоные";*/
            }
            
            
            $("#calc_rez").html(rez);
            $("#calc_rez_ed_izm").html(rez_ed_izm);
            
            console.log( "meters = " + meters + "; roll_ed_izm = " + roll_ed_izm +"; roll_width = " + roll_width + "; meters = " + meters + ";");
            
          }
        </script>
        ';
    
    return $output;
  }
  
  static function show_calculator_mason_grid(&$site, $cid){
    $output = '';
    
    $site->bread = self::show_path_link($cid, '', "→", false); // Хлебные крошки 
    
    $output .= '
                  <div class = "content_box full_page" >
                    <h1 class = "c_h1">Калькулятор сетки кладочной</h1>     
                  </div>  
    ';
    
    $output .= '
      <br><br>
      <table class="table table-sm table-striped" style = "max-width: 100%;">
        <thead>
          <tr>
            <th scope="col" style = "text-align: center; vertical-align: middle;">Кол-во квадратных метров </th>';
            /*<th scope="col" style = "text-align: center; vertical-align: middle;">Ячейка по ширине мм</th>
            <th scope="col" style = "text-align: center; vertical-align: middle;">Ячейка по длине мм</th>*/
    $output .= '
            <th scope="col" style = "text-align: center; vertical-align: middle;">Размер ячеки мм</th>
            <th scope="col" style = "text-align: center; vertical-align: middle;">Ширина м</th>
            <th scope="col" style = "text-align: center; vertical-align: middle;">Длина м</th>';
            /*<th scope="col" style = "text-align: center; vertical-align: middle;">кол-во прутков по ширине</th>
            <th scope="col" style = "text-align: center; vertical-align: middle;">кол-во прутков по длине</th>*/
    $output .= '
            <th scope="col" style = "text-align: center; vertical-align: middle;">Диаметр прута</th>
            <th scope="col" style = "text-align: center; vertical-align: middle;">ГОСТ или ТУ</th>
          </tr>
        </thead>
        <tbody>
    ';
    #Кол-во рулонов понадобиться
    $output .= '
          <tr>
            <th style = "text-align: center;">
              <input 
                     id = "number_square_meters"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="0.1"
                     value = "3000.0"> 
            </th>';
            /*<th style = "text-align: center;">
              <input 
                     id = "cell_by_width"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="1"
                     value = "50"> 
            </th>
            
            <th style = "text-align: center;">
              <input 
                     id = "cell_by_length"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="1"
                     value = "50"> 
            </th>*/
    $output .= '
            <th style = "text-align: center;">
              <select id = "size_masonry_cell"
                      class = "cacl_input form-control">
                <option class = "gost_size" value = "50">50х50</option>
                <option class = "gost_size" value = "100">100х100</option>
                <option class = "gost_size" value = "150">150х150</option>
                <option class = "gost_size" value = "200">200х200</option>';
                /*<option class = "tu_size" style="display: none;" value = "75">75х75</option>
                <option class = "tu_size" style="display: none;" value = "125">125х125</option>
                <option class = "tu_size" style="display: none;" value = "175">175х175</option>
                <option class = "tu_size" style="display: none;" value = "225">225х225</option>*/
    $output .= '
                <option class = "tu_size" style="display: none;" value = "75">50х50</option>
                <option class = "tu_size" style="display: none;" value = "125">100х100</option>
                <option class = "tu_size" style="display: none;" value = "175">150х150</option>
                <option class = "tu_size" style="display: none;" value = "225">200х200</option>
              </select> 
            </th>
            
            <th style = "text-align: center;">
              <input 
                     id = "width"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="0.01"
                     value = "1.00"> 
            </th>
            
            <th style = "text-align: center;">
              <input 
                     id = "length"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="0.01"
                     value = "2.00"> 
            </th>';
            /*

            <th style = "text-align: center;">
              <input 
                     id = "number_bars_width"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="1"
                     value = "20"> 
            </th>
            <th style = "text-align: center;">
              <input 
                     id = "number_bars_length"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="1"
                     value = "40"> 
            </th>
*/
            
    $output .= '
            <th style = "text-align: center;">';
              /*<input 
                     id = "weight"
                     class = "cacl_input form-control" 
                     style = "text-align: center;" 
                     type="number" 
                     min="0" 
                     max="1000000" 
                     step="0.01"
                     value = "0.08"> */
    $output .= '
              <select id = "weight"
                      class = "cacl_input form-control" 
                      style = "text-align: center; text-align-last: center;" >
                <option value = "0.045">3</option>
                <option value = "0.08" selected >4</option>
                <option value = "0.14">5</option>
                <option value = "0.222">6</option>
                <option value = "0.395">8</option>
                <option value = "0.617">10</option>
                <option value = "0.888">12</option>
                <option value = "1.21">14</option>
                <option value = "1.58">16</option>
                <option value = "2">18</option>
                <option value = "2.47">20</option>
                <option value = "2.98">22</option>
                <option value = "3.85">25</option>
                <option value = "4.83">28</option>
                <option value = "6.31">32</option>
                <option value = "7.986">36</option>
                <option value = "9.86">40</option>
              </select>
            </th>';
            /*<th style = "text-align: center;"><span class="form-control" style = "background-color: rgba(0,0,0,.05);">ГОСТ или ТУ </span><th>*/
    $output .= '
            <th style = "text-align: center;">
              <select id = "type_masonry_cell"
                      class = "cacl_input form-control" 
                      style = "text-align: center; text-align-last: center;" >
                <option value = "masonry_gost">ГОСТ</option>
                <option value = "masonry_tu">ТУ</option>
              </select>
            <th>
          </tr>
          <tr>
            <th style = "text-align: center; vertical-align: middle;">';
            #кол-во прутков по ширине
    $output .= '
            </th>
            <th style = "text-align: center; vertical-align: middle;">';
            #кол-во прутков по длине
    $output .= '
            </th>
            <th style = "text-align: center; vertical-align: middle;">вес 1 сетки:</th>
            <th style = "text-align: center; vertical-align: middle;" colspan = "2">кол-во сеток понадобиться:</th>
            <th style = "text-align: center; vertical-align: middle;" colspan = "2">Вес всей партии кг:</th>
          </tr>
          <tr>
            <th style = "text-align: center;"><span id = "calc_number_bars_width" style = "visibility: hidden;">0</span></th>
            <th style = "text-align: center;"><span id = "calc_number_bars_length" style = "visibility: hidden;">0</span></th>
            <th style = "text-align: center;"><span id = "calc_weight_one_grid">0</span></th>
            <th style = "text-align: center;" colspan = "2"><span id = "calc_number_grids">0</span></th>
            <th style = "text-align: center;" colspan = "2"> <span id = "calc_weight_whole_batc">0</span> </th>
          </tr>
          
        </tbody>
      </table>
      <br><br><br>
    ';
    
    $site->js_scripts .= '
        <script type="text/javascript">
          $(function() {  
            
            
            $("#type_masonry_cell").bind("change paste keyup", function() { 
              var type_masonry_cell = $( this ).val();
              
              if( type_masonry_cell == "masonry_gost" ){
                $( "#size_masonry_cell option" ).removeAttr("selected");
                $( ".tu_size" ).hide();
                $( ".gost_size" ).show();
                $( ".gost_size" ).filter( ":first" ).attr("selected", "selected");
              }else if( type_masonry_cell == "masonry_tu" ){
                $( "#size_masonry_cell option" ).removeAttr("selected");
                $( ".gost_size" ).hide();
                $( ".tu_size" ).show();
                $( ".tu_size" ).filter( ":first" ).attr("selected", "selected");
              }
              
              console.log( "type_masonry_cell = " + type_masonry_cell );
              
              /*$("#type_masonry_cell").*/
              cacl_grid(); 
            });
            
            cacl_grid();
            
            $("#number_square_meters").bind("change paste keyup", function() { cacl_grid(); });
            $("#cell_by_width").bind("change paste keyup", function() { cacl_grid(); });
            $("#cell_by_length").bind("change paste keyup", function() { cacl_grid(); });
            
            $("#size_masonry_cell").bind("change paste keyup", function() { cacl_grid(); });
            
            $("#width").bind("change paste keyup", function() { cacl_grid(); });
            $("#length").bind("change paste keyup", function() { cacl_grid(); });
            /*$("#number_bars_width").bind("change paste keyup", function() { cacl_grid(); });
            $("#number_bars_length").bind("change paste keyup", function() { cacl_grid(); });*/
            $("#weight").bind("change paste keyup", function() { cacl_grid(); });
         
          });
          
          
          
          
          function cacl_grid() {
            var number_square_meters = parseFloat($("#number_square_meters").val());
            /*var cell_by_width = parseFloat($("#cell_by_width").val());
            var cell_by_length = parseFloat($("#cell_by_length").val());*/
            
            var cell_by_width = parseFloat($("#size_masonry_cell").val());
            var cell_by_length = parseFloat($("#size_masonry_cell").val());
            
            var width = parseFloat($("#width").val());
            var length = parseFloat($("#length").val());
            var weight = parseFloat($("#weight").val());
            
            
            
            var rez_number_bars_width = 0;
            var rez_number_bars_length = 0;
            var rez_weight_one_grid = 0;
            var rez_number_grids = 0;
            var rez_weight_whole_batch = 0;
            
            number_square_meters = validate(number_square_meters);
            cell_by_width = validate(cell_by_width / 1000);
            cell_by_length = validate(cell_by_length / 1000);
            width = validate(width);
            length = validate(length);
            weight = validate(weight);
            
            console.log( (width / cell_by_width) * weight * width);
            
            rez_number_bars_width = width / cell_by_width;
            
            rez_number_bars_length = length / cell_by_length;
            
            rez_weight_one_grid =  
               (
                  ( width * rez_number_bars_length ) + 
                  ( length * rez_number_bars_width )  
                ) * weight;
            
            
            rez_number_grids = number_square_meters / ( width * length );
            
            rez_weight_whole_batch = rez_weight_one_grid * rez_number_grids;
            rez_weight_one_grid = Math.ceil( rez_weight_one_grid * 1000) / 1000;
            rez_number_bars_width = Math.ceil( rez_number_bars_width * 1000) / 1000;
            rez_number_bars_length = Math.ceil( rez_number_bars_length * 1000) / 1000;
            
            rez_number_grids = Math.ceil( rez_number_grids * 1000) / 1000;
            rez_weight_whole_batch = Math.ceil( rez_weight_whole_batch * 1000) / 1000;
            
            $("#calc_number_bars_width").html(rez_number_bars_width);
            $("#calc_number_bars_length").html(rez_number_bars_length);
            $("#calc_weight_one_grid").html(rez_weight_one_grid);
            $("#calc_number_grids").html(rez_number_grids);
            $("#calc_weight_whole_batc").html(rez_weight_whole_batch);
            
            console.log( "cell_by_width = " + cell_by_width + "; cell_by_length = " + cell_by_length + ";");
            console.log( "width = " + width + "; length = " + length + ";");
            console.log( "rez_number_bars_width = " + rez_number_bars_width + "; rez_number_bars_length = " + rez_number_bars_length + ";");
            
            console.log( "rez_weight_one_grid = " + rez_weight_one_grid + "; rez_number_grids = " + rez_number_grids +"; rez_weight_whole_batch = " + rez_weight_whole_batch + "; width = " + width + ";");
            
          }
          
          function validate(data){
            if(data < 0) data = 0;
            if(data > 1000000) data = 1000000;
            
            return data;
          }
        </script>
        ';
    
    return $output;
  }

  static function tmp_cat_item(&$arr, $i = 1){
    
    $output = '';
    
    if(!isset($site->units) || !$site->units){
      $unit_items =  db::select("*", DB_PFX."units" );
      
      foreach($unit_items as $unit_item){
        $site->units[ $unit_item['id'] ] = $unit_item['reduction'];
      }
    }
    
    if( isset($site->units[ $arr['units_id'] ]) && $site->units[ $arr['units_id'] ] ){
      $arr['units'] = $site->units[ $arr['units_id'] ];   
    }else{
      $arr['units'] = "шт";
    }
    /*
    $output .= '
            <tr>
              <th scope="row">'.$i.'</th>
              <td>
                <a href="/'.$arr['url'].'">
                  <div class="card_img_box">  
                    '.$arr['image'].'
                    <div class="card_img_shadow"></div>
                  </div>
                </a> 
              </td>
              <td>
                <a href="/'.$arr['url'].'">'.$arr['title'].'</a>
                <div>
                  <table class = "ttc">';
    $output .= self::get_item_characteristic('Артикул', $arr['article']);
    $output .= '
                  </table>
                </div>
              </td>
              <td>
                <div class = "item_unit">  
                  <span class="buy_price cost">'.$arr['price'].'</span> <i class="fa fa-rub" aria-hidden="true"></i> / <span class = "units">'.$arr['units'].'</span>
                </div>
              </td>
              
              <td>
                <div class = "buy_count_cont"> 
                  <input  class = "store_buy_input bye_count" 
                        data-id = "'.$arr['id'].'" 
                        data-min = "'.$arr['min_count'].'" 
                        data-price = "'.$arr['price'].'"
                        data-portion = "'.$arr['portion'].'" 
                        type="number" 
                        value = "'.$arr['min_count'].'"  /> 
                </div>
              </td>
              <td  >
                <div class="store_buy_price" style = "margin-right: 15px;">
                  <span class="buy_price cost" id = "price_id_'.$arr['id'].'" data-id = "'.$arr['id'].'" >'.$arr['price'].'</span> 
                                          <i class="fa fa-rub" aria-hidden="true"></i>
                </div>
              </td>
              <td>
              	<div class = "store_buy_order_box">
                  <button class="buy_btn good_buy store_buy"
                          id = "store_id_'.$arr['id'].'" 
                          data-id="'.$arr['id'].'" 
                          data-count = "'.$arr['min_count'].'"
                          >В корзину</button>
                </div>
              </td>
              
            </tr>
    ';*/
    $output .= '
            <div class="card tac">
              <a href="/'.$arr['url'].'">
                <div class="card_img_box " >  
                  '.$arr['image'].'
                  <div class="card_img_shadow"></div>
                </div>
              </a> 
              <div class="card-body">
                <p class="card-title"><a href="/'.$arr['url'].'">'.$arr['title'].'</a></p> 
              </div>
              <div class="card-footer">
                <div class="available">'.$arr['availability'].'</div>
                <div class="npr">
                  <span class="price">'.number_format( $arr['price'], 0, ',', ' ').' </span><span class="rouble">руб.</span>
                </div>
                <div class = "card_btn_box">
                  <button class="buy_btn good_buy store_buy btn btn-primary btn-md" data-id="'.$arr['id'].'" >Купить</button>
                </div>
              </div>
            </div>
    ';
    
    return $output;
    
  }
  
  static function tmp_cat_page(&$arr){
    
    $output = '';

    $output .= '
                  <div class = "content_box full_page" >
                    <h1 class = "c_h1">'.$arr['title'].'</h1>';
    if( isset($arr['image'])     && $arr['image']     ) $output .= $arr['image'];
    if( isset($arr['longtxt2'])  && $arr['longtxt2']  ) $output .= '<div class = "clt1">'.$arr['longtxt2'].'</div>';
    if( isset($arr['cat_items']) && $arr['cat_items'] ) $output .= $arr['cat_items'];
    if( isset($arr['sub_cats'])  && $arr['sub_cats']  ) $output .= $arr['sub_cats'];
    if( isset($arr['longtxt3'])  && $arr['longtxt3']  ) $output .= '<div class = "clt2">'.$arr['longtxt3'].'</div>';
    $output .= '
                  </div>  
    ';
    
    return $output;
    
  }
  
  static function tmp_full_item(&$arr){
    #pri($arr);
    
    $output = '';
    
    $output .= '

                        <div class = "full_item_box">
                          <div class = "full_item">
                          
                            <div class="row">
                              <div class="col-12 col-md-5 full_item_left">
                                '.$arr['image'].'
                              </div>
                              <div class="col-12 col-md-7">
                                <h1>'.$arr['title'].'</h1> 
                                
                                <div class = "store_buy_box">
                                  <div class = "row align-items-center ">
                                    
                                    <div class = "col-12 col-md-auto ">
                                      <div class="store_buy">
                                        <div class="store_buy_price">
                                          <span class="buy_price cost price" id = "price_id_'.$arr['id'].'" data-id = "'.$arr['id'].'" >'.number_format( $arr['price'], 0, ',', ' ').'</span> <span class="rouble">руб.</span>';
                                          #<span class = "units">'.$arr['units'].'</span>
                                          #<i class="fa fa-rub" aria-hidden="true"></i>
    $output .= '                          
                                        </div>
                                      </div>
                                    </div>
                                    
                                    <div class = "col-12 col-md-auto store_buy_order_box">
                                      <button class="buy_btn good_buy store_buy btn btn-primary btn-lg"
                                              id = "store_id_'.$arr['id'].'" 
                                              data-id="'.$arr['id'].'" 
                                              ><i class="fas fa-cart-plus fa-md"></i>&nbsp; В корзину</button>
                                      
                                    </div>
                                  </div>
                                  
                                  <div class = "row align-items-center ">
                                    <div class = "col-12 store_buy_info">
                                      <div class="row characteristic_box">
                                        <div class="col-12 col-md">
                                          <table>';
    $output .= self::get_item_characteristic('Артикул', $arr['article']);
    $output .= '
                                          </table>';
    if( isset($arr['longtxt1']) && $arr['longtxt1']){
      $output .= ''.$arr['longtxt1'].'';
    }
    $output .= '
                                        </div>
                                        
                                      </div>

                                    </div>
                                  </div>';
    #$fast_title = '<a href = \''.$_SERVER["REQUEST_URI"].'\' target = \'_blank\'>'.preg_replace('#[^A-ZА-Яa-zа-я\d\s\+\-\_\,\«\/\»\(\)]#u', '', $arr['title'])."</a>";
    $fast_title = preg_replace('#[^A-ZА-Яa-zа-я\d\s\+\-\_\,\«\/\»\(\)]#u', '', $arr['title']);
    $output .= '
                                  <div class = "row align-items-center ">
                                    <div class = "col-12">
                                      <button class="btn btn-success btn-lg flmenu1 " data-id="0" data-target="#myModal" data-title="Быстрый заказ <br>'.$fast_title.'" data-toggle="modal"><i class="fas fa-fighter-jet fa-md"></i>&nbsp; Быстрый заказ</button>
                                    </div>
                                  </div>';
    if( isset($arr['whatsap_phone']) && $arr['whatsap_phone'] ){
      $output .= '
                                  <div class = "row align-items-center ">
                                    <div class = "col-12">
                                      <a href="https://wa.me/'.$arr['whatsap_phone'].'" target = "_blank" class = "whatsapp_link btn-success btn-lg" ><i class="fab fa-whatsapp"></i>&nbsp; '.$arr['whatsap_phone'].'</a> &nbsp; Вопрос по whatsapp 
                                    </div>
                                  </div>';                     
    }
    
    $output .= '
                                </div> 
                              </div>
                            </div>
    ';

    
    /*if( isset($arr['longtxt2']) && $arr['longtxt2']){
      
      $output .= '
                            <div class="full_item_descr_box">
                              <div class="full_item_descr_title_box">
                                <div class="full_item_descr_title">Описание</div>
                              </div>  
                              <div class="full_item_descr_cont">
                                '.$arr['longtxt2'].'
                              </div>
                            </div>';
    }*/
    $output .= '
                            '.$arr['files'].'
                            
                          </div>
                        </div>
    ';
    $nav_item = $tab_pane = '';
    if( isset($arr['longtxt2']) && $arr['longtxt2']){
      $nav_item .= '
      <li class="nav-item">
        <a class="nav-link active" id="circumscribing-tab" data-toggle="tab" href="#circumscribing" role="tab" aria-controls="circumscribing" aria-selected="true">Описание</a>
      </li>';
      $tab_pane .= '
      <div class="tab-pane fade show active" id="circumscribing" role="tabpanel" aria-labelledby="circumscribing-tab">
        '.$arr['longtxt2'].'
      </div>';
      
      if( isset($arr['longtxt3']) && $arr['longtxt3']){
        $nav_item .= '
      <li class="nav-item">
        <a class="nav-link" id="specifications-tab" data-toggle="tab" href="#specifications" role="tab" aria-controls="specifications" aria-selected="false">Характеристики</a>
      </li>';
        $tab_pane .= '
      <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
        '.$arr['longtxt3'].'
      </div>';
      }
      $output .= '
                            <div class="full_item_descr_box"> 
                              <div class="full_item_descr_cont">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                  '.$nav_item.'
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                  '.$tab_pane.'
                                </div>
                              </div>
                            </div>';
      
    }
    
    /*
    
    
                                            <span class = "buy_count">1500</span> 
                                            <span>шт</span> 
                                      <button class="buy_btn good_buy" 
                                                data-toggle="modal" 
                                                data-target="#myModal"
                                                data-id="'.$arr['id'].'"
                                                data-title=\'<a href = "http://'.$_SERVER["SERVER_NAME"].'/'.$arr['url'].'">'.$arr['title'].'</a>  \' 
                                              > 
                                      Купить
                                      </button>
    */
    
    return $output;
  }
  
  static function get_item_characteristic($title, $val){
    $output = '';
    
    if($val){
      $output .= '
      <tr>
        <td class = "first_col"> <span>'.$title.' </span> <div class="first_col_line"></div> </td>
        <td>'.$val.'</td></tr>
      ';
    }
    
    return $output;
  }
}