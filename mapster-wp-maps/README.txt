=== Mapster WP Maps — Mapbox, MapLibre & Google Maps for WordPress ===
Contributors: tempranova, mapster
Donate link: https://wpmaps.mapster.me/
Tags: mapbox, maplibre, google maps, store locator, geojson
Requires at least: 5.0.0
Tested up to: 7.1
Stable tag: 2.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The friendly way to build real Mapbox, MapLibre, or Google maps in WordPress — store locators, GeoJSON data, custom popups, and more. No API key needed to start.

== Description ==

Mapster WP Maps is a map editor and builder for WordPress — built for people who want more than a pin on a map, without needing to write code to get it.

We integrate **MapLibre, Mapbox, and Google Maps**, and you pick which one powers your site. Prefer not to deal with API keys at all? MapLibre works out of the box, no account or token required.

* [Main site](https://wpmaps.mapster.me/)
* [Example site](https://wpmaps.mapster.me/examples/)
* [Documentation](https://wpmaps-docs.mapster.me/) (with video guides!)

Full feature list below.

💽 Developer? You can extend this plugin with custom code via JS hooks and events — see the Developer section.

Happy mapping!

== 🌟 Free Version ==

Grab the free version from this page — we built it to genuinely cover most sites, not just to tease you into Pro. If you hit a wall, [Pro](https://wpmaps.mapster.me/pro) is there.

= 🗺️ Map Features =

The actual map-building core — pick an engine, pick a look, and get real control over how it behaves, not just where the pin drops.

* Choose between Maplibre (no API key required), Mapbox, and Google Maps
* Live map editor and preview
* Add unlimited points, polygons, and lines — with clustering for when you've got a lot of them
* 20 default map styles, or bring your own custom style from Mapbox or any tile provider
* Mapbox Standard with 3D features and environment, plus 3D terrain
* Shortcodes and Block editor integrations to place your maps anywhere
* Variety of map projections available
* Custom size ranging from specific pixel sizes to full-screen maps
* Set custom center, zoom, tilt and rotation of map
* Have the map automatically fit to your features
* Control how (or whether) users can interact with the map
* Move the map to the user's location on load

= 🕹️ Map Controls =

What makes a map usable once it holds more than a handful of pins — search, filter, and navigate real data.

* Category filter — show/hide specific categories of features, with nested AND/OR logic
* Clickable, filterable dropdown list of features on the map
* Geocoder (search by address)
* All controls can be positioned around the map as desired
* Custom search through features on the map (by text)
* Basic list
* 3D switcher
* Directions (origin and destination)
* Geolocation (track the user's position)
* Fullscreen mode
* Zoom control, scale control
* Modify logo and attribution

= 📍 Geographic Features =

Every feature on the map — point, line, polygon — gets the same level of styling control, not a stripped-down subset.

* Create features by drawing, entering an address, or uploading data
* Custom markers, icons, and text, with full control over color, size, rotation, and opacity
* Reuse geographic features across multiple maps
* Unlimited points, labels, text, markers, circles, lines, bezier curves, polygons
* Customize color, borders, opacity, line thickness
* Circle radius, opacity, color, border width and color
* Custom text labels with font choice, positioning, font size, color
* Click or hover interaction on any feature
* Open popups on click, hover, or map load

= 💬 Popups =

Popups are where most "embed a map" plugins stop. This is where Mapster starts.

* Build your own from scratch with the Popup Builder, with live preview as you go
* Add custom CSS for full control of popup styling
* Trigger popups on click, hover, or automatically on map load
* Default popup styles if you'd rather not build one
* Optional header, description, thumbnail image, and button footer
* Set colors and text per popup
* Center and/or zoom the map on popup open
* Close popups on map click
* Link popups to directions, custom URLs, post content, or a modal

= 📥 Import =

Bring your data in however it already exists.

* Import GeoJSON, KML, GPX, and Shapefiles
* All metadata retained as custom fields on import
* Up to 100 features per file (depending on coordinate complexity)
* Import and export settings between installs

= 🖳 Developer =

Real JS events to hook into, not a black box.

* Use [JS hooks](https://wpmaps-docs.mapster.me/for-developers/javascript-hooks) to run code on specific map events
* Add custom scripts with direct access to the `map` variable
* Shortcodes for outputting specific features by ID
* Custom permalinks for geographies and maps

== 🌟🌟 Pro Version ==

Free covers most sites. Pro is for when "most sites" isn't you — thousands of imported locations, a multi-location storefront, crowdsourced submissions, or maps wired directly into ACF and Gravity Forms. It removes the caps rather than selling you a different product. [Pro](https://wpmaps.mapster.me/pro) is built for that, and we're genuinely reachable if you need a hand or a new feature.

> "I have a very demanding and very specific need with literally thousands of locations, lines and areas that needs to be added, with custom pins, and there is simply nothing else that does it so well as this one, even in the free one." — Matt, [WordPress.org review](https://wordpress.org/support/topic/by-far-the-most-comprehensive-map-plugin-i-have-tried/)

= 🗺️ Map Features =

More control over how the map behaves once it's carrying real weight.

* Advanced clustering — control size, color, and behavior of clusters, or replace them with your own images
* Pull in custom post type data via the Mapster ACF field
* 16 additional map styles (36 total)
* 3D globe view and 3D buildings
* Custom images as the map base, including scalable SVGs
* Restrict map movement to a defined boundary
* Custom map sizes per screen width (mobile breakpoints)
* Set map language
* Cooperative gestures (avoid accidental scroll-jacking)
* Customize the map loading graphic
* Geolocate by IP address

= 🕹️ Map Controls =

The control set for real multi-location sites — a proper store locator, not just a longer list.

* Store Locator list, with feature lists ordered and grouped by category
* Inclusive/exclusive filtering, initial visibility, and filters on custom fields
* High-accuracy geolocation — heading, live tracking, accuracy circle
* Download control — let users export the map's geographic data
* Print control — download the map as an image
* Style switcher between Mapbox or Maplibre style JSONs
* Layer switcher for your Mapbox style's layers
* Custom HTML control for your own interactivity
* Search by latitude/longitude in the geocoder
* Images in clickable feature lists
* Manually order how controls and categories appear
* Collapsible container to show/hide your controls

= 📍 Geographic Features =

For when a flat icon isn't enough, or you've got hundreds of features to manage at once.

* Mass-edit hundreds of features' styles at once
* 3D models (GLB) with custom size and rotation
* MultiLineStrings and MultiPolygons
* Hover styles for features
* Images layered on top of polygons, or clipped to drawn polygon shapes
* Static icon sizes at real-world scale

= 💬 Popups =

Pull real data in, or throw out the template entirely.

* Pull custom field data (ACF or built-in) into popups
* Replace popups entirely with custom HTML
* Render shortcodes inside popups
* Button to open a modal with more detail, or open directly to one on marker click

= 💾 Big Data =

No ceiling on how much data your map can hold, and it still loads fast.

* No limit on import size
* Caching for fast map loads
* Spatial API access, built from your own data
* Import directly from Google Sheets
* Automatic upload to the Mapbox Tiling Service for large datasets

= 🌎 Specialty Maps =

Purpose-built map types for the two requests we hear most: crowdsourcing and directories.

* Crowdsourced maps — let users submit their own points
* Listing pages connecting custom posts to geographic data
* Heatmaps for large datasets
* Compare two maps side by side
* Elevation detail along a route

= 🖳 Developer =

Location data flows in from the tools you already use.

* Advanced Custom Fields (ACF) field type for maps
* Gravity Forms map field

Love,

Your Friends at [Mapster](https://mapster.me)

== Installation ==

1. Upload `mapster-wp-maps.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on "Maps" to start building your maps.
4. Add an Access Token if you'd like custom Mapbox styles or the geocoder.
5. Use the provided shortcodes (or the block editor) to place your maps in posts or pages.

== Frequently Asked Questions ==

= Do I need a Mapbox or Google API key to use this? =

No. MapLibre works completely free with no API key or account — it's the easiest way to get started. Mapbox and Google Maps are there if you want their specific styles or features, and each needs its own key from that provider.

= Does this work with Elementor, Divi, Bricks, or other page builders? =

Yes — Mapster outputs a standard shortcode or block, so it drops into Elementor, Divi, Bricks, the Classic Editor, or Gutenberg like any other embeddable content.

= Can I import GeoJSON, KML, GPX, or Shapefiles? =

Yes, all four are supported in the free version, with metadata preserved as custom fields on import. Pro removes the size limit and adds CSV and Google Sheets import, including scheduled recurring imports.

= How is this different from a basic Google Maps embed plugin? =

Most WordPress map plugins give you a pin on an embed. Mapster is closer to a real map editor — styling, clustering, popups, filters, data import, and three different rendering engines (Mapbox, MapLibre, Google Maps) under one interface. If you just need "here's our office," this might be more than you need. If you need a map that actually does something, that's what it's built for.

= Will this slow down my site? =

The map library loads only on pages where you've placed a map. Pro adds map caching specifically to keep larger datasets (hundreds or thousands of features) loading quickly.

= Is there a store locator? =

Yes. The free version includes a basic feature list; Pro adds a dedicated Store Locator control with filtering, hours, grouping by category, and distance sorting.

= Can users submit their own locations (crowdsourced maps)? =

Yes, this is a Pro feature — visitors can submit points through a front-end form, which you can moderate before they appear on the map.

= Is this GDPR-friendly? =

Mapster doesn't track visitors or send personal data anywhere by itself. Using Google Maps or Mapbox means their respective terms and data practices apply, same as embedding a video from any third-party service — MapLibre avoids that entirely since it doesn't require an account with anyone.

= Does this work with WPML or other translation plugins? =

Yes, map content and labels are translatable through WPML, and several control strings (like User Submission) ship with translation support out of the box.

= Can I use Advanced Custom Fields (ACF) or Gravity Forms with my maps? =

Yes — Pro includes a dedicated Mapster field type for ACF, and a Gravity Forms map field, so location data from your existing forms and post types can feed directly into a map.

= Something seems broken! =

Get in touch at [https://wpmaps.mapster.me/](https://wpmaps.mapster.me/) and we'll get back to you quickly — there's a real person (not a ticket queue) on the other end.

= Where can I learn how to use the plugin? =

Start with [the documentation](https://wpmaps-docs.mapster.me/), which includes written guides and video walkthroughs for both the free and Pro feature sets.

= You almost have what I need, but not quite... =

Email us at [https://mapster.me](https://mapster.me), post a [feature request](https://mapster-wp-maps.canny.io/feature-requests), or leave a note in the WordPress.org support forum. We build a lot of what gets asked for.

== Screenshots ==

1. A map on the front end, in globe view with styled markers and clustering.
2. Mapbox's Standard Style, available directly in the plugin.
3. 3D extruded countries colored and stretched based on values.
4. A store locator map in the Mapmaker editor.
5. Custom markers and popups ("A Traveller's Diary" example map).
6. Building a popup in the Popup Builder.
7. A listing map with cross-interaction between HTML and the map.
8. The Mapmaker editor, building a map with 3D terrain.
9. A heatmap built from imported data.

= 2.0.7 =
* Security fix for ACF Photo Gallery (credit to Dreni)
= 2.0.6 =
* Security fix on Mapmaker endpoint
* Bug fix for scrolling display of geocoder list
* Fix for cooperative gestures
* Numerous performance improvements
* Fixes to pattern polygon functionality
* Style selector in classic editor
* Correcting excluded categories in category control
= 2.0.5 =
* Cache busting
* Cluster clicking fixes
* Patchstack vulnerability patched
* DOMPurify updated
* De-duping popup template response
* Fix with globe bug in Google Maps
= 1.23.0 =
* Improving parsing in custom JSON
* Adding font color selection to clusters
= 1.22.0 =
* Fix for user submission fetching
= 1.21.0 =
* Fix and reversion for sanitization
= 1.20.0 =
* Improvement to ensure assets loaded for globe animation
* Further Wordfence security sanitization
= 1.19.0 =
* Updating ACF to 6.4.3
* Adding selection by CPT category
* Fix for Store Locator missing hours
* Adding basic polygon patterns
* Wordfence vulnerability patches
* Geocoder able to embed on separate page
* Fixing image thumbnail in popup preview
= 1.18.0 =
* Added thumbnails to Locations, Lines, and Polygons
* Fixes to permalink jankiness
* Improved validation Popup Template
* Removing unneeded local fonts
* Error checking for coordinate points Google Map
* Bounds working with filter dropdown
* Adding popup functionality to listing map
* Adding better bounds reset to listing map
* Bug fix for clicking when complex clusters
* Improvement in simulated clicks on layer features
* Adding clustering polygons and clustering lines
= 1.17.0 =
* Static CSV import added
* Advanced Static CSV options
* Adding default translucent borders to clusters
* Changing default render order to put polygons on the bottom
* Minor access token loading Mapbox issue
* Base image input removed
* Documentation major update
* Adding post display options to CSV import
* AutomatorWP bug fix
* Fix to filter dropdown query click
* Improving CSV image import
* Adding lat, lng, and zoom to query string load parameters
* Adding custom position translation to popup templates
* Bug fix with custom filter and sidebar popup
* Fix for default custom fields showing (ACF)
* Upgrade Freemius version
= 1.16.0 =
* Enhanced CSV upload with recurring scheduled CSV
* Adding complete custom fields to enhanced CSV
* Attribution control added to Maplibre
* Allowing rendering of linebreaks in labels
* Return glyphs to remote source
= 1.15.0 =
* Fix to full page height in some cases
* Adding header to modal popup
* Permalink flush fix
= 1.14.0 =
* Improving null location error alert
* Added static circles
* Fix to ACF field sans styling, more clarity
* Major enhancements to Listing Map
* Option to have category filter parent be cosmetic only
* Fix for Google Maps marker popups
= 1.13.0 =
* Loading fonts locally for free map types
* Fix to hover popup when hovering over other polygons
* Update for 6.8
= 1.12.0 =
* Adding {post_title} tag to HTML editor
* Adding Gravity Forms integration and custom field
* Fix to distance sorting when category list control enabled
* Fix to popups opening on listing page
* Unlimited categories during geoJSON import (set_categories property)
* Adding support for WEBP, AVIF, and SVG for icon images and polygon images
* Adding support for scalable SVGs as background images for maps
* Bug fix for permalink flushes
= 1.11.0 =
* Store Locator loading fix
* Freemius update
= 1.10.0 =
* Fix for zooming on custom search filter
* Fix for multiple image maps
* Adding cursor effect on hover
* Freemius update
= 1.9.0 =
= 1.8.0 =
* Bug fix for geocoder search
= 1.7.1 =
* Fix with category list special properties
* Large custom image maps working in mobile
* Fix for missing popups on full screen maps
* Allow text overlap
* Adding popup anchor options
* Popup fixes and map centering fixes for Store Locator
= 1.6.0 =
* Fix for clicking 3D objects
* Bug fix for "Text" Mass Editing Popup description
* Quick set for manual view values
* Map compare shortcode fix
* Heatmap trimming fix
* Style switch fix with Mapbox update
* Improving security for API endpoints
* Alignment fix for popup text
* Security fix for popup JS injection
= 1.5.0 =
* Custom search working with custom zoom level
* Adding restriction by bounds to geocoder
* Security fix to options
* Opacity hover effect on icons added
= 1.4.0 =
* Create completely full-page maps
* Update Mapbox 3.6.0
* Adding Standard Styles with lighting
* Control menu toggle container for hiding controls
* Fixing event listener bugs associated with controls on multi-map pages
* Freemius update
* Improved printer icon for Print Control
* Add custom padding for bounds on load
* Labels present on Category Filter Dynamic Properties
* Adding a ton of awesome new styles
* More restrictive scoping of event listeners on various controls
* Bug with download control
= 1.3.0 =
* Adding Reset button for category filter
* Adding image layer for polygons
* Making Mapster ACF field functional for other post types
* Fixing bug for tilt and rotation in mobile
* Adding ability to draw bezier curve
* Adding shortcode render for popup content
* Adding ability to reorder categories in filter
* Reorganized map editor interface for easier navigation
* Cleaning up some interface text in Pro
* Ordering control load order
* Can enter multiple features in shortcodes
* New version numbering system
* Validating ACF Mapster Map field for required field
* Ability to geolocate to user IP address
* Improved selector for map styles
* Fix for Carbon Fields meta fields display
* Advanced List Control functionality (accordion, ordering features, sorting by categories)
* Fix on List Control clicking
* Fix on modal opening immediately showing undefined
= 1.2.42 =
* Fix for google maps markers
= 1.2.41 =
* Loading a map via querystring and opening popup automatically
* Select map language for Mapbox maps
* Added blueprint for testing
* Custom zoom level for locations
* Adding images for clusters
* Fixing globe transparency
* Adding Print Control
* 3D shapes working with category filter
* Images with filter dropdown
* Modal details including custom properties
* Attribution control added
* Static icon sizes
* Images with list control
* Embed to iframe for Safari
* Geocoder now accepts lat/lng entries
* Added optional animation to globe view
= 1.2.40 =
* Updating Mapbox GL JS to v3.1.2
* Adding marker color option for geocoder
* Updating image thumbnail sizes for sidebar slideshow images
* Updating ACF and more deprecation notices for PHP 8.2+
* Allowing display for non-category symbols during category clustering
* Bug fixes
* Freemius update
= 1.2.39 =
* Stamen tiles update (still not open except from localhost)
* Adding cooperative gestures
* Fix to filter dropdown clicking in some browsers
* Changing how symbols show/hide during category filters
* Centering coordinates when edited manually in Locations
* Adding listing specialty map
* Ability to open directly to modal
* Popups working on 3D models
* Fix on directions layers not appearing (not detecting loaded state)
* Prevent focus on popups when opened
* Store hours properly hidden
* Google Maps Store Locator Mobile fix, scrolling fix
* PHP 8 fix for deprecated dynamic properties
* Security fix for shortcode XSS vulnerability
= 1.2.38 =
* Ensuring borders are hidden when circles are hidden
* Prepping for major update of Maplibre version
* Fixing some mobile popup issues with Maplibre
* Popup image resolution options added
* Adding additional geolocation options
* Fixing reloading of custom icons on default style switch
* Interactions between search filter and category toggle
* Updates for PHP 8.1
* Fix for cursors on label icons with popups
* Moving cache generation to wp-uploads
* Fix to user submission posts showing in single feature shortcodes
= 1.2.37 =
* Ability to create map caches
* Simplification tool for shapes
* Properly output content in feature object
* Hovering bug fix for overlapping polygons
= 1.2.36 =
* User submission datepicker bug
* Modals able to render shortcode content
* Category option to have numbers rendered
* Ability to show category name as cluster instead of number
* Freemius update
= 1.2.35 =
* Previews for specialty map types
* Improving modal close button
* Renaming Mapbox Style URL to Style URL for all providers
* Added ESRI Satellite layer
* Layer control error correction
* Improved user submission flow
* Removing references to CSV regular importer
* Adding features images to maps
= 1.2.34 =
* Fix for popup image backgrounds
* Incorrect update
= 1.2.33 =
* User Submission error
* Ensuring custom HTML shows correctly in map edit
* Removing unneeded vendor files
* Mass Edit for showing map in post
* Correcting import count on title
* GeoJSON download button fix
* User Submission edit post display script fix
* Map style slider beta
* Heatmap beta
* Reorganize map editor tabs
* Elevation map beta
* Fix for none permalink type
* Download only included fields
* Ensuring general and specific hooks both run
= 1.2.32 =
* Fix to accordion layout (oops)
* Ensuring premium plugin deactivates free plugin without an error
* Improving script loading by pre-compiling
* Restricting enqueued backend scripts to appropriate post types
* Fixing click-to-link so doesn't open when disabled
* Fixing overflow scroll on List Control
* More elegant marker clustering (no flash of single markers)
* Ability to set number of search results for Custom Search Filter
* Style switcher control
* Layer switcher control
* Different search options for Custom Search Filter
= 1.2.31 =
* Removing extra field User Submission
* Minor CSS fixes User Submission
= 1.2.31 =
* Removing test code limiting uploads /facepalm
= 1.2.30 =
* Fixes to category marker clustering
= 1.2.29 =
* List control mobile layout fix
* Limited geocoder country fix
* Shortcode generator for features
* Setting default template map
* Improving centering of popups
* Update instructions for importing shapefiles
* Improving hover flickering on polygon popups
* Completing first version CSV import
* Fix to 3D terrain adding
* Hovering on layer features improved
* Extra list elements removed category filter
* Speed improvements in Pro
* Move attribution control
* Move logo position
* Allowing category exclusion for filter
* Collapsible menu for category filter
* Improved loading UX
* Fixing icon size bug with large images
= 1.2.28 =
* Added indicators of current map details in Manual Center and Zoom
* Fixing geolocation on load bug
* Custom list control store locator button
* Fill extrusion bug fix
= 1.2.27 =
* Extend zooming functionality to non-markers in List
* Adding functionality for zoom on click popups
= 1.2.26 =
* Allowing disable rotation and pitch
* Adding functionality for center on click popups
= 1.2.25 =
* Fix to embed code
* Allowing uploading errored geojsons
* Ability to change editing map style
= 1.2.24 =
* Fixing encoding in Dropdown List
* Mobile breakpoints
* Update to latest ACF version, extracting styles
= 1.2.21 =
* Improvements to User Submission
* Expandable map edit screen
* Download geoJSON edit screen
* Showing proper labels for complex ACF fields
* Adding Gutenberg selection block for map
* Custom Images as base maps
* Fix to map rotation (bearing)
= 1.2.20 =
* Labels on checkboxes for filter
* Strings for User Submission
* WPML integration User Submission
* Editing permissions User Submission
= 1.2.19 =
* User Submission popups
* Adding link to store locator fields
* Category filter interact with Store Locator
* Multi features initial shape
= 1.2.18 =
* Restriction of user movement on map
* Allowed area during user submission
* Adding custom HTML control
* Adding CSV export user submissions
* Adding clustering by category
* Translation strings for submission process
= 1.2.17 =
* Bug fix layer trigger popup on load
= 1.2.16 =
* Revision of user submission
= 1.2.15 =
* Geocoder control fix
= 1.2.14 =
* Ensure code fields show up in Free version
* Adding globe view
* Add geolocation on by default to control
* Properly render HTML entities in Custom Search Filter
* Remove marker from Custom Search Filter, add automatic popup open on markers
= 1.2.13 =
* Open popup on feature layer on map load
* Bug related to lines automatically showing dashes
* Bug with X/Y positioning on labels and icons in production
* Adding function to add category to a div
* Making sure properties formatted properly when objects
* Bug editing features in user submission with template overwrite
* Loading support for Elementor elements
* Unhiding height fields
* Adding custom lat/long and zoom options to shortcode
= 1.2.12 =
* Layout user submission
* Adding wrapper, CSS, JS to user submission
= 1.2.11 =
* Bug with duplication importing
* Migration tool and documentation
* Category fix
* Semantic change user submission
= 1.2.10 =
* First implementation 3D models
* Adding starter tutorial
* ListControl fix
= 1.2.9 =
* Adding custom spatial database
* Adding MultiPolygon, MultiLineString editing
* Spatial API 1.0
* Fix for local ACF and ACF Pro
= 1.2.8 =
* Minor Mass Edit js bug
= 1.2.7 =
* Full width fix for all containers
* Optional widths for sidebar
* Secondary categories in advanced importer
* Adding dynamic features to custom HTML popup templates
* Gallery margin-left fix
= 1.2.6 =
* Adding custom style JSON
* Google Maps first integration
* Hiding Mapster WP Maps ACF field
= 1.2.5 =
* Import bug with many properties
* Mass edit small bug
= 1.2.4 =
* Map projections
* Update Mapbox 2.8.2
* Adding 3D models first pass
= 1.2.3 =
* Clustering for markers
* 3D extrusions
* Pitch and rotate enabled
= 1.2.2 =
* Hover effects for popups
= 1.2.1 =
* Custom HTML for popups
= 1.2.0 =
* Integrating Freemius
= 1.1.5 =
* Updates for WPML fetch all language posts
* Fixing front end geocoder
= 1.1.4 =
* Adding map display to location, line, and polygon post types
* Fatal error
* Image issue fixed
= 1.1.3 =
* Major PHP bug fixed
= 1.1.2 =
* CSS Admin menu
* Domain fix
= 1.1.1 =
* Popups stay open on mouse hover option
* Customizable permalinks
* Breaking errors
= 1.1.0 =
* Numerous bug fixes
* Splitting plugin into Basic and Pro
* Quality of life fixes
= 1.0.3 =
* Editor bug fixes
* Marker overlapping bug fix
= 1.0.2 =
* Bug fixes custom scripts
* Allowing large file uploads
= 1.0.1 =
* Bug fixes marker popups and dropdown list
= 1.0.0 =
* Rewrite to make plugin more extendable and developer-friendly
* Store locator functionality and addon
* Numerous smaller bug fixes
= 0.5.16 =
* Allow MultiLineString and MultiPolygon uploads into features
* Significantly expanded Store Locator functionality
* Prepping for addon plugins
= 0.5.15 =
* Adding user submission of features
* Bug with search filter not loading geocoder
= 0.5.14 =
* Allowing category filter to be initialized "off"
= 0.5.13 =
* Updating initial bounds zoom
* Some issues with the last update, missing scripts
= 0.5.12 =
* Improvement to script loading
* Placeholder option for list control
* Ability to turn off map horizontal duplication
= 0.5.11 =
* Conditional script loading
* Editor improvements adding/deleting
* Editor improvements clustering icons
* Addition of gallery slideshow to popups
* Ability to open popups in a sidebar instead
* Ability to enter in location coordinates directly
= 0.5.10 =
* More API bug fixes
= 0.5.9 =
* Bug fixes editor
* Bug fixes API
* Marker fix for category filter
= 0.5.8 =
* Marking some features as in development
* Improving marker image sizes
= 0.5.7 =
* Improving marker clicks during mobile
* Major loading improvements for larger maps
= 0.5.6 =
* Fixing multiple maps on one page
* Cross-origin image fix
* Properly calling titles for filters
* Click hover popups in mobile
= 0.5.5 =
* Increasing specificity of button styles
* Adding geosearch to Lines and Polygons
= 0.5.4 =
* Fix pointer background bug
* Easy duplication of posts
* Improved bounds loading
* Marker click to URL
= 0.5.3 =
* Import geoJSON
* Ability to interact via custom scripts
* Full width maps
* Fixed Safari/Firefox icon bug
= 0.5.1 =
* Import from Mapbox WP GL JS
* Add/remove features by category
= 0.5.0 =
* Complete rewrite with ACF and jQuery
= 0.4.3 =
* Adding Mapbox Terrain
* Adding custom tilesets
* Set view with a button
* Tutorial links
= 0.4 =
* Switching Mapbox 1.13 to MapLibre
= 0.3 =
* Adding delete functionality
* Improving geocoding
* Improving view control
= 0.2 =
* Adding location geocoding
= 0.1 =
* Push to do beta testing.
