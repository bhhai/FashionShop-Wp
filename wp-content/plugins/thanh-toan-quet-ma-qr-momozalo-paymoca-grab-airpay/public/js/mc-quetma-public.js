jQuery(document).ready(function(){
	function runFrame(){
        if( jQuery('#mc-mobileguide .framegif .frame').length == 0 ) return;
        var curFrame = 0;
        setInterval(function(){ 
            jQuery('#mc-mobileguide .framegif .frame').hide();
            jQuery('#mc-mobileguide .framegif .frame'+curFrame).fadeIn();
            curFrame++;
            curFrame = curFrame % jQuery('#mc-mobileguide .framegif .frame').length;
        }, 3000);
    }

    function runFrameWithClass(className){
        var curFrame = 0;
        setInterval(function(){ 
            jQuery('#mc-mobileguide .'+className+' .frame').hide();
            jQuery('#mc-mobileguide .'+className+' .frame'+curFrame).show();
            curFrame++;
            curFrame = curFrame % jQuery('#mc-mobileguide .'+className+' .frame').length;
        }, 3000);
    }
    function showMobileGuide(){
        jQuery('#mc-mobileguide').show();
        runFrame();
    }
    jQuery('#downloadqrcode').click(function(){
    	showMobileGuide();
    	jQuery('#openappbtn').show();
        jQuery('.openappbtn').show();
    });

    jQuery('.openappbtn.moca').click(function(){
        jQuery('.framemoca').show();
        jQuery('.framegrab').hide();
        runFrameWithClass('framemoca');
    });

    jQuery('.openappbtn.grab').click(function(){
        jQuery('.framegrab').show();
        jQuery('.framemoca').hide();
        runFrameWithClass('framegrab');
    });

    setTimeout(function(){
        jQuery('#finishscan').fadeIn();
    }, 10000);
    jQuery('#finishscan .btnfinish-scan').click(function(){
        jQuery('.questionfinish').hide();
        jQuery('#finishscan .btnfinish-scan').hide();
        jQuery('#thongbaofinish').show();
        jQuery('#footer_scan .loading-quetma').hide();
    });
    
});