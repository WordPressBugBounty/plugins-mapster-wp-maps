;return t}(),r=function(){let t=2;
;return t}();if(t(".mapster-map-submission-frontend").length){n=t(".mapster-front-submission-options").data("center");r=t(".mapster-front-submission-options").data("zoom")}let i={container:e,style:{version:8,glyphs:"https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",sources:{"raster-tiles":{type:"raster",tiles:["https://a.tile.openstreetmap.org/{z}/{x}/{y}.png"],tileSize:256,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}},layers:[{id:"simple-tiles",type:"raster",source:"raster-tiles",minzoom:0,maxzoom:22}]},center:n,zoom:r}
let o=new maplibregl.Map(i);o.on("load",(()=>{o.resize();L();if(C().features.length>0){var t=turf.bbox(C());o.fitBounds(t,{padding:20,maxZoom:13,duration:0})}}));return o}function a(){if("location"===T()){"circle"===_()&&n.addLayer({id:"feature",type:"circle",source:"feature"});if("label"===_()||"marker"===_()){n.addLayer({id:"feature",type:"symbol",source:"feature"});"marker"===_()&&u()}}"line"===T()&&n.addLayer({id:"feature",type:"line",source:"feature"});"polygon"===T()&&"fill"===x()&&n.addLayer({id:"feature",type:"fill",source:"feature"});"polygon"===T()&&"fill-extrusion"===x()&&n.addLayer({id:"feature",type:"fill-extrusion",source:"feature"});if("polygon"===T()&&"fill-image"===x()){n.addLayer({id:"feature",type:"fill",source:"feature"});l()}f()}function s(){r&&r.remove()}function u(){if("location"===T()&&"marker"===_()){s();if(C().features.length>0){var t=b().marker,e=new maplibregl.Marker(t).setLngLat(C().features[0].geometry.coordinates);e.addTo(n);r=e}}}function l(){
}function c(){let t=turf.bbox(C());return[[t[0],t[3]],[t[2],t[3]],[t[2],t[1]],[t[0],t[1]]]}function h(){t('.acf-field[data-name="polygon_style"]').length&&t(document).on("change",'.acf-field[data-name="polygon_style"] :input',(function(){!function(){n.getLayer("feature")&&n.removeLayer("feature");a();d()}();f()}))}function p(){t('.acf-field[data-name="location_style"]').length&&t(document).on("change",'.acf-field[data-name="location_style"] :input',(function(){!function(){s();n.getLayer("feature")&&n.removeLayer("feature");a();d()}();f()}))}function f(){var t=b();if("location"===T()&&"marker"===_())u();else if("location"===T()&&"marker"!==_()&&"3d-model"!==_()||"line"===T()||"polygon"===T()){for(var e in t.paint)n.setPaintProperty("feature",`${w()}${e}`,t.paint[e]);for(var e in t.layout)n.setLayoutProperty("feature",`${w()}${e}`,t.layout[e])}else"location"===T()&&"3d-model"===_()&&E();"polygon"===T()&&"fill-image"===x()&&l()}function d(){var e=b();for(var n in e)for(var r in e[n])t(document).on("change",`.acf-field[data-name="${r}"] :input`,(function(){f()}));if("line"===T()){["dashed_line","dash_length","gap_length"].forEach((e=>{t(document).on("change",`.acf-field[data-name="${e}"] :input`,(function(){f()}))}))}if("location"===T()&&"label"===_()){["label_on","icon_on","icon-image","icon-translate-x","icon-translate-y","text-translate-x","text-translate-y","icon-static-size"].forEach((e=>{t(document).on("change",`.acf-field[data-name="${e}"] :input`,(function(){f()}))}))}
}function m(){let e=!1;t(document).on("click",".open-multigeom",(function(){t(".open-multigeom-a").trigger("click");if(!e){"line"===T()?t("#draw-single-line").show():"polygon"===T()&&t("#draw-single-polygon").show();!function(e){
}(o(S().attr("id")+"-multi"));e=!0}}))}function g(r){e=r;!function(){if(t(".mapster-extra-form-fields").length){t(".mapster-extra-form-fields").find(".acf-field-mapster-map").remove();t(".mapster-extra-form-fields #acf-form-data").remove();let e=t(".acf-field-mapster-map").closest(".acf-postbox");t(".acf-field-mapster-map .mapster-extra-form-fields").appendTo(e)}}();n=o(S().attr("id"));!function(){jQuery(document).on("click",".mapster-edit-full-width",(()=>{if("flex"===jQuery(".mapster-map-container").css("display")){jQuery(".mapster-map-container").css("display","block");jQuery(".mapster-map-container > div").css("width","100%");jQuery(".mapster-map-container > div").css("marginBottom","15px");jQuery(".mapster-edit-full-width").text("Collapse Map");n.resize()}else{jQuery(".mapster-map-container").css("display","flex");jQuery(".mapster-map-container > div").css("width","50%");jQuery(".mapster-map-container > div").css("marginBottom","0px");jQuery(".mapster-edit-full-width").text("Expand Map");n.resize()}}));let t=!1;jQuery(document).on("click",".mapster-simplify-shape",(()=>{if(jQuery(".mapster-simplify-shape-slider").length){jQuery(".mapster-simplify-shape").html("Simplify Shape");jQuery(".mapster-simplify-container").remove();y(t)}else{jQuery(".mapster-simplify-shape").html("Cancel Simplify Shape");jQuery(".mapster-map-container > div:first-child").append('\n\t\t\t\t\t<div class="mapster-simplify-container" style="margin-top: 30px; text-align: right;">\n\t\t\t\t\t\t<input class="mapster-simplify-shape-slider" type="range" min="1" max="100" value="1" />\n\t\t\t\t\t</div>\n\t\t\t\t');t=C()}}));jQuery(document).on("input",".mapster-simplify-shape-slider",(function(){let e=jQuery(this).val();y(turf.simplify(t,{tolerance:parseInt(e)/100}))}));jQuery(document).on("click",".mapster-download-geojson",(()=>{var t=document.createElement("a"),e=new Blob(["\ufeff",JSON.stringify(C())]),n=URL.createObjectURL(e);t.href=n;t.download="geojson-download.json";document.getElementById("acf-form-data").appendChild(t);t.click();document.getElementById("acf-form-data").removeChild(t)}));jQuery(document).on("click",".mapster-image-base",(()=>{
}))}();!function(){var e=I();let r={displayControlsDefault:!1,userProperties:!0,controls:{point:e.pointAllowed,line_string:e.lineStringAllowed,polygon:e.polygonAllowed,trash:!0}};if(e.lineStringAllowed){r.modes={...MapboxDraw.modes,simple_select:window.bezier.SimpleSelectModeBezierOverride,direct_select:window.bezier.DirectModeBezierOverride,draw_bezier_curve:window.bezier.DrawBezierCurve};r.styles=window.bezier.customStyles}var o=new MapboxDraw(r);if(e.lineStringAllowed){const e={on:"click",action:e=>{e.preventDefault();t(".mapster-map-instructions").html("Alt + drag to draw bezier lines.");t(".mapster-map-instructions").fadeIn();o.changeMode("draw_bezier_curve")},classes:["bezier-curve-icon"],title:"Bezier tool"},r=new i({draw:o,buttons:[e]});n.addControl(r,"top-left")}else n.addControl(o);n.on("draw.create",(e=>{var r=C();r.features=[e.features[0]];n.setLayoutProperty("feature","visibility","visible");t("#finish-drawing div").fadeOut();o.deleteAll();y(r)}));n.on("draw.delete",(t=>{var e=C();e.features=[];n.setLayoutProperty("feature","visibility","visible");y(e)}));n.on("draw.update",(t=>{var e=C();e.features=[t.features[0]];y(e)}));n.on("draw.modechange",(e=>{t(".mapster-map-instructions").fadeOut(100,(function(){t(".mapster-map-instructions").html("")}))}));n.on("load",(()=>{t(document).on("click","#draw-point",(()=>{n.setLayoutProperty("feature","visibility","none");t("#finish-drawing div").fadeIn();o.changeMode("draw_point")}));t(document).on("click","#draw-linestring",(()=>{n.setLayoutProperty("feature","visibility","none");t("#finish-drawing div").fadeIn();o.changeMode("draw_line_string")}));t(document).on("click","#edit-linestring",(()=>{if(C().features[0]){n.setLayoutProperty("feature","visibility","none");t("#finish-drawing div").fadeIn();var e=o.add(C());o.changeMode("direct_select",{featureId:e[0]})}}));t(document).on("click","#draw-polygon",(()=>{n.setLayoutProperty("feature","visibility","none");o.changeMode("draw_polygon")}));t(document).on("click","#edit-polygon",(()=>{if(C().features[0]){n.setLayoutProperty("feature","visibility","none");t("#finish-drawing div").fadeIn();var e=o.add(C());o.changeMode("direct_select",{featureId:e[0]})}}));t(document).on("click","#finish-drawing div",(()=>{var e=C(),r=o.getAll(),i=e.features.findIndex((t=>t.id===r.features[0].id));i>-1&&(e.features[i]=r.features[0]);n.setLayoutProperty("feature","visibility","visible");t("#finish-drawing div").fadeOut();o.deleteAll();y(e)}));t(document).on("click","#draw-delete",(()=>{o.deleteAll();n.setLayoutProperty("feature","visibility","none");y({type:"FeatureCollection",features:[]})}))}))}();!function(){"location"===T()&&A();b();n.on("load",(()=>{n.addSource("feature",{type:"geojson",data:C()});a()}))}();d();!function(){if(t("#mapster-map-geosearch").length){const e=new GeoSearch.OpenStreetMapProvider;t(document).on("click","#mapster-get-results",(async function(){var n=t("#mapster-map-geosearch").val();if(n.length>2){const o=await e.search({query:n});var i=r(o);t("#mapster-geocoder-results").empty().append(i)}else t("#mapster-geocoder-results").empty()}));t(document).on("keyup","#mapster-map-geosearch",(function(){t("#mapster-map-geosearch").val().length<=2&&t("#mapster-geocoder-results").empty()}));t(document).on("click","#mapster-geocoder-results li",(function(){var e=t(this).data("bounds");y({type:"FeatureCollection",features:[{type:"Feature",properties:{},geometry:{type:"Point",coordinates:t(this).data("center")}}]});n.fitBounds(e.map((t=>t.slice().reverse())),{padding:20});t("#mapster-geocoder-results").empty()}));const r=t=>{var e="";t.slice(0,5).forEach((t=>{e+=`<li data-center="${JSON.stringify([t.x,t.y])}" data-bounds="${JSON.stringify(t.bounds)}">${t.label}</li>`}));return e}}if(t("#mapster-geocoder-mapbox").length){const e=t("#mapster-geocoder-mapbox").data("access_token"),r=new MapboxGeocoder({accessToken:e,mapboxgl:maplibregl,marker:!1});r.on("result",(t=>{y({type:"FeatureCollection",features:[{type:"Feature",properties:{},geometry:{type:"Point",coordinates:t.result.center}}]})}));document.getElementById("mapster-geocoder-mapbox").appendChild(r.onAdd(n))}}();m();"line"!==T()&&"polygon"!==T()||function(){t("#mapster-map-upload").change((function(t){e(t)}));const e=async t=>{const e=await new Response(t.target.files[0]).json();n(e)},n=e=>{if(e){const r=geojsonhint.hint(e);if(0===r.length){if(n=e.features.find((e=>t("#mapster-map-upload").data("type").indexOf(e.geometry.type)>-1))){if(window.confirm("Are you sure you want to upload this geoJSON? It will overwrite all existing features.")){n.id||(n.id=parseInt(Math.random()*Math.random()*1e7));y({type:"FeatureCollection",features:[n]})}}else window.alert("Please double-check that your geoJSON has the right geometry type for this post.")}else{var n;if(n=e.features.find((e=>t("#mapster-map-upload").data("type").indexOf(e.geometry.type)>-1))){if(window.confirm("There was an error with your upload: "+JSON.stringify(r)+". Are you sure you want to upload this geoJSON? It will overwrite all existing features.")){n.id||(n.id=parseInt(Math.random()*Math.random()*1e7));y({type:"FeatureCollection",features:[n]})}}else window.alert("GeoJSON error: "+JSON.stringify(r))}}else window.alert("Please upload a file.")}}();if("location"===T()){p();t(document).on("click","#set-manual-point",(function(){let e=t("#mapster-map-point-longitude").val(),r=t("#mapster-map-point-latitude").val();y({type:"FeatureCollection",features:[{type:"Feature",properties:{},geometry:{type:"Point",coordinates:[parseFloat(e),parseFloat(r)]}}]});n.jumpTo({center:[parseFloat(e),parseFloat(r)]})}))}"polygon"===T()&&h()}function y(e){!function(t){t.features.forEach((t=>{t.geometry.coordinates=t.geometry.coordinates.map(v)}))}(e);!function(e){t(`#mapster-map-geojson-${M()}`).attr("value",JSON.stringify(e))}(e);n.getSource("feature").setData(e);u();if("location"===T()){A();"3d-model"===_()&&E()}"polygon"===T()&&"fill-image"===x()&&l()}function v(t){return isNaN(t)?t.map(v):Math.round(1e8*t)/1e8}function _(){return t(".mapster-map-submission-frontend").length?"marker":t('.acf-field[data-name="location_style"]').find("select").val()}function x(){return t('.acf-field[data-name="polygon_style"]').find("select").val()}function b(){if("location"===T()&&"circle"===_()){var e=t('.acf-field[data-name="circle"] .acf-field[data-name="color"]').find(":input").val(),r=t('.acf-field[data-name="circle"] .acf-field[data-name="opacity"]').find(":input").val(),i=t('.acf-field[data-name="circle"] .acf-field[data-name="radius"]').find(":input").val(),o=t('.acf-field[data-name="circle"] .acf-field[data-name="stroke-width"]').find(":input").val(),a=t('.acf-field[data-name="circle"] .acf-field[data-name="stroke-color"]').find(":input").val(),s=t('.acf-field[data-name="circle"] .acf-field[data-name="stroke-opacity"]').find(":input").val();return{paint:{color:""!==e?e:"#000",opacity:""!==r?parseFloat(r)/100:1,radius:""!==i?parseFloat(i):5,"stroke-width":""!==o?parseFloat(o):0,"stroke-color":""!==a?a:"#FFF","stroke-opacity":""!==s?parseFloat(s)/100:1}}}if("location"===T()&&"marker"===_()){e=t('.acf-field[data-name="marker"] .acf-field[data-name="color"]').find(":input").val();var u=t('.acf-field[data-name="marker"] .acf-field[data-name="scale"]').find(":input").val(),l=t('.acf-field[data-name="marker"] .acf-field[data-name="rotation"]').find(":input").val(),c=t('.acf-field[data-name="marker"] .acf-field[data-name="anchor"]').find(":input").val();return{marker:{color:""!==e?e:"#000",scale:""!==u?parseFloat(u)/100:1,rotation:""!==l?parseFloat(l):0,anchor:""!==c?c:"center"}}}if("location"===T()&&"label"===_()){var h=t('.acf-field[data-name="label"] .acf-field[data-name="label_on"]').find(":input").is(":checked"),p=t('.acf-field[data-name="label"] .acf-field[data-name="text-field"]').find(":input").val(),f=t('.acf-field[data-name="label"] .acf-field[data-name="text-font"]').find(":input").val(),d=t('.acf-field[data-name="label"] .acf-field[data-name="text-size"]').find(":input").val(),m=t('.acf-field[data-name="label"] .acf-field[data-name="text-color"]').find(":input").val(),g=t('.acf-field[data-name="label"] .acf-field[data-name="text-opacity"]').find(":input").val(),y=t('.acf-field[data-name="label"] .acf-field[data-name="text-rotate"]').find(":input").val(),v=t('.acf-field[data-name="label"] .acf-field[data-name="text-translate-x"]').find(":input").val(),b=t('.acf-field[data-name="label"] .acf-field[data-name="text-translate-y"]').find(":input").val(),w=t('.acf-field[data-name="label"] .acf-field[data-name="text-halo-width"]').find(":input").val(),E=t('.acf-field[data-name="label"] .acf-field[data-name="text-halo-color"]').find(":input").val(),S=t('.acf-field[data-name="label"] .acf-field[data-name="text-halo-blur"]').find(":input").val(),M=t('.acf-field[data-name="icon"] .acf-field[data-name="icon_on"]').find(":input").is(":checked"),C=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-image"] img').attr("src"),I=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-size"]').find(":input").val(),A=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-opacity"]').find(":input").val(),L=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-rotate"]').find(":input").val(),P=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-translate-x"]').find(":input").val(),R=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-translate-y"]').find(":input").val(),N=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-anchor"]').find(":input").val();""!==C&&function(t,e){t||e();"https:"===window.location.protocol&&t.indexOf("http://")>-1&&(t=t.replace("http","https"));n.loadImage(t,((t,r)=>{if(n.loaded()){n.hasImage("icon-image-location")?n.updateImage("icon-image-location",r):n.addImage("icon-image-location",r);e()}else n.once("idle",(()=>{n.hasImage("icon-image-location")?n.updateImage("icon-image-location",r):n.addImage("icon-image-location",r);e()}))}))}(C,(()=>{n.setLayoutProperty("feature","icon-image","icon-image-location")}));I=t('.acf-field[data-name="icon"] .acf-field[data-name="icon-static-size"]').find(":input").is(":checked")?["interpolate",["exponential",2],["zoom"],1,I*Math.pow(2,-15),22,I*Math.pow(2,6)]:""!==I?parseFloat(I)/100:1;const e={layout:{"text-field":""!==p?p:"","text-font":[f],"text-rotate":""!==y?parseFloat(y):0,"text-size":""!==d?parseFloat(d):16,"icon-size":I,"icon-rotate":""!==L?parseFloat(L):0,"icon-anchor":""!==N?N:"center","icon-offset":""!==P&&""!==R?[parseFloat(P),parseFloat(R)]:[0,0],"text-offset":""!==v&&""!==b?[parseFloat(v),parseFloat(b)]:[0,0]},paint:{"text-color":""!==m?m:"#000000","text-halo-width":""!==w?parseFloat(w):1,"text-halo-color":""!==E?E:"#FFFFFF","text-halo-blur":""!==S?parseFloat(S)/100:.5,"text-opacity":""!==g?parseFloat(g)/100:1,"icon-opacity":""!==A?parseFloat(A)/100:1}};h||(e.layout["text-size"]=0);M||(e.layout["icon-size"]=0);return e}if("line"===T()){e=t('.acf-field[data-name="color"]').find(":input").val(),r=t('.acf-field[data-name="opacity"]').find(":input").val();var O=t('.acf-field[data-name="width"]').find(":input").val(),D=t('.acf-field[data-name="dashed_line"]').find(":input").is(":checked"),k=t('.acf-field[data-name="dash_length"]').find(":input").val(),F=t('.acf-field[data-name="gap_length"]').find(":input").val();const n={paint:{color:e&&""!==e?e:"#000",opacity:r&&""!==r?parseFloat(r)/100:1,width:O&&""!==O?parseFloat(O):5}};n.paint.dasharray=D?""!==k&&""!==F?[parseFloat(k),parseFloat(F)]:[1,1]:[1,0];return n}if("polygon"===T()&&"fill"===x()){e=t('.acf-field[data-name="color"]').find("input").val(),r=t('.acf-field[data-name="opacity"]').find("input").val();var z=t('.acf-field[data-name="outline-color"]').find("input").val();return{paint:{color:e&&""!==e?e:"#000",opacity:r&&""!==r?parseFloat(r)/100:1,"outline-color":z&&""!==z?z:"rgba(0, 0, 0, 0)"}}}if("polygon"===T()&&"fill-extrusion"===x()){e=t('.acf-field[data-name="color"]').find("input").val(),r=t('.acf-field[data-name="opacity"]').find("input").val();var B=t('.acf-field[data-name="base"]').find("input").val(),U=t('.acf-field[data-name="height"]').find("input").val();return{paint:{color:e&&""!==e?e:"#000",opacity:r&&""!==r?parseFloat(r)/100:1,base:B&&""!==B?parseFloat(B):0,height:U&&""!==U?parseFloat(U):0}}}if("polygon"===T()&&"fill-image"===x()){return{paint:{color:"#000",opacity:.2,"outline-color":"#000000"}}}}function w(){return"location"===T()&&"circle"===_()?"circle-":"location"===T()&&"label"===_()?"":"line"===T()?"line-":"polygon"===T()&&"fill"===x()?"fill-":"polygon"===T()&&"fill-extrusion"===x()?"fill-extrusion-":"polygon"===T()&&"fill-image"===x()?"fill-":void 0}function E(){
}function S(){return t(e).find(".mapster-map")}function M(){return S().attr("id").replace("mapster-map-","")}function T(){if(t(".mapster-map-submission-frontend").length){let e=1===t(".mapster-submission-map").data("linestring")?"line":"";e=1===t(".mapster-submission-map").data("point")?"location":e;e=1===t(".mapster-submission-map").data("polygon")?"polygon":e;return e}{let t=S();if(1===t.data("point"))return"location";if(1===t.data("linestring"))return"line";if(1===t.data("polygon"))return"polygon"}}function C(){var e=t(`#mapster-map-geojson-${M()}`).val();if(e&&""!==e){return JSON.parse(e)}return{type:"FeatureCollection",features:[]}}function I(){return{pointAllowed:1===S().data("point"),lineStringAllowed:1===S().data("linestring"),polygonAllowed:1===S().data("polygon")}}function A(){if(C().features[0]){var e=C().features[0].geometry.coordinates;t("#mapster-map-point-longitude").val(e[0]);t("#mapster-map-point-latitude").val(e[1])}else{t("#mapster-map-point-longitude").val("");t("#mapster-map-point-latitude").val("")}}function L(){
}if(void 0!==acf.add_action){acf.add_action("ready_field/type=mapster-map",g);acf.add_action("append_field/type=mapster-map",g);acf.add_action("show_field/type=mapster-map",g)}else t(document).on("acf/setup_fields",(function(e,n){t(n).find('.field[data-field_type="mapster-map"]').each((function(){g(t(this))}))}))}(jQuery);