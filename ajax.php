<?php
require_once('require.php');

if(isset($_POST['hide_hpv_panel'])){
  $hide_hpv_panel = $_POST['hide_hpv_panel'];
  
  if($hide_hpv_panel){
    $_SESSION['is_having_poor_vision'] = false;
    #echo $_SESSION['is_having_poor_vision'];
    echo "ok";
  }else{
    echo "error";
  }
 
}

if(isset($_POST['show_hpv_panel'])){
  $show_hpv_panel = $_POST['show_hpv_panel'];
  
  if($show_hpv_panel){
    $_SESSION['is_having_poor_vision'] = true;
    #echo $_SESSION['is_having_poor_vision'];
    echo "ok";
  }else{
    echo "error";
  }
 
}

if(isset($_POST['search_query'])){
  $search_quer = $_POST['search_query'];
  
  if($search_quer){
    
    if(!isset($_SESSION['search_q'])){
      $_SESSION['search_q'] = '';
    }
    
    #session_destroy();
    if($search_quer != $_SESSION['search_q']){#echo "ne sovpadaet";
      $_SESSION['search_q'] = $search_quer;
    }else{ 
    }

    echo "ok";
  }else{
    echo "error";
  }
}


if(isset($_POST['good_buy'])){
  global $PDO;
  #echo "test";
  #pri($_POST);
  
  #if(!$_POST['user_title']) return 'Нет user_title';
	#if(!$_POST['userName']) return 'Не заданно имя';
  #if(!$_POST['userMail']) return 'Не задана почта';
  if(!$_POST['userPhone']) return 'Не задана телефон';
  
  #if(!isset($_POST['id_good'])) return 'Нет id_good';
  #if(!isset($_POST['userText'])) return 'Нет userText';
  
       /* var  id_good = '.$arr['id'].';
        var  userName = $("#UserName").val();
        var  userPhone = $("#UserPhone").val();
        var  userMail = $("#UserMail").val();*/
        
  $output = '';
  $uFio = $uPhone = $uMail = $uText = $uTextMess = $error = $is_send = '';
  $error_arr = array();
  
  $send_link = $_SERVER ['HTTP_REFERER'];
  if ($send_link){
    $send_link .= ' <a href = "'.$send_link.'" target = "_blank">Перейти</a>';
  }
  
  $mail = EMail::Factory();
  $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
  
  $id_good = 0;
  #$id_good = intval($_POST['id_good']);
  $uTitle = substr(addslashes(trim($_POST['user_title'])), 0, 1000);
  #$uFio = substr(htmlspecialchars(trim($_POST['userName'])), 0, 1000);
  $uPhone = substr(htmlspecialchars(trim($_POST['userPhone'])), 0, 1000);
  #$uPostIndex = substr(htmlspecialchars(trim($_POST['userPostIndex'])), 0, 1000);
  #$uAddress = substr(htmlspecialchars(trim($_POST['userAddress'])), 0, 1000);
  #$uMail = substr(htmlspecialchars(trim($_POST['userMail'])), 0, 1000);
  #$uText = substr(htmlspecialchars(trim($_POST['userText'])), 0, 5000);
  
  if(isset($_POST['userName'])){
    $uFio = substr(htmlspecialchars(trim($_POST['userName'])), 0, 1000);
  }
  if(isset($_POST['userMail'])){
    $uMail = substr(htmlspecialchars(trim($_POST['userMail'])), 0, 1000);
  }  
  if(isset($_POST['userText'])){
    $uText = substr(htmlspecialchars(trim($_POST['userText'])), 0, 2000);
    $uText = addslashes(trim($_POST['userText']));
    $uText .= '
          <br>Страница заявки: '.$send_link.'<br>
    ';
    $uTextMess = trim($_POST['userText']);
    $uTextMess .= '
          <br>Страница заявки: '.$send_link.'<br>
    ';
  }  

  
  /*if (empty($uFio)){
    $error_arr['fio'] = 'Не введенно имя'; 
    $error = 1;
  }*/
  if (empty($uPhone)){
    $error_arr['phone'] = 'Не введенн телефон'; 
    $error = 1;
  }
  /*if (empty($uMail)){
    $error_arr['mail'] = 'Не введена почта'; 
    $error = 1;
  }*/
  //Ставим капчу
  /*require_once 'recaptcha-master/src/autoload.php';
  // Register API keys at https://www.google.com/recaptcha/admin
  $siteKey = '6LeclzkUAAAAALjr8-he8iludg6DwFZD_vEymWTF';
  $secret = '6LeclzkUAAAAADQoLudnksZt0wISdxEbN29peKoJ';
  
  if (isset($_POST['g-recaptcha-response'])){
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
  }
  if ($resp->isSuccess()){
    #echo "кангратулейшенс";
  }else{
    $error_arr['captcha'] = 'Не праильно введена капча!';
    $error = 1;
    #foreach ($resp->getErrorCodes() as $code) {
    #  echo '<tt>' , $code , '</tt> ';
    #}
  }*/
  
  if($error != 1){
    $date = date("Y-m-d h:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $goodPrice = 0;
    $userGoodId = $id_good;
    $uTitle;
    
    if($id_good){
      $sg = "
        SELECT `".DB_PFX."goods`.*, `".DB_PFX."url`.`url` 
        FROM `".DB_PFX."goods`
        LEFT JOIN `".DB_PFX."url`
        ON (`".DB_PFX."url`.`module` = '".DB_PFX."goods') AND (`".DB_PFX."url`.`module_id` = `".DB_PFX."goods`.`id`)
        WHERE `".DB_PFX."goods`.`id` = $id_good
      ";
      //echo $sg;
      $qg = mysql_query($sg);
      
      $rg = mysql_fetch_assoc($qg); 
      
      /*echo "<pre>";
      print_r($rg);
      echo "</pre>";*/
      
      $userGoodId = $rg['id'];
      $goodPrice = $rg['price'];
        
    }

    
    $st = $PDO->prepare("
          INSERT INTO `".DB_PFX."reservations` 
              (`title`, `userStatus`, `date`, `userPhone`, `userName`, `userMail`, `longTxt1`, `userIp`, `hide`) 
      VALUES  (:title,  'Новая',      :date,  :userPhone,  :userName,  :userMail,  :longTxt1,  :userIp,   0    )
    ");
        if (!$st->execute(array( 
                            'title'=>$uTitle, 
                            'date'=>$date,                                 
                            'userPhone'=>$uPhone,
                            'userName'=>$uFio,
                            'userMail'=>$uMail,
                            'longTxt1'=>$uText,
                            'userIp'=>$ip,
                            )
                          )
              ) {
            
            die('cant rew'); #ошибка
        }
    
    $nuber = $PDO->lastInsertId();
     
     
    $subject = "Поступила заявка ".$_SERVER['HTTP_REFERER'];
    $message = '
        Заявка: '.$uTitle.'<br><br>
        № заявки: '.$nuber.'<br>
        Дата: '.$date.'<br>
        Имя: '.$uFio.'<br>
        Телефон: '.$uPhone.'<br>
    ';
    if($uMail){
      $message .= '
      Почта: '.$uMail.'<br><br>';
    }
    if($uTextMess){
      $message .= '
      Текст: '.$uTextMess.'<br><br><br>';
    }
    if($goodPrice)
      $message .= 'Цена: '.$goodPrice.'<br>';
      
    $message .= '    
        <br><br>
        
        IP: '.$_SERVER['REMOTE_ADDR'].'<br>
     
        <a href = "http://'.$_SERVER["HTTP_HOST"].'/'.ADM_DIR.'/reservations.php?edits='.$nuber.'">Перейти в админку</a><br><br>
    ';
        
    $tosend = $message;
        
  	if(isset($_SESSION['city_id']) && $_SESSION['city_id']  ){
      $default_phone = db::value("val", DB_PFX."config", "name = 'phone'", 0 );
    }
        
    $res = $mail->smtpmail ($email_order, $subject, $tosend);
    //$headers = 'From: test <'.$from.'>' . "\r\n";
    
    
    /*$headers = "Content-type: text/html; charset=\"utf-8\"";
    
    $res = mail($email_order, $subject, $tosend, $headers);
    */
        
    if($res){
      $is_send = true;
    }else{
      //$output = '<script>alert("Ошибка!");</script>';
      $error_arr['send'] = "Ошибка при отправке сообщения";
    }
    
    if($is_send){
      echo "ok";
      
    }else{
      echo "Возникли сложности при отправке заявки. Вы можете позвонить по телефону";
    }
    
  }else{
    
    foreach($error_arr as $err){
      echo $err."<br>";
    }
    
    echo $output;
    
  }
  
}

if(isset($_POST['feedback'])){
	if(!$_POST['requestName']) return 'Не заданно имя';
  if(!$_POST['requestPhone']) return 'Не задана почта';
  $output = '';
  $fio = $phone = $error = $is_send = '';
  
  $mail = EMail::Factory();
  $email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
  $fio = substr(htmlspecialchars(trim($_POST['requestName'])), 0, 1000);
  $phone = substr(htmlspecialchars(trim($_POST['requestPhone'])), 0, 1000);
  
  if (empty($fio)){
    $error_arr['fio'] = 'Не введенно имя'; 
    $error = 1;
  }
  if (empty($phone)){
    $error_arr['fio'] = 'Не введенн телефон'; 
    $error = 1;
  }
  
  if($error != 1){
    $date = date("Y-m-d");
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $s = "
      INSERT INTO `".DB_PFX."feedback` 
              (`title`, `txt3`,  `date`,  `phone`,  `txt1`, `hide`) 
      VALUES  ('$fio',  'Новая', '$date', '$phone', '$ip',   0);
    ";
    
    //echo "s = $s";
      
    mysql_query($s);
    $nuber = mysql_insert_id();
     
     
    $subject = "Заказ обратного звонка ".$_SERVER['HTTP_REFERER'];
    $message = '
        № заявки: '.$nuber.'<br>
        Дата: '.date("d.m.Y h:i:s").'<br>
        Имя: '.$fio.'<br>
        Телефон: '.$phone.'<br>
        IP: '.$_SERVER['REMOTE_ADDR'].'<br>
     
        <a href = "http://'.$_SERVER["HTTP_HOST"].'/'.ADM_DIR.'/feedback.php?edits='.$nuber.'">Перейти в админку</a><br><br>
    ';
        
    $tosend = $message;
        
  	$res = $mail->send($email_order, $subject, $tosend);
        
    if($res){
      //echo "Ваше заявка на получение карты отправленна.<br> В ближайшее время мы с вами свяжемся!";
      $is_send = true;
    }else{
      //$output = '<script>alert("Ошибка!");</script>';
      $error_arr['send'] = "Ошибка при отправке сообщения";
    }
    
    if($is_send){
      //echo "Ваша заявка принята. Наш менеджер свяжется и рассказет как забрать вашу карту";
      echo "ok";
      
    }else{
      echo "Возникли сложности при отправке заявки. Вы можете позвонить по телефону";
    }
    
  }else{
    
    foreach($error_arr as $err){
      echo $err."<br>";
    }
    
    echo $output;
    
  }
  
}
