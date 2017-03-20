<?php   

$glt_css = get_option("googlelanguagetranslator_css");

  echo '<style type="text/css">';
  echo $glt_css;

if (get_option('googlelanguagetranslator_flags') == 'show_flags') {

	if(get_option('googlelanguagetranslator_display')=='Vertical') { 
	  if (get_option('googlelanguagetranslator_language_option')=='specific') {
		echo '#flags {display:none !important; }';
	  }
  
	    echo 'p.hello { font-size:12px; color:darkgray; }';
            echo '#google_language_translator, #flags { text-align:left; }';
	} elseif (get_option('googlelanguagetranslator_display')=='Horizontal') {
  
	  if (get_option('googlelanguagetranslator_language_option')=='specific') {
		echo '#flags {display:none !important; }';
	  }
  
	  if (get_option('googlelanguagetranslator_flags_alignment')=='flags_right') {
	    echo '#google_language_translator { text-align:left !important; }';
	    echo 'select.goog-te-combo { float:right; }';
	    echo '.goog-te-gadget { padding-top:13px; }';
	    echo '.goog-te-gadget .goog-te-combo { margin-top:-7px !important; }';
	  }
  
	    echo '.goog-te-gadget { margin-top:2px !important; }';
	    echo 'p.hello { font-size:12px; color:#666; }';
	} else if (get_option('googlelanguagetranslator_display')=='SIMPLE') {
	    if (get_option('googlelanguagetranslator_language_option')=='specific') {
		echo '#flags {display:none !important; }';
	    }
            if (get_option('googlelanguagetranslator_flags_alignment')=='flags_right') {
                echo '.goog-te-gadget { float:right; padding-right:10px; clear:right; }';
            }
        }

	if ( get_option ('googlelanguagetranslator_flags_alignment') == 'flags_right') {
	  echo '#google_language_translator, #language { clear:both; width:160px; text-align:right; }';
	  echo '#language { float:right; }';
	  echo '#flags { text-align:right; width:165px; float:right; clear:right; }';
	  echo '#flags ul { float:right !important; }';
	  echo 'p.hello { text-align:right; float:right; clear:both; }';
      echo '.glt-clear { height:0px; clear:both; margin:0px; padding:0px; }';
	}

	if ( get_option ('googlelanguagetranslator_flags_alignment') == 'flags_left') {
	  echo '#google_language_translator { clear:both; }';
	  echo '#flags { width:165px; }';
	  echo '#flags a { display:inline-block; margin-right:2px; }';
	} elseif ( get_option ('googlelanguagetranslator_flags_alignment') == 'flags_right') {
	  echo '#flags { width:165px; }';
	  echo '#flags a { display:inline-block; margin-left:2px; }';
	}
}

if (get_option('googlelanguagetranslator_manage_translations') == 0) {
    if(get_option('googlelanguagetranslator_active')==1) {
      echo '.goog-tooltip {display: none !important;}';
      echo '.goog-tooltip:hover {display: none !important;}';
      echo '.goog-text-highlight {background-color: transparent !important; border: none !important; box-shadow: none !important;}';
    }
}

if (get_option('googlelanguagetranslator_showbranding')=='Yes') {
    if(get_option('googlelanguagetranslator_active')==1) {
      echo '#google_language_translator { width:auto !important; }';
    }
  
} elseif(get_option('googlelanguagetranslator_showbranding')=='No' && get_option('googlelanguagetranslator_display')!='SIMPLE') {
    if(get_option('googlelanguagetranslator_active')==1) { 
      echo '#google_language_translator a {display: none !important; }';
      echo '.goog-te-gadget {color:transparent !important;}';  
      echo '.goog-te-gadget { font-size:0px !important; }';
      echo '.goog-branding { display:none; }';
    }
}

if (get_option('googlelanguagetranslator_translatebox') == 'no') {
    if(get_option('googlelanguagetranslator_active')==1) {
      echo '#google_language_translator { display:none; }';
    }
}

if (get_option('googlelanguagetranslator_flags') == 'hide_flags') {
    if(get_option('googlelanguagetranslator_active') ==1) {
      echo '#flags { display:none; }';
    }
}

if(get_option('googlelanguagetranslator_toolbar')=='Yes') {
    if(get_option('googlelanguagetranslator_active')==1) {
      echo '#google_language_translator {color: transparent;}';
	  echo 'body { top:0px !important; }';
    }
} elseif(get_option('googlelanguagetranslator_toolbar')=='No') {
    if(get_option('googlelanguagetranslator_active')==1) {
      echo '.goog-te-banner-frame{visibility:hidden !important;}';
      echo 'body { top:0px !important;}';
    }
}
echo '</style>';
?>