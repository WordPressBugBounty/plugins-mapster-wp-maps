<?php

 $path = preg_replace('/wp-content.*$/','',__DIR__);
 include($path.'wp-load.php');

 $map_id = $_GET["map_id"];
 if(!get_field('embed_allow_embed', $map_id)) {
   header('X-Frame-Options: DENY');
 } else {
   if(!get_field('embed_protect_embed', $map_id)) {
     // header('X-Frame-Options: ALLOW');
   } else {
     $allowed_origins = get_field('embed_allowed_origins', $map_id);
     $array = preg_split("/\r\n|\n|\r/", $allowed_origins);
     $cspString = "Content-Security-Policy: frame-ancestors 'self'";
     foreach($array as $domain) {
       $cspString .= ' ' . $domain;
     }
     header($cspString);
   }
 }

 wp_head();

 ?>

 <style>

 body {
   visibility: hidden;
 }
 div {
   display: none;
 }
 p {
   display: none;
 }

 div.mapster-wp-maps-container,
 div.mapster-wp-maps-container div,
 div.mapster-wp-maps-container p {
   display: block;
 }

 div.mapster-wp-maps {
   top: 0;
   bottom: 0;
   width: 100%;
   position: absolute;
   height: auto !important;
 }

 </style>

<?php

  $set_zoom = "";
  $set_lat = "";
  $set_lng = "";
  if(isset($_GET['zoom'])) {
    $set_zoom = $_GET['zoom'];
  }
  if(isset($_GET['latitude'])) {
    $set_lat = $_GET['latitude'];
  }
  if(isset($_GET['longitude'])) {
    $set_lng = $_GET['longitude'];
  }

  echo do_shortcode('[mapster_wp_map id="' . $map_id . '" zoom="' . $set_zoom . '" latitude="' . $set_lat . '" longitude="' . $set_lng . '"]'); 

?>

<?php wp_footer(); ?>

<script>

jQuery(document).ready(function() {
  window.mwm.add_action('map_set', (map) => {
    map.on('load', () => {
      jQuery('body').css("visibility", "visible");
    })
  })
  setTimeout(() => {
    jQuery('body').css("visibility", "visible");
  }, 3000)
})

</script>
