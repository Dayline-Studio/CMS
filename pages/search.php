<?php
// Include CMS System
/**--**/ include "../inc/config.php";
//------------------------------------------------
// Site Informations
/**--**/  $meta['title'] = "Search";
//------------------------------------------------

$content = show("search/head");
if (isset($_GET['tags'])) {
    $content .= search($_GET['tags']);
}

function search($tags)
{
    $tags = exTags($tags);
    foreach ($tags as $tag)
    {
        $search_results .= replaceTextBackground(searchEngine($tag),$tags);
    }
    if ($search_results == null) $search_results = "Nichts gefunden ...";
    return $search_results;
}

function searchEngine($tag)
{
    $search = db("SELECT post,title FROM news WHERE post LIKE ".sqlString("%$tag%"));
    while ($result = _assoc($search))
    {
        $res = $result['post'];
    }
    return $res;
}

function exTags($tags)
{
    return explode("+", $tags);
}

function replaceTextBackground($replace_string, $tags)
{
    foreach ($tags as $tag)
    {
        $replace_string = str_ireplace($tag, '<span style="background-color: #ffff99;">'.strtoupper($tag).'</span>', $replace_string);
    }
    return $replace_string;
}

init($content,$meta);