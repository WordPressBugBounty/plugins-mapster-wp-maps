<?php
  class Mapster_Wordpress_Maps_Pro_Admin_API {

    public function mapster_wp_maps_get_associated_posts_details() {
        register_rest_route('mapster-wp-maps', 'get-associated-posts', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_associated_posts',
            'permission_callback' => function() {
                return true;
            }
        ));

        function mapster_wp_maps_get_associated_posts($request) {

            $post_ids = $request->get_param( 'post_ids' );
            $post_ids_array = explode(",", $post_ids);

            $toReturn = array();
            $args = array(
              'post_status' => 'publish',
              'posts_per_page' => -1,
              'post_type' => 'any',
              'post__in' => $post_ids_array
            );
            $the_query = new WP_Query( $args );
            if ( $the_query->have_posts() ) :
              while ( $the_query->have_posts() ) : $the_query->the_post();
                array_push($toReturn, array(
                  "id" => get_the_ID(),
                  "link" => get_the_permalink(),
                  "title" => get_the_title(),
                  "image" => get_the_post_thumbnail_url()
                ));
              endwhile;
            endif;

            ob_get_clean();

            return $toReturn;

        }
    }

    public function mapster_wp_maps_get_cache_url() {
        register_rest_route('mapster-wp-maps', 'create-cached-file', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_create_cached_file',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            }
        ));

        function mapster_wp_maps_create_cached_file($request) {

            $post_id = $request->get_param( 'post_id' );

            $new_folder = trailingslashit(wp_upload_dir()['basedir']) . 'mapster';
            wp_mkdir_p($new_folder);

            $new_file = $new_folder . '/map-' . $post_id . '.json';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, get_rest_url() . "mapster-wp-maps/map?id=" . $post_id . "&ignore_cache=1");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      			$response = curl_exec($ch);

            function write_to_file($new_file, $response) {
              $file   = fopen($new_file, "w");
              $pieces = str_split($response, 1024 * 4);
              foreach ($pieces as $piece) {
                  fwrite($file, $piece, strlen($piece));
              }
              fclose($file);
            }

            if(file_exists($new_file)) {
              if (is_writable($new_file)) {
                write_to_file($new_file, $response);
              } else {
                return "Error writing to file.";
              }
            } else {
              write_to_file($new_file, $response);
            }

            return is_writable($new_file);
        }
    }

    public function mapster_wp_maps_get_csv_export_options() {
        register_rest_route('mapster-wp-maps', 'csv-export-options', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_csv_export_options',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            }
        ));

        function mapster_wp_maps_csv_export_options($request) {

            $csv_export_settings = get_option('mapster_csv_export_settings');

            $toReturn = array(
              'post_data' => array(),
              'settings' => $csv_export_settings
            );
            $args = array(
              'post_type' => 'mapster-wp-user-sub',
              'post_status' => 'publish',
              'posts_per_page' => -1
            );
            $the_query = new WP_Query( $args );
            if ( $the_query->have_posts() ) :
              while ( $the_query->have_posts() ) : $the_query->the_post();
                $term_obj_list = get_the_terms( get_the_ID(), 'wp-map-category' );
                $post_to_export = array(
                  'ID'                    => get_the_ID(),
              		'post_author'           => get_the_author(),
              		'post_content'          => get_the_content(),
              		'post_title'            => get_the_title(),
                  'terms'                 => join(', ', wp_list_pluck($term_obj_list, 'name')),
              		'post_status'           => get_post_status(get_the_ID()),
                  'location'              => trim(preg_replace('/\s\s+/', '', get_field('location', get_the_ID())))
                );

            		$meta_data = get_post_meta(get_the_ID());
                $data_to_exclude = array('mapster_user_edited', 'mapster_defaults_set', 'location');
                if(mapster_can_be_looped($meta_data)) {
              		foreach($meta_data as $meta_key => $meta_value) {
                    if(substr($meta_key, 0, 1) !== "_" && !in_array($meta_key, $data_to_exclude)) {
                      $post_to_export[$meta_key] = $meta_value[0];
                    }
                  }
                }
                array_push($toReturn['post_data'], $post_to_export);
              endwhile;
            endif;

            ob_get_clean();

            return $toReturn;
        }
    }

    public function mapster_wp_maps_post_csv_export_options_save() {
        register_rest_route('mapster-wp-maps', 'csv-export-options-save', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_csv_export_options_save',
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            }
        ));

        function mapster_wp_maps_csv_export_options_save($request) {

            $body = $request->get_body();
            $decoded_body = json_decode($body);
            $checkedCheckboxes = $decoded_body->checkedCheckboxes;

            $csv_export_settings = update_option('mapster_csv_export_settings', $checkedCheckboxes);

            return $csv_export_settings;
        }
    }

    public function mapster_wp_maps_get_submission_info() {
        register_rest_route('mapster-wp-maps', 'submission-info', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_submission_information',
            'permission_callback' => function(){
                return true;
            }
        ));

        function mapster_wp_maps_get_submission_information($request) {

            $categories = $request->get_param( 'categories' );
            $categoriesArray = explode(',', $categories);

            $toReturn = array();

            foreach($categoriesArray as $category) {
              $term = get_term($category, 'wp-map-category');
              $term->color = get_field('color', $term->taxonomy . '_' . $term->term_id);
              $icon = get_field('icon', $term->taxonomy . '_' . $term->term_id);
              if($icon) {
                $term->icon = $icon;
              } else {
                $term->icon = null;
              }
              array_push($toReturn, $term);
            }

            ob_get_clean();

            return $toReturn;
        }
    }

    public function mapster_wp_maps_do_install_pre_import() {
        register_rest_route('mapster-wp-maps', 'mapster-pre-import', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_do_pre_import',
            'permission_callback' => function(){
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_do_pre_import($request) {

            $body = $request->get_body();
            $decoded_body = json_decode($body);
            $settings = $decoded_body->settings;

            $settings_page_id = get_option('mapster_settings_page');
        		foreach($settings as $meta_key => $meta_value) {
        			update_post_meta($settings_page_id, $meta_key, maybe_unserialize($meta_value[0]));
        		}

            ob_get_clean();

            return array("result" => "Success");
        }
    }

    public function mapster_wp_maps_get_install_export() {
        register_rest_route('mapster-wp-maps', 'mapster-export', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_make_export',
            'permission_callback' => function(){
                // return true;
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_make_export() {

          $full_export = array();

      		$settings_page_id = get_option('mapster_settings_page');
          $settings_post_metas = array();
      		$settings_meta_data = get_post_meta($settings_page_id);

          $full_export['settings'] = $settings_meta_data;

          ob_get_clean();

          return $full_export;
        }
    }

    public function mapster_wp_maps_mapbox_tileset_update() {
        register_rest_route('mapster-wp-maps', 'tileset-update', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_tileset_api',
            'permission_callback' => function(){
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_tileset_api($request) {
          $username = $request->get_param( 'username' );
          $secret_token = $request->get_param( 'access_token' );
          $tileset_source = $request->get_param( 'tileset_source' );
          $request_type = $request->get_param( 'request_type' );
          $category = $request->get_param( 'category' );

          ini_set('serialize_precision','-1');

          $lineDelimitedFeatures = "";
          $args = array(
            'tax_query' => array(
              array(
                "taxonomy" => "wp-map-category",
                "field" => "term_id",
                "terms" => $category,
                "include_children" => false
              )
            ),
            'post_status' => 'publish',
            'posts_per_page' => -1
          );
          $the_query = new WP_Query( $args );
          if ( $the_query->have_posts() ) :
            while ( $the_query->have_posts() ) : $the_query->the_post();
              $field_type = "location";
              if(get_post_type() == "mapster-wp-line") {
                $field_type = "line";
              }
              if(get_post_type() == "mapster-wp-polygon") {
                $field_type = "polygon";
              }
              if($field_type) {
                $coordinate_data = json_decode(get_field($field_type, get_the_ID(), true));
                $custom_properties = carbon_get_post_meta(get_the_ID(), 'mapster_custom_properties');
                $thisFeature = array(
                  "type" => "Feature",
                  "properties" => (object)[],
                  "geometry" => $coordinate_data->features[0]->geometry
                );
                foreach($custom_properties as $property) {
                  $thisFeature["properties"]->{$property['property_name']} = $property['property_value'];
                }
                // Adding custom properties, restricted to specific coded stuff
                $settings_page_id = get_option('mapster_settings_page');
                if($settings_page_id) {
                 $formatting = get_field('pro_tileset_management_format', $settings_page_id);
                 $exploded_by_line = preg_split('/\r\n|\r|\n/', $formatting);
                 foreach($exploded_by_line as $line) {
                   $trimmed_line = trim(str_replace(' ', '', $line));
                   if($line !== "" && strpos($line, ':') !== false) {
                     $exploded_by_colon = explode(':', $line);
                     $property_name = trim($exploded_by_colon[0]);
                     $property_value = trim($exploded_by_colon[1]);
                     if($property_value == 'title') {
                       $thisFeature["properties"]->{$property_name} = html_entity_decode(get_the_title(get_the_ID()));
                     }
                     if($property_value == 'permalink') {
                       $thisFeature["properties"]->{$property_name} = get_the_permalink(get_the_ID());
                     }
                     if($property_value == 'post_name') {
                       $post = get_post(get_the_ID());
                       $thisFeature["properties"]->{$property_name} = $post->post_name;
                     }
                     if($property_value == 'ID') {
                       $thisFeature["properties"]->{$property_name} = get_the_ID();
                     }
                     if(strpos($property_value, 'acf.') !== false) {
                       $acf_field_name = str_replace('acf.', '', $property_value);
                       $field_data = get_field_object($acf_field_name, get_the_ID());
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
                         $thisFeature["properties"]->{$property_name} = $field_data;
                       }
                     }
                   }
                 }
               }
               $lineDelimitedFeatures .= json_encode($thisFeature) . "\n";
              }
            endwhile;
          endif;

          // Create Tileset Source
          $responses = array();
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "https://api.mapbox.com/tilesets/v1/sources/" . $username . "/" . $tileset_source . "?access_token=" . $secret_token);
          if($request_type == 'update') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
          }
          if($request_type == 'new') {
            curl_setopt($ch, CURLOPT_POST, 1);
          }
          curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "file" => new CURLStringFile($lineDelimitedFeatures, "tileset_source_upload.txt", "text/plain")
          ));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
          $tileset_source_response = curl_exec($ch);
          array_push($responses, $tileset_source_response);
          curl_close($ch);

          // Create Tileset
          $tileset_name = $username . "." . $tileset_source . "_tileset";
          if($request_type == 'new') {
            $recipe = array(
              "version" => 1,
              "layers" => array()
            );
            $recipe["layers"][$tileset_source] = array(
              "source" => "mapbox://tileset-source/" . $username . "/" . $tileset_source,
              "minzoom" => 1,
              "maxzoom" => 10
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.mapbox.com/tilesets/v1/" . $tileset_name . "?access_token=" . $secret_token);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
              "recipe" => $recipe,
              "name" => $tileset_source . " Tileset"
            )));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $tileset_response = curl_exec($ch);
            array_push($responses, $tileset_response);
            curl_close($ch);
          }

          // Publish tileset
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, "https://api.mapbox.com/tilesets/v1/" . $tileset_name . "/publish?access_token=" . $secret_token);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
          $tileset_publish = curl_exec($ch);
          array_push($responses, $tileset_publish);
          curl_close($ch);

          return array(
            "responses" => $responses
          );

        }
    }

    public function mapster_wp_maps_query_spatial_database() {
        register_rest_route('mapster-wp-maps', 'query', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_query_db',
            'permission_callback' => function() {
        			$settings_page_id = get_option('mapster_settings_page');
        			if($settings_page_id) {
        				$spatial_api_enabled = get_field('pro_spatial_api', $settings_page_id);
                if($spatial_api_enabled) {
                  return true;
                } else {
                  return false;
                }
              } else {
                return false;
              }
            },
        ));

        function mapster_wp_maps_query_db($request) {

          global $wpdb;
          ini_set('serialize_precision','-1');

          $body = $request->get_body();
          $decoded_body = json_decode($body);
          $query_type = $decoded_body->query_type;
          $bounds_only = $decoded_body->bounds_only;
          $category = $decoded_body->category;
          $custom_fields = $decoded_body->custom_fields;
          $query_feature = $decoded_body->query_feature;

      		$table_name = $wpdb->prefix . 'mapster_maps_geometry';

          $post_ids = false;
          $sql_query = "
            SELECT post_id, ST_AsGeoJSON(coordinates) AS coordinates
              FROM $table_name
              WHERE
          ";

          if($category) {
            $args = array(
              'tax_query' => array(
                array(
                  "taxonomy" => "wp-map-category",
                  "field" => "term_id",
                  "terms" => $category,
                  "include_children" => false
                )
              ),
              'post_status' => 'publish',
              'posts_per_page' => -1,
              'fields' => 'ids'
            );
            $get_posts = new WP_Query;
            $post_ids = $get_posts->query($args);
            $sql_query .= " post_id IN (" . implode(",", $post_ids) . ")";
          }

          if($query_type == 'pip') {
            $queried_wkt = geoPHP::load($query_feature, 'json')->out('wkt');
            if($post_ids) {
              $sql_query .= " AND";
            }
            $sql_query .= " ST_Contains(coordinates, ST_GeomFromText('" . $queried_wkt . "', 3857))";
          }

          if($query_type == 'polygon-overlap') {
            $queried_wkt = geoPHP::load($query_feature, 'json')->out('wkt');
            if($post_ids) {
              $sql_query .= " AND";
            }
            $sql_query .= " ST_Overlaps(coordinates, ST_GeomFromText('" . $queried_wkt . "', 3857))";
          }

          if($bounds_only == true) {
            $sql_query = str_replace("ST_AsGeoJSON(coordinates)", "ST_AsGeoJSON(ST_Envelope(coordinates))", $sql_query);
          }

          $results = $wpdb->get_results( $sql_query );

          $formatted_results = array(
            "type" => "FeatureCollection",
            "features" => array()
          );
          foreach($results as $result) {
            $custom_properties = carbon_get_post_meta($result->post_id, 'mapster_custom_properties');
            $formatted_result = array(
              "type" => "Feature",
              "properties" => array(
                "post_id" => $result->post_id,
                "title" => get_the_title($result->post_id),
                "permalink" => get_the_permalink($result->post_id),
                "excerpt" => get_the_excerpt($result->post_id),
              ),
              "geometry" => json_decode($result->coordinates)
            );
            foreach($custom_properties as $property) {
              $formatted_result['properties'][$property['property_name']] = $property['property_value'];
            }
            if($custom_fields) {
              foreach($custom_fields as $custom_field) {
                $formatted_result['properties'][$custom_field] = get_field($custom_field, $result->post_id);
              }
            }
            array_push($formatted_results["features"], $formatted_result);
          }

          ob_get_clean();

          return $formatted_results;

        }
    }

    public function mapster_wp_maps_mass_edit_features() {
        register_rest_route('mapster-wp-maps', 'mass-edit', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_mass_edit',
            'permission_callback' => function(){
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_mass_edit($request) {

          $toReturn = array();

          global $wpdb;

          $body = $request->get_body();
          $decoded_body = json_decode($body);
          $categories = $decoded_body->categories;
          $post_ids = $decoded_body->post_ids;
          $json_mass_edit = $decoded_body->json_mass_edit;

          $ids_to_update = $post_ids;
          if(mapster_can_be_looped($categories)) {
            if(count($categories) > 0) {
              $args = array(
                'tax_query' => array(
                  array(
                    "taxonomy" => "wp-map-category",
                    "field" => "term_id",
                    "terms" => $categories,
                    "include_children" => false
                  )
                ),
                'posts_per_page' => -1
              );
              $the_query = new WP_Query( $args );
              if ( $the_query->have_posts() ) :
                while ( $the_query->have_posts() ) : $the_query->the_post();
                  array_push($ids_to_update, get_the_ID());
                endwhile;
              endif;
            }
          }

          foreach($ids_to_update as $post_id) {
            if(get_post_type($post_id) === 'mapster-wp-location') {
              update_posts($post_id, $json_mass_edit->point);
              update_posts($post_id, $json_mass_edit->popup);
            }
            if(get_post_type($post_id) === 'mapster-wp-line') {
              update_posts($post_id, $json_mass_edit->line);
              update_posts($post_id, $json_mass_edit->popup);
            }
            if(get_post_type($post_id) === 'mapster-wp-polygon') {
              update_posts($post_id, $json_mass_edit->polygon);
              update_posts($post_id, $json_mass_edit->popup);
            }
          }

          return array(
            "ids" => $ids_to_update
          );
        }

        function update_posts($post_id, $fields) {
          foreach($fields as $name=>$value) {
            update_field($name, $value, $post_id);
          }
        }
    }

    public function mapster_wp_maps_get_default_fields() {
        register_rest_route('mapster-wp-maps', 'get-mapster-fields', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_fields',
            'permission_callback' => function(){
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_get_fields() {

          $fields_to_exclude = ["field_6163b9b4b4ddc", "field_616377f9af36c", "field_61637961f8c23", "field_61637538a4710"];

          $point_fields = acf_get_fields('group_6163732e0426e');
          $line_fields = acf_get_fields('group_616377d62836b');
          $polygon_fields = acf_get_fields('group_616379566202f');
          $popup_fields = acf_get_fields('group_6163d357655f4');

          $reduced_fields = array(
            'point' => array(),
            'line' => array(),
            'polygon' => array(),
            'popup' => array()
          );
          foreach($point_fields as $field) {
            if(!in_array($field['key'], $fields_to_exclude)) {
              $reduced_fields['point'] = array_merge($reduced_fields['point'], reduce_fields($field, $fields_to_exclude));
            }
          }
          foreach($line_fields as $field) {
            if(!in_array($field['key'], $fields_to_exclude)) {
              $reduced_fields['line'] = array_merge($reduced_fields['line'], reduce_fields($field, $fields_to_exclude));
            }
          }
          foreach($polygon_fields as $field) {
            if(!in_array($field['key'], $fields_to_exclude)) {
              $reduced_fields['polygon'] = array_merge($reduced_fields['polygon'], reduce_fields($field, $fields_to_exclude));
            }
          }
          foreach($popup_fields as $field) {
            if(!in_array($field['key'], $fields_to_exclude)) {
              $reduced_fields['popup'] = array_merge($reduced_fields['popup'], reduce_fields($field, $fields_to_exclude));
            }
          }

          return $reduced_fields;
        }

        function reduce_fields($field, $fields_to_exclude, $parent_key = "") {
          $toReturn = array();
          if($field['type'] == 'group') {
            foreach($field['sub_fields'] as $sub_field) {
              if(!in_array($sub_field['key'], $fields_to_exclude)) {
                $toReturn = array_merge($toReturn, reduce_fields($sub_field, $fields_to_exclude, $parent_key . $field['name'] . '_'));
              }
            }
          } else {
            array_push($toReturn, array(
              "label" => $field['label'],
              "composite_name" => $parent_key . $field['name'],
              "default_value" => isset($field['default_value']) ? $field['default_value'] : null
            ));
          }

          return $toReturn;
        }
    }

    public function mapster_wp_maps_import_geojson_features() {
        register_rest_route('mapster-wp-maps', 'import-geojson', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_import_geojson',
            'permission_callback' => function(){
                return current_user_can( 'manage_options' );
            },
        ));

        function mapster_wp_maps_import_geojson($request) {

            global $wpdb;

            $body = $request->get_body();
            $decoded_body = json_decode($body);

            $geojson = $decoded_body->file;
            $import_json = $decoded_body->import_json;
            $category_id = $decoded_body->category;
            $features_imported = $decoded_body->features_imported;

            $marker_count = 0;
            $poly_count = 0;
            $line_count = 0;

            wp_defer_term_counting( true );
            wp_defer_comment_counting( true );
            $wpdb->query( 'SET autocommit = 0;' );

            $testing = array();
            ini_set('serialize_precision','-1');

            if(mapster_can_be_looped($geojson->features)) {
              foreach($geojson->features as $feature) {
                $feature_copy = clone $feature;
                $feature_copy->properties = new stdClass();
                $feature_geojson = array(
                  "type" => "FeatureCollection",
                  "features" => array($feature_copy)
                );

                if($feature->geometry->type == 'Point') {
                  $marker_count = $marker_count + 1;

                  $any_true_rules = false;
                  foreach($import_json->point as $condition) {
                    $rule_true = check_conditions($condition, $feature);
                    if($rule_true) {
                      $any_true_rules = true;
                      $featureName = get_post_title(($features_imported + $marker_count), $condition, $feature);
                      if(isset($condition->template)) {
                        $new_shape = post_duplication($condition->template, $featureName);
                      } else {
                        $new_shape = create_location($featureName, $category_id);
                      }
                    }
                  }
                  if(!$any_true_rules) { // Still create a default feature anyway
                    $new_shape = create_location('Point ' . ($features_imported + $marker_count), $category_id);
                  }
                  if($category_id !== "") {
                    $secondary_category = get_secondary_category($condition, $feature);
                    if($secondary_category !== false) {
                      wp_set_post_terms($new_shape, array($category_id, $secondary_category), 'wp-map-category');
                    } else {
                      wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                    }
                  }
                  if($feature->properties->set_categories) {
                    if(is_array($feature->properties->set_categories)) {
                      wp_set_post_terms($new_shape, $feature->properties->set_categories, 'wp-map-category');
                    }
                  }

                  update_fields_from_json($import_json->point, $feature, $new_shape);
                  set_property_fields($feature, $new_shape);
                  update_field('location', json_encode($feature_geojson), $new_shape);
                }
                if($feature->geometry->type == 'Polygon' || $feature->geometry->type == 'MultiPolygon' ) {
                  $poly_count = $poly_count + 1;

                  $any_true_rules = false;
                  foreach($import_json->polygon as $condition) {
                    $rule_true = check_conditions($condition, $feature);
                    if($rule_true) {
                      $any_true_rules = true;
                      $featureName = get_post_title(($features_imported + $poly_count), $condition, $feature);
                      if(isset($condition->template)) {
                        $new_shape = post_duplication($condition->template, $featureName);
                      } else {
                        $new_shape = create_polygon($featureName, $category_id);
                      }
                    }
                  }
                  if(!$any_true_rules) { // Still create a default feature anyway
                    $new_shape = create_polygon('Polygon ' . ($features_imported + $poly_count), $category_id);
                  }
                  if($category_id !== "") {
                    $secondary_category = get_secondary_category($condition, $feature);
                    if($secondary_category !== false) {
                      wp_set_post_terms($new_shape, array($category_id, $secondary_category), 'wp-map-category');
                    } else {
                      wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                    }
                    array_push($testing, $secondary_category);
                  }
                  if($feature->properties->set_categories) {
                    if(is_array($feature->properties->set_categories)) {
                      wp_set_post_terms($new_shape, $feature->properties->set_categories, 'wp-map-category');
                    }
                  }

                  update_fields_from_json($import_json->polygon, $feature, $new_shape);
                  set_property_fields($feature, $new_shape);
                  update_field('polygon', json_encode($feature_geojson), $new_shape);
                }
                if($feature->geometry->type == 'LineString' || $feature->geometry->type == 'MultiLineString' ) {
                  $line_count = $line_count + 1;

                  $any_true_rules = false;
                  foreach($import_json->line as $condition) {
                    $rule_true = check_conditions($condition, $feature);
                    if($rule_true) {
                      $any_true_rules = true;
                      $featureName = get_post_title(($features_imported + $line_count), $condition, $feature);
                      if(isset($condition->template)) {
                        $new_shape = post_duplication($condition->template, $featureName);
                      } else {
                        $new_shape = create_line($featureName, $category_id);
                      }
                    }
                  }
                  if(!$any_true_rules) { // Still create a default feature anyway
                    $new_shape = create_line('Line ' . ($features_imported + $line_count), $category_id);
                  }
                  if($category_id !== "") {
                    $secondary_category = get_secondary_category($condition, $feature);
                    if($secondary_category !== false) {
                      wp_set_post_terms($new_shape, array($category_id, $secondary_category), 'wp-map-category');
                    } else {
                      wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                    }
                  }
                  if($feature->properties->set_categories) {
                    if(is_array($feature->properties->set_categories)) {
                      wp_set_post_terms($new_shape, $feature->properties->set_categories, 'wp-map-category');
                    }
                  }

                  update_fields_from_json($import_json->line, $feature, $new_shape);
                  set_property_fields($feature, $new_shape);
                  update_field('line', json_encode($feature_geojson), $new_shape);
                }

              }
            }

            wp_defer_term_counting( false );
            wp_defer_comment_counting( false );
            $wpdb->query( 'COMMIT;' );
            $wpdb->query( 'SET autocommit = 1;' );

            ob_get_clean();

            return array(
              // "test" => $test_return,
              "count" => $marker_count + $poly_count + $line_count,
              "testing" => $testing
            );
        }

        function set_property_fields($feature, $new_shape) {
          $count = 0;
          foreach($feature->properties as $property=>$value) {
            carbon_set_post_meta( $new_shape, 'mapster_custom_properties['. $count .']/property_name', $property );
            carbon_set_post_meta( $new_shape, 'mapster_custom_properties['. $count .']/property_value', !is_scalar($value) ? json_encode($value) : $value );
            $count = $count + 1;
          }
        }

        function create_location($featureName, $category_id) {
          $new_shape = wp_insert_post(array(
            'post_type' => 'mapster-wp-location',
            'post_status' => 'publish',
            'post_title' => $featureName
          ));
          if($category_id !== "") {
            wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
          }
          mapster_setDefaults(acf_get_fields('group_6163732e0426e'), $new_shape);
          mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);
          mapster_update_wpml_post(false, $new_shape, 'mapster-wp-location');
          return $new_shape;
        }

        function create_polygon($featureName, $category_id) {
          $new_shape = wp_insert_post(array(
            'post_type' => 'mapster-wp-polygon',
            'post_status' => 'publish',
            'post_title' => $featureName
          ));
          if($category_id !== "") {
            wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
          }
          mapster_setDefaults(acf_get_fields('group_616379566202f'), $new_shape);
          mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);
          mapster_update_wpml_post(false, $new_shape, 'mapster-wp-polygon');
          return $new_shape;
        }

        function create_line($featureName, $category_id) {
          $new_shape = wp_insert_post(array(
            'post_type' => 'mapster-wp-line',
            'post_status' => 'publish',
            'post_title' => $featureName
          ));
          if($category_id !== "") {
            wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
          }
          mapster_setDefaults(acf_get_fields('group_616377d62836b'), $new_shape);
          mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);
          mapster_update_wpml_post(false, $new_shape, 'mapster-wp-line');
          return $new_shape;
        }

        function get_secondary_category($condition, $feature) {
          if($condition->category_property) {
            return $condition->category_property;
          } else {
            return false;
          }
        }

        function get_post_title($count, $condition, $feature) {
          $featureName = $feature->geometry->type . ' ' . $count;
          if($condition->title_property) {
            $featureName = $feature->properties->{$condition->title_property};
          }
          if($condition->title_direct) {
            $featureName = $condition->title_direct;
          }
          return $featureName;
        }

        function update_fields_from_json($shape_conditions, $feature, $new_shape) {
          foreach($shape_conditions as $condition) {
            $rule_true = check_conditions($condition, $feature);
            if($rule_true) {
              set_values($condition->style_properties, $feature, $new_shape);
              set_values($condition->style_values, $feature, $new_shape, 'direct');
              set_values($condition->popup_properties, $feature, $new_shape);
              set_values($condition->popup_values, $feature, $new_shape, 'direct');
            }
          }
        }

        function check_conditions($condition, $feature) {
          $rule_true = true;
          foreach($condition->rules as $rule) {
            if($rule->value !== 'all' && $feature->properties->{$rule->property} !== $rule->value) {
              $rule_true = false;
            }
          }
          return $rule_true;
        }

        function set_values($loopable, $feature, $new_shape, $directOrNot = false) {
          if(isset($loopable)) {
            foreach($loopable as $field_name=>$value) {
              if(!empty($value)) {
                update_field($field_name, $directOrNot ? $value : $feature->properties->{$value}, $new_shape);
              }
            }
          }
        }

        function post_duplication($post_id, $featureName) {
          $old_post = get_post($post_id);
      		$meta_data = get_post_meta($post_id);

          $new_post = array(
        		'post_author'           => $old_post->post_author,
        		'post_content'          => $old_post->post_content,
        		'post_title'            => $featureName,
        		'post_excerpt'          => $old_post->post_excerpt,
        		'post_status'           => $old_post->post_status,
        		'comment_status'        => $old_post->comment_status,
        		'ping_status'           => $old_post->ping_status,
        		'post_password'         => $old_post->post_password,
        		'to_ping'               => $old_post->to_ping,
        		'pinged'                => $old_post->pinged,
        		'post_content_filtered' => $old_post->post_content_filtered,
        		'post_parent'           => $old_post->post_parent,
        		'menu_order'            => $old_post->menu_order,
        		'post_type'             => $old_post->post_type,
        		'post_mime_type'        => $old_post->post_mime_type
          );

          $new_post_id = wp_insert_post($new_post);
          if(mapster_can_be_looped($meta_data)) {
        		foreach($meta_data as $meta_key => $meta_value) {
        			update_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value[0]));
        		}
          }
          mapster_update_wpml_post($post_id, $new_post_id, $old_post->post_type);
      		return $new_post_id;
        }

    }

    public function mapster_wp_maps_email_notify() {
        register_rest_route('mapster-wp-maps', 'send-email', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_email_notification',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_email_notification($request) {

          $body = $request->get_body();
          $decoded_body = json_decode($body);
          $users_to_notify = $decoded_body->users;
          $active_filters = $decoded_body->active_filters;

          foreach($users_to_notify as $user_id) {
             $user_info = get_userdata($user_id);
             $to = $user_info->user_email;
             $subject = "Data Downloaded from " . get_bloginfo('name');
             $body = "A user downloaded data from your map at " . current_time('d/m/y H:i:s') . ". The active filters were " . implode(",", $active_filters) . ".";
             // $headers = "Content-Type: text/html; charset=UTF-8";
             $mailResult = wp_mail( $to, $subject, $body );
          }

          return array();
        }
    }

    public function mapster_wp_maps_frontend_search() {
        register_rest_route('mapster-wp-maps', 'search-features', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_search_features',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_search_features($request) {
          global $wpdb;

          $response = array(
            'results' => array(),
            'total' => ''
          );

          $query = strtoupper($request->get_param( 'query' ));
          $feature_types = $request->get_param( 'feature_types' );
          $page = $request->get_param( 'page' );
          $permissions = $request->get_param( 'permissions' );
          $user_id = $request->get_param( 'user_id' );
          $post_types = explode(',', $feature_types);
          array_walk($post_types, function(&$value, $key) {
            $value = "mapster-wp-" . $value;
          });
          $post_types_to_fetch = "";
          if(count($post_types) == 1) {
            $post_types_to_fetch = "post_type = '" . $post_types[0] ."'";
          } else {
            foreach($post_types as $key=>$type) {
              if($key == 0) {
                $post_types_to_fetch = "(post_type = '" . $type ."'";
              } else {
                $post_types_to_fetch .= " OR post_type = '" . $type ."'";
              }
              if($key == count($post_types) - 1) {
                $post_types_to_fetch .= ")";
              }
            }
          }

          $keyword = '%' . $wpdb->esc_like( $query ) . '%';
          $post_meta = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT post_id FROM {$wpdb->postmeta}
                WHERE UPPER(meta_value) LIKE %s
          ", $keyword));
          $post_search = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT ID FROM {$wpdb->posts}
                WHERE ({$post_types_to_fetch}
                AND UPPER(post_title) LIKE %s)
                OR ({$post_types_to_fetch}
                AND UPPER(post_content) LIKE %s)
          ", $keyword, $keyword));
          $post_ids = array_merge( $post_meta, $post_search );
          // Query arguments
          if(count($post_ids) > 0) {
            $args = array(
                'post_type'   => $post_types,
                'post_status' => 'publish',
                'post__in'    => $post_ids,
                'posts_per_page' => 10,
                'paged' => $page
            );
            if($permissions == 'user') {
              $args['author'] = $user_id;
            }
            $query = new WP_Query( $args );
            $response['total'] = $query->found_posts;
            if ( $query->have_posts() ): while ( $query->have_posts() ) : $query->the_post();
              array_push($response['results'], array(
                'title' => get_the_title(),
                'id' => get_the_ID(),
                'link' => get_the_permalink(),
                'content' => get_the_content(),
                'excerpt' => get_the_excerpt()
              ));
            endwhile; endif;
          }

          ob_get_clean();

          return $response;
        }
    }
 }

?>
