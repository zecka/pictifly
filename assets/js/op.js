(function($) { 
	$(document).ready( function() {
			pf_background_cover();
    });
    $(window).on('resize', function(){
			pf_background_cover();
    });
    function pf_background_cover(){
        $('.pf_background').each(function(){
            let el_width = $(this).width();
            let el_height= $(this).height();

            let img_width = $(this).find('img.pf_background_img').width();
            let img_height= $(this).find('img.pf_background_img').height();

            let el_ratio = el_width / el_height;
            let img_ratio = img_width / img_height;
   
            if(img_ratio > el_ratio){
                $(this).find('img.pf_background_img').addClass('pf_heightbased');
            }else{
                $(this).find('img.pf_background_img').removeClass('pf_heightbased');
            }
        });
    }

})(jQuery)
