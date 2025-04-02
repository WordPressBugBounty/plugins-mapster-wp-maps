<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mapster.me
 * @since      1.0.0
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/public
 * @author     Mapster Technology Inc <hello@mapster.me>
 */
class Mapster_Wordpress_Maps_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Modal created or not
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $modal_created;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
 		$this->modal_created = false;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
			wp_enqueue_style('dashicons');

		  if ( mwm_fs()->is__premium_only() ) {
				$this->enqueue_pro_styles__premium_only();
			}
	}

	/**
	 * Register the stylesheets for the public area (pro).
	 *
	 * @since    1.0.0
	 */
	public function enqueue_pro_styles__premium_only() {

		global $post;

		$settings_page_id = get_option('mapster_settings_page');
		if($settings_page_id) {
		  $store_locator = get_field('pro_mwm_store_locator', $settings_page_id);

	 		if($store_locator) {
	 			wp_register_style( 'mapster_map_store_locator', plugin_dir_url( __FILE__ ) . '../admin/css/pro/mapster-wordpress-maps-pro-store-locator.css', array(), $this->version, 'all' );
	 		}
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( mwm_fs()->is__premium_only() ) {
			$this->enqueue_pro_scripts__premium_only();
		}
	}

	/**
	 * Get the right delimiter for all permalink types
	 *
	 * @since    1.0.0
	 */
	function mapster_get_rest_url_delimiter() {
		$qd = '?';
		$rest_url = get_rest_url();
		if(str_contains($rest_url, '?')) {
			$qd = '&';
		}
		return $qd;
	}

	/**
	 * Register the stylesheets for the admin area (pro).
	 *
	 * @since    1.0.0
	 */
	public function enqueue_pro_scripts__premium_only() {
	}

	/**
	 * Register shortcode
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_register_shortcodes() {
		add_shortcode( 'mapster_wp_map', array( $this, 'mapster_wordpress_maps_shortcode_display') );
	  if ( mwm_fs()->is__premium_only() ) {
			if( mwm_fs()->can_use_premium_code() ) {
				add_shortcode( 'mapster_wp_map_search', array( $this, 'mapster_wordpress_maps_search_shortcode_display__premium_only') );
				add_shortcode( 'mapster_wp_map_submit', array( $this, 'mapster_wordpress_maps_submission_shortcode_display__premium_only') );
				add_shortcode( 'mapster_listing_posts', array( $this, 'mapster_wordpress_maps_listing_posts_shortcode_display__premium_only') );
			}
		}
	}

	/**
	 * Add shortcode to Map type content
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_output_shortcode( $content ) {
		if( is_singular('mapster-wp-map') ) {
			$output_shortcode = do_shortcode( '[mapster_wp_map id="' . get_the_ID() . '"]' );
			$output_shortcode .= $content;
			return $output_shortcode;
		} else if(is_singular('mapster-wp-location') || is_singular('mapster-wp-line') || is_singular('mapster-wp-polygon')) {
			$show_in_post = get_field('field_626492dbed193', get_the_ID(), true);
			if($show_in_post) {
				$settings_page_id = get_option('mapster_settings_page');
				$base_map = false;
				if ( mwm_fs()->is__premium_only() ) {
					$base_map = get_field('pro_default_shortcode_map', $settings_page_id, true);
				}
				$post_template = get_field('field_6264930aed194', get_the_ID(), true);
				if($post_template) {
					$base_map = $post_template;
				}
				if($base_map) {
					$output_shortcode = do_shortcode( '[mapster_wp_map id="' . $base_map . '" single_feature_id="' . get_the_ID() . '"]' );
					$output_shortcode .= $content;
					return $output_shortcode;
				} else {
					return $content;
				}
			} else {
				return $content;
			}
		} else {
			return $content;
		}
	}

	/**
	 * Map shortcode logic
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_shortcode_display( $atts ) {

		// Handle script loading
		$settings_page_id = get_option('mapster_settings_page');
		$access_token = get_field('default_access_token', $settings_page_id);

		$user_submission_template = false;
		$settings_page_id = get_option('mapster_settings_page');
		$default_latitude = get_field('pro_default_map_view_default_latitude', $settings_page_id);
		$default_longitude = get_field('pro_default_map_view_default_longitude', $settings_page_id);
		$default_zoom = get_field('pro_default_map_view_default_zoom', $settings_page_id);
		if($settings_page_id) {
			$user_submission = get_field('pro_mwm_user_submission', $settings_page_id);

			if($user_submission) {
			  $query = new WP_Query( array(
					'posts_per_page' => 1,
					'post_type' => 'page',
					'meta_key' => '_wp_page_template',
					'meta_value' => 'mapster-submission-template.php'
			  ) );
			  if ($query->have_posts()) {
					$query->the_post();
					$user_submission_template = get_permalink(get_the_ID());
				}
				wp_reset_postdata();
			}
		}

		$i8ln = new Mapster_Wordpress_Maps_i18n();
		$injectedParams = array(
				'strings' => $i8ln->get_mapster_strings()['admin_js'],
				'public' => true,
				'activated' => mwm_fs()->can_use_premium_code() ? '1' : '0',
				'rest_url' => get_rest_url(),
				'qd' => $this->mapster_get_rest_url_delimiter(),
				'directory' => plugin_dir_url( __FILE__ ),
				'mapbox_access_token' => $access_token,
				'user_submission_permalink' => $user_submission_template,
				'mapster_default_lat' => $default_latitude,
				'mapster_default_lng' => $default_longitude,
				'mapster_default_zoom' => $default_zoom,
				'ip' => $_SERVER['REMOTE_ADDR']
		);

		$map_provider = get_field('map_type', $atts['id'])['map_provider'];
		$model_3d_library = get_field('load_3d_model_libraries', $atts['id']);
		$elevation_chart_enabled = get_field('elevation_line_chart_enable_elevation_chart', $atts['id']);

		// Check for required dependencies
		$directions_enabled = get_field('directions_control', $atts['id']) ? get_field('directions_control', $atts['id'])['enable'] : false;
		$store_locator_enabled = false;
		if(get_field('list', $atts['id']) && isset(get_field('list', $atts['id'])['store_locator_options'])) {
			if(is_array(get_field('list', $atts['id'])) && !empty( get_field('list', $atts['id'])['store_locator_options']['enable'] )) {
				$store_locator_enabled = get_field('list', $atts['id'])['store_locator_options']['enable'];
			}
		}
		$geocoder_enabled = false;
		$compare_enabled = get_field('map_compare_enable_map_slider', $atts['id']) ? get_field('map_compare_enable_map_slider', $atts['id']) : false;
		if(get_field('geocoder_control', $atts['id'])['enable'] == true) {
			$geocoder_enabled = true;
		};
		if(get_field('filter', $atts['id'])['custom_search_filter']['enable'] == true) {
			$geocoder_enabled = true;
		};
		if(get_field('filter', $atts['id'])['filter_dropdown']['enable'] == true) {
			$geocoder_enabled = true;
		};
		if(get_field('submission_enable_submission', $atts['id']) == true) {
			$geocoder_enabled = true;
		};
		// $searchbox_enabled = false;
		// if(get_field('searchbox_control', $atts['id']) && get_field('searchbox_control', $atts['id'])['enable'] == true) {
		// 	$searchbox_enabled = true;
		// }

		$last_dependency = 'jquery';

		if(MAPSTER_LOCAL_TESTING) {

			$this->mapster_wordpress_maps_script_loading_dev($last_dependency, $map_provider, $settings_page_id, $directions_enabled, $geocoder_enabled, $compare_enabled, $model_3d_library, $elevation_chart_enabled, $store_locator_enabled, $injectedParams, $atts);

		} else {

			$scripts_to_load = "";
			if($map_provider === 'maplibre' || $map_provider === 'custom-image') {
				$scripts_to_load = "maplibre-mwp";
			}
			if($map_provider === 'mapbox') {
				$scripts_to_load = "mapbox-mwp";
			}
			if($map_provider === 'google-maps') {
				$google_api_key = get_field('google_maps_api_key', $settings_page_id);
				wp_enqueue_script('mapster_map_'.$map_provider, "https://maps.googleapis.com/maps/api/js?key=" . $google_api_key . "&libraries=places", array($last_dependency), $this->version);
				$last_dependency = 'mapster_map_'.$map_provider;
				$scripts_to_load = "google-mwp";
			}

			if($directions_enabled || $geocoder_enabled) {
				if($map_provider === 'maplibre' || $map_provider === 'custom-image') {
					$scripts_to_load = "maplibre-geocoding-mwp";
				}
				if($map_provider === 'mapbox') {
					$scripts_to_load = "mapbox-geocoding-mwp";
				}
			}
			if($model_3d_library) {
				if($map_provider === 'maplibre' || $map_provider === 'custom-image') {
					$scripts_to_load = "maplibre-threebox-mwp";
				}
				if($map_provider === 'mapbox') {
					$scripts_to_load = "mapbox-threebox-mwp";
				}
			}
			if($elevation_chart_enabled) {
				$scripts_to_load = "mapbox-chart-mwp";
			}
			if($store_locator_enabled) {
				wp_enqueue_style( 'mapster_map_store_locator');
			}
			// DO NOT UNCOMMENT
			// if($encoding_enabled) {
				// wp_enqueue_script('mapster_map_polyline_encoding', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/geojson-polyline.min.js", array($last_dependency), $this->version);
				// $last_dependency = 'mapster_map_polyline_encoding';
			// }

			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/js/dist/compiled/' . $scripts_to_load . '.js', array($last_dependency), $this->version, true);
			wp_localize_script($this->plugin_name, 'mapster_params', $injectedParams);
			wp_enqueue_script($this->plugin_name);

			wp_register_style($this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/css/dist/' . $scripts_to_load . '.css', array(), $this->version, 'all' );
			wp_enqueue_style($this->plugin_name);
		}

		$single_feature_id = "";
		if(isset($atts["single_feature_id"])) {
			$single_feature_id = $atts["single_feature_id"];
		}
		$feature_ids = "";
		if(isset($atts["feature_ids"])) {
			$feature_ids = $atts["feature_ids"];
		}

		$map_div_height = get_field('layout_height', $atts['id']) . get_field('layout_height_units', $atts['id']);
		$map_div_width = get_field('layout_width', $atts['id']) . get_field('layout_width_units', $atts['id']);

		$map_container_html = "<div class='mapster-wp-maps-container'>";
		$compare_map_html = "";
		if($compare_enabled) {
			$compare_map_id = get_field('map_compare_compared_map', $atts['id']);
			$compare_map_html = "
				<div class='mapster-wp-maps'
					id='mapster-wp-maps-" . esc_attr($compare_map_id) . "'
					data-id='" . esc_attr($compare_map_id) . "'>
				</div>
			";
			$map_container_html = "<div class='mapster-wp-maps-container' style='height: ".esc_attr($map_div_height).";width: ".esc_attr($map_div_width)."; position:relative;'>";
		}

		$loader = get_field('loading_loading_graphic', $atts['id']);
		$custom_loader = get_field('loading_custom_loader', $atts['id']);
		$loader_background = get_field('loading_background_color', $atts['id']);
		$loader_color = get_field('loading_loader_color', $atts['id']);
		if($loader == 'custom') {
			$loader = "<img src='" . $custom_loader . "' />";
		} else {
			if($loader) {
				$loader = str_replace('svg ', 'svg stroke="' . $loader_color . '" fill="' . $loader_color . '"', $loader);
			} else {
				$loader_background = "rgba(255, 255, 255, 0)";
				$loader = "<svg width='38' height='38' viewBox='0 0 38 38' xmlns='https://www.w3.org/2000/svg' stroke='#333'> <g fill='none' fill-rule='evenodd'> <g transform='translate(1 1)' stroke-width='2'> <circle stroke-opacity='.5' cx='18' cy='18' r='18'/> <path d='M36 18c0-9.94-8.06-18-18-18'> <animateTransform attributeName='transform' type='rotate' from='0 18 18' to='360 18 18' dur='1s' repeatCount='indefinite'/> </path> </g> </g> </svg>";
			}
		}

		return "
				" . $map_container_html . "
				<div class='mapster-wp-maps-loader-container' style='height: ".esc_attr($map_div_height).";width: ".esc_attr($map_div_width).";'>
					<div class='mapster-map-loader-initial' style='background-color: " . $loader_background . "'>
						" . $loader . "
					</div>
				</div>
				<div class='mapster-wp-maps'
					id='mapster-wp-maps-" . esc_attr($atts['id']) . "'
					data-id='" . esc_attr($atts['id']) . "'
					data-latitude='" . esc_attr((isset($atts['latitude']) ? $atts['latitude'] : "null")) . "'
					data-longitude='" . esc_attr((isset($atts['longitude']) ? $atts['longitude'] : "null")) . "'
					data-zoom='" . esc_attr((isset($atts['zoom']) ? $atts['zoom'] : "null")) . "'
					data-single_feature_id='" . esc_attr($single_feature_id) . "'
					data-feature_ids='" . esc_attr($feature_ids) . "'>
				</div>
				" . $compare_map_html . "
			</div>
		";
	}

/**
 * Strictly for faster testing during development
 *
 * @since    1.0.0
 */
 public function mapster_wordpress_maps_script_loading_dev($last_dependency, $map_provider, $settings_page_id, $directions_enabled, $geocoder_enabled, $compare_enabled, $model_3d_library, $elevation_chart_enabled, $store_locator_enabled, $injectedParams, $atts) {

		wp_register_style('mapster_map_mapbox_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-3.6.0.css", array(), $this->version);
		wp_register_style('mapster_map_maplibre_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/maplibre-1.15.2.css", array(), $this->version);
		wp_register_style('mapster_map_maplibre_compare_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/maplibre-gl-compare.css", array(), $this->version);
		wp_register_style('mapster_map_directions_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/directions.css", array(), $this->version);
		// wp_register_style('mapster_map_searchbox_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-searchbox-beta.css", array(), $this->version);
		wp_register_style('mapster_map_geocoder_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-geocoder-4.7.2.css", array(), $this->version);
		wp_register_style('mapster_map_mapbox_compare_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-compare.css", array(), $this->version);
		wp_register_style($this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/css/mapster-wordpress-maps.css', array(), $this->version, 'all' );
		wp_register_style('mapster_map_public_css', plugin_dir_url( __FILE__ ) . 'css/mapster-wordpress-maps-public.css', array(), $this->version, 'all' );
		wp_register_style('mapster_map_threebox_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/threebox.css", array(), $this->version);

		if($map_provider === 'maplibre' || $map_provider === 'custom-image') {
			wp_enqueue_script('mapster_map_'.$map_provider, plugin_dir_url( __FILE__ ) . "../admin/js/vendor/maplibre-1.15.2.js", array($last_dependency), $this->version);
			wp_enqueue_style( "mapster_map_maplibre_css" );
			$last_dependency = 'mapster_map_'.$map_provider;
		}
		if($map_provider === 'mapbox') {
			wp_enqueue_script('mapster_map_'.$map_provider, plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-3.6.0.js", array($last_dependency), $this->version);
			wp_enqueue_style( "mapster_map_".$map_provider."_css" );
			$last_dependency = 'mapster_map_'.$map_provider;
		}
		if($map_provider === 'google-maps') {
			$google_api_key = get_field('google_maps_api_key', $settings_page_id);
			wp_enqueue_script('mapster_map_'.$map_provider, "https://maps.googleapis.com/maps/api/js?key=" . $google_api_key . "&libraries=places", array($last_dependency), $this->version);
			wp_enqueue_style( "mapster_map_".$map_provider."_css" );
			$last_dependency = 'mapster_map_'.$map_provider;
		}

		wp_enqueue_script('mapster_map_turf', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/custom-turf.js", array($last_dependency), $this->version);
		$last_dependency = 'mapster_map_turf';

		if($directions_enabled) {
			wp_enqueue_style( "mapster_map_directions_css" );
			wp_enqueue_script('mapster_map_directions_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-directions-4.1.0.js", array($last_dependency), $this->version);
			$last_dependency = 'mapster_map_directions_js';
		}
		if($geocoder_enabled) {
			wp_enqueue_style( "mapster_map_geocoder_css" );
			wp_enqueue_script('mapster_map_geocoder_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-geocoder-4.7.2.js", array($last_dependency), $this->version);
			$last_dependency = 'mapster_map_geocoder_js';
		}
		// if($searchbox_enabled) {
		// 	wp_enqueue_style( "mapster_map_searchbox_css" );
		// 	wp_enqueue_script('mapster_map_searchbox_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-searchbox-beta.js", array($last_dependency), $this->version);
		// 	$last_dependency = 'mapster_map_searchbox_js';
		// }
		if($compare_enabled) {
			wp_enqueue_style( "mapster_map_".$map_provider."_compare_css" );
			wp_enqueue_script('mapster_map_compare_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/".$map_provider."-gl-compare.js", array($last_dependency), $this->version);
			$last_dependency = 'mapster_map_compare_js';
		}
		if($model_3d_library) {
			wp_enqueue_style( "mapster_map_threebox_css" );
			wp_enqueue_script('mapster_map_threebox_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/threebox.min.js", array($last_dependency), $this->version);
			$last_dependency = 'mapster_map_threebox_js';
		}
		if($elevation_chart_enabled) {
			wp_enqueue_script('mapster_map_chartjs', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/chart.min.js", array($last_dependency), $this->version);
			$last_dependency = 'mapster_map_chartjs';
		}
		if($store_locator_enabled) {
			wp_enqueue_style( 'mapster_map_store_locator');
		}

		wp_enqueue_script($this->plugin_name . "-ElevationControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/ElevationControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-ElevationControl";
		wp_enqueue_script($this->plugin_name . "-StyleControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/StyleControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-StyleControl";
		wp_enqueue_script($this->plugin_name . "-LayerControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/LayerControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-LayerControl";
		wp_enqueue_script($this->plugin_name . "-ControlMenu", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/ControlMenu.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-ControlMenu";
		wp_enqueue_script($this->plugin_name . "-CustomHTMLControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/CustomHTMLControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-CustomHTMLControl";
		wp_enqueue_script($this->plugin_name . "-DownloadControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/DownloadControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-DownloadControl";
		// wp_enqueue_script($this->plugin_name . "-MapsterSearchBoxControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/MapsterSearchBoxControl.js', array($last_dependency), $this->version);
		// $last_dependency = $this->plugin_name . "-MapsterSearchBoxControl";
		wp_enqueue_script($this->plugin_name . "-CategoryControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/CategoryControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-CategoryControl";
		wp_enqueue_script($this->plugin_name . "-ListControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/ListControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-ListControl";
		wp_enqueue_script($this->plugin_name . "-PitchToggle", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/PitchToggle.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-PitchToggle";
		wp_enqueue_script($this->plugin_name . "-PrintControl", plugin_dir_url( __FILE__ ) . '../admin/js/dev/controls/PrintControl.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-PrintControl";
		wp_enqueue_script($this->plugin_name . "-constants", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterConstants.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-constants";
		wp_enqueue_script($this->plugin_name . "-helpers", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterHelpers.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-helpers";
		wp_enqueue_script($this->plugin_name . "-core", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterCore.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-core";
		wp_enqueue_script($this->plugin_name . "-container", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterContainer.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-container";
		wp_enqueue_script($this->plugin_name . "-map", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterMap.js', array($last_dependency), $this->version);
		$last_dependency = $this->plugin_name . "-map";
		wp_register_script( $this->plugin_name . "-main-js", plugin_dir_url( __FILE__ ) . '../admin/js/dev/MapsterLoader.js', array($last_dependency), $this->version, true);

		if($map_provider == 'google-maps') {
		 wp_enqueue_script($this->plugin_name . "-google-label", plugin_dir_url( __FILE__ ) . '../admin/js/vendor/google-maps-label.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-google-label";
		 wp_enqueue_script($this->plugin_name . "-google-clustering", plugin_dir_url( __FILE__ ) . '../admin/js/vendor/google-maps-clustering.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-google-clustering";
		 wp_enqueue_script($this->plugin_name . "-google-category-control", plugin_dir_url( __FILE__ ) . '../admin/js/dev/google/CategoryControlGoogle.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-google-category-control";
		 wp_enqueue_script($this->plugin_name . "-google-list-control", plugin_dir_url( __FILE__ ) . '../admin/js/dev/google/ListControlGoogle.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-google-list-control";
		 wp_enqueue_script($this->plugin_name . "-core-google", plugin_dir_url( __FILE__ ) . '../admin/js/dev/google/MapsterCoreGoogle.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-core-google";
		 wp_enqueue_script($this->plugin_name . "-helpers-google", plugin_dir_url( __FILE__ ) . '../admin/js/dev/google/MapsterHelpersGoogle.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-helpers-google";
		 wp_enqueue_script($this->plugin_name . "-map-google", plugin_dir_url( __FILE__ ) . '../admin/js/dev/google/MapsterMapGoogle.js', array($last_dependency), $this->version);
		 $last_dependency = $this->plugin_name . "-map-google";
		}

		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_style( "mapster_map_public_css" );

		wp_localize_script($this->plugin_name . "-main-js", 'mapster_params', $injectedParams);
		wp_enqueue_script($this->plugin_name . "-main-js");

		if($map_provider == 'google-maps') {
			wp_enqueue_script($this->plugin_name . "-google");
		}

		wp_enqueue_script($this->plugin_name);

  }

	/**
	 * Listing page posts
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_listing_posts_shortcode_display__premium_only($atts) {
		$post_id = isset($atts['id']) ? $atts['id'] : false;
		$output = "<div class='mapster-listing-container' data-map_id='" . esc_attr($post_id) . "'>";
		$output .= "</div>";
		return $output;
	}

	/**
	 * Submission button
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_submission_shortcode_display__premium_only($atts) {
		$type = isset($atts['type']) ? $atts['type'] : 'create_point';
		$map_id = isset($atts['map_id']) ? $atts['map_id'] : false;
		$button_text = isset($atts['button_text']) ? $atts['button_text'] : 'Submit a Point';
		$header_text = isset($atts['header_text']) ? $atts['header_text'] : 'Submit a Point';
		$modal_size = isset($atts['modal_size']) ? $atts['modal_size'] : 'lg';
		$output = "";

		if(!$map_id) {
			$output = "<p>You must include a map_id as an attribute in your shortcode.</p>";
		} else {

			if(!$this->modal_created) {

				$output = "
					<div class='mapster-submit-parent'>
						<button id='mapster-submit-" . esc_attr($map_id) . "' class='mapster-submit' data-type='" . esc_attr($type) . "' data-header='" . esc_attr($header_text) . "'>" . esc_attr($button_text) . "</button>
						<div class='mapster-submit-" . esc_attr($map_id) . "'>
							<div id='mapster-submission-modal-overlay-" . esc_attr($map_id) . "' class='mapster-submission-modal-overlay'></div>
							<div id='mapster-submission-modal-" . esc_attr($map_id) . "' class='mapster-submission-modal  mapster-modal-" . esc_attr($modal_size) . "' style='display:none;'>
								<div id='mapster-submission-modal-close-" . esc_attr($map_id) . "' class='mapster-submission-modal-close'>
									<svg xmlns='https://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 14 14' fill='none'>
										<path fill-rule='evenodd' clip-rule='evenodd' d='M13.7071 1.70711C14.0976 1.31658 14.0976 0.683417 13.7071 0.292893C13.3166 -0.0976311 12.6834 -0.0976311 12.2929 0.292893L7 5.58579L1.70711 0.292893C1.31658 -0.0976311 0.683417 -0.0976311 0.292893 0.292893C-0.0976311 0.683417 -0.0976311 1.31658 0.292893 1.70711L5.58579 7L0.292893 12.2929C-0.0976311 12.6834 -0.0976311 13.3166 0.292893 13.7071C0.683417 14.0976 1.31658 14.0976 1.70711 13.7071L7 8.41421L12.2929 13.7071C12.6834 14.0976 13.3166 14.0976 13.7071 13.7071C14.0976 13.3166 14.0976 12.6834 13.7071 12.2929L8.41421 7L13.7071 1.70711Z' fill='black' />
									</svg>
								</div>
								<div class='mapster-submission-modal-title'>
									<h2>" . esc_attr($header_text) . "</h2>
								</div>
								<div class='mapster-submission-modal-content'>
								</div>
							</div>
						</div>
					</div>";
				$this->modal_created = true;
			} else {
				$output = "
					<div class='mapster-submit-parent'>
						<button id='mapster-submit-" . esc_attr($map_id) . "' class='mapster-submit' data-type='" . esc_attr($type) . "' data-header='" . esc_attr($header_text) . "'>" . esc_attr($button_text) . "</button>
					</div>
				";
			}
		}
		return $output;
	}

	/**
	 * Adding meta data to indicate user created post
	 *
	 * @since    1.0.0
	 */
	function mapster_submission_after_save_post__premium_only($form, $post_id) {
		add_post_meta($post_id, 'mapster_user_edited', true);

		function recursive_meta_condense($fields_to_loop, $field_name = "") {
			$toReturn = array();
			foreach($fields_to_loop as $this_field) {
        $full_field_name = $field_name . $this_field['name'];
				array_push($toReturn, $full_field_name);
				if(isset($this_field['sub_fields'])) {
					$sub_fields = recursive_meta_condense($this_field['sub_fields'], $full_field_name . "_");
					$toReturn = array_merge($toReturn, $sub_fields);
				}
			}
			return $toReturn;
		}

		// Only apply template if post was created less than 15 seconds ago
		$local_time  = current_datetime();
		if(get_post_meta($post_id, 'mapster_defaults_set') == false && ($local_time->getTimestamp() - 15) < get_post_timestamp($post_id, 'date')) {
			if(isset($form['form_attributes']['template_id']) && $form['form_attributes']['template_id'] !== '0') {
				add_post_meta($post_id, 'mapster_defaults_set', true);
				$template_id = $form['form_attributes']['template_id'];

    		$meta_data = get_post_meta($template_id);
        if(mapster_can_be_looped($meta_data)) {

	        $location_fields = recursive_meta_condense(acf_get_fields('group_6163732e0426e'));
	        $popup_fields = recursive_meta_condense(acf_get_fields('group_6163d357655f4'));

      		foreach($meta_data as $meta_key => $meta_value) {
						if($meta_key !== "location") {
							$meta_key_to_test = $meta_key;
							if($meta_key[0] == "_") {
								$meta_key_to_test = substr($meta_key, 1);
							}
							if((in_array($meta_key_to_test, $location_fields) || in_array($meta_key_to_test, $popup_fields) || in_array('_' . $meta_key, $location_fields) || in_array('_' . $meta_key, $popup_fields))) {
	      				update_post_meta($post_id, $meta_key, maybe_unserialize($meta_value[0]));
							}
						}
      		}
        }
			}
		}

		if(isset($form['form_attributes']['category'])) {
			wp_set_post_terms($post_id, array($form['form_attributes']['category']), 'wp-map-category');
		}

		if(get_post_type($post_id) == "mapster-wp-user-sub" && $form['form_attributes']['longitude'] && $form['form_attributes']['latitude']) {
			$geojson = '{
					"type" : "FeatureCollection",
					"features" : [{
						"type" : "Feature",
						"properties" : {},
						"geometry" : {
							"type" : "Point",
							"coordinates" : ['.$form['form_attributes']['longitude'].', '.$form['form_attributes']['latitude'].']
						}
					}]
			}';
			update_field('location', $geojson, $post_id);
		}

		if(isset($form['form_attributes']) && isset($form['form_attributes']['title_field'])) {
			wp_update_post(array(
				'ID' => $post_id,
				'post_name' => get_field($form['form_attributes']['title_field'], $post_id, true),
				'post_title' => get_field($form['form_attributes']['title_field'], $post_id, true)
			));
		}
	}

	/**
	 * Add shortcode for search functionality
	 *
	 * @since    1.0.0
	 */
 public function mapster_wordpress_maps_search_shortcode_display__premium_only( $atts ){

	 $injectedParams = array(
	 	'rest_url' => get_rest_url(),
		'qd' => $this->mapster_get_rest_url_delimiter()
	 );

	 wp_register_script('mapster_mapbox_search_js', plugin_dir_url( __FILE__ ) . "js/pro/mapster-wordpress-maps-pro-search.js", array('jquery'), $this->version);
	 wp_localize_script('mapster_mapbox_search_js', 'mapster_search', $injectedParams);
	 wp_enqueue_script('mapster_mapbox_search_js');

	 wp_enqueue_style( 'mapster_map_public_css' );

	 return "
		 <div>
		 	<input id='mapster-search-input' type='text' placeholder='Search...' />
			<button id='mapster-search-button' data-permissions=".esc_attr($atts['permissions'])." data-user=".get_current_user_id()." data-feature_types=".esc_attr($atts["feature_types"]).">Search</button>
			<div id='mapster-search-loader' style='text-align:center; display: none;'>
				<div class='mapster-map-loader'>
					<svg width='38' height='38' viewBox='0 0 38 38' xmlns='https://www.w3.org/2000/svg' stroke='#333'>
						<g fill='none' fill-rule='evenodd'>
								<g transform='translate(1 1)' stroke-width='2'>
										<circle stroke-opacity='.5' cx='18' cy='18' r='18'/>
										<path d='M36 18c0-9.94-8.06-18-18-18'>
												<animateTransform
														attributeName='transform'
														type='rotate'
														from='0 18 18'
														to='360 18 18'
														dur='1s'
														repeatCount='indefinite'/>
										</path>
								</g>
						</g>
					</svg>
				</div>
			</div>
			<div class='mapster-search-results'>
				<ul></ul>
			</div>
			<div class='mapster-search-paginate'>
				<button class='mapster-search-page-next'>Next</button>
				<button class='mapster-search-page-prev'>Previous</button>
			</div>
		 </div>
	 ";
 }

}
