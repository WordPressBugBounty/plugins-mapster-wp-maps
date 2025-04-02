(function($) {

  const mapTypes = {
    satellite : {
      tiles : 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
      attribution : 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    },
    streets : {
      tiles : 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
      attribution : '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }
  }
  let mapType = window.mapster_gravity_params.mapster_map_type ? window.mapster_gravity_params.mapster_map_type : "satellite";

  // Form editing
  if(!window.mapster_gravity_params.entry_view) {

    let mapOptions = {
      container: 'mapster-form-map',
      style: {
        "version": 8,
        "glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
        "sources": {
          "raster-tiles": {
          "type": "raster",
          "tiles": [
              mapTypes[mapType].tiles
            ],
            "tileSize": 256,
            "attribution": mapTypes[mapType].attribution
          }
        },
        "layers": [
          {
          "id": "simple-tiles",
          "type": "raster",
          "source": "raster-tiles",
          "minzoom": 0,
          "maxzoom": 22
          }
        ]
      },
      center : [
        window.mapster_gravity_params.mapster_center_lon && !isNaN(window.mapster_gravity_params.mapster_center_lon) ? parseFloat(window.mapster_gravity_params.mapster_center_lon) : 0,
        window.mapster_gravity_params.mapster_center_lon && !isNaN(window.mapster_gravity_params.mapster_center_lat) ? parseFloat(window.mapster_gravity_params.mapster_center_lat) : 0
      ],
      zoom : window.mapster_gravity_params.mapster_center_zoom && !isNaN(window.mapster_gravity_params.mapster_center_zoom) ? parseFloat(window.mapster_gravity_params.mapster_center_zoom) : 2,
      maplibreLogo: false
    }

    const map = new maplibregl.Map(mapOptions);

    // Do something with the map
    map.on('load', () => {

      const allowedType = window.mapster_gravity_params.mapster_geo_type;
      let draw = new MapboxDraw({
        displayControlsDefault : false,
        controls : {
          point : window.mapster_gravity_params.mapster_geo_type === "point",
          line_string : window.mapster_gravity_params.mapster_geo_type === "line",
          polygon : window.mapster_gravity_params.mapster_geo_type === "polygon",
          trash : true,
          combine_features : false,
          uncombine_features : false
        }
      });
      map.on('draw.create', (e) => {
        if(e.features[0]) {
          const newFeature = e.features[0]
          draw.deleteAll()
          draw.add(newFeature)
          jQuery(`.mapster_geo_val`).val(JSON.stringify(newFeature));
        }
      })
      map.on('draw.update', (e) => {
        if(e.features[0]) {
          const newFeature = e.features[0]
          draw.deleteAll()
          draw.add(newFeature)
          jQuery(`.mapster_geo_val`).val(JSON.stringify(newFeature));
        }
      })
      map.on('draw.delete', (e) => {
        draw.deleteAll()
        jQuery(`.mapster_geo_val`).val("");
      })
      map.addControl(draw, 'top-left');

      setTimeout(() => {
        modifyButtonClasses();
      },100)

      // Events to load live changes into the map
      jQuery(document).on('change', '#mapster_map_latitude', function() {
        const currentMapCenter = map.getCenter();
        map.flyTo({
          center : [currentMapCenter.lng, parseFloat(jQuery(this).val())]
        })
      })
      jQuery(document).on('change', '#mapster_map_longitude', function() {
        const currentMapCenter = map.getCenter();
        map.flyTo({
          center : [parseFloat(jQuery(this).val()), currentMapCenter.lat]
        })
      })
      jQuery(document).on('change', '#mapster_map_zoom', function() {
        map.flyTo({
          zoom : parseFloat(jQuery(this).val())
        })
      })
      jQuery(document).on('change', '#mapster_map_type', function() {
        mapType = jQuery(this).val();
        map.setStyle({
          "version": 8,
          "glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
          "sources": {
            "raster-tiles": {
            "type": "raster",
            "tiles": [
                mapTypes[mapType].tiles
              ],
              "tileSize": 256,
              "attribution": mapTypes[mapType].attribution
            }
          },
          "layers": [
            {
            "id": "simple-tiles",
            "type": "raster",
            "source": "raster-tiles",
            "minzoom": 0,
            "maxzoom": 22
            }
          ]
        })
      })
      jQuery(document).on('change', '#mapster_map_geographic_type', function() {
        map.removeControl(draw);
        draw = new MapboxDraw({
          displayControlsDefault : false,
          controls : {
            point : jQuery(this).val() === "point",
            line_string : jQuery(this).val() === "line",
            polygon : jQuery(this).val() === "polygon",
            trash : true,
            combine_features : false,
            uncombine_features : false
          }
        });
        map.addControl(draw, 'top-left')

        setTimeout(() => {
          modifyButtonClasses();
        },100)
      })

      window.gmm = {
        addShape : (geoJSONFeature) => {
          addFeature(map, geoJSONFeature)
          setBounds(map, geoJSONFeature)
        },
        removeDraw : () => {
          map.removeControl(draw);
        }
      }
      document.dispatchEvent(new Event("gmm-ready"));

    })

    const modifyButtonClasses = () => {
      jQuery('.mapster-form-map').find('button').each(function() {
        if(!jQuery(this).hasClass('gform-theme__disable')) {
          jQuery(this).addClass('gform-theme__disable')
          // jQuery(this).addClass('mapster-map-button-force-style')
        }
      })
    }
  }

  // Form entry display
  if(window.mapster_gravity_params.entry_view) {

    const savedFeature = window.mapster_gravity_params.mapster_geo_val;
    let parsedFeature = {};

    parsedFeature = JSON.parse(savedFeature)

    let mapOptions = {
      container: 'mapster-entry-map',
      style: {
        "version": 8,
        "glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
        "sources": {
          "raster-tiles": {
          "type": "raster",
          "tiles": [
              mapTypes[mapType].tiles
            ],
            "tileSize": 256,
            "attribution": mapTypes[mapType].attribution
          }
        },
        "layers": [
          {
          "id": "simple-tiles",
          "type": "raster",
          "source": "raster-tiles",
          "minzoom": 0,
          "maxzoom": 22
          }
        ]
      },
      maplibreLogo: false,
      fitBoundsOptions : { padding : 20 }
    }
    const bounds = new maplibregl.LngLatBounds();
    if(parsedFeature.geometry.type === "Point") {
      mapOptions['center'] = parsedFeature.geometry.coordinates;
      mapOptions['zoom'] = 12;
    } else if(parsedFeature.geometry.type === "LineString") {
      parsedFeature.geometry.coordinates.forEach(coord => {
        bounds.extend(coord);
      })
      mapOptions['bounds'] = bounds;
    } else if(parsedFeature.geometry.type === "Polygon") {
      parsedFeature.geometry.coordinates[0].forEach(coord => {
        bounds.extend(coord);
      })
      mapOptions['bounds'] = bounds;
    }

    const map = new maplibregl.Map(mapOptions);

    map.on('load', () => {
      addFeature(map, parsedFeature)

      window.gmm = {
        addShape : (geoJSONFeature) => {
          addFeature(map, geoJSONFeature)
          setBounds(map, geoJSONFeature)
        }
      }
      document.dispatchEvent(new Event("gmm-ready"));

    })

  }

  const setBounds = (map, feature) => {
    const bounds = new maplibregl.LngLatBounds();
    if(feature.geometry.type === "Point") {
      map.flyTo({
        center : feature.geometry.coordinates,
        zoom : 12
      });
    } else if(feature.geometry.type === "LineString") {
      feature.geometry.coordinates.forEach(coord => {
        bounds.extend(coord);
      })
      map.fitBounds(bounds, { duration : 0 });
    } else if(feature.geometry.type === "Polygon") {
      feature.geometry.coordinates[0].forEach(coord => {
        bounds.extend(coord);
      })
      map.fitBounds(bounds, { duration : 0 });
    }
  }

  const addFeature = (map, feature) => {
    if(map.getSource('feature')) {
      map.getSource('feature').setData({ type : "FeatureCollection", features : [ feature ]})
    } else {
      map.addSource("feature", {
        type : "geojson",
        data : { type : "FeatureCollection", features : [ feature ]}
      })
    }
    if(map.getLayer('feature')) {
      map.removeLayer('feature')
    }
    if(feature.geometry.type === "Point") {
      map.addLayer({
        id : "feature",
        type : "circle",
        source : "feature"
      })
    } else if(feature.geometry.type === "LineString") {
      map.addLayer({
        id : "feature",
        type : "line",
        source : "feature"
      })
    } else if(feature.geometry.type === "Polygon") {
      map.addLayer({
        id : "feature",
        type : "fill",
        source : "feature",
        paint : {
          'fill-opacity' : 0.6
        }
      })
    }
  }

})(jQuery);
