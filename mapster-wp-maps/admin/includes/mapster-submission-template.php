<?php
  acf_form_head();
?>
<style>
  <?php // echo get_field("page_style_custom_css"); ?>
  .acf-field-mapster-map, .acf-field-mapster-map ~ .acf-field {
    display: none;
  }
  .acf-field-6270b6fdaf84f ~ .acf-field {
    display: block;
  }
  .acf-field-616a60c610c96.acf-field, .acf-field-6329e82c1b138.acf-field, .acf-field-616a60c610c96 ~ .acf-field {
    display: none;
  }
  .acf-field[data-name="form_language"] {
    display: none;
  }
  body > * {
    display: none;
  }
  body > .mapster-submission-container {
    display: block;
    color: black;
  }
</style>
<?php
  wp_head();
?>
<style>
  #wpadminbar { display: none; }
  body {
    background: white;
    color: white;
  }
</style>

<div class="mapster-submission-container">
  <?php

  $i18n = new Mapster_Wordpress_Maps_i18n();

  $pagetype = isset($_GET['pagetype']) ? $_GET['pagetype'] : 'new';
  $updated = isset($_GET['updated']) ? true : false;
  $category = isset($_GET['category']) ? $_GET['category'] : false;
  $coords = isset($_GET['coords']) ? explode(",", $_GET['coords']) : false;
  $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : "new_post";
  $map_id = isset($_GET['map_id']) ? $_GET['map_id'] : false;

  if($map_id) {
    $publish = get_field('submission_administration_publish_immediately', $map_id);
    $permissions = get_field('submission_administration_permissions', $map_id);
    $editing_permissions = get_field('submission_administration_editing_permissions', $map_id);
    $users_to_notify = get_field('submission_administration_notify_users', $map_id);
    $title_field = get_field('submission_submission_interface_title_field', $map_id);
    $multiple_templates = get_field('submission_administration_multiple_templates', $map_id);
    $template_id = get_field('submission_administration_template_post', $map_id);
    if($multiple_templates) {
      $categories = get_field('submission_submission_interface_categories', $map_id);
      $index = array_search($category, $categories);
      $template_ids = get_field('submission_administration_template_posts', $map_id);
      $template_id = $template_ids[$index];
    }
  }


  if($pagetype == 'search') {

    if($editing_permissions == 'public' || ($editing_permissions == 'user' && is_user_logged_in()) || ($editing_permissions == 'private' && is_user_logged_in()) || ($editing_permissions == 'admin' && current_user_can('administrator')) ) {
      ?>
      <div>
        <?php echo do_shortcode('[mapster_wp_map_search feature_types="user-sub" permissions="' . $editing_permissions . '"]'); ?>
      </div>
      <?php
    } else {
      echo "<p>" . get_field('submission_custom_texts_no_permissions', $map_id) . "</p>";
    }

  } else if($pagetype == 'edit' || $pagetype == 'new') {

    if($permissions == 'public' || ($permissions == 'private' && is_user_logged_in()) || ($permissions == 'admin' && current_user_can('administrator')) ) {

        if($pagetype == 'edit') {
          ?>

            <div class="mapster-category-container" data-category="none">

              <button class="mapster-category-tile mapster-cat-set"><?php echo get_field('submission_custom_texts_change_map_location', $map_id); ?></button>
            </div>

          <?php
        }

        if($updated) {

          echo '<p>' . get_field('submission_custom_texts_thanks', $map_id) . '</p>';

          ?>
            <div>
              <button class="mapster-close-modal"><?php echo get_field('submission_custom_texts_back', $map_id); ?></button>
            </div>
          <?php

          if($users_to_notify && count($users_to_notify) > 0) {
            foreach($users_to_notify as $user_id) {
               $user_info = get_userdata($user_id);
               $to = $user_info->user_email;
               $subject = "Geography Submitted to " . get_bloginfo('name');
               $body = "A new geography feature post was submitted or edited for " . get_bloginfo('name') . " at " . current_time('d/m/y H:i:s') . ". Head over and take a look!";
               // $headers = "Content-Type: text/html; charset=UTF-8";
               $mailResult = wp_mail( $to, $subject, $body );
            }
          }

        } else {

          $latitude = false;
          $longitude = false;
          if($coords) {
            $latitude = $coords[1];
            $longitude = $coords[0];
          }

      		acf_form(array(
      			'post_id' => $post_id,
      			'post_title' => false,
      			'post_content' => false,
      			'new_post' => array(
      				'post_type' => 'mapster-wp-user-sub',
      				'post_status' => $publish == 'true' ? "publish" : "draft"
      			),
            'form_attributes' => array(
              'template_id' => $template_id ? $template_id : "0",
              'latitude' => $latitude,
              'longitude' => $longitude,
              'category' => $category,
              'title_field' => $title_field
            ),
            'return' => '?post_id=%post_id%&updated=true&map_id='.$map_id,
            'submit_value' => isset($_GET['post_id']) ? get_field('submission_custom_texts_save', $map_id) : get_field('submission_custom_texts_submit', $map_id)
          ));
        }
      } else {
        echo "<p>" . get_field('submission_custom_texts_no_permissions', $map_id) . "</p>";
      }
    }
  ?>

</div>

<?php wp_footer(); ?>

<script>
  jQuery(document).on('click', '.mapster-cat-set', function() {
    window.top.postMessage('set_point-<?php echo $post_id; ?>', '*')
  })
  jQuery(document).on('click', '.mapster-close-modal', function() {
    window.top.postMessage('close_modal', '*')
  })
  jQuery(document).ready(function() {
    jQuery('div').each(function() {
      let thisClass = jQuery(this).attr('class');
      if(thisClass && thisClass.indexOf('acf') > -1) {
        jQuery('.' + thisClass).show();
      }
    })
  })
  <?php
  if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
    ?>
    acf.addAction('ready_field/name=form_language', function() {
      let lang = '<?php echo apply_filters( 'wpml_current_language', NULL ); ?>'
      let field_id = jQuery('.acf-field[data-name="form_language"]').data('key');
      var field = acf.getField(field_id);
      field.val(lang);
    });
    <?php
  }
  ?>
</script>
