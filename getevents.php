<?php
// getevents.php holt alle Events einer Facebook Seite, schreibt sie als Veranstaltung in den Wordpress Kalender und erzeugt  ein ./butze_events.ics File.
header("Content-Type: text/html; charset=utf-8");
include("db.php");


$fb_page_id ="247XXXX";
$year_range = 10;
$since_date = date('Y-m-d');
$until_date = date('Y-01-01', strtotime('+' . $year_range . ' years'));
$since_unix_timestamp = strtotime($since_date);
$until_unix_timestamp = strtotime($until_date);
$access_token_dummy=file("./token.txt");
$access_token=$access_token_dummy[0];
$fields="id,name,description,location,venue,timezone,start_time,cover";
$api_key="16018XXXXXXXX"; 
$app_secret="2d0bfXXXXXXXXXXXX0";
function linkify($string)
{

    $regex = "/( (?:
(?:https?|ftp) : \\/*
(?:
(?: (?: [a-zA-Z0-9-]{1,} \\. )+
(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
| aero | biz | coop | info | museum | name | pro
| example | invalid | localhost | test | local | onion | swift ) )
| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
| (?: [0-9A-Fa-f:]+ : [0-9A-Fa-f]{1,4} )
)
(?: : [0-9]+ )?
(?! [a-zA-Z0-9.:-] )
(?:
\\/
[^&?#\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]*
)?
(?:
[?#]
[^\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]+
)?
) | (?:
(?:
(?: (?: [a-zA-Z0-9-]{2,} \\. )+
(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
| aero | biz | coop | info | museum | name | pro
| example | invalid | localhost | test | local | onion | swift ) )
| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
)
(?: : [0-9]+ )?
(?! [a-zA-Z0-9.:-] )
(?:
\\/
[^&?#\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]*
)?
(?:
[?#]
[^\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]+
)?
) | (?:
[a-zA-Z0-9._-]{2,} @
(?:
(?: (?: [a-zA-Z0-9-]{2,} \\. )+
(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
| aero | biz | coop | info | museum | name | pro
| example | invalid | localhost | test | local | onion | swift ) )
| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
)
) )/Dx";


	$string = htmlspecialchars($string);   

	if(!function_exists('valid_url')) {
	   
	 function valid_url($url)
	 {
	  if(substr($url[0], 0, 7) != 'http://') {
		 $valid_url = 'http://'.$url[0];
	  } else {
		 $valid_url = $url[0];
	  }
	  return '<a class="link" href="'.$valid_url.'">'.$url[0].'</a>';
	 }

	}

	$output = preg_replace_callback($regex, 'valid_url', $string);
	return $output;
}

function getNextPostId(){
    Global $db;

	$ergebnis = mysqli_query($db, "SELECT id from  `wp_posts`  ORDER BY id DESC LIMIT 1");
	while($row = mysqli_fetch_object($ergebnis)){
	  $ret=$row->id+1;
	}

    return  $ret;
}

function checkFbId($id){
    Global $db;
    $sql="SELECT post_id FROM `wp_postmeta` WHERE meta_key=\"_EventFbId\" and meta_value=".$id;
    $ergebnis = mysqli_query($db, $sql);
    
    while($row = mysqli_fetch_object($ergebnis))
    {         
        $post_id=$row->post_id;
        
        $sql="DELETE FROM `wp_posts` WHERE ID=".$post_id;
        $ergebnis = mysqli_query($db, $sql);
        $sql="DELETE FROM `wp_postmeta` WHERE post_id=".$post_id;
        $ergebnis = mysqli_query($db, $sql);
        $sql="ALTER TABLE `wp_postmeta` AUTO_INCREMENT = 1";
        $ergebnis = mysqli_query($db, $sql);
        $sql="ALTER TABLE `wp_posts` AUTO_INCREMENT = 1";
        $ergebnis = mysqli_query($db, $sql);
    }

}
function preparesql($var){
	$var=addslashes($var);
	$var=utf8_decode($var);
	return $var;
}

function insertEvent($dummy){
    Global $db,$f;

    if (isset($dummy->id)){
        checkFbId($dummy->id);
        $url="http://www.butze.org/wp/";
      
        $post_id=getNextPostId();
        $post_author="1";
        $post_date=date("Y-m-d H:i");
        $post_date_gmt="";-
        $post_content="";
         if (isset($dummy->cover)){
            $cover=$dummy->cover;
            if (isset($cover->source)){
                $wp_attached_file="<a href=\"".$cover->source."\"><img src=\"".$cover->source."\" width=\"199\" height=\"300\" class=\"alignleft wp-image-229 size-medium\" /></a>";
            }
        }
        $post_content.=  $wp_attached_file;
        $post_content.=linkify($dummy->description);
        
        $EventFbId=$dummy->id;        
        $post_title=$dummy->name;
        $post_excerpt="";
        $post_status="publish";
        $comment_status="closed";
        $ping_status="closed";
        $post_password="";
        $post_name=$dummy->id;
        $to_ping="";
        $pinged="";
        $post_modified="";
        $post_modified_gmt="";
        $post_content_filtered="";
        $post_parent=0;
        $guid=$url.$post_name;
        $menu_order=0;
        $post_type="tribe_events";
        $post_mime_type="";
        $comment_count=0;
        $wp_attached_file="";
       
        
        $sql="INSERT INTO  `wp_posts` (
        ID,post_author,post_date,post_date_gmt,post_content,
        post_title,post_excerpt,post_status,comment_status,
        ping_status,post_password,post_name,to_ping,
        pinged,post_modified,post_modified_gmt,post_content_filtered,
        post_parent,guid,menu_order,post_type,
        post_mime_type,comment_count
        ) 
         VALUES ( 
        \"".preparesql($post_id)."\",\"".preparesql($post_author)."\",\"".preparesql($post_date)."\",\"".preparesql($post_date_gmt)."\",\"".preparesql($post_content)."\",
        \"".preparesql($post_title)."\",\"".preparesql($post_excerpt)."\",\"".preparesql($post_status)."\",\"".preparesql($comment_status)."\",
        \"".preparesql($ping_status)."\",\"".preparesql($post_password)."\",\"".preparesql($post_name)."\",\"".preparesql($to_ping)."\",
        \"".preparesql($pinged)."\",\"".preparesql($post_modified)."\",\"".preparesql($post_modified_gmt)."\",\"".preparesql($post_content_filtered)."\",
        \"".preparesql($post_parent)."\",\"".preparesql($guid)."\",\"".preparesql($menu_order)."\",\"".preparesql($post_type)."\",
        \"".preparesql($post_mime_type)."\",\"".preparesql($comment_count)."\"
        )";

        $ergebnis = mysqli_query($db, $sql);
        $end_time="";    
        $start_time="";  
        if (isset($dummy->end_time)){
            $end_time=date("Y-m-d H:i",strtotime($dummy->end_time));    
        }
        if (isset($dummy->start_time)){
            $start_time=date("Y-m-d H:i",strtotime($dummy->start_time));
        }
        $end_time=date("Y-m-d H:i",strtotime($dummy->start_time));    
            
        $meta=array(
            array("meta_key" => "_EventOrigin", "meta_value" =>"events-calendar"),
            array("meta_key" => "_edit_last", "meta_value" =>"1"),
            array("meta_key" => "edit_lock", "meta_value" =>"1425551862:1"),
            array("meta_key" => "_EventShowMapLink", "meta_value" =>"1"),
            array("meta_key" => "_EventShowMap", "meta_value" =>"0"),
            array("meta_key" => "_EventStartDate", "meta_value" =>$start_time),
            array("meta_key" => "_EventEndDate", "meta_value" =>$end_time),
            array("meta_key" => "_EventDuration", "meta_value" =>""),
            array("meta_key" => "_EventVenueID", "meta_value" =>$post_id),
            array("meta_key" => "_EventCurrency", "meta_value" =>"€"),
            array("meta_key" => "_EventCurrencyPosition", "meta_value" =>"prefix"),
            array("meta_key" => "_EventURL", "meta_value" =>""),
            array("meta_key" => "_EventOrganizerID", "meta_value" =>"0"),
            array("meta_key" => "_EventCost", "meta_value" =>""),
            array("meta_key" => "_EventFbId", "meta_value" =>$EventFbId)
            );
    

        foreach ($meta as $metadummy){
            $sql="INSERT INTO  `wp_postmeta`  ( post_id,meta_key,meta_value)
            VALUES ( $post_id,\"".preparesql($metadummy['meta_key'])."\", \"".preparesql($metadummy['meta_value'])."\")";
            $ergebnis = mysqli_query($db, $sql);

        }
        fwrite($f,"
BEGIN:VEVENT
CREATED;VALUE=DATE-TIME:20150326T130842Z
LAST-MODIFIED;VALUE=DATE-TIME:20150326T130901Z
DTSTAMP;VALUE=DATE-TIME:20150326T130901Z
SUMMARY:".preparedata($post_title)."
DTSTART;VALUE=DATE-TIME:".date("Ymd",strtotime($dummy->start_time))."T".date("Hi",strtotime('-2 hour',strtotime($dummy->start_time)))."00Z
DTEND;VALUE=DATE-TIME:".date("Ymd",strtotime($dummy->start_time))."T".date("Hi",strtotime('-2 hour',strtotime($dummy->start_time)))."00Z
CLASS:PUBLIC
END:VEVENT");
    }
}
function preparedata($var){
	$var=str_replace('"', "", $var);
	$var=str_replace("'", "", $var);
	$var=str_replace(":", "-", $var);
	$var=str_replace(";", "-", $var);
	$var=str_replace(",", " ", $var);

	$var=str_replace("é", "e", $var);
	$var=str_replace("\n", "", $var);
	$var=str_replace("\r", "", $var);
	$var=htmlentities($var, ENT_QUOTES | ENT_IGNORE, "UTF-8");
    return $var;
}

$json_link = "https://graph.facebook.com/{$fb_page_id}/events/attending/?fields={$fields}&access_token={$access_token}&since={$since_unix_timestamp}&until={$until_unix_timestamp}";
$json = file_get_contents($json_link);
$obj = json_decode($json);
$f= fopen("./butze_events.ics","w");
fwrite($f,"BEGIN:VCALENDAR
VERSION:2.0
PRODID:ownCloud Calendar 0.6.3
X-WR-CALNAME:Test calendar");

    
foreach($obj as $dummy1){
    foreach($dummy1 as $dummy){
        insertEvent($dummy);
    }
}
fwrite($f,"
END:VCALENDAR");
fclose($f);
?>
