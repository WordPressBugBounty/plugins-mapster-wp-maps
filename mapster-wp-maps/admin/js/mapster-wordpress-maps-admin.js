(function( $ ) {
	'use strict';

	// Setting from current map view
	$(document).on('click', '.mapster-set-manual-map-view', (e) => {
		e.preventDefault();
		let latitudeText = $('.acf-field[data-name="manual_latitude"]').find('label').find('i').text().replace('(currently: ', '').replace(')', '');
		let longitudeText = $('.acf-field[data-name="manual_longitude"]').find('label').find('i').text().replace('(currently: ', '').replace(')', '');
		let zoomText = $('.acf-field[data-name="manual_zoom"]').find('label').find('i').text().replace('(currently: ', '').replace(')', '');
		let pitchText = $('.acf-field[data-name="manual_pitch"]').find('label').find('i').text().replace('(currently: ', '').replace(')', '');
		let rotationText = $('.acf-field[data-name="manual_rotation"]').find('label').find('i').text().replace('(currently: ', '').replace(')', '');
		$('.acf-field[data-name="manual_latitude"] input').val(parseFloat(latitudeText).toFixed(8))
		$('.acf-field[data-name="manual_longitude"] input').val(parseFloat(longitudeText).toFixed(8))
		$('.acf-field[data-name="manual_zoom"] input').val(parseFloat(zoomText).toFixed(8))
		$('.acf-field[data-name="manual_pitch"] input').val(parseFloat(pitchText).toFixed(8))
		$('.acf-field[data-name="manual_rotation"] input').val(parseFloat(rotationText).toFixed(8))
	});

	// Managing duplicated fields (after moving around a field after creating it)
	window.addEventListener('load', function () {

		// Horizontal duplication
		if($('.acf-field[data-name="duplicate_horizontally"] input').length) {
			let originalKey = $('.acf-field[data-name="duplicate_horizontally"]').data('key');
			let newKey = $('.acf-field[data-name="duplicate_horizontally_copy"]').data('key');
			let originalField = acf.getField(originalKey);
			let newField = acf.getField(newKey);
			$('.acf-field[data-name="duplicate_horizontally_copy"] input[type="checkbox"]').prop('checked', originalField.val())
			$('.acf-field[data-name="duplicate_horizontally_copy"] input[type="checkbox"]').trigger('change')
			newField.on('change', () => {
				$('.acf-field[data-name="duplicate_horizontally"] input[type="checkbox"]').prop('checked', newField.val())
				$('.acf-field[data-name="duplicate_horizontally"] input[type="checkbox"]').trigger('change')
			})
		}
	});

	// Moving around ACF field instructions
	window.addEventListener('load', function () {
		let count = 0;
		$('#acf-group_61636c62b003e .acf-fields .acf-field:not(".acf-field-group"):not(".acf-field-accordion")').each(function() {
			$(this).find('.description').prev().append(`
				<svg id="tippy-${count}" width="25" height="25">
				  <circle cx="12.5" cy="12.5" r="10" fill="#eeeeee" />
				  <text x="50%" y="50%" text-anchor="middle" fill="#333" font-size="10px" font-family="Verdana" dy=".3em">?</text>
				?
				</svg>
			`);
			$(this).find('.description').hide();
			let descriptionHTML = $(this).find('.description').html();
			tippy(`#tippy-${count}`, {
        content: descriptionHTML,
				allowHTML: true,
				interactive: true,
				placement: 'right'
      });
			count = count + 1;
		})
		// Adding documentation link
		$('#acf-group_61636c62b003e .acf-tab-wrap.-left .acf-tab-group').append(`
			<li class="acf-tab-button"><a href="https://wpmaps-docs.mapster.me/" target="_blank">🔗 Documentation</a></li>
		`)
		$('#acf-group_61636c62b003e > .inside.acf-fields.-top').css('minHeight', $('#acf-group_61636c62b003e .acf-tab-wrap.-left .acf-tab-group').height() + 'px')
	})

	// Handling control menu selection
	window.addEventListener('load', function () {
		if($('#acf-group_61636c62b003e .mapster-controls-in-menu').length) {
			$('#acf-group_61636c62b003e .acf-field[data-name="included_controls"] .acf-input').hide();
		  /* <fs_premium_only> */
			let controlObjects = [];
			let allControls = $('*[data-name*="_control"]');
			allControls.push($('[data-name="category_filter"]'))
			allControls.push($('[data-name="custom_search_filter"]'))
			allControls.push($('[data-name="filter_dropdown"]'))
			allControls.push($('[data-name="list"]'))
			allControls.each(function() {
				if($(this).data('name') !== "map_type_control" && $(this).data('name') !== "street_view_control") {
					if($(this).find('[data-name="enable"] input[type="checkbox"]').is(":checked")) {
						controlObjects.push({
							slug : $(this).data('name'),
							name : $(this).find('label').first().text()
						})
					}
				}
			})
			let currentChecked = $('#acf-group_61636c62b003e .acf-field[data-name="included_controls"] input').val();
			if(currentChecked && currentChecked !== "") {
				let currentCheckedParsed = JSON.parse(currentChecked)
				currentCheckedParsed.forEach(checkedControl => {
					let thisControlIndex = controlObjects.findIndex(control => checkedControl === control.slug)
					controlObjects[thisControlIndex].checked = true;
				})
			}
			let html = '<div class="mapster-checkbox-controls">';
			controlObjects.forEach(control => {
				html += `<div>`
					html += `<input type="checkbox" ${control.checked ? "checked='checked'" : ""}" data-slug=${control.slug} /> ${control.name}`;
				html += "</div>";
			})
			html += "</div>";
			$('.mapster-controls-in-menu').html(html);

			jQuery(document).on('change', '.mapster-checkbox-controls input', function() {
				let currentChecked = $('#acf-group_61636c62b003e .acf-field[data-name="included_controls"] input[type="text"]').val();
				let newChecked = [];
				if(currentChecked && currentChecked !== "") {
					newChecked = JSON.parse(currentChecked)
				}
				if($(this).is(':checked')) {
					newChecked.push(jQuery(this).data('slug'));
				} else {
					let index = newChecked.indexOf(jQuery(this).data('slug'));
					if(index > -1) {
						newChecked.splice(index, 1);
					}
				}
				$('#acf-field_66e210d711526-field_66e2113e11528').attr('value', JSON.stringify(newChecked));
			})
		  /* </fs_premium_only> */
		}
	})

	// Handling control ordering
	window.addEventListener('load', function () {
		if($('#acf-group_61636c62b003e .mapster-reorder-controls').length) {
			$('#acf-group_61636c62b003e .acf-field[data-name="control_order"] .acf-input').hide();

		  /* <fs_premium_only> */
			let controlObjects = [];
			let allControls = $('*[data-name*="_control"]');
			allControls.push($('[data-name="category_filter"]'))
			allControls.push($('[data-name="custom_search_filter"]'))
			allControls.push($('[data-name="filter_dropdown"]'))
			allControls.push($('[data-name="list"]'))
			allControls.each(function() {
				if($(this).data('name') !== "map_type_control" && $(this).data('name') !== "street_view_control") {
					controlObjects.push({
						slug : $(this).data('name'),
						name : $(this).find('label').first().text(),
						enabled : $(this).find('[data-name="enable"] input[type="checkbox"]').is(":checked")
					})
				}
			})
			let currentOrder = $('#acf-group_61636c62b003e .acf-field[data-name="control_order"] input').val();
			let currentOrderParsed = false;
			if(currentOrder !== "") {
				currentOrderParsed = JSON.parse(currentOrder);
			}
			let orderedControls = [];
			if(currentOrderParsed) {
				currentOrderParsed.forEach(parsedControl => {
					let foundControlObject = controlObjects.find(thisControl => thisControl.slug === parsedControl.slug);
					if(foundControlObject.enabled) {
						orderedControls.push(foundControlObject)
					}
				})
			}
			controlObjects.forEach(controlObject => {
				let foundControlObject = orderedControls.find(thisControl => thisControl.slug === controlObject.slug)
				if(!foundControlObject && controlObject.enabled) {
					orderedControls.push(controlObject);
				}
			})
			let html = '<div class="mapster-draggable-controls">';
			orderedControls.forEach(control => {
				html += `<div class="mapster-draggable-controls-parent-container" data-slug="${control.slug}">`
					html += `${control.name}`;
				html += "</div>";
			})
			html += "</div>";
			$('.mapster-reorder-controls').html(html);
			new Sortable($('.mapster-draggable-controls')[0], {
				animation: 150, swapThreshold: 0.65, onEnd : checkControlOrderAndInsert
			});
		  /* </fs_premium_only> */
		}

		function checkControlOrderAndInsert() {
			let controlOrder = [];
			$('.mapster-draggable-controls-parent-container').each(function() {
				let thisControl = {
					slug : $(this).data('slug')
				}
				controlOrder.push(thisControl);
			})
			$('#acf-field_66cca5f0c567e').attr('value', JSON.stringify(controlOrder));
		}
	});

	// Handling category ordering
	window.addEventListener('load', function () {
		if($('#acf-group_61636c62b003e .mapster-reorder-categories').length) {
			$('#acf-group_61636c62b003e .acf-field[data-name="category_order"] .acf-input').hide();
		  /* <fs_premium_only> */
			// Check after map loads for category control, easiest way to grab the current order
			let checkCategoryListInterval = setInterval(() => {
				if($('.mapster-category-control').length) {
					clearInterval(checkCategoryListInterval);
					let categories = [];
					$('.mapster-category-control > div > ul').children().each(function() {
						if($(this).is('li')) {
							let labelWithFor = $(this).find('label[for]');
							let categoryInfo = {
								id : labelWithFor.attr('for'),
								name : labelWithFor.text(),
								children : []
							};
							categories.push(categoryInfo);
						}
						if($(this).is('ul')) {
							$(this).children().each(function() {
								let labelWithFor = $(this).find('label[for]');
								let categoryInfo = {
									id : labelWithFor.attr('for'),
									name : labelWithFor.text()
								};
								categories[categories.length - 1].children.push(categoryInfo)
							})
						}
					})
					let html = '<div class="mapster-draggable-categories">';
					categories.forEach(category => {
						html += `<div class="mapster-draggable-category-parent-container" data-id="${category.id}">`
							html += `${category.name}`;
							if(category.children.length > 0) {
								html += `<div class="mapster-draggable-category-child-container">`
								category.children.forEach(catChild => {
									html += `<div class="mapster-draggable-category-child" data-id="${catChild.id}">${catChild.name}</div>`;
								})
								html += "</div>";
							}
						html += "</div>";
					})
					html += "</div>";
					$('.mapster-reorder-categories').html(html);
					new Sortable($('.mapster-draggable-categories')[0], {
						animation: 150, swapThreshold: 0.65, onEnd : checkCategoryOrderAndInsert
					});
					$('.mapster-draggable-category-child-container').each(function() {
						new Sortable($(this)[0], {
							group : 'nested2', fallbackOnBody : true, animation: 150, swapThreshold: 0.65, onEnd : checkCategoryOrderAndInsert
						});
					})
				}
			}, 100);

			function checkCategoryOrderAndInsert() {
				let categoryOrder = [];
				$('.mapster-draggable-category-parent-container').each(function() {
					let thisCategory = {
						id : $(this).data('id'),
						children : []
					}
					if($(this).find('.mapster-draggable-category-child')) {
						$(this).find('.mapster-draggable-category-child').each(function() {
							let thisCategoryChild = { id : $(this).data('id') }
							thisCategory.children.push(thisCategoryChild);
						});
					}
					categoryOrder.push(thisCategory);
				})
				$('#acf-field_616769a697bdd-field_61676c30af8b3-field_66c748c85807e').attr('value', JSON.stringify(categoryOrder));
			}
		  /* </fs_premium_only> */
		}
	})

	// Adding style selector
	window.addEventListener('load', function () {
		if($('#mapster-style-selector').length) {
			let openStyles = [];
			$('.acf-field[data-name="map_tile_style_no_access_token"] select option').each(function() {
				openStyles.push({
					type : "no_access_token",
					value : $(this).attr('value'),
					name : $(this).text()
				})
			})
			let html = `
				<div class="mapster-accordion">
					<h2>Free Styles (no access token required)</h2>
					<div class="mapster-accordion-toggle">
						${openStyles.map(style => {
							return `<div class="mapster-style-thumb" data-slug="${style.value}" data-type="free">
								<img src="${window.mapster_admin.directory}images/styles/${style.value}.png" />
								${style.name}
							</div>`
						}).join('')}
					</div>
				</div>
			`;
			let mapboxStyles = [];
			$('.acf-field[data-name="map_tile_style_access_token"] select option').each(function() {
				if($(this).attr('value').indexOf('mapster-') === -1) {
					if(!openStyles.find(style => style.value === $(this).attr('value'))) {
						mapboxStyles.push({
							type : "access_token",
							value : $(this).attr('value'),
							name : $(this).text()
						})
					}
				}
			})
			html += `
				<div class="mapster-accordion">
					<h2>Mapbox Styles</h2>
					<p>These styles require an access token and the use of a Mapbox map.</strong></p>
					<div class="mapster-accordion-toggle">
						${mapboxStyles.map(style => {
							return `<div class="mapster-style-thumb" data-slug="${style.value}" data-type="access_token">
								<img src="${window.mapster_admin.directory}images/styles/${style.value}.png" />
								${style.name}
							</div>`
						}).join('')}
					</div>
				</div>
			`;
			let mapsterStyles = [];
			$('.acf-field[data-name="map_tile_style_access_token"] select option').each(function() {
				if($(this).attr('value').indexOf('mapster-') > -1) {
					mapsterStyles.push({
						type : "access_token",
						value : $(this).attr('value'),
						name : $(this).text()
					})
				}
			})
			html += `
				<div class="mapster-accordion">
					<h2>Mapster Pro Styles</h2>
					<p>These styles require a <a href="https://wpmaps.mapster.me/" target="_blank">Mapster Pro</a> license and a Mapbox access token.</p>
					<div class="mapster-accordion-toggle">
						${mapsterStyles.map(style => {
							return `<div class="mapster-style-thumb" data-slug="${style.value}" data-type="access_token_pro">
								<img src="${window.mapster_admin.directory}images/styles/${style.value}.png" />
								${style.name}
							</div>`
						}).join('')}
					</div>
				</div>
			`;
			$('#mapster-style-selector').html(html);

			// Set current style
			if($('#mapster-current-style').length) {
				let acfNoAccessTokenField = acf.getField("field_61636f4ed9390");
				let acfAccessTokenField = acf.getField("field_61636d141864b");
				if($('.acf-field[data-name="map_tile_style_access_token"]').hasClass("acf-hidden")) {
					let selectedOption = $('.acf-field[data-name="map_tile_style_no_access_token"] select').find(`option[value=${acfNoAccessTokenField.val()}]`);
					$('#mapster-current-style').html(selectedOption.text())
				}
				if($('.acf-field[data-name="map_tile_style_no_access_token"]').hasClass("acf-hidden")) {
					let selectedOption = $('.acf-field[data-name="map_tile_style_access_token"] select').find(`option[value=${acfAccessTokenField.val()}]`);
					$('#mapster-current-style').html(selectedOption.text())
				}
			}
		}
		$(document).on('click', ".mapster-style-thumb", function() {
			let thisSlug = $(this).data('slug');
			let thisType = $(this).data('type');
			let mapProvider = acf.getField('field_61636c71d48e1');
			let acfNoAccessTokenField = acf.getField("field_61636f4ed9390");
			let acfAccessTokenField = acf.getField("field_61636d141864b");
			if(($('.acf-field[data-name="map_tile_style_access_token"]').hasClass("acf-hidden") || mapProvider.val() !== "mapbox") && thisType.indexOf('access_token') > -1) {
				alert("You need to set your map type to Mapbox, and enter a Mapbox access token to use this style.");
			} else {
				if(thisType.indexOf('access_token') > -1) {
					acfAccessTokenField.val(thisSlug)
				} else {
					if($('.acf-field[data-name="map_tile_style_access_token"]').hasClass("acf-hidden")) {
						acfNoAccessTokenField.val(thisSlug)
					} else {
						acfAccessTokenField.val(thisSlug)
					}
				}
				$('#mapster-current-style').html($(this).text())
			}
		})
	});


	// Caching functionality interaction
	$(document).on('click', '.acf-field[data-name="generate_cache"] .acf-button-group input', function() {
		if($('#post_ID').length) {
			let post_id = $('#post_ID').val();
			if(post_id) {
		    fetch(window.mapster_admin.rest_url + `mapster-wp-maps/create-cached-file?post_id=${post_id}`, {
		      headers : {
		        'X-WP-Nonce' : window.mapster_admin.nonce,
		        'Content-Type' : 'application/json'
		      },
		      method : "GET"
		    }).then(resp => resp.json()).then(response => {
					if(response === true) {
						window.alert("Your new cache was generated!");
					} else {
						window.alert("There was an issue generating your cache. Try again or get in touch with Mapster support.");
					}
		    })
			} else {
				window.alert("Please save your post before generating a cache.");
			}
		} else {
			window.alert("Please save your post before generating a cache.");
		}
	})

})( jQuery );
