(function( $ ) {

  const feature_type = $('.mapster-front-submission-options').data('feature_type');
  if(feature_type === 'location-circle') {
    $('#acf-field_61637347292a4').val('circle');
  }

  $(document).on('click', '.mapster-search-label', function() {
    $(this).addClass('active');
    $('.mapster-manual-label').removeClass('active');
    $('.mapster-search-container').show();
    $('.mapster-manual-container').hide();
  });

  $(document).on('click', '.mapster-manual-label', function() {
    $(this).addClass('active');
    $('.mapster-search-label').removeClass('active');
    $('.mapster-search-container').hide();
    $('.mapster-manual-container').show();
  });

})(jQuery)
