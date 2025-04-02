
MWM_Settings_Importer.prototype.setUpgradeMessage = function() {
  this.upgradeMessage = window.mapster_settings.strings["Upgrade Message 2"];
}

MWM_Settings_Importer.prototype.verifyCSV = function() {
  const that = this;
  const urlText = jQuery('#mapster-csv-url').val();
  if(urlText !== "") {
    // https://docs.google.com/spreadsheets/d/e/2PACX-1vTMBimgxn7THinyU2cWKxNWn_XPoLQsDIH9RmZi_GDhRJEY8AVum7B_22ui9ISydrwWYTAFZlbTRD5n/pub?output=csv
    fetch(urlText).then(resp => resp.text()).then(response => {
      console.log(window)
      csv().fromString(response).then((result) => {
        if(result.length === 0) {
          alert("Your spreadsheet is empty.")
        } else {
          const missingGeography = result.filter(row => !row.latitude || !row.longitude || row.latitude === "" || row.longitude === "")
          if(missingGeography.length > 0) {
            alert("Your sheet is missing longitude or latitude, or some values are empty for some rows. Please check row")
          }
          const missingTitles = result.filter(row => typeof row.post_title === 'undefined')
          if(missingTitles.length > 0) {
            if(confirm("Your import will be titled numerically unless you add a column called 'post_title'. Do you want to proceed without custom post titles?")) {
              this.currentCSVImport = result
              jQuery("#mapster-csv-import").fadeIn()
              jQuery(".mapster-importer-options-csv").fadeIn()
            }
          } else {
            this.currentCSVImport = result
            jQuery("#mapster-csv-import").fadeIn()
            jQuery(".mapster-importer-options-csv").fadeIn()
          }
        }
      })
    })
  }
}

MWM_Settings_Importer.prototype.doCSVImport = function() {
  let geoJSON = { type : "FeatureCollection", features : [] }
  // Do a totally basic geojson Import
  let excludedColumns = jQuery("#mapster-csv-exclude-columns").val();
  let excludedColumnsArray = excludedColumns.split(',').map(term => term.trim())
  this.currentCSVImport.forEach(item => {
    let thisFeature = {
      type : "Feature",
      properties : {},
      geometry : {
        type : "Point",
        coordinates : [parseFloat(item.longitude), parseFloat(item.latitude)]
      }
    }
    excludedColumnsArray = excludedColumnsArray.concat(['latitude', 'longitude'])
    for(let term in item) {
      if(excludedColumnsArray.indexOf(term) === -1) {
        thisFeature.properties[term] = item[term]
      }
    }
    geoJSON.features.push(thisFeature)
  })
  this.geojsondocumentToImport = geoJSON
  this.jsonImport = {
      "categories": [],
      "point": [
          {
              "id": "fkglmfwfdlg",
              "rules": [
                  {
                      "property": "post_title",
                      "comparison": "x",
                      "value": "all"
                  }
              ],
              "title_property": "post_title"
          }
      ],
      "line": [],
      "polygon": []
  }
  this.doGeoJSONImport()
}

MWM_Settings_Importer.prototype.exportMapster = function() {
  jQuery('.mapster-map-loader').show();
  fetch(`${window.mapster_settings.rest_url}mapster-wp-maps/mapster-export`, {
    method : "GET",
    headers : {
      'X-WP-Nonce' : window.mapster_settings.nonce,
      'Content-Type' : 'application/json'
    },
  }).then(resp => resp.json()).then(response => {
    jQuery('.mapster-map-loader').hide();
    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response));
    var dlAnchorElem = document.createElement('a');
    dlAnchorElem.setAttribute("href",     dataStr     );
    dlAnchorElem.setAttribute("download", "export-mapster-installation.json");
    dlAnchorElem.click();
  })
}

MWM_Settings_Importer.prototype.uploadMapster = async function(e) {
  const fileContents = await new Response(e.target.files[0]).json()
  if(fileContents.settings) {
    this.importInstallationFile = fileContents;
    jQuery('#import-mapster-installation').fadeIn();
  } else {
    window.alert("Are you sure this is the right file? The format is incorrect. Please double check or get in touch with us.");
  }
}

MWM_Settings_Importer.prototype.importMapster = function() {
  if(this.importInstallationFile) {
    jQuery('.mapster-map-loader').show();
    fetch(`${window.mapster_settings.rest_url}mapster-wp-maps/mapster-pre-import`, {
      method : "POST",
      headers : {
        'X-WP-Nonce' : window.mapster_settings.nonce,
        'Content-Type' : 'application/json'
      },
      body : JSON.stringify({
        categories : this.importInstallationFile.categories,
        settings : this.importInstallationFile.settings
      })
    }).then(resp => resp.json()).then(response => {
      window.alert("Settings import successful!");
      jQuery('.mapster-map-loader').hide();
    })
  } else {
    window.alert("Please upload an installation file.");
  }
}

MWM_Settings_Importer.prototype.createNewTileset = function() {
  jQuery('#new-tileset-creation').show();
  jQuery('#existing-tileset-selection').hide();
}

MWM_Settings_Importer.prototype.selectExistingTileset = function() {
  jQuery('#new-tileset-creation').hide();
  jQuery('#existing-tileset-selection').show();

  if(!window.mapster_settings.mapbox_username || !window.mapster_settings.mapbox_secret_token) {
    window.alert('You need to enter a Mapbox username and add a secret token.');
  } else {
    fetch(`https://api.mapbox.com/tilesets/v1/sources/${window.mapster_settings.mapbox_username}?access_token=${window.mapster_settings.mapbox_secret_token}`, {
      method : "GET",
      headers : {
        'Content-Type' : 'application/json'
      },
    }).then(resp => resp.json()).then(response => {
      jQuery('.tileset-sources').remove();
      response.forEach(tileset_source => {
        let tilesetSourceID = tileset_source.id.split('/')[tileset_source.id.split('/').length - 1];
        jQuery("#existing-tilesets").append(`<option class="tileset-sources" id="${tilesetSourceID}">${tilesetSourceID}</option>`)
      })
    })
  }
}

MWM_Settings_Importer.prototype.updateTileset = function() {
  const category = jQuery('#tileset-management-category').val();
  let request_type;
  let tileset_source_name;
  if(jQuery('#existing-tileset-selection').is(':visible')) {
    tileset_source_name = jQuery('#existing-tilesets').val();
    request_type = 'update';
  }
  if(jQuery("#new-tileset-creation").is(':visible')) {
    tileset_source_name = jQuery('#new-tileset-creation input').val();
    request_type = 'new';
  }
  if(request_type && tileset_source_name) {
    jQuery('.mapster-map-loader').show();
    fetch(`${window.mapster_settings.rest_url}mapster-wp-maps/tileset-update${window.mapster_settings.qd}request_type=${request_type}&category=${category}&username=${window.mapster_settings.mapbox_username}&access_token=${window.mapster_settings.mapbox_secret_token}&tileset_source=${tileset_source_name}`, {
      method : "GET",
      headers : {
        'X-WP-Nonce' : window.mapster_settings.nonce,
        'Content-Type' : 'application/json'
      },
    }).then(resp => resp.json()).then(response => {
      jQuery('.mapster-map-loader').hide();
      jQuery('#tileset-responses').show();
      let htmlResponse = '';
      response.responses.forEach(resp => {
        htmlResponse += `<div>${resp}</div>`;
      })
      jQuery('#tileset-responses > div').html(htmlResponse);
    })
  }
}

MWM_Settings_Importer.prototype.doGeoJSONImport = function() {
  if(this.geojsondocumentToImport) {
    const chunkSize = 50;
    let loopVar = 0;
    let chunks = [];
    for (let i = 0; i < this.geojsondocumentToImport.features.length; i += chunkSize) {
      const chunk = this.geojsondocumentToImport.features.slice(i, i + chunkSize);
      chunks.push(chunk);
    }
    this.processArray(chunks, this.doGeoJSONImportPost, this.geojsondocumentToImport.features.length);
  } else {
    window.alert(window.mapster_settings.strings["Please Upload"]);
  }
}

MWM_Settings_Importer.prototype.processArray = async function(array, fn, totalFeatures) {
  let results = [];
  for (let i = 0; i < array.length; i++) {
    let r = await fn({
      type : "FeatureCollection",
      features : array[i]
    }, this.jsonImport, this.currentImportCategory, this.featuresImported);
    this.featuresImported = this.featuresImported + r.count;
    jQuery('.geojson-import-result').fadeIn();
    jQuery('.geojson-import-result span').html(this.featuresImported + ' / ' + totalFeatures);
    jQuery('.geojson-import-progress').val((this.featuresImported/totalFeatures) * 100)
    results.push(r);
    if(this.featuresImported === totalFeatures) {
      this.displayAfterImport();
    }
  }
  return results;
}

MWM_Settings_Importer.prototype.displayAfterImport = function() {
  jQuery('.mapster-map-loader').hide();
  jQuery('.mapster-import-error').html(window.mapster_settings.strings["Seem Wrong"] + this.upgradeMessage);
}

MWM_Settings_Importer.prototype.saveJSON = function(newJSON) {
  this.jsonImport = newJSON;
  jQuery('#mapster-import-json').val(JSON.stringify(this.jsonImport, null, 2))
  const pointRules = this.jsonImport.point.length
  const lineRules = this.jsonImport.line.length
  const polygonRules = this.jsonImport.polygon.length
  if((pointRules + lineRules + polygonRules) > 0) {
    jQuery('#geojson-import-details').html(`
      ${window.mapster_settings.strings["Import Details"]} ${pointRules + lineRules + polygonRules}.
    `).fadeIn();
  }
}

MWM_Settings_Importer.prototype.getPropertiesAndValues = function(type) {
  let allProperties = {};
  const theseTypeFeatures = this.currentFile.features.filter(feature => feature.geometry.type.toLowerCase().indexOf(type.toLowerCase()) > -1)
  theseTypeFeatures.forEach(feature => {
    for(let property in feature.properties) {
      if(!allProperties[property]) {
        allProperties[property] = [feature.properties[property]];
      } else {
        if(allProperties[property].indexOf(feature.properties[property]) === -1) {
          allProperties[property].push(feature.properties[property])
        }
      }
    }
  })
  return allProperties;
}

MWM_Settings_Importer.prototype.generateRuleHTML = function(type, conditionID) {
  const allProperties = this.getPropertiesAndValues(type);
  // Condition setting
  let theseSetValues = this.jsonImport[type].find(item => item.id === conditionID);
  let inputsHTMLs = `<p>${window.mapster_settings.strings["Rules Description"]}</p>`;
  inputsHTMLs += `<table><thead><th>Property</th><th>Value</th></thead>`;
  inputsHTMLs += `<tbody>`
  for(property in allProperties) {
    inputsHTMLs += `<tr class="mapster-condition" data-type="${type}">`
    inputsHTMLs += `<td><label>${property}</label><td>`
    inputsHTMLs += `<td><select data-property="${property}" value="${theseSetValues ? theseSetValues.rules.find(rule => rule.property === property).value : ""}">`
      inputsHTMLs += `<option value="all">${window.mapster_settings.strings["All"]}</option>`
      allProperties[property].forEach(value => {
        inputsHTMLs += `<option value="${value}">${value}</option>`
      })
    inputsHTMLs += `</select></td>`
    inputsHTMLs += `</tr>`
  }
  inputsHTMLs += `</tbody></table>`
  return inputsHTMLs;
}

MWM_Settings_Importer.prototype.generateTemplateHTML = function(type, conditionID) {
  let theseSetValues = this.jsonImport[type].find(item => item.id === conditionID);
  let htmlToReturn = `<p>${window.mapster_settings.strings["Template Description"]}</p>`;
  htmlToReturn += `<input type="text" class="mapster-template-selector" placeholder="${window.mapster_settings.strings["Enter ID"]}" value="${theseSetValues.template ? theseSetValues.template : ""}" />`
  htmlToReturn += '<hr />'
  return htmlToReturn;
}

MWM_Settings_Importer.prototype.generatePropertiesHTML = function(type, conditionID) {
  let theseSetValues = this.jsonImport[type].find(item => item.id === conditionID);
  let htmlToReturn = `<p>${window.mapster_settings.strings["Properties Description"]}</p>`;
  const allProperties = this.getPropertiesAndValues(type);
  const allCategories = jQuery('#geojson-import-category option');
  htmlToReturn += `
    <table>
      <thead>
        <th>${window.mapster_settings.strings["Table Field"]}</th>
        <th>${window.mapster_settings.strings["Table Value"]}</th>
        <th>${window.mapster_settings.strings["Table Input"]}</th>
      </thead>
      <tbody>
        <tr>
          <td>Post Title</td>
          <td><select class="mapster-title-select" value="${theseSetValues.title_property ? theseSetValues.title_property : ""}"><option value="">(${window.mapster_settings.strings["None"]})</option>`;
          for(let property in allProperties) {
            htmlToReturn += `<option value="${property}" ${theseSetValues.title_property && theseSetValues.title_property === property ? 'selected' : ""}>${property}</option>`
          }
        htmlToReturn += `</select></td>`;
        htmlToReturn += `<td><input type="text" class="mapster-title-input" placeholder="${window.mapster_settings.strings["Table Input"]}" value="${theseSetValues.title_direct ? theseSetValues.title_direct : ""}"></td>`;
        htmlToReturn += `</tr>`;
        htmlToReturn += `
        <tr>
          <td>Secondary Category</td>
          <td colspan="2"><select class="mapster-category-select" value="${theseSetValues.category_property ? theseSetValues.category_property : ""}">`;
          allCategories.each(function() {
            htmlToReturn += `<option value="${jQuery(this).val()}" ${theseSetValues.category_property && theseSetValues.category_property === jQuery(this).val() ? 'selected' : ""}>${jQuery(this).text()}</option>`
          })
        htmlToReturn += `</select></td>`;
        htmlToReturn += `</tr>`;
        // All other normal fields
        this.fetchedFields[type].forEach(field => {
          htmlToReturn += `<tr>`;
            htmlToReturn += `<td>${field.composite_name}<br /><span class="mapster-default">(${window.mapster_settings.strings["Default"]} ${field.default_value})</span></td>`;
            htmlToReturn += `<td><select class="mapster-property-select" data-name="${field.composite_name}" value="${theseSetValues.style_properties && theseSetValues.style_properties[field.composite_name] ? theseSetValues.style_properties[field.composite_name] : ""}"><option value="">(${window.mapster_settings.strings["None"]})</option>`;
            for(let property in allProperties) {
              htmlToReturn += `<option value="${property}" ${theseSetValues.style_properties && theseSetValues.style_properties[field.composite_name] && theseSetValues.style_properties[field.composite_name] === property ? 'selected' : ""}>${property}</option>`
            }
            htmlToReturn += `</select></td>`;
            htmlToReturn += `<td><input type="text" class="mapster-property-input" data-name="${field.composite_name}" placeholder="${window.mapster_settings.strings["Table Input"]}" value="${theseSetValues.style_values && theseSetValues.style_values[field.composite_name] ? theseSetValues.style_values[field.composite_name] : ""}"></td>`;
          htmlToReturn += `</tr>`;
        })
  htmlToReturn += `</tbody></table>`;
  return htmlToReturn;
}

MWM_Settings_Importer.prototype.generatePopupHTML = function(type, conditionID) {
  let theseSetValues = this.jsonImport[type].find(item => item.id === conditionID);
  let htmlToReturn = `<p>${window.mapster_settings.strings["Popup Description"]}</p>`;
  const allProperties = this.getPropertiesAndValues(type);
  htmlToReturn += `
    <table>
      <thead>
        <th>${window.mapster_settings.strings["Table Field"]}</th>
        <th>${window.mapster_settings.strings["Table Value"]}</th>
        <th>${window.mapster_settings.strings["Table Input"]}</th>
      </thead>
      <tbody>
  `
      this.fetchedFields['popup'].forEach(field => {
        htmlToReturn += `<tr>`;
          htmlToReturn += `<td>${field.composite_name}<br /><span class="mapster-default">(${window.mapster_settings.strings["Default"]} ${field.default_value})</span></td>`;
          htmlToReturn += `<td><select class="mapster-popup-select" data-name="${field.composite_name}" value="${theseSetValues.popup_properties && theseSetValues.popup_properties[field.composite_name] ? theseSetValues.popup_properties[field.composite_name] : ""}"><option value="">(${window.mapster_settings.strings["None"]})</option>`;
          for(let property in allProperties) {
            htmlToReturn += `<option value="${property}" ${theseSetValues.popup_properties && theseSetValues.popup_properties[field.composite_name] && theseSetValues.popup_properties[field.composite_name] === property  ? 'selected' : ""}>${property}</option>`
          }
          htmlToReturn += `</select></td>`;
          htmlToReturn += `<td><input type="text" class="mapster-popup-input" data-name="${field.composite_name}" placeholder="${window.mapster_settings.strings["Table Input"]}" value="${theseSetValues.popup_values && theseSetValues.popup_values[field.composite_name] ? theseSetValues.popup_values[field.composite_name] : ""}"></td>`;
        htmlToReturn += `</tr>`;
      })
  htmlToReturn += `</tbody></table>`;
  return htmlToReturn;
}

MWM_Settings_Importer.prototype.setHTMLFromJSON = function() {

  let that = this;

  ['point', 'line', 'polygon'].forEach(type => {

    jQuery(`#feature-type-${type} span`).html(`(${this.jsonImport[type].length})`);
    this.jsonImport[type].forEach(condition => {
      let parentElement = jQuery(document).find(`.mapster-collapse[data-id="${condition.id}"]`)
      if(parentElement.length === 0) {
        jQuery(`#feature-type-${type}-options .mapster-add-condition`).after(`
          <div class="mapster-collapse" data-id="${condition.id}">
            <div class="mapster-collapse-header">
              <div class="mapster-collapse-clicker">
                ${window.mapster_settings.strings["Condition"]} <span class="dashicons dashicons-arrow-left-alt2"></span>
              </div>
              <div class="mapster-collapse-remove">
                <span class="dashicons dashicons-trash"></span>
              </div>
            </div>
            <div class="mapster-collapse-body" data-type="${type}">
              <div class="mapster-select-buttons">
                <button class="button mapster-rules">${window.mapster_settings.strings["Button Rules"]}</button>
                <button class="button mapster-template">${window.mapster_settings.strings["Button Template"]}</button>
                <button class="button mapster-properties">${window.mapster_settings.strings["Button Properties"]}</button>
                <button class="button mapster-popup">${window.mapster_settings.strings["Button Popup"]}</button>
              </div>
              <div class="mapster-condition-container mapster-rules-container">
                ${this.generateRuleHTML(type, condition.id)}
              </div>
              <div class="mapster-condition-container mapster-template-container">
                ${this.generateTemplateHTML(type, condition.id)}
              </div>
              <div class="mapster-condition-container mapster-properties-container">
                ${this.generatePropertiesHTML(type, condition.id)}
              </div>
              <div class="mapster-condition-container mapster-popup-container">
                ${this.generatePopupHTML(type, condition.id)}
              </div>
            </div>
          </div>
        `)
      } else {
        condition.rules.forEach(rule => {
          jQuery(parentElement).find(`.mapster-condition select[data-property=${rule.property}]`).val(rule.value)
        })
      }
    })

    jQuery(`#feature-type-${type}-options .mapster-collapse`).each(function() {
      const thisID = jQuery(this).data('id');
      const idStillExists = that.jsonImport[type].filter(condition => condition.id === thisID);
      if(idStillExists.length === 0) {
        jQuery(this).remove();
      }
    })

  })

}

MWM_Settings_Importer.prototype.getDefaultRules = function(type) {
  const allProperties = this.getPropertiesAndValues(type);
  let rules = [];
  for(let property in allProperties) {
    rules.push({
      property : property,
      comparison : 'x',
      value : 'all'
    })
  }
  return rules;
}

MWM_Settings_Importer.prototype.generateRandomID = function() {
  return Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 12);
}

MWM_Settings_Importer.prototype.setJSONValue = function(element, propertyName, group) {
  const thisID = jQuery(element).closest('.mapster-collapse').data('id');
  const thisType = jQuery(element).closest('.mapster-collapse-body').data('type');
  const thisValue = jQuery(element).val();
  let newJSON = JSON.parse(JSON.stringify(this.jsonImport));
  const thisIndex = newJSON[thisType].findIndex(condition => condition.id === thisID);
  if(!group) {
    newJSON[thisType][thisIndex][propertyName] = thisValue;
  } else {
    if(!newJSON[thisType][thisIndex][group]) {
      newJSON[thisType][thisIndex][group] = {}
    }
    newJSON[thisType][thisIndex][group][propertyName] = thisValue;
  }
  this.saveJSON(newJSON);
  this.setHTMLFromJSON();
}

MWM_Settings_Importer.prototype.advancedOptions = function() {

  let that = this;

  fetch(window.mapster_settings.rest_url + "mapster-wp-maps/get-mapster-fields", {
    method : "GET",
    headers : {
      'X-WP-Nonce' : window.mapster_settings.nonce,
      'Content-Type' : 'application/json'
    },
  }).then(resp => resp.json()).then(response => {
    that.fetchedFields = response;
  });

  // Set JSON text
  this.saveJSON(this.jsonImport);
  this.setHTMLFromJSON();

  jQuery(document).on('keyup', '#mapster-import-json', function() {
    jQuery('#mapster-load-json').fadeIn();
  });

  jQuery(document).on('click', '#mapster-load-json', function() {
    let json = jQuery('#mapster-import-json').val();
    that.jsonImport = JSON.parse(json);
    that.setHTMLFromJSON();
    jQuery('#mapster-load-json').fadeOut();
  });

  ['point', 'line', 'polygon'].forEach(type => {
    jQuery(document).on('click', `#feature-type-${type}-options .mapster-add-condition`, function() {
      let newJSON = JSON.parse(JSON.stringify(that.jsonImport));
      newJSON[type].push({ id: that.generateRandomID(), rules : that.getDefaultRules(type) });
      that.saveJSON(newJSON);
      that.setHTMLFromJSON();
    });
  });

  // Change rule logic
  jQuery(document).on('change', '.mapster-condition select', function() {
    const thisID = jQuery(this).closest('.mapster-collapse').data('id');
    const thisType = jQuery(this).closest('.mapster-collapse-body').data('type');
    const thisProperty = jQuery(this).data('property');
    const thisValue = jQuery(this).val();
    let newJSON = JSON.parse(JSON.stringify(that.jsonImport));
    const thisIndex = newJSON[thisType].findIndex(condition => condition.id === thisID);
    const thisRuleIndex = newJSON[thisType][thisIndex].rules.findIndex(rule => rule.property === thisProperty);
    newJSON[thisType][thisIndex].rules[thisRuleIndex].value = jQuery(this).val();
    if(thisValue === 'all') {
      newJSON[thisType][thisIndex].rules[thisRuleIndex].comparison = 'x'
    } else {
      newJSON[thisType][thisIndex].rules[thisRuleIndex].comparison = '=='
    }
    that.saveJSON(newJSON);
    that.setHTMLFromJSON();
  });

  // Change template logic
  jQuery(document).on('keyup', '.mapster-template-selector', function() {
    that.setJSONValue(this, 'template')
  });

  jQuery(document).on('change', '.mapster-popup-select', function() {
    const propertyName = jQuery(this).data('name');
    that.setJSONValue(this, propertyName, 'popup_properties')
  });

  jQuery(document).on('keyup', '.mapster-popup-input', function() {
    const propertyName = jQuery(this).data('name');
    that.setJSONValue(this, propertyName, 'popup_values')
  });

  jQuery(document).on('change', '.mapster-title-select', function() {
    that.setJSONValue(this, 'title_property')
  });

  jQuery(document).on('change', '.mapster-category-select', function() {
    that.setJSONValue(this, 'category_property')
  });

  jQuery(document).on('keyup', '.mapster-title-input', function() {
    that.setJSONValue(this, 'title_direct')
  });

  jQuery(document).on('change', '.mapster-property-select', function() {
    const propertyName = jQuery(this).data('name');
    that.setJSONValue(this, propertyName, 'style_properties')
  });

  jQuery(document).on('keyup', '.mapster-property-input', function() {
    const propertyName = jQuery(this).data('name');
    that.setJSONValue(this, propertyName, 'style_values')
  });

  // Selection logic
  ['mapster-rules', 'mapster-template', 'mapster-properties', 'mapster-popup'].forEach(className => {
    jQuery(document).on('click', `.${className}`, function() {
      let closestCollapse = jQuery(this).closest('.mapster-collapse-body');
      let type = closestCollapse.data('type');
      closestCollapse.find(`.mapster-condition-container`).hide();
      closestCollapse.find(`.${className}-container`).show();
    })
  })

  // General interface setup
  jQuery(document).on('click', '.mapster-collapse-clicker', function() {
    if(jQuery(this).find('span').hasClass('dashicons-arrow-left-alt2')) {
      jQuery('.mapster-collapse-clicker').find('span').removeClass('dashicons-arrow-down-alt2')
      jQuery('.mapster-collapse-clicker').find('span').addClass('dashicons-arrow-left-alt2')
      jQuery('.mapster-collapse-body').hide();
      jQuery(this).find('span').removeClass('dashicons-arrow-left-alt2')
      jQuery(this).find('span').addClass('dashicons-arrow-down-alt2')
      jQuery(this).parent().next().toggle();
    } else {
      jQuery('.mapster-collapse-clicker').find('span').removeClass('dashicons-arrow-down-alt2')
      jQuery('.mapster-collapse-clicker').find('span').addClass('dashicons-arrow-left-alt2')
      jQuery('.mapster-collapse-body').hide();
    }
  })

  jQuery(document).on('click', '.mapster-collapse-remove', function() {
    const thisID = jQuery(this).closest('.mapster-collapse').data('id');
    let newJSON = JSON.parse(JSON.stringify(that.jsonImport));
    ['point', 'line', 'polygon'].forEach(type => {
      const thisIndex = newJSON[type].findIndex(condition => condition.id === thisID);
      newJSON[type].splice(thisIndex, 1);
    });
    that.saveJSON(newJSON);
    that.setHTMLFromJSON();
  });

  jQuery(document).on('click', '#mapster-download-json', function() {
    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(that.jsonImport));
    var dlAnchorElem = document.createElement('a');
    dlAnchorElem.setAttribute("href",     dataStr     );
    dlAnchorElem.setAttribute("download", "mapster-import.json");
    dlAnchorElem.click();
  })
}
