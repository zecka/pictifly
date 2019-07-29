(function($) {
  class PFadmin {
    constructor() {
      this.init();
    }
    init() {
      $(document).ready(() => {
        this.nonce = $(".pf_option_page button").data("nonce");
        this.totalpost = 0;
        this.nbpost = 0;
        this.nbimage = 0;
        this.keypoint();
        this.getItems();
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

        //$('.pf_keypoint_wrapper').addClass('pf_keypoint_wrapper--lock');
      });
    }
    getItems() {
      $(document).on("click", ".pf_option_page button", e => {
        this.totalpost = 0;
        this.nbpost = 0;
        $(".pf_progress_percent").html("0%");

        const data = {
          action: "pf_ajax_regenerate_get_items",
          nonce: this.nonce
        };

        $.ajax({
          type: "post",
          url: myAjax.ajaxurl,
          data: data,
          success: response => {
            this.totalpost = response.data.length;
            this.regenerateItems(response.data);
          }
        });
      });
    }
    regenerateItems(items) {
      let p = $.when();

      items.map(item => {
        const data = {
          action: "pf_ajax_item_image_regenerate",
          id: item.id,
          post_type: item.post_type,
          nonce: this.nonce
        };

        p = p.then(() => {
          return this.regenerateItem(data);
        });
      });
    }
    regenerateItem(data) {
      return $.ajax({
        type: "post",
        url: myAjax.ajaxurl,
        data: data,
        success: response => {
          this.nbpost = this.nbpost + 1;
          this.progressBar();
          console.log("response", response);
          this.nbimage += response.data.images.length;
        }
      });
    }
    progressBar() {
      let percent = (this.nbpost / this.totalpost) * 100;
      // Round percent 2 decimal
      percent = Math.round(percent * 100) / 100;
      $(".pf_progress_statut").width(percent + "%");
      $(".pf_progress_nbimage .value").html(this.nbimage);
      $(".pf_progress_nbpost .value").html(this.nbpost);
      if (percent == 100) {
        $(".pf_progress_percent").html(percent + "% - finish");
      } else {
        $(".pf_progress_percent").html(percent + "%");
      }
    }
  }
  new PFadmin();
})(jQuery);
