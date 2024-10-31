<?php
/*
Plugin Name: Plot Over Time
Version: 1.0.0
Plugin URI: http://www.midnightryder.com/wordpress-plugins/plot-over-time-for-wordpress
Description: Uses the Google Chart Tools API for charting data entered with posts using MetaTags.  Tracks up to 10 different data points, supports Area Chart, Line Chart, Pie Chart, Bar Chart, and Column Chart styles, any custom style options you'd like to include, and number of other options.  Be sure to read the webpage for full notes and updates.  Based on Tom Fotherby's Fotherplot.

Author: Davis Ray Sickmon, Jr
Author URI: http://www.midnightryder.org
*/

/*  Copyright 2011  Davis Ray Sickmon, Jr, (email : daviss@midnightryder.com)

    This program is free software; you can redistribute it and/or modify
       it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */

add_shortcode('plotovertime', 'plotDataAgainstTime_Handler');

function plotDataAgainstTime_Handler( $atts )
{
	extract(shortcode_atts(array(
		"field1" => '',
		"field2" => '',
		"field3" => '',
		"field4" => '',
		"field5" => '',
		"field6" => '',
		"field7" => '',
		"field8" => '',
		"field9" => '',
		"field10" => '',
		"width" => '400',
		"height" => "300",
		"legend" => "right",
		"type" => "LineChart",
		"options" => '',
		"usepostdate" => "",
		"maxdays" => "",
		"dateformat" => "m/d/y"   
	), $atts));


    $dataFromPlot = plotDataAgainstTime($field1, $field2 , $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10, $width, $height, $legend, $type, $options, $usepostdate, $maxdays, $dateformat);
    return $dataFromPlot;

}

function plotDataAgainstTime( $field1, $field2 , $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10, $width, $height, $legend, $type, $options, $usePostDate, $maxDays, $dateformat)
{
 global $wpdb;
 $postDate = strtotime(strip_tags(get_the_date()));
 $skipf = 1; 	
 // Query the database for the posts that contain this custom field
 $query_string = "
 SELECT *
 FROM $wpdb->posts
 LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
 WHERE ( $wpdb->postmeta.meta_key = '" . $field1 . "'";
 if($field2){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field2 . "'"; $skipf = 2; }
 if($field3){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field3 . "'"; $skipf = 3; }
 if($field4){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field4 . "'";  $skipf = 4; }
  if($field5){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field5 . "'"; $skipf = 5; }
 if($field6){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field6 . "'"; $skipf = 6; } 
 if($field7){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field7 . "'"; $skipf = 7; } 
 if($field8){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field8 . "'"; $skipf = 8; } 
 if($field9){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field9 . "'"; $skipf = 9; } 
 if($field10){
 	$query_string .= " OR $wpdb->postmeta.meta_key = '" . $field10 . "'"; $skipf = 10; } 
 
 $query_string .= " )  AND $wpdb->posts.post_status = 'publish'
 AND $wpdb->posts.post_type = 'post'
 ORDER BY $wpdb->posts.post_date ASC
 ";
 

 // List the posts
 $series_posts = $wpdb->get_results($query_string, OBJECT);


 $firstDate = "";
 $numOfPoints = sizeof($series_posts);
 $googleJSAPIData = "";
 

 $googleJSAPI = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    
      // Load the Visualization API and the piechart package.
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
           var data = new google.visualization.DataTable();
           data.addColumn("string", "Date");
           data.addColumn("number", "' . $field1 . '");';
      if($field2)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field2 . '");';
      if($field3)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field3 . '");';
      if($field4)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field4 . '");';
      if($field5)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field5 . '");';
      if($field6)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field6 . '");';
      if($field7)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field7 . '");';
      if($field8)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field8 . '");';
      if($field9)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field9 . '");';
      if($field10)
      	$googleJSAPI .= 'data.addColumn("number", "' . $field10 . '");';	
      
 $googleJSAPIEnd = 'var chart = new google.visualization.' . $type . '(document.getElementById(\'chart_div\'));
      chart.draw(data, options);
  }
 </script>
 <div id="chart_div" style="width:' . $width . '; height: ' . $height . '"></div>';

$minDate = 0;
if($usePostDate) {
	$maxDate = $postDate;
	if($maxDays)
		$minDate = strtotime( ($maxDays . " days ago"), $maxDate );
} else {
	$maxDate = strtotime('now');
	if($maxDays)	
		$minDate = strtotime( ($maxDays . " days ago"), $maxDate);
}

 if ($series_posts):
   foreach ($series_posts as $post):
	   $i++;
	   $postdate = (strtotime($post->post_date));
	   if(($postdate > $minDate) && ($postdate < $maxDate))
	   { 
	   		if($i == $skipf) {
	   	  		$googleJSAPIData .= "data.addRow(['" . date($dateformat, $postdate) . "', " . ( get_post_meta($post->ID, $field1, true)); //' . date_format($post->post_date, "m-d")  . "', " .
				if($field2) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field2, true));
				if($field3) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field3, true));
				if($field4) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field4, true));
				if($field5) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field5, true));
				if($field6) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field6, true));
				if($field7) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field7, true));
				if($field8) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field8, true));
				if($field9) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field9, true));
				if($field10) 
					$googleJSAPIData .= ", " . ( get_post_meta($post->ID, $field10, true));
				$googleJSAPIData .= "]);";
				$i = 0;
			}
		}
   endforeach;
 endif;
   $googleJSAPIOptions = "var options= {" ;
   if($options)
   	$googleJSAPIOptions .= $options . ", ";
   $googleJSAPIOptions .= "'width . ': " . $width . ", 'height': " . $height . "};";
   
 return  $debugReturn . $googleJSAPI . $googleJSAPIData . $googleJSAPIOptions . $googleJSAPIEnd;
}

add_filter('the_content', 'filterPlotovertime');


function filterPlotovertime($content) 
{
  if(preg_match("[plotovertime]",$content))
  {
    $posOfFilter = strpos($content,'[[plotovertime]]');
    if ($posOfFilter !== FALSE)
    {
      $contentStart = substr($content,0,$posOfFilter); 
      $contentEnd   = substr($content,$posOfFilter+strlen('[[plotovertime]]'),strlen($content));
      $content = $contentStart . plotDataAgainstTime("",1,0) . $contentEnd;
    }
  }

  return $content;
} 

?>