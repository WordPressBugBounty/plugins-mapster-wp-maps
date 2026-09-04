<?php

include_once plugin_dir_path( __FILE__ ) . 'config-converter.php';
include_once plugin_dir_path( __FILE__ ) . 'feature-writer.php';
include_once plugin_dir_path( __FILE__ ) . 'map-sources.php';
class Mapster_Wordpress_Maps_Admin_API {
    public function mapster_wp_maps_dismiss_v2_notice() {
        register_rest_route( 'mapster-wp-maps', 'dismiss-v2-notice', array(
            'methods'             => 'POST',
            'callback'            => function () {
                update_option( 'mapster_v2_notice_dismissed', true );
                return array(
                    'success' => true,
                );
            },
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
    }

    public function mapster_wp_maps_restore_v2_backup() {
        register_rest_route( 'mapster-wp-maps', 'restore-v2-backup', array(
            'methods'             => 'POST',
            'callback'            => function ( $request ) {
                $map_id = intval( $request->get_param( 'map_id' ) );
                if ( !$map_id ) {
                    return new WP_Error('missing_id', 'Map ID is required', [
                        'status' => 400,
                    ]);
                }
                $raw = get_option( 'mapster_v2_backup_' . $map_id );
                $backup = ( $raw ? unserialize( $raw ) : null );
                if ( !$backup || empty( $backup['features'] ) ) {
                    return new WP_Error('no_backup', 'No backup found for this map', [
                        'status' => 404,
                    ]);
                }
                // Restore each feature's ACF data via raw meta writes — this bypasses ACF hooks
                // and faithfully restores the exact bytes that were there before the V2 save.
                foreach ( $backup['features'] as $fid => $meta ) {
                    foreach ( $meta as $field_name => $entry ) {
                        update_post_meta( intval( $fid ), $field_name, $entry['value'] );
                        update_post_meta( intval( $fid ), '_' . $field_name, $entry['field_key'] );
                    }
                }
                // Restore the map's feature associations by re-deriving them from the backup's
                // feature list. We don't restore the full map config (it's user-fixable and the
                // meta restore was causing the Mapmaker to break). We only reassociate features.
                $locs = [];
                $lines = [];
                $polys = [];
                foreach ( array_keys( $backup['features'] ) as $fid ) {
                    $type = get_post_type( intval( $fid ) );
                    if ( $type === 'mapster-wp-location' ) {
                        $locs[] = intval( $fid );
                    } elseif ( $type === 'mapster-wp-line' ) {
                        $lines[] = intval( $fid );
                    } elseif ( $type === 'mapster-wp-polygon' ) {
                        $polys[] = intval( $fid );
                    }
                }
                if ( !empty( $locs ) ) {
                    update_field( 'locations', $locs, $map_id );
                }
                if ( !empty( $lines ) ) {
                    update_field( 'lines', $lines, $map_id );
                }
                if ( !empty( $polys ) ) {
                    update_field( 'polygons', $polys, $map_id );
                }
                return [
                    'success'        => true,
                    'snapshot_taken' => $backup['timestamp'],
                ];
            },
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
    }

    public function mapster_wp_maps_set_tutorial_option() {
        register_rest_route( 'mapster-wp-maps', 'set-tutorial-option', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_set_tutorial_option_from_js',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_set_tutorial_option_from_js(  $request  ) {
            $value = $request->get_param( 'value' );
            update_option( 'mapster_tutorial', $value );
            return array(
                "success" => true,
            );
        }

    }

    public function mapster_wp_maps_duplicate_post() {
        register_rest_route( 'mapster-wp-maps', 'duplicate', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_duplication',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_duplication(  $request  ) {
            $body = $request->get_body();
            $decoded_body = json_decode( $body );
            $post_id = $decoded_body->id;
            $old_post = get_post( $post_id );
            $new_post = array(
                'post_author'           => $old_post->post_author,
                'post_content'          => $old_post->post_content,
                'post_title'            => $old_post->post_title,
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
                'post_mime_type'        => $old_post->post_mime_type,
            );
            $new_post_id = wp_insert_post( $new_post );
            if ( $new_post_id ) {
                $meta_data = get_post_meta( $post_id );
                if ( mapster_can_be_looped( $meta_data ) ) {
                    foreach ( $meta_data as $meta_key => $meta_value ) {
                        update_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value[0] ) );
                    }
                }
                mapster_update_wpml_post( $post_id, $new_post_id, $old_post->post_type );
                return $new_post_id;
            }
            return array(
                "new_post_id" => $new_post_id,
            );
        }

    }

    public function mapster_wp_maps_import_geojson_features() {
        register_rest_route( 'mapster-wp-maps', 'import-geojson', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_import_geojson',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_import_geojson(  $request  ) {
            $body = $request->get_body();
            $decoded_body = json_decode( $body );
            $geojson = $decoded_body->file;
            $category_id = $decoded_body->category;
            $marker_count = 0;
            $poly_count = 0;
            $line_count = 0;
            if ( mapster_can_be_looped( $geojson->features ) ) {
                foreach ( $geojson->features as $feature ) {
                    $feature_copy = clone $feature;
                    $feature_copy->properties = new stdClass();
                    $feature_geojson = array(
                        "type"     => "FeatureCollection",
                        "features" => array($feature_copy),
                    );
                    if ( $feature->geometry->type == 'Point' ) {
                        $marker_count = $marker_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-location',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $marker_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_6163732e0426e' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'location', json_encode( $feature_geojson ), $new_shape );
                    }
                    if ( $feature->geometry->type == 'Polygon' || $feature->geometry->type == 'MultiPolygon' ) {
                        $poly_count = $poly_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-polygon',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $poly_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_616379566202f' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'polygon', json_encode( $feature_geojson ), $new_shape );
                    }
                    if ( $feature->geometry->type == 'LineString' || $feature->geometry->type == 'MultiLineString' ) {
                        $line_count = $line_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-line',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $line_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_616377d62836b' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'line', json_encode( $feature_geojson ), $new_shape );
                    }
                }
            }
            ob_get_clean();
            return array(
                "count" => $marker_count + $poly_count + $line_count,
            );
        }

    }

    public function mapster_wp_maps_import_gl_js_features() {
        register_rest_route( 'mapster-wp-maps', 'import-gl-js', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_import_gl_js',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_import_gl_js(  $request  ) {
            $body = $request->get_body();
            $decoded_body = json_decode( $body );
            $geojson = $decoded_body->file;
            $category_id = $decoded_body->category;
            $marker_count = 0;
            $poly_count = 0;
            $line_count = 0;
            $uploaded_images = array();
            $uploaded_images_new = array();
            if ( mapster_can_be_looped( $geojson->features ) ) {
                foreach ( $geojson->features as $feature ) {
                    $feature_copy = clone $feature;
                    $feature_copy->properties = new stdClass();
                    $geojson = array(
                        "type"     => "FeatureCollection",
                        "features" => array($feature_copy),
                    );
                    if ( $feature->geometry->type == 'Point' ) {
                        $marker_count = $marker_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-location',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $marker_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_6163732e0426e' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'location_style', 'label', $new_shape );
                        update_field( 'icon_icon_on', true, $new_shape );
                        update_field( 'icon_icon_properties_icon-anchor', $feature->properties->marker_icon_anchor, $new_shape );
                        update_field( 'icon_icon_properties_icon-size', 30, $new_shape );
                        update_field( 'enable_popup', true, $new_shape );
                        update_field( 'popup_style', get_option( 'mapster_default_popup' ), $new_shape );
                        update_field( 'popup_body_text', $feature->properties->description, $new_shape );
                        // Upload marker image
                        require_once ABSPATH . 'wp-admin/includes/media.php';
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $filename = explode( '-wp_mapbox_gl_js_sizing', $feature->properties->marker_icon_url )[0];
                        if ( !in_array( $filename, $uploaded_images ) ) {
                            $attachment_id = media_sideload_image(
                                $filename,
                                0,
                                null,
                                'id'
                            );
                            array_push( $uploaded_images, $filename );
                            array_push( $uploaded_images_new, $attachment_id );
                            update_field( 'icon_icon_properties_icon-image', $attachment_id, $new_shape );
                        } else {
                            $index = array_search( $filename, $uploaded_images );
                            update_field( 'icon_icon_properties_icon-image', $uploaded_images_new[$index], $new_shape );
                        }
                        update_field( 'location', json_encode( $geojson ), $new_shape );
                    }
                    if ( $feature->geometry->type == 'Polygon' || $feature->geometry->type == 'MultiPolygon' ) {
                        $poly_count = $poly_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-polygon',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $poly_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_616379566202f' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'color', $feature->properties->color, $new_shape );
                        update_field( 'opacity', $feature->properties->opacity * 100, $new_shape );
                        update_field( 'polygon', json_encode( $geojson ), $new_shape );
                    }
                    if ( $feature->geometry->type == 'LineString' || $feature->geometry->type == 'MultiLineString' ) {
                        $line_count = $line_count + 1;
                        $new_shape = wp_insert_post( array(
                            'post_type'   => 'mapster-wp-line',
                            'post_status' => 'publish',
                            'post_title'  => ( $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $line_count ),
                        ) );
                        if ( $category_id !== "" ) {
                            wp_set_post_terms( $new_shape, array($category_id), 'wp-map-category' );
                        }
                        mapster_setDefaults( acf_get_fields( 'group_616377d62836b' ), $new_shape );
                        mapster_setDefaults( acf_get_fields( 'group_6163d357655f4' ), $new_shape );
                        update_field( 'color', $feature->properties->color, $new_shape );
                        update_field( 'opacity', $feature->properties->opacity * 100, $new_shape );
                        update_field( 'line', json_encode( $geojson ), $new_shape );
                    }
                }
            }
            ob_get_clean();
            return array(
                "count" => $line_count + $poly_count + $marker_count,
            );
        }

    }

    public function mapster_wp_maps_get_category_features() {
        register_rest_route( 'mapster-wp-maps', 'category', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_category',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_get_category(  $params  ) {
            $response = array();
            $id = json_decode( $params['id'] );
            $args = array(
                'tax_query'      => array(array(
                    "taxonomy"         => "wp-map-category",
                    "field"            => "term_id",
                    "terms"            => $id,
                    "include_children" => false,
                )),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            );
            $the_query = new WP_Query($args);
            if ( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $thisResponse = mapster_getOnlyValues( get_the_ID() );
                    array_push( $response, $thisResponse );
                }
            }
            ob_get_clean();
            return $response;
        }

    }

    public function mapster_wp_maps_get_all_features() {
        register_rest_route( 'mapster-wp-maps', 'features', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_features',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_get_features(  $params  ) {
            $response = array();
            $idsArray = json_decode( $params['ids'] );
            $catsArray = json_decode( $params['categories'] );
            $customArray = json_decode( $params['custom'] );
            $customCatsArray = json_decode( $params['custom_cats'] );
            if ( mapster_can_be_looped( $idsArray ) ) {
                foreach ( $idsArray as $id ) {
                    $thisResponse = mapster_getOnlyValues( $id );
                    array_push( $response, $thisResponse );
                }
            }
            // Check for category additions
            if ( mapster_can_be_looped( $catsArray ) ) {
                if ( count( $catsArray ) > 0 ) {
                    $args = array(
                        'post_type'      => array(
                            'mapster-wp-user-sub',
                            'mapster-wp-location',
                            'mapster-wp-polygon',
                            'mapster-wp-line'
                        ),
                        'tax_query'      => array(array(
                            "taxonomy"         => "wp-map-category",
                            "field"            => "term_id",
                            "terms"            => $catsArray,
                            "include_children" => false,
                        )),
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                    );
                    $the_query = new WP_Query($args);
                    if ( $the_query->have_posts() ) {
                        while ( $the_query->have_posts() ) {
                            $the_query->the_post();
                            $thisResponse = mapster_getOnlyValues( get_the_ID() );
                            array_push( $response, $thisResponse );
                        }
                    }
                }
            }
            // Check for custom additions
            if ( mapster_can_be_looped( $customArray ) ) {
                foreach ( $customArray as $id ) {
                    $thisResponse = mapster_getOnlyValues( $id );
                    $customData = mapster_organizeCustomData( $thisResponse );
                    if ( $customData ) {
                        array_push( $response, $customData );
                    }
                }
            }
            if ( mapster_can_be_looped( $customCatsArray ) ) {
                if ( count( $customCatsArray ) > 0 ) {
                    foreach ( $customCatsArray as $customCat ) {
                        $term = get_term( $customCat );
                        $args = array(
                            'post_type'      => "any",
                            'tax_query'      => array(array(
                                "taxonomy"         => $term->taxonomy,
                                "field"            => "term_id",
                                "terms"            => $customCat,
                                "include_children" => false,
                            )),
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                        );
                        $the_query = new WP_Query($args);
                        if ( $the_query->have_posts() ) {
                            while ( $the_query->have_posts() ) {
                                $the_query->the_post();
                                $thisResponse = mapster_getOnlyValues( get_the_ID() );
                                $customData = mapster_organizeCustomData( $thisResponse );
                                if ( $customData ) {
                                    array_push( $response, $customData );
                                }
                            }
                        }
                    }
                }
            }
            ob_get_clean();
            return $response;
        }

    }

    public function mapster_wp_maps_get_single_feature() {
        register_rest_route( 'mapster-wp-maps', 'feature', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_feature',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_get_feature(  $params  ) {
            $post_id = intval( $params['id'] );
            if ( $post_id ) {
                if ( get_post_status( $post_id ) == "publish" ) {
                    $thisResponse = mapster_getOnlyValues( $post_id );
                    ob_get_clean();
                    return $thisResponse;
                }
            }
            return false;
        }

    }

    public function mapster_wp_maps_save_mapmaker() {
        register_rest_route( 'mapster-wp-maps', 'save-mapmaker', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_mapmaker_save',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_mapmaker_save(  $request  ) {
            $body = json_decode( $request->get_body(), true );
            $post_id = $body['config']['id'] ?? null;
            if ( !$post_id ) {
                return new WP_Error('missing_id', 'Map ID is required', [
                    'status' => 400,
                ]);
            }
            // One-time pre-V2 snapshot: taken on first save after the update, never overwritten.
            // Only runs when the map has existing features — fresh maps with no V1 data don't need it.
            $backup_option = 'mapster_v2_backup_' . $post_id;
            if ( !get_option( $backup_option ) ) {
                // Capture ACF-managed meta keys only (those with a field_ pointer).
                // Stores both the data key and pointer key so update_post_meta can restore exactly.
                // Keyed by readable field name so the stored JSON is inspectable.
                $snap_acf = function ( $id ) {
                    $out = [];
                    $all = get_post_meta( $id );
                    foreach ( $all as $key => $v ) {
                        if ( $key[0] === '_' ) {
                            continue;
                        }
                        $acf_field_key = $all['_' . $key][0] ?? null;
                        if ( $acf_field_key && strpos( $acf_field_key, 'field_' ) === 0 ) {
                            $out[$key] = [
                                'value'     => $v[0],
                                'field_key' => $acf_field_key,
                            ];
                        }
                    }
                    return $out;
                };
                $to_ids = fn( $items ) => array_map( fn( $i ) => ( is_object( $i ) ? $i->ID : intval( $i ) ), (array) $items );
                $feature_ids = array_filter( array_merge( $to_ids( ( get_field( 'locations', $post_id ) ?: [] ) ), $to_ids( ( get_field( 'lines', $post_id ) ?: [] ) ), $to_ids( ( get_field( 'polygons', $post_id ) ?: [] ) ) ) );
                if ( !empty( $feature_ids ) ) {
                    $backup = [
                        'timestamp' => time(),
                        'map'       => $snap_acf( $post_id ),
                        'features'  => [],
                    ];
                    foreach ( $feature_ids as $fid ) {
                        $backup['features'][$fid] = $snap_acf( $fid );
                    }
                    // PHP serialize preserves exact PHP types; stored as non-autoloaded option.
                    update_option( $backup_option, serialize( $backup ), false );
                }
            }
            // Write map provider and/or style ID when the user switches style systems.
            if ( !empty( $body['provider'] ) ) {
                update_field( 'map_type_map_provider', sanitize_text_field( $body['provider'] ), $post_id );
            }
            if ( !empty( $body['style']['id'] ) ) {
                $style_field_map = [
                    'access_token'    => 'map_type_map_tile_style_access_token',
                    'no_access_token' => 'map_type_map_tile_style_no_access_token',
                ];
                $style_field = $style_field_map[sanitize_key( $body['style']['type'] ?? '' )] ?? null;
                if ( $style_field ) {
                    update_field( $style_field, sanitize_text_field( $body['style']['id'] ), $post_id );
                }
            }
            // Write map config
            mapster_write_config_to_acf( $post_id, $body['config'] );
            // Persist Google key to settings page if provided
            $google_key = $body['config']['google_key'] ?? '';
            if ( $google_key !== '' && $google_key !== false ) {
                $settings_page_id = get_option( 'mapster_settings_page' );
                update_field( 'google_maps_api_key', sanitize_text_field( $google_key ), $settings_page_id );
            }
            // Write features — create new ones, update existing ones
            $id_map = [];
            // tempId → real WP post ID, returned to client
            $writer = new Mapster_Feature_Writer();
            $features = $body['features'] ?? [];
            foreach ( $features as $feature ) {
                if ( !empty( $feature['_new'] ) ) {
                    $real_id = $writer->create_feature( $feature );
                    $id_map[strval( $feature['metadata']['id'] )] = $real_id;
                } else {
                    $feature_id = $feature['metadata']['id'] ?? null;
                    if ( $feature_id ) {
                        $writer->write_feature_to_acf( (int) $feature_id, $feature );
                    }
                }
            }
            if ( !empty( $features ) ) {
                $writer->sync_direct_associations( $post_id, $features, $id_map );
            }
            // Collect popup styles for all saved features (using resolved real IDs for new ones)
            $popup_styles = [];
            $popup_styles_seen = [];
            $all_feature_ids = array_merge( array_values( $id_map ), array_filter( array_map( fn( $f ) => ( empty( $f['_new'] ) ? intval( $f['metadata']['id'] ?? 0 ) : 0 ), $features ) ) );
            foreach ( $all_feature_ids as $fid ) {
                if ( !$fid ) {
                    continue;
                }
                $style = get_field( 'popup_style', $fid );
                if ( !$style ) {
                    continue;
                }
                $sid = $style->ID;
                if ( in_array( $sid, $popup_styles_seen ) ) {
                    continue;
                }
                $popup_styles_seen[] = $sid;
                $popup_styles[] = mapster_build_popup_template( $sid );
            }
            return [
                'success'      => true,
                'id'           => $post_id,
                'id_map'       => $id_map,
                'popup_styles' => $popup_styles,
            ];
        }

    }

    public function mapster_wp_maps_get_popup_templates() {
        register_rest_route( 'mapster-wp-maps', 'popup-templates', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_popup_templates_cb',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_get_popup_templates_cb(  $request  ) {
            $page = max( 1, intval( $request->get_param( 'page' ) ?? 1 ) );
            $per_page = 20;
            $query = new WP_Query([
                'post_type'      => 'mapster-wp-popup',
                'post_status'    => 'publish',
                'posts_per_page' => $per_page,
                'paged'          => $page,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]);
            $templates = array_map( fn( $post ) => mapster_build_popup_template( $post->ID ), $query->posts );
            return [
                'templates' => $templates,
                'total'     => (int) $query->found_posts,
                'pages'     => (int) $query->max_num_pages,
                'page'      => $page,
            ];
        }

    }

    public function mapster_wp_maps_update_post_title() {
        register_rest_route( 'mapster-wp-maps', 'update-post-title', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_update_post_title_cb',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_update_post_title_cb(  $request  ) {
            $body = json_decode( $request->get_body(), true );
            $post_id = intval( $body['id'] ?? 0 );
            $title = sanitize_text_field( $body['title'] ?? '' );
            if ( !$post_id || !$title ) {
                return new WP_Error('missing_params', 'id and title are required', [
                    'status' => 400,
                ]);
            }
            $result = wp_update_post( [
                'ID'         => $post_id,
                'post_title' => $title,
            ], true );
            if ( is_wp_error( $result ) ) {
                return new WP_Error('update_failed', $result->get_error_message(), [
                    'status' => 500,
                ]);
            }
            return [
                'id'    => $post_id,
                'title' => $title,
            ];
        }

    }

    public function mapster_wp_maps_get_map_sources() {
        register_rest_route( 'mapster-wp-maps', 'map-sources', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_map_sources_cb',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_get_map_sources_cb(  $request  ) {
            $map_id = intval( $request->get_param( 'id' ) );
            if ( !$map_id ) {
                return new WP_Error('missing_id', 'Map ID is required', [
                    'status' => 400,
                ]);
            }
            return Mapster_Map_Sources::get( $map_id );
        }

    }

    public function mapster_wp_maps_save_map_sources() {
        register_rest_route( 'mapster-wp-maps', 'save-sources', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_save_map_sources_cb',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_save_map_sources_cb(  $request  ) {
            $body = json_decode( $request->get_body(), true );
            $map_id = intval( $body['id'] ?? 0 );
            if ( !$map_id ) {
                return new WP_Error('missing_id', 'Map ID is required', [
                    'status' => 400,
                ]);
            }
            return Mapster_Map_Sources::save( $map_id, $body );
        }

    }

    public function mapster_wp_maps_search_sources() {
        register_rest_route( 'mapster-wp-maps', 'search-sources', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_search_sources_cb',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        function mapster_wp_maps_search_sources_cb(  $request  ) {
            $body = json_decode( $request->get_body(), true );
            $type = sanitize_key( $body['type'] ?? '' );
            $search = sanitize_text_field( $body['search'] ?? '' );
            if ( !$type ) {
                return new WP_Error('missing_type', 'Search type is required', [
                    'status' => 400,
                ]);
            }
            return Mapster_Map_Sources::search( $type, $search );
        }

    }

    public function mapster_wp_maps_get_live_config() {
        register_rest_route( 'mapster-wp-maps', 'map-live', array(
            'methods'             => 'POST',
            'callback'            => 'mapster_wp_maps_live_config',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_live_config(  $request  ) {
            $body = $request->get_body();
            $decoded_post = json_decode( $body, true );
            $post_id = $decoded_post['id'];
            $overrides = $decoded_post['overrides'];
            $features = $decoded_post['features'];
            $feature_ids = getFeatureIDs( $features );
            $response = createSdkResponse( $post_id, $feature_ids, $overrides );
            $response['config']['element'] = "mapster-wp-maps";
            return $response;
            // return array($response);
        }

        function getFeatureIDs(  $features  ) {
            $response = array();
            $idsArray = $features['ids'];
            $catsArray = $features['categories'];
            $customArray = $features['custom'];
            $customCatsArray = $features['custom_cats'];
            if ( mapster_can_be_looped( $idsArray ) ) {
                foreach ( $idsArray as $id ) {
                    array_push( $response, $id );
                }
            }
            // Check for category additions
            if ( mapster_can_be_looped( $catsArray ) ) {
                if ( count( $catsArray ) > 0 ) {
                    $args = array(
                        'post_type'      => array(
                            'mapster-wp-user-sub',
                            'mapster-wp-location',
                            'mapster-wp-polygon',
                            'mapster-wp-line'
                        ),
                        'tax_query'      => array(array(
                            "taxonomy"         => "wp-map-category",
                            "field"            => "term_id",
                            "terms"            => $catsArray,
                            "include_children" => false,
                        )),
                        'post_status'    => 'publish',
                        'posts_per_page' => -1,
                    );
                    $the_query = new WP_Query($args);
                    if ( $the_query->have_posts() ) {
                        while ( $the_query->have_posts() ) {
                            $the_query->the_post();
                            array_push( $response, get_the_ID() );
                        }
                    }
                }
            }
            // Check for custom additions
            if ( mapster_can_be_looped( $customArray ) ) {
                foreach ( $customArray as $id ) {
                    array_push( $response, $id );
                }
            }
            if ( mapster_can_be_looped( $customCatsArray ) ) {
                if ( count( $customCatsArray ) > 0 ) {
                    foreach ( $customCatsArray as $customCat ) {
                        $term = get_term( $customCat );
                        $args = array(
                            'post_type'      => "any",
                            'tax_query'      => array(array(
                                "taxonomy"         => $term->taxonomy,
                                "field"            => "term_id",
                                "terms"            => $customCat,
                                "include_children" => false,
                            )),
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                        );
                        $the_query = new WP_Query($args);
                        if ( $the_query->have_posts() ) {
                            while ( $the_query->have_posts() ) {
                                $the_query->the_post();
                                array_push( $response, get_the_ID() );
                            }
                        }
                    }
                }
            }
            ob_get_clean();
            return $response;
        }

    }

    public function mapster_wp_maps_get_map() {
        register_rest_route( 'mapster-wp-maps', 'map', array(
            'methods'             => 'GET',
            'callback'            => 'mapster_wp_maps_get_single_map',
            'permission_callback' => function () {
                return true;
                // open to public
            },
        ) );
        function mapster_wp_maps_get_single_map(  $params  ) {
            $post_id = intval( $params['id'] );
            $ignore_cache = $params['ignore_cache'];
            $single_feature_id = ( isset( $params['single_feature_id'] ) ? intval( $params['single_feature_id'] ) : false );
            $feature_ids = ( isset( $params['feature_ids'] ) ? explode( ',', $params['feature_ids'] ) : false );
            $acf_data = get_field_objects( $post_id );
            $minimized_data = array();
            // Top level properties
            if ( mapster_can_be_looped( $acf_data ) ) {
                foreach ( $acf_data as $key => $data ) {
                    $minimized_data[$key] = $data['value'];
                }
            }
            $popup_styles = array();
            $popup_styles_added = array();
            $minimized_location_data = array();
            $minimized_line_data = array();
            $minimized_polygon_data = array();
            $categories = array();
            $feature_ids_to_load = array();
            $progressive_map = false;
            $returnSdk = ( isset( $params['sdk'] ) ? true : false );
            $testdata = false;
            $cache_enabled = false;
            // Load one feature if it's specified
            if ( $single_feature_id || $feature_ids ) {
                if ( $single_feature_id ) {
                    array_push( $feature_ids_to_load, $single_feature_id );
                } else {
                    $feature_ids_to_load = array_merge( $feature_ids_to_load, $feature_ids );
                }
                foreach ( $features_to_fetch as $single_feature ) {
                    $this_feature_id = intval( $single_feature );
                    array_push( $feature_ids_to_load, $this_feature_id );
                    $single_feature_post_type = get_post_type( $this_feature_id );
                    $dataToAdd = mapster_getOnlyValues( $single_feature );
                    if ( $dataToAdd['data']['popup_style'] ) {
                        if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                            array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                            array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                        }
                        $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                    }
                    if ( $single_feature_post_type === 'mapster-wp-location' || $single_feature_post_type == 'mapster-wp-user-sub' ) {
                        array_push( $minimized_location_data, $dataToAdd );
                    } else {
                        if ( $single_feature_post_type === 'mapster-wp-line' ) {
                            array_push( $minimized_line_data, $dataToAdd );
                        } else {
                            if ( $single_feature_post_type === 'mapster-wp-polygon' ) {
                                array_push( $minimized_polygon_data, $dataToAdd );
                            }
                        }
                    }
                }
            } else {
                if ( $progressive_map ) {
                    $minimized_data['all_features'] = array();
                    if ( mapster_can_be_looped( $minimized_data['locations'] ) ) {
                        foreach ( $minimized_data['locations'] as $location ) {
                            array_push( $minimized_data['all_features'], $location->ID );
                        }
                    }
                    if ( mapster_can_be_looped( $minimized_data['lines'] ) ) {
                        foreach ( $minimized_data['lines'] as $line ) {
                            array_push( $minimized_data['all_features'], $line->ID );
                        }
                    }
                    if ( mapster_can_be_looped( $minimized_data['polygons'] ) ) {
                        foreach ( $minimized_data['polygons'] as $polygon ) {
                            array_push( $minimized_data['all_features'], $polygon->ID );
                        }
                    }
                    $categories = get_field( 'add_by_category', $post_id );
                    if ( mapster_can_be_looped( $categories ) ) {
                        if ( count( $categories ) > 0 ) {
                            $args = array(
                                'post_type'      => array(
                                    'mapster-wp-user-sub',
                                    'mapster-wp-location',
                                    'mapster-wp-polygon',
                                    'mapster-wp-line'
                                ),
                                'tax_query'      => array(array(
                                    "taxonomy"         => "wp-map-category",
                                    "field"            => "term_id",
                                    "terms"            => $categories,
                                    "include_children" => false,
                                )),
                                'post_status'    => 'publish',
                                'posts_per_page' => -1,
                            );
                            $the_query = new WP_Query($args);
                            if ( $the_query->have_posts() ) {
                                while ( $the_query->have_posts() ) {
                                    $the_query->the_post();
                                    array_push( $minimized_data['all_features'], get_the_ID() );
                                }
                            }
                        }
                    }
                    if ( isset( $minimized_data['add_custom_posts'] ) && mapster_can_be_looped( $minimized_data['add_custom_posts'] ) ) {
                        foreach ( $minimized_data['add_custom_posts'] as $custom_post ) {
                            array_push( $minimized_data['all_features'], $custom_post->ID );
                        }
                    }
                } else {
                    // Normal feature additions
                    if ( mapster_can_be_looped( $minimized_data['locations'] ) ) {
                        foreach ( $minimized_data['locations'] as $location ) {
                            array_push( $feature_ids_to_load, $location->ID );
                            if ( !$returnSdk ) {
                                $dataToAdd = mapster_getOnlyValues( $location->ID );
                                if ( isset( $dataToAdd['data']['popup_style'] ) && $dataToAdd['data']['popup_style'] ) {
                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                    }
                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                }
                                array_push( $minimized_location_data, $dataToAdd );
                            }
                        }
                    }
                    if ( mapster_can_be_looped( $minimized_data['lines'] ) ) {
                        foreach ( $minimized_data['lines'] as $line ) {
                            array_push( $feature_ids_to_load, $line->ID );
                            if ( !$returnSdk ) {
                                $dataToAdd = mapster_getOnlyValues( $line->ID );
                                if ( isset( $dataToAdd['data']['popup_style'] ) ) {
                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                    }
                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                }
                                array_push( $minimized_line_data, $dataToAdd );
                            }
                        }
                    }
                    if ( mapster_can_be_looped( $minimized_data['polygons'] ) ) {
                        foreach ( $minimized_data['polygons'] as $polygon ) {
                            array_push( $feature_ids_to_load, $polygon->ID );
                            if ( !$returnSdk ) {
                                $dataToAdd = mapster_getOnlyValues( $polygon->ID );
                                if ( isset( $dataToAdd['data']['popup_style'] ) ) {
                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                    }
                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                }
                                array_push( $minimized_polygon_data, $dataToAdd );
                            }
                        }
                    }
                    // Check for category additions
                    $categories = get_field( 'add_by_category', $post_id );
                    $customCategories = get_field( 'add_by_custom_category', $post_id );
                    $tax_queries = array(
                        "relation" => "OR",
                    );
                    $all_posts = array();
                    $post_ids_added = array();
                    if ( mapster_can_be_looped( $categories ) ) {
                        if ( count( $categories ) > 0 ) {
                            if ( mapster_can_be_looped( $categories ) ) {
                                array_push( $tax_queries, array(
                                    "taxonomy"         => "wp-map-category",
                                    "field"            => "term_id",
                                    "terms"            => $categories,
                                    "include_children" => false,
                                ) );
                            }
                        }
                    }
                    if ( mapster_can_be_looped( $tax_queries ) ) {
                        if ( count( $tax_queries ) > 1 ) {
                            if ( get_field( 'submission_administration_show_all_languages_on_one_map', $post_id, true ) && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
                                $current_lang = apply_filters( 'wpml_current_language', NULL );
                                $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
                                foreach ( $languages as $language ) {
                                    do_action( 'wpml_switch_language', $language["language_code"] );
                                    $args = array(
                                        'post_type'      => "any",
                                        'tax_query'      => $tax_queries,
                                        'post_status'    => 'publish',
                                        'posts_per_page' => -1,
                                    );
                                    $the_query = new WP_Query($args);
                                    if ( $the_query->have_posts() ) {
                                        while ( $the_query->have_posts() ) {
                                            $the_query->the_post();
                                            if ( !in_array( get_the_ID(), $post_ids_added ) ) {
                                                array_push( $all_posts, array(
                                                    "post_type" => get_post_type(),
                                                    "post_id"   => get_the_ID(),
                                                ) );
                                                array_push( $post_ids_added, get_the_ID() );
                                            }
                                        }
                                    }
                                }
                                $language_code = "";
                                if ( is_string( $current_lang ) ) {
                                    $language_code = $current_lang;
                                } else {
                                    $language_code = $current_lang["language_code"];
                                }
                                do_action( 'wpml_switch_language', $language_code );
                            } else {
                                $args = array(
                                    'post_type'      => "any",
                                    'tax_query'      => $tax_queries,
                                    'post_status'    => 'publish',
                                    'posts_per_page' => -1,
                                );
                                $the_query = new WP_Query($args);
                                if ( $the_query->have_posts() ) {
                                    while ( $the_query->have_posts() ) {
                                        $the_query->the_post();
                                        array_push( $all_posts, array(
                                            "post_type" => get_post_type(),
                                            "post_id"   => get_the_ID(),
                                        ) );
                                    }
                                }
                            }
                            foreach ( $all_posts as $post ) {
                                array_push( $feature_ids_to_load, $post['post_id'] );
                                if ( !$returnSdk ) {
                                    $initialData = mapster_getOnlyValues( $post['post_id'] );
                                    $dataToAdd = mapster_organizeCustomData( $initialData );
                                    if ( $dataToAdd ) {
                                        if ( isset( $dataToAdd['data']['location'] ) ) {
                                            if ( isset( $dataToAdd['data']['popup_style'] ) ) {
                                                if ( $dataToAdd['data']['popup_style'] ) {
                                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                                    }
                                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                                }
                                            }
                                            array_push( $minimized_location_data, $dataToAdd );
                                        }
                                        if ( isset( $dataToAdd['data']['line'] ) ) {
                                            if ( isset( $dataToAdd['data']['popup_style'] ) ) {
                                                if ( $dataToAdd['data']['popup_style'] ) {
                                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                                    }
                                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                                }
                                            }
                                            array_push( $minimized_line_data, $dataToAdd );
                                        }
                                        if ( isset( $dataToAdd['data']['polygon'] ) ) {
                                            if ( isset( $dataToAdd['data']['popup_style'] ) ) {
                                                if ( $dataToAdd['data']['popup_style'] ) {
                                                    if ( !in_array( $dataToAdd['data']['popup_style']['id'], $popup_styles_added ) ) {
                                                        array_push( $popup_styles, $dataToAdd['data']['popup_style'] );
                                                        array_push( $popup_styles_added, $dataToAdd['data']['popup_style']['id'] );
                                                    }
                                                    $dataToAdd['data']['popup_style'] = $dataToAdd['data']['popup_style']['id'];
                                                }
                                            }
                                            array_push( $minimized_polygon_data, $dataToAdd );
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unset($minimized_data['locations']);
                    unset($minimized_data['lines']);
                    unset($minimized_data['polygons']);
                }
            }
            ob_get_clean();
            // return json_decode('');
            //    $ch = curl_init();
            //    curl_setopt($ch, CURLOPT_URL, "https://ycik.co.za/staging/wp-json/mapster-wp-maps/map?id=4309&sdk=true");
            //    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            //    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            //    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            // $response = curl_exec($ch);
            //    $decoded_response = json_decode($response);
            //    $decoded_response->config->element = "mapster-wp-maps-" . $post_id;
            //    return $decoded_response;
            if ( $returnSdk ) {
                $sdk_response = createSdkResponse( $post_id, $feature_ids_to_load );
                // Cache was enabled but the file was missing (never generated, or just
                // invalidated by an edit) -- save this response so the next visitor
                // gets the fast path instead of rebuilding it again from the DB.
                if ( $cache_enabled && class_exists( 'Mapster_Wordpress_Maps_Pro_Admin_API' ) ) {
                    ( new Mapster_Wordpress_Maps_Pro_Admin_API() )->mapster_write_map_cache( $post_id, $sdk_response );
                }
                return $sdk_response;
            } else {
                $toReturn = array(
                    'id'                => $post_id,
                    'cats'              => $categories,
                    'popup_styles'      => $popup_styles,
                    'location_template' => mapster_getTemplate( 'location' ),
                    'line_template'     => mapster_getTemplate( 'line' ),
                    'polygon_template'  => mapster_getTemplate( 'polygon' ),
                    'map'               => mapster_remakeUsingTemplate( $minimized_data, 'map' ),
                    'locations'         => mapster_minimizeUsingTemplate( dynamic_popup_replace( $minimized_location_data ), 'location' ),
                    'lines'             => mapster_minimizeUsingTemplate( dynamic_popup_replace( $minimized_line_data ), 'line' ),
                    'polygons'          => mapster_minimizeUsingTemplate( dynamic_popup_replace( $minimized_polygon_data ), 'polygon' ),
                );
                return $toReturn;
            }
        }

    }

}

/// Returns the mtime of the map cache file, or false if it doesn't exist yet.
function mapster_get_cache_date(  int $post_id  ) {
    $file = trailingslashit( wp_upload_dir()['basedir'] ) . 'mapster/map-' . $post_id . '.json';
    return ( file_exists( $file ) ? filemtime( $file ) : false );
}

// Returns {id, url} for an ACF image/file field stored as raw attachment ID in post meta.
function mapster_format_loader_custom(  $raw_meta  ) {
    $id = intval( $raw_meta );
    if ( !$id ) {
        return false;
    }
    $url = wp_get_attachment_url( $id );
    $metadata = wp_get_attachment_metadata( $id );
    return ( $url ? [
        'id'         => $id,
        'url'        => $url,
        'dimensions' => array($metadata['width'], $metadata['height']),
    ] : false );
}

// Parses textarea "property : Label\n..." into [{property, label}] for the frontend.
function mapster_parse_additional_filters(  $value  ) : array {
    if ( !$value || !is_string( $value ) ) {
        return [];
    }
    $result = [];
    foreach ( explode( "\n", trim( $value ) ) as $line ) {
        $line = trim( $line );
        if ( $line === '' ) {
            continue;
        }
        $parts = explode( ':', $line, 2 );
        if ( count( $parts ) === 2 ) {
            $result[] = [
                'property' => trim( $parts[0] ),
                'label'    => trim( $parts[1] ),
            ];
        }
    }
    return $result;
}

// Resolves ACF excluded_categories value (term IDs or term objects) to [{value, label, taxonomy}].
function mapster_resolve_category_terms(  $value  ) : array {
    if ( !$value || !is_array( $value ) ) {
        return [];
    }
    $result = [];
    foreach ( $value as $item ) {
        if ( is_object( $item ) && isset( $item->term_id ) ) {
            $result[] = [
                'value'    => $item->term_id,
                'label'    => $item->name,
                'taxonomy' => $item->taxonomy,
            ];
        } elseif ( is_numeric( $item ) ) {
            $term = get_term( intval( $item ) );
            if ( $term && !is_wp_error( $term ) ) {
                $result[] = [
                    'value'    => $term->term_id,
                    'label'    => $term->name,
                    'taxonomy' => $term->taxonomy,
                ];
            }
        }
    }
    return $result;
}

// Normalises ACF user field output (array of user arrays or IDs) to the
// [{value, label, email}] shape used by React Select on the frontend.
function mapster_format_notify_users(  $value  ) : array {
    if ( !$value || !is_array( $value ) ) {
        return [];
    }
    return array_values( array_filter( array_map( function ( $user ) {
        if ( is_numeric( $user ) ) {
            $u = get_userdata( intval( $user ) );
            return ( $u ? [
                'value' => $u->ID,
                'label' => $u->display_name,
                'email' => $u->user_email,
            ] : null );
        }
        $id = $user['ID'] ?? $user['id'] ?? null;
        if ( !$id ) {
            return null;
        }
        return [
            'value' => intval( $id ),
            'label' => $user['display_name'] ?? $user['user_login'] ?? '',
            'email' => $user['user_email'] ?? '',
        ];
    }, $value ) ) );
}

function mapster_organizeCustomData(  $dataToAdd  ) {
    $customDataToAdd = $dataToAdd;
    if ( isset( $dataToAdd['data'] ) ) {
        $geographic_data_key = false;
        if ( mapster_can_be_looped( $dataToAdd['data'] ) ) {
            foreach ( $dataToAdd['data'] as $key => $data_field ) {
                if ( is_array( $data_field ) && array_key_exists( 'type', $data_field ) ) {
                    if ( isset( $data_field['type'] ) && isset( $data_field['coordinates'] ) ) {
                        $geographic_data_key = $key;
                    }
                }
            }
            if ( isset( $dataToAdd['data'][$geographic_data_key] ) && isset( $dataToAdd['data'][$geographic_data_key]['type'] ) ) {
                if ( str_contains( $dataToAdd['data'][$geographic_data_key]['type'], "Point" ) ) {
                    $customDataToAdd['data']['location'] = $dataToAdd['data'][$geographic_data_key];
                }
                if ( str_contains( $dataToAdd['data'][$geographic_data_key]['type'], "Line" ) ) {
                    $customDataToAdd['data']['line'] = $dataToAdd['data'][$geographic_data_key];
                }
                if ( str_contains( $dataToAdd['data'][$geographic_data_key]['type'], "Polygon" ) ) {
                    $customDataToAdd['data']['polygon'] = $dataToAdd['data'][$geographic_data_key];
                }
                if ( $geographic_data_key !== "location" && $geographic_data_key !== "line" && $geographic_data_key !== "polygon" ) {
                    unset($customDataToAdd['data'][$geographic_data_key]);
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
    return $customDataToAdd;
}

function dynamic_popup_replace(  $data  ) {
    return $data;
}

function replace_text_with_property_or_acf(  $post_id, $properties, $text  ) {
    if ( $text ) {
        $new_text = $text;
        preg_match_all( '#\\{(.*?)\\}#', $new_text, $matches );
        foreach ( $matches[0] as $index => $match ) {
            if ( isset( $properties[$matches[1][$index]] ) ) {
                $new_text = str_replace( $match, $properties[$matches[1][$index]], $new_text );
            } else {
                if ( strpos( $match, "acf" ) !== false ) {
                    $acf_field_id = str_replace( "acf.", "", $matches[1][$index] );
                    $field_data = get_field_object( $acf_field_id, $post_id );
                    if ( $field_data ) {
                        if ( isset( $field_data['choices'] ) ) {
                            if ( is_array( $field_data['value'] ) ) {
                                $arranged_values = array();
                                foreach ( $field_data['value'] as $value ) {
                                    array_push( $arranged_values, $field_data['choices'][$value] );
                                }
                                $field_data = implode( ', ', $arranged_values );
                            } else {
                                $field_data = $field_data['choices'][$field_data['value']];
                            }
                        } else {
                            $field_data = $field_data['value'];
                        }
                        $new_text = str_replace( $match, $field_data, $new_text );
                    } else {
                        $new_text = str_replace( $match, "", $new_text );
                    }
                }
            }
        }
        $new_text = preg_replace( '/\\\\n/', "\n", $new_text );
        return $new_text;
    }
    return $text;
}

function mapster_setGroup(  $field, $sub_fields, $post_id  ) {
    $array_to_add = array();
    if ( mapster_can_be_looped( $sub_fields ) ) {
        foreach ( $sub_fields as $sub_field ) {
            if ( isset( $sub_field['default_value'] ) ) {
                $array_to_add[$sub_field['name']] = $sub_field['default_value'];
                update_field( $field['name'], $array_to_add, $post_id );
            }
            if ( $sub_field['type'] == 'group' ) {
                mapster_setGroup( $sub_field, $sub_field['sub_fields'], $post_id );
            }
        }
    }
}

function mapster_setDefaults(  $all_fields, $post_id  ) {
    $field_names = array();
    if ( mapster_can_be_looped( $all_fields ) ) {
        foreach ( $all_fields as $field ) {
            array_push( $field_names, $field );
            if ( isset( $field['default_value'] ) ) {
                update_field( $field['name'], $field['default_value'], $post_id );
            }
            if ( $field['type'] == 'group' ) {
                mapster_setGroup( $field, $field['sub_fields'], $post_id );
            }
        }
    }
    return $field_names;
}

function mapster_minimizeUsingTemplate(  $data, $type  ) {
    $toReturn = array();
    $template = mapster_getTemplate( $type );
    if ( $type == 'map' ) {
        $toReturn = mapster_replaceValueIfNotDefault( $template, $data );
    } else {
        if ( mapster_can_be_looped( $data ) ) {
            foreach ( $data as $feature ) {
                array_push( $toReturn, mapster_replaceValueIfNotDefault( $template, $feature ) );
            }
        }
    }
    return $toReturn;
}

// Using default ACF values to make the object
// Therefore not worrying about undefined values that are newly added
function mapster_remakeUsingTemplate(  $data, $type  ) {
    $toReturn = array();
    $template = mapster_getTemplate( $type );
    if ( $type == 'map' ) {
        $toReturn = mapster_replaceValueOrNot( $template, $data );
    } else {
        if ( mapster_can_be_looped( $data ) ) {
            foreach ( $data as $feature ) {
                array_push( $toReturn, mapster_replaceValueOrNot( $template, $feature ) );
            }
        }
    }
    return $toReturn;
}

function mapster_replaceValueIfNotDefault(  $template, $data  ) {
    $toReturn = array();
    if ( mapster_can_be_looped( $template ) ) {
        foreach ( $template as $key => $field ) {
            if ( !is_null( $field ) || $key == 'popup_style' || isset( $data[$key] ) && !is_null( $data[$key] ) ) {
                if ( !isset( $data[$key] ) ) {
                    // $toReturn[$key] = $field;
                } else {
                    // var_dump($field);
                    if ( mapster_can_be_looped( $field ) ) {
                        $valueToAdd = mapster_replaceValueIfNotDefault( $field, $data[$key] );
                        if ( count( $valueToAdd ) > 0 ) {
                            $toReturn[$key] = $valueToAdd;
                        }
                    } else {
                        if ( mapster_can_be_looped( $data[$key] ) ) {
                            if ( $data[$key] !== $field ) {
                                $toReturn[$key] = $data[$key];
                            }
                        } else {
                            if ( strval( $data[$key] ) !== strval( $field ) ) {
                                $toReturn[$key] = $data[$key];
                            }
                        }
                    }
                }
            }
        }
    }
    if ( mapster_can_be_looped( $data ) ) {
        $all_field_keys = mapster_getAllTemplateFields( $template );
        foreach ( $data as $key => $dataPiece ) {
            if ( !is_null( $dataPiece ) ) {
                if ( !in_array( $key, $all_field_keys ) ) {
                    $toReturn['additional_details'][$key] = $dataPiece;
                }
            }
        }
        $extra_properties = mapster_getPropertyList( $data );
        foreach ( $extra_properties as $property_name => $value ) {
            $toReturn['data']['additional_details'][$property_name] = $value;
        }
    }
    return $toReturn;
}

function mapster_getAllTemplateFields(  $template  ) {
    $toReturn = array();
    foreach ( $template as $templateKey => $field ) {
        if ( mapster_can_be_looped( $field ) ) {
            array_push( $toReturn, $templateKey );
            $toReturn = array_merge( $toReturn, mapster_getAllTemplateFields( $field ) );
        } else {
            array_push( $toReturn, $templateKey );
        }
    }
    return $toReturn;
}

function mapster_replaceValueOrNot(  $template, $data  ) {
    $toReturn = array();
    if ( mapster_can_be_looped( $template ) ) {
        foreach ( $template as $key => $field ) {
            if ( !isset( $data[$key] ) ) {
                $toReturn[$key] = $field;
            } else {
                if ( mapster_can_be_looped( $field ) ) {
                    $toReturn[$key] = mapster_replaceValueOrNot( $field, $data[$key] );
                } else {
                    $toReturn[$key] = $data[$key];
                }
            }
        }
    }
    if ( mapster_can_be_looped( $data ) ) {
        foreach ( $data as $key => $dataPiece ) {
            if ( !isset( $toReturn[$key] ) && $dataPiece !== null ) {
                $toReturn['additional_details'][$key] = $dataPiece;
            }
            if ( $key == 'allowed_area' ) {
                $toReturn[$key] = get_field( 'polygon', $dataPiece );
            }
        }
        $extra_properties = mapster_getPropertyList( $data );
        foreach ( $extra_properties as $property_name => $value ) {
            $toReturn['data']['additional_details'][$property_name] = $value;
        }
    }
    return $toReturn;
}

function mapster_getTemplate(  $type  ) {
    if ( $type == 'map' ) {
        return mapster_arrange_fields( acf_get_fields( 'group_61636c62b003e' ), false );
    } elseif ( $type == 'line' ) {
        return mapster_arrange_fields( acf_get_fields( 'group_616377d62836b' ), true );
    } elseif ( $type == 'location' ) {
        return mapster_arrange_fields( acf_get_fields( 'group_6163732e0426e' ), true );
    } elseif ( $type == 'polygon' ) {
        return mapster_arrange_fields( acf_get_fields( 'group_616379566202f' ), true );
    }
}

function mapster_update_wpml_post(  $duplicated_post_id, $new_post_id, $post_type  ) {
    if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
        $post_language_code = false;
        if ( $duplicated_post_id ) {
            $get_language_args = array(
                'element_id'   => $duplicated_post_id,
                'element_type' => "post_" . $post_type,
            );
            $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
            $post_language_code = $original_post_language_info->language_code;
        } else {
            $post_language_code = apply_filters( 'wpml_current_language', NULL );
        }
        $set_language_args = array(
            'element_id'    => $new_post_id,
            'element_type'  => "post_" . $post_type,
            'trid'          => false,
            'language_code' => $post_language_code,
        );
        do_action( 'wpml_set_element_language_details', $set_language_args );
    }
}
