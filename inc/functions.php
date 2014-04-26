<?php

    require_once 'cachesystem/fastcache.php';
    require_once 'phpmailer/class.phpmailer.php';
    
    phpFastCache::$storage = "auto";
    phpFastCache::setup("path", "../inc/_cache");

    function show($file_content = "", $tags = array(null => null))
    {
        global $path;

        if(file_exists($path['style']."/".$file_content.".html"))
        {
            $file_content = file_get_contents($path['style']."/".$file_content.".html");		
        }
        foreach($tags as $name => $value)
        {
            $file_content = str_replace('['.$name.']', $value, $file_content);
        }
        return $file_content;
    }
    
    function getUserInformations($userid, $informations)
    {
        return db("SELECT ".$informations." FROM users WHERE id = ".$userid, 'object');
    }
    
    function randomstring($length = 6) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        srand((double)microtime()*1000000);
        $i = 0;
        while ($i < $length) 
        {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
	
    function customHasher($pw, $rounds)
    {
        global $config;
        $hash = $pw;
        for ($i = 0; $i < $rounds; $i++)
        {
            if (!($i%3.5)){
                $hash = sha1($hash.$config['salt']);
            } else {
                $hash = md5($config['salt'].$hash);
            }
        }
        sha1($hash); 
        return $hash;
    }

    function sqlString($param) {
        return (NULL === $param ? "NULL" : "'".mysql_real_escape_string($param)."'");
    }
    
    function sqlStringCon($param) {
        return (NULL === $param ? "NULL" : "'".mysql_real_escape_string(con($param))."'");
    }

    function sqlInt($param) {
        return (NULL === $param ? "NULL" : intVal($param));
    }

    function get_gravatar( $email, $s = 80, $img = false) 
    {
        $d = 'wavatar';
        $r = 'g';
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&amp;d=$d&amp;r=$r";
        if ( $img ) 
        {
                $url = '<img src="' . $url . '" />';
        }	
        return $url;
    }

    function msg($msg, $kind = 'stock')
    {
        global $path, $meta;
        switch ($kind)
        {
            default:
                $file = show("msg/msg_stock");     
        }

        $meta['title'] = $msg;
        $msg = show ($file, array(	"msg" => 'Housslave: '.$msg,
                                    "link" => $_SESSION['last_site']));
        backSideFix();
        return $msg;
    }

    function getNews($groupid = 0)
    {
            $news_posts = db("Select * From news where public_show = 1 AND grp = ".$groupid." ORDER BY date DESC");
            $list_news = "";
            while ($get_news = _assoc($news_posts))
            {
                $list_news .= show("news/post",array(
                    "news_headline" => $get_news['title'],
                    "news_date" => date("F j, Y, G:i",$get_news['date']),
                    "news_content" => $get_news['content'],
                    "id" => $get_news['id'],
                    "post_comment" => '<a href="../pages/news.php?id='.$get_news['id'].'#comments">Kommentare: '.db("SELECT count(id) as counted FROM comments where site = 2 AND subsite = ".$get_news['id'],'object')->counted.'</a>'
                                                ));
            }
            if ($list_news == ""){
                $list_news = _news_not_found;
            }
            return show("news/layout_posts", array("posts" => $list_news));
    }

    function permTo($permission)
    {
        if (isset($_SESSION['group_main_id'])) {
            $perm = db("SELECT ".$permission." From groups WHERE id = ".sqlInt($_SESSION['group_main_id']),'object')->$permission;
            return $perm;
        }
        return 0;
    }

    function con($txt) {
    $txt = stripslashes($txt);
    $txt = str_replace("& ","&amp; ",$txt);
    $txt = str_replace("[","&#91;",$txt);
    $txt = str_replace("]","&#93;",$txt);
    $txt = str_replace("\"","&#34;",$txt);
    $txt = str_replace("<","&#60;",$txt);
    $txt = str_replace(">","&#62;",$txt);
    $txt = str_replace("(", "&#40;", $txt);
    $txt = str_replace("'", "&lsquo;", $txt);
    $txt = str_replace("(", "&#40;", $txt);
    return str_replace(")", "&#41;", $txt);
}

  function check_email_address($str_email_address) {
    if('' != $str_email_address && !((eregi("^[_\.0-9a-z-]+@([0-9a-z-]+\.)+[a-z]{2,6}$",$str_email_address)))) {
      return false;
    } else {
      return true;
    }
  }
  
  function getCommentBox($site, $subsite = 0)
  {
      return getComments($site,$subsite).dispCommentInput();
  }
  
  function getComment($comment)
  {
    $com_disp = "";
    $date = ((time()-$comment['date'])/60);
    if ($date*60 < 60){
        $out = (int)($date*60) . " sec ago";
    }
    else if ($date < 60) {
        $out = (int)$date." min ago";
    }
    else if ($date > 59 && $date/60 < 24){
        $out = "vor ".(int)($date/60)."h at ". date("h:i A",$comment['date']);
    }
    else if ($date/60 > 23 && $date/60/24 < 4) {
        $out = (int)($date/60/24)." day(s) ago at ". date("h:i A",$comment['date']);
    }
    else {
        $out = date("F j, g:i a", $comment['date']);
    }

    $comment_view = substr($comment['content'], 0, 200);
    $comment_expand = substr($comment['content'], 0, -200);
    $gravatar = get_gravatar($comment['email'], 52, false);
    $com_disp .= show("ucp/comment", array("user" => $comment['name'],
                                            "gravatar" => $gravatar,
                                            "content" => $comment_view,
                                            "content_expand" => $comment_expand,
                                            "id" => $comment['id'],
                                            "date" => $out));

    
    return $com_disp;
    }
    
  
  function getComments($site = 0, $subsite = 0)
  {
    $comments = db("SELECT "
                    . "c.id as id,"
                    . "c.content as content,"
                    . "u.name as name,"
                    . "u.email as email,"
                    . "c.date as date"
                . " FROM "
                    . "comments as c,"
                    . " users as u "
                . "WHERE c.site = ".$site." "
                . "AND u.id = c.userid "
                . "AND c.active = 1 "
                . "AND c.subsite = ".$subsite." "
                . "ORDER BY c.date ASC"
            );
    
    $output ='';
    while ($comment = _assoc($comments))
    {
        $output .= getComment($comment);
    }
    return $output;
  }
  
  function dispCommentInput($site = 0, $subsite = 0)
  {
     $gravatar = get_gravatar($_SESSION['email'], 52, false);
     return show("ucp/comment_input", 
          array(
               "gravatar" => $gravatar,
               "user" => $_SESSION['name'],
               "site" => $site,
               "subsite" => $subsite,
                 )); 
  }
  
  function dispComments($site, $subsite)
  {
     return show("ucp/comment_body", array("comments" => getComments($site,$subsite), "input" => dispCommentInput($site,$subsite)));
  }
  
    function updateRSS() {
        global $path;

        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $roo = $xml->createElement('rss');
        $roo->setAttribute('version', '2.0');
        $xml->appendChild($roo);
        $cha = $xml->createElement('channel');
        $roo->appendChild($cha); 
        $new = $xml->createElement('title', 'CMS - News');
        $cha->appendChild($new); 
        $new = $xml->createElement('description','D4ho.de - CMS');
        $cha->appendChild($new);
        $bld = $xml->createElement('image');
        $cha->appendChild($bld);
        $bld ->appendChild($xml ->createElement('url',"http://dummyimage.com/120x61"));


        $qry = db("SELECT * FROM news WHERE grp = 2 AND public_show = 1 ORDER BY date DESC");
        while($rss_feed = mysqli_fetch_assoc($qry))
        {
            $new = $xml->createElement('item');
            $cha->appendChild($new); 


            $rss['title'] = $rss_feed['title'];
            $image = '&lt;img style="border: 0px none; margin: 0px; padding: 0px;" align="right" alt="" width="60" height="60" src="'.$rss_feed['main_image'].'" &gt;';
            $rss['description'] = $image.$rss_feed['description'];
            $rss['language'] = $lang;
            $rss['link'] = "http://cms.d4ho.de/pages/news.php?id=".$rss_feed['id'];
            $rss['pubDate'] = date("D, j M Y H:i:s ", $rss_feed['date']);
            $hea = $xml ->createElement('image');
            $new ->appendChild($hea);					
            $img = $xml ->createElement('url',$rss_feed['main_image']);
            $hea ->appendChild($img);

            foreach ($rss as $tag => $value)
            {
                    $hea = $xml ->createElement($tag, utf8_encode($value));
                    $new ->appendChild($hea);
            }
        }

        if($xml->save($path['upload'].'rss/rss.xml')) {
                return true;
        }		
        return false;
    }
        
    function convertMatch($matches)
    {
        $new = array();
        foreach ($matches as $value)
        {
            if (defined("_".$value)) {
                $new["s_".$value] = constant("_".$value);
            } else {
                $new["s_".$value] = "STRING_NOT_FOUND_".strtoupper($value);
            }
        }
        return $new;
    }

    function convertMatchDyn($matches)
    {
        foreach ($matches as $value)
        {
            $new["dyn_".$value] = show("allround/ajax_loading", array('panel_name' => $value));
        }
        return $new;
    }
    
    function searchBetween($start_tag, $String ,$end_tag)
    {
        if (preg_match_all('/'.preg_quote($start_tag).'(.*?)'.preg_quote($end_tag).'/s', $String, $matches)) {
                return $matches[1];
        }
        return array();
    }
    
    function backSideFix() {
        $_SESSION['current_site'] = $_SESSION['last_site'];
    }
    
    function tagConverter($tags) {
        $tags = str_replace("/",",",$tags);
        $tags = str_replace(" , ",",",$tags);
        $tags = str_replace(" ,",",",$tags);
        $tags = str_replace(", ",",",$tags);
        return explode(",", $tags);
    }
    
    function sendMessage($sender, $receiver, $content, $title, $email = "",$server = false) {
        sendmail($content, $title, getUserInformations($receiver, 'email')->email);
        return up('INSERT INTO messages ('
            . 'sender_id,'
            . 'receiver_id,'
            . 'opened,'
            . 'inbox,'
            . 'outbox,'
            . 'email,'
            . 'date,'
            . 'content,'
            . 'title'
            . ') VALUES ( '
            . sqlInt($sender).','
            . sqlInt($receiver).','
            . '0,1,1,'
            . sqlString($email).','
            . time().','
            . sqlString($content).','
            . sqlString($title).')');
    }
    
    function sendmail($content, $subject, $receiver) {
        $mail = new PHPMailer();
        $mail->isSendmail();
        
        $mail->SetFrom('admin@'.$_SERVER['HTTP_HOST'] , 'mailFrom->'.$_SERVER['HTTP_HOST']);
        
        $mail->CharSet  =  "utf-8";
        $mail->Subject = $subject;
        $mail->msgHTML($content);
        $mail->addAddress($receiver);
        if ($mail->send()) {
            return true;
        }
        return false;
    }