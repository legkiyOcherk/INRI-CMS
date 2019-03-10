<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php'); 

class MineCarusel extends Carusel{
  
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
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>';
    if($link){
                $output .= '
                    <br><a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">Ссылка: '.trim(strip_tags($link)).'</a>';
              }
    $output .= '
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
   
}

$date_arr = array(
    'title'     => 'Название',
    'link'      => 'Ссылка',
    'txt1'      => 'Текст',
    'longtxt1'  => 'Описание',
    'hide'      => 'Скрыть',
    'img_alt'   => 'Alt изображение',
    'img_title' => 'Title изображение',
  );

$carisel = new MineCarusel('carusel', $date_arr, true, true);


$carisel->setHeader('Слайдер на главной');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsPager(true);
$carisel->setIsLog(true);
$carisel->setImg_ideal_width(1920);  
$carisel->setImg_ideal_height(666); 
$carisel->checkbox_array = array('hide');                # Галочка в форме

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
