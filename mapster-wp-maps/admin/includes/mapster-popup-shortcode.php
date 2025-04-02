<?php

 $path = preg_replace('/wp-content.*$/','',__DIR__);
 include($path.'wp-load.php');

 $feature_id = $_GET["feature_id"];
 $popup_id = $_GET["popup_id"];

 ?>
<head>
 <?php wp_head(); ?>
 <base target="_parent">
</head>
<?php

 $body_background_color = get_field('body', $popup_id);

 ?>

 <style>

 body {
   background: <?php echo $body_background_color; ?> !important;
 }
 #mapster-popup-shortcode-footer div {
   display: none;
 }
 #mapster-popup-shortcode-footer p {
   display: none;
 }

 </style>

 <div id="mapster-popup-shortcode-content">
   <?php

    if ( mwm_fs()->is__premium_only() ) {
      if( mwm_fs()->can_use_premium_code() ) {
        $text = get_field('popup_body_text', $feature_id);
        // Copied from API "replace_text_with_property_or_acf" function
        $custom_properties = carbon_get_post_meta($feature_id, 'mapster_custom_properties');
        $properties = array();
        foreach($custom_properties as $property) {
         $properties[$property['property_name']] = $property['property_value'];
        }
        $new_text = $text;
        preg_match_all('#\{(.*?)\}#', $new_text, $matches);
        foreach($matches[0] as $index=>$match) {
         if(isset($properties[$matches[1][$index]])) {
           $new_text = str_replace($match, $properties[$matches[1][$index]], $new_text);
         } else if(strpos($match, "acf") !== false) {
           $acf_field_id = str_replace("acf.", "", $matches[1][$index]);
           $field_data = get_field_object($acf_field_id, $post_id);
           if($field_data) {
             if(isset($field_data['choices'])) {
               if(is_array($field_data['value'])) {
                 $arranged_values = array();
                 foreach($field_data['value'] as $value) {
                   array_push($arranged_values, $field_data['choices'][$value]);
                 }
                 $field_data = implode(', ', $arranged_values);
               } else {
                 $field_data = $field_data['choices'][$field_data['value']];
               }
             } else {
               $field_data = $field_data['value'];
             }
             $new_text = str_replace($match, $field_data, $new_text);
           } else {
             $new_text = str_replace($match, "", $new_text);
           }
         }
        }
        echo do_shortcode($new_text);
      }
    }

   ?>
 </div>

<div id="mapster-shortcode-footer">
<?php wp_footer(); ?>
</div>
