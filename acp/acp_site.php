<?php
// Site Informations
/**--**/  $meta['title'] = "Seite Erstellen";
//------------------------------------------------
// Site Permissions
/**--**/ if (!permTo("site_create")) $error = msg(_no_permissions);
//------------------------------------------------
 
if ($do == "")
{
	switch ($action)
	{
		default:
				$sites = db("select title,id from sites");
			 $options = "";
			while ($site = _assoc($sites))
			{
				$options .= show("site/option", array("id" => $site['id'], "title" => $site['title']));
			}
			   
				$content = show("acp/acp_site_create", array("options_addafter" => $options));		
		break;
	}
}

switch ($do)
{
    case 'new_site':
            if(up("INSERT INTO sites (`id`, `title`, `content`, `author`, `keywords`, `description`, `subfrom`, `position`, lastedit,editby,date) 
                   VALUES (NULL, ".sqlString($_POST['title']).", '"._site_content_input."', ".sqlString($_SESSION['name']).", ".sqlString($_POST['keywords']).", ".sqlString($_POST['description']).", ".sqlInt($_POST['subfrom']).", 0, '', '', '".time()."')")){
            $content = msg(_site_created_sucessful);
            }
            break;
}
 