<?php
  wp_enqueue_editor();
  wp_enqueue_media();
?>

<style>
  body {
    visibility: hidden;
  }
  #loading {
    visibility: visible;
    text-align: center;
  }
</style>

<?php if ( defined('MAPSTER_LOCAL_TESTING') && MAPSTER_LOCAL_TESTING ) : ?>
  <script type="module">
    import RefreshRuntime from 'http://localhost:5174/@react-refresh'
    RefreshRuntime.injectIntoGlobalHook(window)
    window.$RefreshReg$ = () => {}
    window.$RefreshSig$ = () => () => {}
    window.__vite_plugin_react_preamble_installed__ = true
  </script>
  <script type="module" src="http://localhost:5174/@vite/client"></script>
  <script type="module" src="http://localhost:5174/src/main.jsx"></script>
<?php else : ?>
  <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) . '../js/mapmaker/mapster-mapmaker.css'; ?>" />
  <script type="module" src="<?php echo plugin_dir_url( __FILE__ ) . '../js/mapmaker/mapster-mapmaker.js'; ?>"></script>
<?php endif; ?>

<div id="loading">
  Loading...
</div>
<?php
  function mapster_seed_acf_fields(int $post_id, array $fields, string $prefix = ''): void {
    foreach ($fields as $field) {
      if ($field['type'] === 'tab') continue;
      $meta_key = $prefix ? $prefix . '_' . $field['name'] : $field['name'];
      update_post_meta($post_id, '_' . $meta_key, $field['key']);
      if (($field['default_value'] ?? '') !== '') {
        update_post_meta($post_id, $meta_key, $field['default_value']);
      }
      if (!empty($field['sub_fields']) && $field['type'] === 'group') {
        mapster_seed_acf_fields($post_id, $field['sub_fields'], $meta_key);
      }
    }
  }

  $id = isset($_GET['id']) ? $_GET['id'] : false;
  if(!$id) {
    $id = wp_insert_post(array("post_type" => "mapster-wp-map", "post_title" => "New Map", "post_status" => "publish"), true);
    foreach (['group_61636c62b003e'] as $group_key) {
      mapster_seed_acf_fields($id, acf_get_fields($group_key) ?: []);
    }
  }
?>

<input type="hidden" id="mapster-wp-nonce" value="<?php echo wp_create_nonce('wp_rest'); ?>" />
<input type="hidden" id="mapster-wp-map-id" value="<?php echo $id; ?>" />
<input type="hidden" id="mapster-wp-map-title" value="<?php echo get_the_title($id); ?>" />
<input type="hidden" id="mapster-wp-map-permalink" value="<?php echo get_the_permalink($id); ?>" />
<input type="hidden" id="mapster-wp-map-provider" value="<?php echo get_field("map_type_map_provider", $id); ?>" />
<input type="hidden" id="mapmaker-api" value="<?php echo get_rest_url(); ?>" />
<input type="hidden" id="wp-admin-url" value="<?php echo get_admin_url(); ?>" />
<input type="hidden" id="mapster-plugin-url" value="<?php echo plugin_dir_url(__FILE__); ?>" />
<input type="hidden" id="mapster-mapmaker-sdk-url" value="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../js/sdk/dist/' ); ?>" />
<input type="hidden" id="mapster-mapmaker-dev" value="<?php echo ( defined('MAPSTER_LOCAL_TESTING') && MAPSTER_LOCAL_TESTING ) ? 'true' : 'false'; ?>" />
<input type="hidden" id="mapster-mapmaker-pro" value="<?php echo ( function_exists('mwm_fs') && mwm_fs()->can_use_premium_code() ) ? 'true' : 'false'; ?>" />
<div id="mapmaker-root"></div>
