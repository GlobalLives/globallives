var ijsc = {
    initFrame: function() {
        jQuery(document).ready(function() {
            jQuery('#tab-ijsc-js, #tab-ijsc-css, #tab-ijsc-help').click(function() {
                jQuery('ul#sidemenu > li > a').removeClass('current');
                jQuery(this).children('a').addClass('current');
                
                jQuery('.ijsc-view').hide();
                jQuery('#' + jQuery(this).children('a').attr('data-child')).show(); 
                ijsc.resizeFrame();
                return false; 
            });
            
            jQuery('#ijsc-save').click(function() {
                jQuery('#ijsc-field-js', window.parent.document).val(jQuery('#ijsc-js-code').val().trim());
                jQuery('#ijsc-field-css', window.parent.document).val(jQuery('#ijsc-css-code').val().trim()); 
                jQuery('#TB_closeWindowButton > img', window.parent.document).click(); 
                parent.ijsc.setPostIcon(); 
                return false; 
            });
            jQuery('#ijsc-cancel').click(function() {
                jQuery('#TB_closeWindowButton > img', window.parent.document).click();
                parent.isjc.setPostIcon(); 
                return false; 
            });
            
            jQuery('#ijsc-js-code').val(jQuery('#ijsc-field-js', window.parent.document).val());
            jQuery('#ijsc-css-code').val(jQuery('#ijsc-field-css', window.parent.document).val()); 
            
            ijsc.resizeFrame(); 
        });
        jQuery(parent).resize(function() { ijsc.resizeFrame(); });
            
    },
    
    initPostPage: function() {
        jQuery(document).ready(function() {
            ijsc.setPostIcon(); 
        });
    },
    
    setPostIcon: function() {
        if (jQuery('#ijsc-field-js').val().trim() == '' && jQuery('#ijsc-field-css').val().trim() == '') {
            jQuery('#ijsc-icon').attr('src', jQuery('#ijsc-icon').attr('data-bw')); 
            jQuery('#ijsc-icon').attr('title', jQuery('#ijsc-icon').attr('data-insert')); 
        } 
        else {
            jQuery('#ijsc-icon').attr('src', jQuery('#ijsc-icon').attr('data-color')); 
            jQuery('#ijsc-icon').attr('title', jQuery('#ijsc-icon').attr('data-edit')); 
        }
        
    },
    
    resizeFrame: function() {
        jQuery('html').height(jQuery(document).height() - 250);
        jQuery('textarea#ijsc-js-code').css({
            // 32 takes care of the margins
            height: jQuery('#ijsc-footer').offset()['top'] - jQuery('#ijsc-js-code').offset()['top'] - 32
        }); 
        jQuery('textarea#ijsc-css-code').css({
            height: jQuery('#ijsc-footer').offset()['top'] - jQuery('#ijsc-css-code').offset()['top'] - 32
        });
        jQuery('div#ijsc-help-wrap').css({
            height: jQuery('#ijsc-footer').offset()['top'] - jQuery('#ijsc-help-wrap').offset()['top'] - 32
        });
    }
}