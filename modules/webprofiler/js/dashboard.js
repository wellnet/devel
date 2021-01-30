(function($, Drupal, drupalSettings) {

  Drupal.behaviors.webprofiler_dashboard = {
    attach(context) {
      $('.webprofiler__collector .use-ajax').click(function () {
        $('.webprofiler__collector .use-ajax.selected').not(this).removeClass('selected');
        $(this).toggleClass('selected');
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
