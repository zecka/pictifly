(function($) {
  class PFadmin {
    constructor() {
      this.init();
    }
    init() {
      $(document).ready(() => {
        this.nonce = $(".pf_option_page button").data("nonce");
        this.keypoint();
      });
    }
    keypoint() {
      $(".pf_keypoint_figure").live("click", function(e) {
        var width = $(this).width();
        var height = $(this).height();
        var posX = $(this).offset().left,
          posY = $(this).offset().top;

        var clickX = e.pageX - posX;
        var clickY = e.pageY - posY;

        var percentX = (clickX / width) * 100;
        var percentY = (clickY / height) * 100;

        $(this)
          .find(".pf_keypoint")
          .css("left", percentX + "%");
        $(this)
          .find(".pf_keypoint")
          .css("top", percentY + "%");

        // First change value of input
        $("input.pf_keypoint_left").attr("value", percentX);
        $("input.pf_keypoint_top").attr("value", percentY);

        // then inform that input have change (so WordPress fire save)
        $(".pf_keypoint_wrapper input").change();
      });
    }
  }
  new PFadmin();
})(jQuery);
