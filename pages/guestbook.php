<?php
// Include CMS System
/**--**/ include "../inc/config.php";
//------------------------------------------------
// Site Informations
/**--**/  $meta['title'] = "G�stebuch";
/**--**/  $meta['page_id'] = 5;
//------------------------------------------------
init(dispComments($meta['page_id'],0),$meta);