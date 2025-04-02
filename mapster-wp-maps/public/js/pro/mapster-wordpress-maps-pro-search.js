(function( $ ) {

  let pageToLoad = 1;
  let feature_types = $('#mapster-search-button').data('feature_types');
  let editing_permissions = $('#mapster-search-button').data('permissions');
  let user_id = $('#mapster-search-button').data('user');

  function doSearch() {
    const val = $('#mapster-search-input').val();
    if(val.length > 2) {
      $('#mapster-search-loader').show();
      fetch(`${window.mapster_search.rest_url}mapster-wp-maps/search-features${window.mapster_search.qd}query=${val}&feature_types=${feature_types}&page=${pageToLoad}&user_id=${user_id}&permissions=${editing_permissions}`).then(resp => resp.json()).then(response => {

        $('#mapster-search-loader').hide();

        let newHTML = '';
        if(response.results.length > 0) {
          response.results.forEach(result => {
            let currentURL = window.location.href;
            let editURL = window.location.href + '&post_id='+result.id;
            editURL = editURL.replace('pagetype=search', 'pagetype=edit');
            newHTML += `
              <li>
                <h3>${result.title}</h3>
                <div class="mapster-search-link">
                  <a href="${editURL}">Edit</a>
                </div>
              </li>
            `
          })
          $('.mapster-search-results ul').html(newHTML);

          if((pageToLoad * 10) < response.total) {
            $('.mapster-search-page-next').fadeIn();
          } else {
            $('.mapster-search-page-next').fadeOut();
          }
          if(pageToLoad > 1) {
            $('.mapster-search-page-prev').fadeIn();
          } else {
            $('.mapster-search-page-prev').fadeOut();
          }
        } else {
          $('.mapster-search-results ul').html('No results found for "'+val+'".');
        }
      })
    } else {
      $('.mapster-search-results ul').html('Please enter at least 3 letters for your search.');
    }
  }

  $(document).on('keypress', '#mapster-search-input', function(e) {
      if (e.which == 13) {
        doSearch();
      }
  });

  $(document).on('click', '#mapster-search-button', function() {
    pageToLoad = 1;
    doSearch();
  })

  $(document).on('click', '.mapster-search-page-next', () => {
    pageToLoad = pageToLoad + 1;
    doSearch();
  })

  $(document).on('click', '.mapster-search-page-prev', () => {
    pageToLoad = pageToLoad - 1;
    doSearch();
  })

})(jQuery)
