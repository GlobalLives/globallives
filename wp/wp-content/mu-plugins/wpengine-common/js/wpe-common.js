var url = window.location.pathname
var filename = url.substring(url.lastIndexOf('/')+1);
var warning = "Before taking this action, we at WP Engine recommend that you create a Restore Point of your site. This will allow you to undo this action within minutes.";

wpe.updates = {}; // wpe is initialized via wp_localize_script().

// Runtime jQuery
jQuery(document).ready(function($) {

	$('button[name="snapshot"]').click(function(e) {
		check = $('button[name="snapshot"]').attr( 'data-confirm' );
		if( check  === "true" ) {
			e.preventDefault();
			$('#stagingModal').modal();
			$(document).on( 'click','button#staging-submit',function() {
				$('form#staging').append("<input type='hidden' name='snapshot' value='true' />").submit();
			});
		}
	});

	/**
	 * Logic for add all tables and remove all tables buttons.
	 */
	if ( has_args('page=wpengine-staging') ) {
		$('#wpe-add-all-tables').on('click', function() {
			$("[name='tables[]'] option").prop('selected', true);
			$("[name='tables[]']").trigger('liszt:updated');
		});

		$('#wpe-remove-all-tables').on('click', function() {
			$("[name='tables[]'] option").prop('selected', false);
			$("[name='tables[]']").trigger('liszt:updated');
		});
	}

	/**
	 * Bind the appropriate buttons and links to the update confirm modal.
	 */
	if( filename == 'update-core.php' && $('form.upgrade').length > 0 && wpe.popup_disabled != 1 ) {
		var $element = $('#upgrade, #upgrade-plugins, #upgrade-themes, #upgrade-plugins-2, #upgrade-themes-2');
		wpe.updates.confirmInit( $element );
		wpe.updates.confirmButton( $element );
	} else if( filename == 'plugins.php' && wpe.popup_disabled !=  1 ) {
		var $element = $('#doaction, .update-link');
		wpe.updates.confirmInit( $element );
		wpe.updates.confirmButton( $element );
	} else if( filename == 'plugin-install.php' && wpe.popup_disabled != 1 ) {
		var $element = $('a.install-now, a.update-now');
		wpe.updates.confirmInit( $element );
		wpe.updates.confirmLink( $element );
	}
});

/*
 * Class for managing the Deploy from staging response
 */
(function($) {
	$.fn.extend({
		//name
		wpeDeploy: function(cmd) {

			var max = 238;
			var current = '';
			var started = 0;
			var finished = 0;
			this.each(function() {
				new $.wpeDeploy(cmd);
			});
				return;
		}
	}); //end return

	$.wpeDeploy = function(cmd) {
			//@TODO there's got to be a better way to do this
			if( 'start' == cmd ) {
				start();
			} else if ('update' == cmd) {
				update();
			}
			/*
			 * Start the deploy
			 */
			function start() {
				$('#myModal').modal().addClass('in');
				$('.modal-header h3').html("A deployment is currently underway ... ");
				$('.modal-body #progress').progressbar({'value':5}).addClass('active progress progress-info progress-striped');
				//$('.ui-progressbar-value').css({'background':'rgb(140, 186, 169)'});
				$('.ui-progressbar-value').addClass('bar loading');
				$('#status').html('<pre>Beginning Deploy ...\n</pre>');
				$('.progress-label').text('Beginning Deploy ...');
				$('.progress-label').effect("slide", 300);
				update();
			}

			/*
			 * Progress bar handler for deploy from staging
			 * @note this will run until a "Deploy Complete" status is received.
			 */
			function update() {
				$.ajax({url:'/wpe-deploy-status-'+wpe.account})
								.done( function(resp) {
					try{
						resp = $.parseJSON( resp );
					} catch(error){
						setTimeout( function() { update() } , 500);
					}

					// if we don't have a valid object, bail.
					if ( typeof(resp) !== 'object' ) return;

					var last_status;
					$.each( resp, function( i, obj ) {
						if (undefined == last_status || obj.timestamp > last_status.timestamp){
							last_status = obj;
						}
					});

					// check that this isn't an old status
					if (undefined !== last_status ){
						var now = new Date();
						if (last_status.timestamp < (now.getTime()/1000 - 60)){
							last_status = undefined;
						}
					}

					if ( undefined !== last_status){
						$.deployStarted = 1;
						$.wpeDeploy.data = last_status.text.split("\n");

						setProgress(last_status);
						if( last_status.text.indexOf("Deploy Completed") !== -1 ) {
							$('.modal-body #progress').progressbar('option','value', 100);
							setTimeout( function() { $('#myModal,.modal-backdrop').removeClass('in',1000).remove() }, 5000);
						} else {
							setTimeout( function() { update() } , 500);
						}
					} else {
						setTimeout( function() { update() } , 500);
					}

				}).fail( function() {
						//else if the deployment never started try again
						setTimeout( function() { update() } ,2000);
				});
			}

			function setProgress(data) {
					$('#status pre').text( data.text + "\n");
					var one_line = $.wpeDeploy.data[$.wpeDeploy.data.length -1];
					$('.progress-label').text(one_line);
					$('.modal-body #progress').progressbar('option','value', data.progress);
					$('.progress-label').html(one_line);
			}
	}


	/**
	 * Helper function to determine if the shiny updates features are available.
	 *
	 * @return {bool} Whether or not shiny updates are available.
	 */
	wpe.updates.areShiny = function() {
		if ( 'function' === typeof wp.updates.installPlugin ) {
			return true;
		}
		return false;
	};

	/**
	 * Sets the initial state of the element before user interaction with the modal.
	 *
	 * @param  {[type]} $element jQuery element that stores the state.
	 */
	wpe.updates.confirmInit = function( $element ) {
		// Initialize buttons and links with a non-confirmed status
		$element.data('confirmChange', false);
	}

	/**
	 * Intercepts the click event handler for Buttons.
	 *
	 * @param  {[type]} $element jQuery element that stores the state.
	 */
	wpe.updates.confirmButton = function( $element ) {
		// Intercept the click handler
		$element.click(function(e) {
			if( false === $(this).data('confirmChange') ) {
				e.preventDefault();
				e.stopImmediatePropagation();
			}
			wpe.updates.confirmChange( $(this) );
		});
	}

	/**
	 * Intercepts the click event handler for Links.
	 *
	 * @param  {[type]} $element jQuery element that stores the state.
	 */
	wpe.updates.confirmLink = function( $element) {
		// Intercept the click handler
		$element.click(function(e) {
			if( false === $(this).data('confirmChange') ) {
				e.preventDefault();
				e.stopImmediatePropagation();
			}
			wpe.updates.confirmChange( $(this), true );
		});
	}

	/**
	 * Displays the apprise modal and prompts the user to create a backup.
	 *
	 * @param  {[type]}  $element    The jQuery element being clicked upon.
	 * @param  {Boolean} actLikeLink Should we resume the click action or redirect to the href attribute?
	 */
	wpe.updates.confirmChange = function($element, actLikeLink) {
		// Set false as the default.
		var actLikeLink = typeof actLikeLink !== 'undefined' ?  actLikeLink : false;
		if( $element.data('confirmChange') === false ) {
			wpe.apprise(warning, {'confirm':true,'textCancel': "Yes, open my WP Engine Dashboard in a new window.",'textOk':'No thanks, I already did this.' }, function(r) {
				if(r != false) {
					if( wpe.updates.areShiny() ) {
						$element.data('confirmChange', true);
						$element.click();
					} else {
						if( true === actLikeLink ) {
							window.location.href = $element.attr('href');
						} else {
							$element.data('confirmChange', true);
							$element.click();
						}
					}
				} else {
					window.open('https://my.wpengine.com/installs/'+wpe.account+'/backup_points','_blank');
				}
			});
		} else {
			// Reset the button/link state.
			$element.data('confirmChange', false);
		}
	}

})(jQuery);

/**
 * Determines whether query args are present
 *
 * @param  {[type]}  str
 * @return {Boolean}
 */
function has_args(str) {
	var querystring = window.location.href.split('?',2);
	var querystring = querystring[1];
	if ( !querystring ) {
		return false;
	} else {
		if( querystring.indexOf(str) != '-1' )
		{
			return true;
		} else {
			return false;
		}
	}
}

function wpe_deploy_staging() {
	jQuery(function($) {
		$('#deploy-from-staging').slideToggle();
		$('.chzn-select').chosen()
		$('select[name="db_mode"]').change(function() {
			if( $(this).attr('name') == 'db_mode' && $(this).find('option:selected').val() == 'tables') {
				$('p.table-select').slideDown();
			} else {
				$('p.table-select').slideUp();
			}
		});

		$('#submit-deploy').click(function(e) {
			e.preventDefault();
			$('#dfs-response').remove();
			var data = {
				'email'		: $('input[name="email"]').val(),
				'tables'	: $('select[name="tables[]"]').val(),
				'db_mode'	: $('select[name="db_mode"]').val(),
				'action'	: 'wpe-ajax',
				'wpe-action'	: 'deploy-staging'
			}
			$('form#deploy-from-staging').slideUp().after('<div id="dfs-response" class="alert alert-success"><span class="spinner" style="display:inline; float:left; margin: 0 10px 0 0 ; padding:0;"></span>Please wait ..</div>');
			$.post(ajaxurl,data,function(resp) {
				$('#dfs-response').html(resp);
				$.wpeDeploy('start');
			});
		});
	});
}

/**
 * Displays popup
 * http://thrivingkings.com/apprise/
 * DON'T USE THIS. USE TWITTER BOOTSTRAP MODAL INSTEAD ... see deploy from staging for example
 */
wpe.apprise = function (string, args, callback) {
	var $ = jQuery.noConflict();
	var default_args =
		{
		'confirm'		:	false, 		// Ok and Cancel buttons
		'verify'		:	false,		// Yes and No buttons
		'input'			:	false, 		// Text input (can be true or string for default text)
		'animate'		:	false,		// Groovy animation (can true or number, default is 400)
		'textOk'		:	'Ok',		// Ok button default text
		'textCancel'	:	'Cancel',	// Cancel button default text
		'textYes'		:	'Yes',		// Yes button default text
		'textNo'		:	'No',		// No button default text
		'cancelable'		: 	false,
		'options'		: 	false
		}

	if(args)
		{
		for(var index in default_args)
			{ if(typeof args[index] == "undefined") args[index] = default_args[index]; }
		}

	var aHeight = $(document).height();
	var aWidth = $(document).width();
	$('body').append('<div class="appriseOverlay" id="aOverlay"></div>');
	$('.appriseOverlay').css('height', aHeight).css('width', aWidth).fadeIn(100);
	$('body').append('<div class="appriseOuter"></div>');
	$('.appriseOuter').append('<div class="appriseInner"></div>');
	$('.appriseInner').append(string);
	$('.appriseOuter').css("left", ( $(window).width() - $('.appriseOuter').width() ) / 2+$(window).scrollLeft() + "px");
	//add a cancel button
		$(document).on('click','.closeit a', function(e) { e.preventDefault(); $('.appriseOverlay,.appriseOuter').remove(); });
	if(args) {
		if( args['cancelable'] ) {
			$('.appriseOuter').prepend('<div class="closeit"><a href="#">cancel</a></div>');
		}
		if(args['animate'])
			{
			var aniSpeed = args['animate'];
			if(isNaN(aniSpeed)) { aniSpeed = 400; }
			$('.appriseOuter').css('top', '-200px').show().animate({top:"100px"}, aniSpeed);
			}
		else
			{ $('.appriseOuter').css('top', '100px').fadeIn(200); }
		}
	else
		{ $('.appriseOuter').css('top', '100px').fadeIn(200); }


	$('.appriseInner').append('<div class="aButtons"></div>');
	if(args)
		{
		if(args['confirm'] )
			{
			$('.aButtons').append('<button value="ok">'+args['textOk']+'</button>');
			$('.aButtons').append('<button value="cancel">'+args['textCancel']+'</button>');
		}
		else if(args['verify'])
			{
			$('.aButtons').append('<button value="ok">'+args['textYes']+'</button>');
			$('.aButtons').append('<button value="cancel">'+args['textNo']+'</button>');
		}
		else if(typeof(args['options']) == 'function' ) {
			args['options']();
		}
		else if(typeof(args['options']) == 'object')
			{
				for(i = 0; i < args['options'].length; i++) {
					$('.aButtons').append('<button value="'+args['options'][i]['db_mode']+'" >'+args['options'][i]['label']+'</button>');
				}
		}
		else
			{ $('.aButtons').append('<button value="ok">'+args['textOk']+'</button>'); }
		}
	else
		{ $('.aButtons').append('<button value="ok">Ok</button>'); }
	//add in input
	if(args)
	{
	if(args['input'])
		{
		if(typeof(args['input'])=='string')
			{
			$('.appriseInner').append('<div class="aInput"><input type="text" class="aTextbox" t="aTextbox" value="'+args['input']+'" /></div>');
			}
		else if (typeof(args['input']) =='object')
			{
				$(args['input'].before).before('<div class="aInput"><span>'+args['input'].label+'</span><input type="text" class="aTextbox" value="'+args['input'].value+'" /></div>');
			}
		else
			{
				$('.appriseInner').append('<div class="aInput"><input type="text" class="aTextbox" t="aTextbox" /></div>');
				}
			$('.aTextbox').focus();
		}
	}

	$(document).keydown(function(e)
		{
		if($('.appriseOverlay').is(':visible'))
			{
			if(e.keyCode == 13)
				{ $('.aButtons > button[value="ok"]').click(); }
			if(e.keyCode == 27)
				{ $('.aButtons > button[value="cancel"]').click(); }
			}
		});

	var aText = $('.aTextbox').val();
	if(!aText) { aText = false; }
	$('.aTextbox').keyup(function()
		{ aText = $(this).val(); });

	$('.aButtons > button').click(function()
		{
		$('.appriseOverlay').remove();
	$('.appriseOuter').remove();
		if(callback) {
			var wButton = $(this).attr("value");
			if(wButton=='ok') {
				if(args) {
					if(args['input'])
						{ callback(aText); }
					else
						{ callback(true); }
				} else { callback(true); }
			} else if( args['options'] ) {
					return_args = { 'option_val': wButton };
					if( args['input'] ) {
						return_args.text_val = aText;
					}
					callback(return_args);
			} else if(wButton=='cancel')
				{ callback(false); }
			}
		});
}//end apprise
