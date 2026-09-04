(function( $ ) {
  (async () => {
    // Creating global for devs
    window.mwm_v2 = {
      preloadedFns : [],
      onload: (fn) => window.mwm_v2.preloadedFns.push(fn)
    }
    // Load
    let mapster = false;
    if (window.mapster_params.is_dev === "true") {
      const base_url = "http://localhost:5173";
      if (window.mapster_params.map_provider === "maplibre" || window.mapster_params.map_provider === "custom-image") {
        mapster = await import(`${base_url}/src/index-maplibre.js`);
      }
      if (window.mapster_params.map_provider === "mapbox") {
        mapster = await import(`${base_url}/src/index-mapbox.js`);
      }
      if (window.mapster_params.map_provider === "google-maps") {
        mapster = await import(`${base_url}/src/index-google.js`);
      }
    } else {
      const base_url = window.mapster_params.sdk_base_url;
      const free = window.mapster_params.is_pro === "true" ? "pro" : "free";
      if (window.mapster_params.map_provider === "maplibre" || window.mapster_params.map_provider === "custom-image") {
        mapster = await import(`${base_url}/${free}/mapster-maplibre-${free}.js`);
      }
      if (window.mapster_params.map_provider === "mapbox") {
        mapster = await import(`${base_url}/${free}/mapster-mapbox-${free}.js`);
      }
      if (window.mapster_params.map_provider === "google-maps") {
        mapster = await import(`${base_url}/${free}/mapster-google-${free}.js`);
      }
    }
    // Public
    waitForElm('.mapster-wp-maps').then(() => {
       mapster.MapManager.registerAll(
         mapster.MapInstance,
         $('.mapster-wp-maps').toArray(),
         async (element) => {
           const post_id = element.id.replace('mapster-wp-maps-', '');
           const single_feature_id = $(`#${element.id}`).data('single_feature_id') !== "" ? $(`#${element.id}`).data('single_feature_id') : false;
           const feature_ids = $(`#${element.id}`).data('feature_ids') !== "" ? $(`#${element.id}`).data('feature_ids') : false;
           const singleFeatureQueryString = single_feature_id ? `&single_feature_id=${single_feature_id}` : '';
           const featuresQueryString = feature_ids ? `&feature_ids=${feature_ids}` : '';
           const data = await fetch(`${window.mapster_params.rest_url}mapster-wp-maps/map${window.mapster_params.qd}id=${post_id}&sdk=true${singleFeatureQueryString}${featuresQueryString}`).then(r => r.json());
           return {
             config: data.config,
             features: data.features,
             paid : window.mapster_params.is_pro === "true"
           };
         }
       );
     });
  })();


  function waitForElm(selector) {
      return new Promise(resolve => {
          if (document.querySelector(selector)) {
              return resolve(document.querySelector(selector));
          }

          const observer = new MutationObserver(mutations => {
              if (document.querySelector(selector)) {
                  resolve(document.querySelector(selector));
                  observer.disconnect();
              }
          });

          observer.observe(document.body, {
              childList: true,
              subtree: true
          });
      });
  }

})( jQuery );
