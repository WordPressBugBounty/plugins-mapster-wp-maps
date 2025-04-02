(function( $ ) {
  let ranInit = false;
  let post_data = {
    categories : [],
    post_ids : [],
    json_mass_edit : {
      point : {},
      line : {},
      polygon : {},
      popup : {}
    }
  }

  $(document).on('click', '.do-mass-edit', function() {
    // Fetch values of all checked fields
    $('.mapster-mass-edit .acf-field').each(function() {
      const type = $(this).closest('.nav-box').attr('id').replace('feature-type-', '').replace('-options', '');
      const firstCheckbox = $(this).find('.mass-edit-checkbox input').first();
      if(firstCheckbox.is(':checked')) {
        let acfFieldName = getACFFieldName(firstCheckbox);
        if($(this).data('type') === 'select' || $(this).data('type') === 'post_object') {
          post_data.json_mass_edit[type][acfFieldName] = $(this).find('.acf-input select').first().val();
        } else if($(this).data('type') === 'true_false' ) {
          post_data.json_mass_edit[type][acfFieldName] = $(this).find('.acf-input input[type="checkbox"]').is(':checked');
        } else if($(this).data('type') === 'wysiwyg') {
          if($(this).find('.acf-input iframe').contents().find('#tinymce').length) {
            post_data.json_mass_edit[type][acfFieldName] = $(this).find('.acf-input iframe').contents().find('#tinymce').html();
          } else {
            post_data.json_mass_edit[type][acfFieldName] = $(this).find('.acf-input .wp-editor-area').val();
          }
        } else {
          post_data.json_mass_edit[type][acfFieldName] = $(this).find('.acf-input input').first().val();
        }
      }
    })
    // Fetch categories and post IDs
    post_data.categories = $('.acf-field[data-name="add_by_category"] select').val();
    const locations = $('.acf-field[data-name="locations"] select').val();
    const lines = $('.acf-field[data-name="lines"] select').val();
    const polygons = $('.acf-field[data-name="polygons"] select').val()
    if(locations) {
      post_data.post_ids = post_data.post_ids.concat(locations);
    }
    if(lines) {
      post_data.post_ids = post_data.post_ids.concat(lines);
    }
    if(polygons) {
      post_data.post_ids = post_data.post_ids.concat(polygons);
    }
    if(window.confirm(window.mapster_mass_edit.strings["Confirmation"])) {
      $('.mapster-map-loader').show();
      fetch(window.mapster_mass_edit.rest_url + "mapster-wp-maps/mass-edit", {
        headers : {
          'X-WP-Nonce' : window.mapster_mass_edit.nonce,
          'Content-Type' : 'application/json'
        },
        method : "POST",
        body : JSON.stringify(post_data)
      }).then(resp => resp.json()).then(response => {
        $('.mapster-map-loader').hide();
        $('#mass-edit-result span').html(response.ids.length);
        $('#mass-edit-result').fadeIn();
      })
    }
  })

  function getACFFieldName(ele, name = "") {
    let thisField = ele.closest('.acf-field');
    let newName = thisField.data('name') + name;
    if(thisField.parent().closest('.acf-field').length > 0) {
      newName = getACFFieldName(thisField.parent(), "_" + newName);
    }
    return newName;
  }

  // Basic navbar interface
  $(document).on('click', '.nav-tab', function() {
    $(this).siblings().each(function() {
      $(this).removeClass('nav-tab-active');
    })
    $(this).addClass('nav-tab-active');
    $(this).parent().siblings().each(function() {
      if($(this).hasClass('nav-box')) {
        $(this).removeClass('nav-box-active');
      }
    })
    $('#' + $(this).attr('id') + '-options').addClass('nav-box-active');
  })

  jQuery(document).on('change', '.mass-edit-checkbox input', function(e) {
    e.preventDefault();
    if($(this).is(':checked')) {
      jQuery(this).closest('.acf-field').find('.mapster-mass-edit-field-container').first().show();
    } else {
      jQuery(this).closest('.acf-field').find('.mapster-mass-edit-field-container').first().hide();
    }
  });

  function init() {
    if(!ranInit) {
      $('.mapster-mass-edit .acf-fields .acf-field').each(function() {
        if($(this).data('type') !== 'group') {
          $(this).find('.acf-label label').first().after(`<div class='mass-edit-checkbox'><input type='checkbox' /> ${window.mapster_mass_edit.strings["Edit This Data"]}</div>`)
          $(this).find('.acf-input').first().wrap("<div class='mapster-mass-edit-field-container'></div>");
        }
      });
      ranInit = true;
    }
  }

	if( typeof acf.add_action !== 'undefined' ) {

		acf.add_action('ready_field', init);
		acf.add_action('append_field', init);
		acf.add_action('show_field', init);

	} else {

		$(document).on('acf/setup_fields', function(e, postbox){
			$(postbox).find('.field[data-field_type="mapster-map"]').each(function(){
				init();
			});
		});

	}

})(jQuery);
