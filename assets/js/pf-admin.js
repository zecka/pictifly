(function($) {
  class PFadmin {
    constructor() {
      this.init();
      this.stopRegeneration = false;
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
      $(document).on("click", ".pf_option_page button.stop", e => {
        this.stopRegeneration = true;
        $(e.currentTarget).removeClass("stop");
        $(e.currentTarget).addClass("start");
        $(e.currentTarget).html("Restart");
      });
      $(document).on("click", ".pf_option_page button.start", e => {
        this.stopRegeneration = false;
        this.totalpost = 0;
        this.nbpost = 0;
        this.nbimage = 0;
        $(".pf_progress_percent").html("0%");
        $(".pf_log_content").html("");

        $(e.currentTarget).removeClass("start");
        $(e.currentTarget).addClass("stop");
        $(e.currentTarget).html("stop");

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
          if (!this.stopRegeneration) {
            return this.regenerateItem(data);
          }
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
          let log =
            "<li> regenerated sizes for <b> " + response.data.post + "</b><ul>";
          response.data.sizes.map(size => {
            log += "<li>" + size + "</li>";
          });
          log += "</ul></li>";
          $(".pf_log_content").prepend(log);
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
