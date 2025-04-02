<?php

class Mapster_Wordpress_Maps_Admin_Settings {

  public function mapster_account_buttons() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <a href="<?php echo mwm_fs()->get_account_url(); ?>" style="margin-left: 10px;" class="mapster-account-button button button-large">
        <?php echo $i18n->get_mapster_strings()['settings']['Account Button']; ?>
      </a>
			<?php if( !mwm_fs()->can_use_premium_code() ) { ?>
        <a href="<?php echo mwm_fs()->get_upgrade_url(); ?>" class="mapster-account-button button button-primary button-large">
          <?php echo $i18n->get_mapster_strings()['settings']['Upgrade Button']; ?>
        </a>
      <?php } ?>
    <?php
  }

  public function mapster_output_tileset_management_info() {
    if ( mwm_fs()->is__premium_only() ) {
  	   $i18n = new Mapster_Wordpress_Maps_i18n();
      ?>
        <h2><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Header']; ?></h2>
        <p><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Description']; ?></p>
      <?php
    }
  }

  public function mapster_output_tileset_management_section() {
    if ( mwm_fs()->is__premium_only() ) {
     $i18n = new Mapster_Wordpress_Maps_i18n();
      ?>
        <div class="mapster-tileset-layout">
          <div>
            <div><label><strong><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Category']; ?></strong></label><br /><br /></div>
            <select id="tileset-management-category">
              <option value="">(<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category None']; ?>)</option>
              <?php
                $terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
                foreach($terms as $term) { ?>
                  <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                <?php }
              ?>
            </select>
        </div>
        <div>
          <div><label><strong><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Details']; ?></strong></label><br /><br /></div>
          <button id="new-tileset-source" class="button button-tertiary button-small"><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management New Tileset Source']; ?></button>
          <button id="existing-tileset-source" class="button button-tertiary button-small"><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Existing Tileset Source']; ?></button>
          <div id="new-tileset-creation">
            <div><label><strong><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Special Characters']; ?></strong></label><br /><br /></div>
            <input type="text" placeholder="<?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Tileset Source Name']; ?>" />
          </div>
          <div id="existing-tileset-selection">
            <select id="existing-tilesets">
              <option value="">(<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category None']; ?>)</option>
            </select>
          </div>
        </div>
      </div>
      <button id="update-tileset-source-button" class="button button-primary button-large" style="margin-top: 10px;">
        <?php echo $i18n->get_mapster_strings()['settings']['Tileset Management Button']; ?>
      </button>
      <div class='mapster-map-loader'>
        <svg width='38' height='38' viewBox='0 0 38 38' xmlns='http://www.w3.org/2000/svg' stroke='#333'>
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
      <div id="tileset-responses">
        <h3><?php echo $i18n->get_mapster_strings()['settings']['Tileset Management API Responses']; ?></h3>
        <div></div>
      </div>
      <?php
    }
  }

  public function mapster_output_importer_button() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <h2><?php echo $i18n->get_mapster_strings()['settings']['Import Data Header']; ?></h2>
      <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Description']; ?></p>
      <a name='Data Importer' href='#TB_inline?width=100&inlineId=mapster-importer-modal' class="thickbox button button-primary button-large">
        <?php echo $i18n->get_mapster_strings()['settings']['Import Data Button']; ?>
      </a>
    <?php
  }

  public function mapster_output_importer_info() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <h2><?php echo $i18n->get_mapster_strings()['settings']['Import Data Header']; ?></h2>
      <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Description']; ?></p>
    <?php
  }

  public function mapster_output_importer_tabs() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <div>
        <div id="geo-file-import" class='nav-tab nav-tab-active'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Button']; ?></div>
        <div id="gl-js-import" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal GL JS Button']; ?></div>
        <div id="mapster-spreadsheet" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Button']; ?></div>
        <div id="mapster-export-import" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Migration Button']; ?></div>
      </div>
      <hr style="clear: both;"/>
    <?php
  }

  public function mapster_output_spreadsheet_parent() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
       <div id="mapster-spreadsheet-options" class="nav-box">
         <p><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Description']; ?></p>
         <?php $this->mapster_spreadsheet_buttons(); ?>
       </div>
    <?php
  }

  public function mapster_spreadsheet_buttons() {
    $i18n = new Mapster_Wordpress_Maps_i18n();
	  if ( mwm_fs()->is__premium_only() ) {
			if( mwm_fs()->can_use_premium_code() ) { ?>
        <div class="mapster-importer-row">
          <div class="mapster-importer-column">
            <div style="margin-bottom: 10px;">
              <div>
                <input id="mapster-csv-url" type="text" placeholder="Enter URL" style="width: 90%;"/>
                <button id="mapster-csv-verify" class="button button-secondary" style="margin-top: 10px;"><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Verify']; ?></button>
                <button id="mapster-csv-import" class="button button-primary" style="display:none; margin-top: 10px;"><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Import']; ?></button>
              </div>
              <div class='mapster-map-loader'>
      					<svg width='38' height='38' viewBox='0 0 38 38' xmlns='http://www.w3.org/2000/svg' stroke='#333'>
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
              <div class="geojson-import-result" style="display: none; margin-top: 10px;">
                <progress class="geojson-import-progress" max="100" value="0"></progress>
                <span></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Imported']; ?>
                <div class="mapster-import-error"></div>
              </div>
            </div>
          </div>
          <div class="mapster-importer-column">
            <div style="margin-bottom: 10px;">
              <div>
                <?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Google Sheet Instructions']; ?>
              </div>
            </div>
            <div class="mapster-importer-options-csv" style="display:none; margin-bottom: 10px;">
              <p><strong><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Import Options']; ?></strong></p>
              <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category Description']; ?></p>
              <select id="csv-import-category">
                <option value="">(<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category None']; ?>)</option>
                <?php
                  $terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
                  foreach($terms as $term) { ?>
                    <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                  <?php }
                ?>
              </select>
              <p><?php echo $i18n->get_mapster_strings()['settings']['Spreadsheet Import Exclude']; ?></p>
              <input id="mapster-csv-exclude-columns" type="text" placeholder="Column1,column3,columnX" style="width: 90%;"/>
            </div>
          </div>
        </div>
      <?php
      }
    }
  }

  public function mapster_output_migration_parent() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
       <div id="mapster-export-import-options" class="nav-box">
         <p><?php echo $i18n->get_mapster_strings()['settings']['Migration Description']; ?></p>
         <?php $this->mapster_migration_buttons(); ?>
       </div>
    <?php
  }

  public function mapster_migration_buttons() {
    $i18n = new Mapster_Wordpress_Maps_i18n();
	  if ( mwm_fs()->is__premium_only() ) {
			if( mwm_fs()->can_use_premium_code() ) { ?>
        <div class="mapster-importer-row">
          <div class="mapster-importer-column">
            <div style="margin-bottom: 10px;">
              <div>
                <button id="export-mapster-installation" class="button button-primary"><?php echo $i18n->get_mapster_strings()['settings']['Migration Button Export']; ?></button>
              </div>
            </div>
          </div>
          <div class="mapster-importer-column">
            <div style="margin-bottom: 10px;">
              <div>
                <input id="upload-mapster-installation" type="file" />
                <button id="import-mapster-installation" class="button button-primary"><?php echo $i18n->get_mapster_strings()['settings']['Migration Button Import']; ?></button>
              </div>
            </div>
          </div>
        </div>
        <div class='mapster-map-loader'>
          <svg width='38' height='38' viewBox='0 0 38 38' xmlns='http://www.w3.org/2000/svg' stroke='#333'>
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
      <?php
      }
    }
  }

  public function mapster_output_geo_file_importer_parent() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <div id="geo-file-import-options" class="nav-box nav-box-active">
        <div class="mapster-importer-row">
          <div class="mapster-importer-column">
            <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Description']; ?></p>
            <div style="margin-bottom: 10px;">
              <div>
                <input id="geojson-import-file" type="file" />
              </div>
            </div>
            <div id="geojson-import-data-summary" style="display: none;">
              <h4><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Type']; ?> <span></span></h4>
              <div id="mapster-projection-warning">
                <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Reprojection Description']; ?></p>
                <input id="mapster-from-projection" type="text" placeholder="<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Reprojection Placeholder']; ?>">
                <button id="mapster-try-reproject" class="button"><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Reprojection Button']; ?></button>
              </div>
              <table class="widefat fixed" cellspacing="0">
                <thead>
                  <th><strong><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Table Type']; ?></strong></th>
                  <th><strong><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Table Features']; ?></strong></th>
                  <th><strong><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Table Example']; ?></strong></th>
                  <th><strong><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Table Warnings']; ?></strong></th>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
            <button id="geojson-import-button" class="button button-primary button-large"><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Button']; ?></button>
    				<div class='mapster-map-loader'>
    					<svg width='38' height='38' viewBox='0 0 38 38' xmlns='http://www.w3.org/2000/svg' stroke='#333'>
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
            <div id="geojson-import-details" style="display: none; margin-top: 10px;">
            </div>
            <div class="geojson-import-result" style="display: none; margin-top: 10px;">
              <progress class="geojson-import-progress" max="100" value="0"></progress>
              <span></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Imported']; ?>
              <div class="mapster-import-error"></div>
            </div>
          </div>
          <div class="mapster-importer-column">
            <div class="mapster-import-options">
              <h3><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Import Options']; ?></h3>
              <?php $this->mapster_output_category_selector(); ?>
              <?php $this->mapster_output_feature_tabs(); ?>
            </div>
          </div>
        </div>
      </div>
    <?php
  }

  public function mapster_output_feature_tabs() {
	  if ( mwm_fs()->is__premium_only() ) {
			if( mwm_fs()->can_use_premium_code() ) {
        $i18n = new Mapster_Wordpress_Maps_i18n();
        ?>
          <h4><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Header']; ?></h4>
          <div style="margin-top: 10px;">
            <div id="feature-type-point" class='nav-tab nav-tab-active'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Points']; ?> <span></span></div>
            <div id="feature-type-line" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Lines']; ?> <span></span></div>
            <div id="feature-type-polygon" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Polygons']; ?> <span></span></div>
            <div id="mapster-edit-import" class='nav-tab'><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional JSON']; ?> <span></span></div>
          </div>
          <hr style="clear: both;"/>
          <div id="feature-type-point-options" class="nav-box nav-box-active">
            <div class="mapster-panel mapster-add-condition">
              <span class="dashicons dashicons-plus-alt"></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Add']; ?>
            </div>
          </div>
          <div id="feature-type-line-options" class="nav-box">
            <div class="mapster-panel mapster-add-condition">
              <span class="dashicons dashicons-plus-alt"></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Add']; ?>
            </div>
          </div>
          <div id="feature-type-polygon-options" class="nav-box">
            <div class="mapster-panel mapster-add-condition">
              <span class="dashicons dashicons-plus-alt"></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional Add']; ?>
            </div>
          </div>
          <div id="mapster-edit-import-options" class="nav-box">
            <p></p>
            <textarea id="mapster-import-json" placeholder="<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional JSON Placeholder']; ?>"></textarea>
            <button id="mapster-load-json" class="button" style="display: none"><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional JSON Load']; ?></button>
            <button id="mapster-download-json" class="button"><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Conditional JSON Download']; ?></button>
          </div>
        <?php
      }
    }
  }

  public function mapster_output_category_selector() {
	  $i18n = new Mapster_Wordpress_Maps_i18n();
    if ( mwm_fs()->is__premium_only() ) {
			if( mwm_fs()->can_use_premium_code() ) {
        ?>
          <h4><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Import Category Header']; ?></h4>
          <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category Description']; ?></p>
          <select id="geojson-import-category">
            <option value="">(<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category None']; ?>)</option>
            <?php
              $terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
              foreach($terms as $term) { ?>
                <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
              <?php }
            ?>
          </select>
        <?php
      } else {
        ?>
         <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Import Category Description']; ?></p>
        <?php
      }
    } else {
      ?>
       <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Geo File Import Category Description']; ?></p>
      <?php
    }
  }

  public function mapster_output_gl_js_importer_parent() {
	   $i18n = new Mapster_Wordpress_Maps_i18n();
    ?>
      <div id="gl-js-import-options" class="nav-box">
        <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal GL JS Description']; ?> </p>
        <div style="margin-bottom: 10px;">
          <div>
            <input id="gl-js-import-file" type="file" />
          </div>
          <div>
            <p><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category Description']; ?></p>
            <select id="gl-js-import-category">
              <option value="">(<?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Category None']; ?>)</option>
              <?php
                $terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
                foreach($terms as $term) { ?>
                  <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                <?php }
              ?>
            </select>
          </div>
        </div>
        <button id="gl-js-import-button" class="button button-primary button-large"><?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Button']; ?></button>
        <div id="gl-js-import-result" style="display: none; margin-top: 10px;">
          <span></span> <?php echo $i18n->get_mapster_strings()['settings']['Import Data Modal Imported']; ?>
        </div>
      </div>
    <?php
  }
}

?>
