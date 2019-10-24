(function($) {
  $(document).ready(function() {
    pf_background();
    pf_background_in_img();
  });
  $(window).on("resize", function() {
    pf_background();
    pf_background_in_img();
  });
  function pf_background_in_img() {
    $(".pf_background").each(function() {
      let el_width = $(this).width();
      let el_height = $(this).height();

      let img_width = $(this)
        .find("img.pf_background_img")
        .width();
      let img_height = $(this)
        .find("img.pf_background_img")
        .height();

      let el_ratio = el_width / el_height;
      let img_ratio = img_width / img_height;

      if (img_ratio > el_ratio) {
        $(this)
          .find("img.pf_background_img")
          .addClass("pf_heightbased");
      } else {
        $(this)
          .find("img.pf_background_img")
          .removeClass("pf_heightbased");
      }
      $(this)
        .find("img.pf_background_img")
        .addClass("pf_is_load");
    });
  }
  function pf_background() {
    const windowOptions = {
      retina: isItRetina(),
      width: $(window).width()
    };
    $("[op-background]").each(function(idx, e) {
      const imageUrl = getMatchingImageSize(
        $(this).data("background"),
        windowOptions
      );
      $(this).css("background-image", "url(" + imageUrl + ")");
    });
  }
  function isItRetina() {
    return (
      ((window.matchMedia &&
        (window.matchMedia(
          "only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx), only screen and (min-resolution: 75.6dpcm)"
        ).matches ||
          window.matchMedia(
            "only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min--moz-device-pixel-ratio: 2), only screen and (min-device-pixel-ratio: 2)"
          ).matches)) ||
        (window.devicePixelRatio && window.devicePixelRatio >= 2)) &&
      /(iPad|iPhone|iPod)/g.test(navigator.userAgent)
    );
  }
  function getMatchingImageSize(backgroundData, windowOptions) {
    let images;
    let matchBP = "xs";
    for (var breakpoint in backgroundData.breakpoints) {
      const minWidth = pf_breakpoints[breakpoint];
      if (windowOptions.width > minWidth) {
        matchBP = breakpoint;
      }
    }
    if (
      windowOptions.retina &&
      backgroundData.breakpoints[matchBP]["2x"] !== undefined
    ) {
      return (
        backgroundData.base_path + backgroundData.breakpoints[matchBP]["2x"]
      );
    } else {
      return (
        backgroundData.base_path + backgroundData.breakpoints[matchBP]["1x"]
      );
    }
  }
})(jQuery);
