<?php

/*
Plugin Name: Gravity Forms: Mapster WP Maps field
Plugin URI: https://mapster.me
Description: Adds a geoJSON output from a map.
Version: 1.0.0
Author: Victor Temprano
Author URI: https://victortemprano.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('GF_Field_Mapster') ) :

  class GF_Field_Mapster extends GF_Field {
    public $type = 'mapster-map';

    public function get_form_editor_field_title() {
      return esc_attr__( 'Mapster Map', 'gravityforms' );
    }
    public function get_form_editor_button() {
      return array(
        'group' => 'advanced_fields',
        'text'  => $this->get_form_editor_field_title()
      );
    }
    function get_form_editor_field_settings() {
      return array(
        'mapster_map_center_setting',
        'mapster_map_zoom_setting',
        'mapster_map_map_type_selector_setting',
        'mapster_map_geo_type_selector_setting',

        'conditional_logic_field_setting',
        'prepopulate_field_setting',
        'error_message_setting',
        'label_setting',
        'label_placement_setting',
        'admin_label_setting',
        'size_setting',
        'rules_setting',
        'visibility_setting',
        'duplicate_setting',
        'default_value_setting',
        'description_setting',
        'css_class_setting',
      );
    }
    public function get_field_input( $form, $value = '', $entry = null ) {
        $form_id         = $form['id'];
        $id              = (int) $this->id;

        $rest_url = get_rest_url();
        $latitude = $this->mapsterMapLatitude;
        $longitude = $this->mapsterMapLongitude;
        $zoom = $this->mapsterMapZoom;
        $map_type = $this->mapsterMapType;
        $geo_type = $this->mapsterMapGeographicType;
        $geo_value = $value;

        $adminInjection = array(
          'entry_view' => false,
          'mapster_center_lat' => $latitude,
          'mapster_center_lon' => $longitude,
          'mapster_center_zoom' => $zoom,
          'mapster_geo_type' => $geo_type,
          'mapster_map_type' => $map_type,
          'mapster_geo_val' => $geo_value
        );

  			if(MAPSTER_LOCAL_TESTING) {
          wp_enqueue_script("mapster_gravity_maplibre_js", plugin_dir_url( __FILE__ ) . '../../admin/js/vendor/maplibre-1.15.2.js', array("jquery"), '1.0', true);
          wp_enqueue_style('mapster_gravity_maplibre_css', plugin_dir_url( __FILE__ ) . "../../admin/css/vendor/maplibre-1.15.2.css", '1.0');
          wp_enqueue_script("mapster_gravity_mapbox_draw", plugin_dir_url( __FILE__ ) . '../../admin/js/vendor/mapbox-gl-draw.js', array("mapster_gravity_maplibre_js"), '1.0', true);
          wp_enqueue_style('mapster_gravity_mapbox_draw', plugin_dir_url( __FILE__ ) . "../../admin/css/vendor/mapbox-gl-draw.css", '1.0');

          wp_enqueue_script('mapster_map_gravity_js', plugin_dir_url( __FILE__ ) . "js/gravity-mapster-map.js", array('mapster_gravity_mapbox_draw'), '1.0', true);
          wp_localize_script('mapster_map_gravity_js', 'mapster_gravity_params', $adminInjection);
          wp_enqueue_script('mapster_map_gravity_js');
          wp_enqueue_style('mapster_map_gravity_css', plugin_dir_url( __FILE__ ) . "css/gravity-mapster-map.css", '1.0');
        } else {
          wp_enqueue_script('mapster_map_gravity_js', plugin_dir_url( __FILE__ ) . "dist/gravity-mapster-map.js", array('jquery'), '1.0', true);
          wp_localize_script('mapster_map_gravity_js', 'mapster_gravity_params', $adminInjection);
          wp_enqueue_script('mapster_map_gravity_js');
          wp_enqueue_style('mapster_map_gravity_css', plugin_dir_url( __FILE__ ) . "dist/gravity-mapster-map.css", '1.0');
        }

        $input = "<div id='mapster-form-map' class='mapster-form-map'></div>";
        $input .= "<input type='hidden' class='mapster_geo_val' id='input_{$id}' name='input_{$id}' value='{$value}'  />";
        return $input;
    }

    public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {


        $adminInjection = array(
          'entry_view' => true,
          'mapster_geo_val' => $value,
        );

  			if(MAPSTER_LOCAL_TESTING) {
          wp_enqueue_script("mapster_gravity_maplibre_js", plugin_dir_url( __FILE__ ) . '../../admin/js/vendor/maplibre-gl-3.3.1.js', array("jquery"), '1.0', true);
          wp_enqueue_style('mapster_gravity_maplibre_css', plugin_dir_url( __FILE__ ) . "../../admin/css/vendor/maplibre-gl-3.3.1.css", '1.0');

          wp_enqueue_script('mapster_map_gravity_js', plugin_dir_url( __FILE__ ) . "js/gravity-mapster-map.js", array('mapster_gravity_maplibre_js'), '1.0', true);
          wp_localize_script('mapster_map_gravity_js', 'mapster_gravity_params', $adminInjection);
          wp_enqueue_script('mapster_map_gravity_js');
          wp_enqueue_style('mapster_map_gravity_css', plugin_dir_url( __FILE__ ) . "css/gravity-mapster-map.css", '1.0');

        } else {
          wp_enqueue_script('mapster_map_gravity_js', plugin_dir_url( __FILE__ ) . "dist/gravity-mapster-map.js", array('jquery'), '1.0', true);
          wp_localize_script('mapster_map_gravity_js', 'mapster_gravity_params', $adminInjection);
          wp_enqueue_script('mapster_map_gravity_js');
          wp_enqueue_style('mapster_map_gravity_css', plugin_dir_url( __FILE__ ) . "dist/gravity-mapster-map.css", '1.0');
        }


        if ( ! empty( $value ) ) {
          return "<div id='mapster-entry-map' class='mapster-entry-map'></div>";
        } else {
          return "No geographic value entered.";
        }
    }

  }

  GF_Fields::register( new GF_Field_Mapster() );


  // https://docs.gravityforms.com/gf_field/

  function mapster_map_selector_setting( $position, $form_id ) {
    if ( $position == 5 ) {
      ?>
      <li class="mapster_map_center_setting field_setting">
        <div style="display: flex;">
          <div>
            <label for="mapster_map_latitude">
                <?php _e("Center Latitude", "mapster_gforms_plugin_map"); ?>
            </label>
            <input type="text" id="mapster_map_latitude" onfocusout="SetFieldProperty('mapsterMapLatitude', this.value);" />
          </div>
          <div>
            <label for="mapster_map_longitude">
                <?php _e("Center Longitude", "mapster_gforms_plugin_map"); ?>
            </label>
            <input type="text" id="mapster_map_longitude" onfocusout="SetFieldProperty('mapsterMapLongitude', this.value);" />
          </div>
        </div>
      </li>
      <li class="mapster_map_zoom_setting field_setting">
        <label for="mapster_map_zoom">
            <?php _e("Zoom", "mapster_gforms_plugin_map"); ?>
        </label>
        <input type="text" id="mapster_map_zoom" onfocusout="SetFieldProperty('mapsterMapZoom', this.value);" />
      </li>
      <li class="mapster_map_map_type_selector_setting field_setting">
        <label for="mapster_map_type">
            <?php _e("Map Type", "mapster_gforms_plugin_map"); ?>
            <?php gform_tooltip("mapster_geographic_type_tooltip") ?>
        </label>
        <select id="mapster_map_type" onchange="SetFieldProperty('mapsterMapType', this.value);" >
          <option value="streets">Streets (OSM)</option>
          <option value="satellite">Satellite</option>
        </select>
      </li>
      <li class="mapster_map_geo_type_selector_setting field_setting">
        <label for="mapster_map_geographic_type">
            <?php _e("Geographic Type", "mapster_gforms_plugin_map"); ?>
            <?php gform_tooltip("mapster_geographic_type_tooltip") ?>
        </label>
        <select id="mapster_map_geographic_type" onchange="SetFieldProperty('mapsterMapGeographicType', this.value);" >
          <option value="point">Point</option>
          <option value="line">Line</option>
          <option value="polygon">Polygon</option>
        </select>
      </li>
      <?php
    }
  }
  add_action( 'gform_field_standard_settings', 'mapster_map_selector_setting', 10, 2 );

  function mapster_editor_script() {
      ?>
      <script type='text/javascript'>
          fieldSettings.text += ', .mapster_map_center_setting';
          fieldSettings.text += ', .mapster_map_zoom_setting';
          fieldSettings.text += ', .mapster_map_map_type_selector_setting';
          fieldSettings.text += ', .mapster_map_geo_type_selector_setting';
          jQuery(document).on('gform_load_field_settings', function(event, field, form) {
              jQuery( '#mapster_map_latitude' ).val( rgar( field, 'mapsterMapLatitude' ) );
              jQuery( '#mapster_map_longitude' ).val( rgar( field, 'mapsterMapLongitude' ) );
              jQuery( '#mapster_map_zoom' ).val( rgar( field, 'mapsterMapZoom' ) );
              const mapTypeField = rgar( field, 'mapsterMapType' ) !== "" ? rgar( field, 'mapsterMapType' ) : "streets";
              jQuery( '#mapster_map_type' ).val( mapTypeField );
              const typeField = rgar( field, 'mapsterMapGeographicType' ) !== "" ? rgar( field, 'mapsterMapGeographicType' ) : "point";
              jQuery( '#mapster_map_geographic_type' ).val( typeField );
          });
      </script>
      <?php
  }
  add_action( 'gform_editor_js', 'mapster_editor_script' );

  function add_mapster_map_tooltip( $tooltips ) {
    $tooltips['mapster_map_geographic_type'] = "<h6>Mapster Geographic Type</h6>Enter the type of geographic shape you want to collect for your form.";
     return $tooltips;
  }
  add_filter( 'gform_tooltips', 'add_mapster_map_tooltip' );


endif;

?>
