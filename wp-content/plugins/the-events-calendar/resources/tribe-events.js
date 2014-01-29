/**
 * @file The core file for the events calendar plugin javascript.
 * This file must load on all front facing events pages and be the first file loaded after vendor dependencies.
 * @version 3.0
 */

/**
 * @namespace tribe_ev
 * @since 3.0
 * @desc The tribe_ev namespace that stores all custom functions, data, application state and an empty events object to bind custom events to.
 * This Object Literal namespace loads for all tribe events pages and is by design fully public so that themers can hook in and/or extend anything they want from their own files.
 * @example <caption>Test for tribe_ev in your own js and then run one of our functions.</caption>
 * jQuery(document).ready(function ($) {
 *      if (Object.prototype.hasOwnProperty.call(window, 'tribe_ev')) {
 *          if(tribe_ev.fn.get_category() === 'Cats'){
 *              alert('Meow!');
 *          }
 *      }
 * });
 */

var tribe_ev = window.tribe_ev || {};

/**
 * @define {boolean} tribe_debug
 * @global tribe_debug is used both by closure compiler to strip debug code on min and as a failsafe short circuit if compiler fails to strip all debug strings.
 * @desc Setup safe enhanced console logging. See the link to get the available methods, then prefix with this short circuit: 'tribe_debug && '. tribe_debug is aliased in all tribe js doc readys as 'dbug'.
 * @link http://benalman.com/code/projects/javascript-debug/docs/files/ba-debug-js.html
 * @example <caption>EG: Place this at the very bottom of the doc ready for tribe-events.js. ALWAYS short circuit with 'tribe_debug && ' or 'dbug &&' if aliased as such.</caption> *
 * 		tribe_debug && debug.info('tribe-events.js successfully loaded');
 */

var tribe_debug = true;

/*!
 * this debug code is stripped out by closure compiler so it is not present in the .min versions.
 */

if(tribe_debug){

	/*!
	 * JavaScript Debug - v0.4 - 6/22/2010
	 * http://benalman.com/projects/javascript-debug-console-log/
	 *
	 * Copyright (c) 2010 "Cowboy" Ben Alman
	 * Dual licensed under the MIT and GPL licenses.
	 * http://benalman.com/about/license/
	 *
	 * With lots of help from Paul Irish!
	 * http://paulirish.com/
	 */

	window.debug = (function () {
		var window = this,
			aps = Array.prototype.slice,
			con = window.console,
			that = {},
			callback_func,
			callback_force,
			log_level = 9,
			log_methods = [ 'error', 'warn', 'info', 'debug', 'log' ],
			pass_methods = 'assert clear count dir dirxml exception group groupCollapsed groupEnd profile profileEnd table time timeEnd trace'.split(' '),
			idx = pass_methods.length,
			logs = [];

		while (--idx >= 0) {
			(function (method) {

				that[ method ] = function () {
					log_level !== 0 && con && con[ method ]
					&& con[ method ].apply(con, arguments);
				}

			})(pass_methods[idx]);
		}

		idx = log_methods.length;
		while (--idx >= 0) {
			(function (idx, level) {

				that[ level ] = function () {
					var args = aps.call(arguments),
						log_arr = [ level ].concat(args);

					logs.push(log_arr);
					exec_callback(log_arr);

					if (!con || !is_level(idx)) {
						return;
					}

					con.firebug ? con[ level ].apply(window, args)
						: con[ level ] ? con[ level ](args)
						: con.log(args);
				};

			})(idx, log_methods[idx]);
		}

		function exec_callback(args) {
			if (callback_func && (callback_force || !con || !con.log)) {
				callback_func.apply(window, args);
			}
		}

		that.setLevel = function (level) {
			log_level = typeof level === 'number' ? level : 9;
		};

		function is_level(level) {
			return log_level > 0
				? log_level > level
				: log_methods.length + log_level <= level;
		}

		that.setCallback = function () {
			var args = aps.call(arguments),
				max = logs.length,
				i = max;

			callback_func = args.shift() || null;
			callback_force = typeof args[0] === 'boolean' ? args.shift() : false;

			i -= typeof args[0] === 'number' ? args.shift() : max;

			while (i < max) {
				exec_callback(logs[i++]);
			}
		};

		return that;
	})();

	if (Object.prototype.hasOwnProperty.call(window, 'tribe_ev')) {
		tribe_ev.diagnostics = {
			init: []
		};
	}
}

/**
 * @global
 * @desc Test for localstorage support. Returns false if not available and tribe_storage as a method if true.
 * @example
 * if (tribe_storage) {
 *      tribe_storage.setItem('cats', 'hairball');
 *      tribe_storage.getItem('cats');
 * }
 */

var tribe_storage, t_fail, t_uid;
try {
	t_uid = new Date;
	(tribe_storage = window.localStorage).setItem(t_uid, t_uid);
	t_fail = tribe_storage.getItem(t_uid) != t_uid;
	tribe_storage.removeItem(t_uid);
	t_fail && (tribe_storage = false);
} catch (e) {}

/**
 * @external "jQuery.fn"
 * @desc The jQuery plugin namespace.
 */


(function ($, undefined) {
	/**
	 * @function external:"jQuery.fn".tribe_clear_form
	 * @since 3.0
	 * @desc Clear a forms inputs with jquery.
	 * @example <caption>Clear a form with the forms id as a selector.</caption>
	 * $('#myForm').tribe_clear_form();
	 */
	$.fn.tribe_clear_form = function () {
		return this.each(function () {
			var type = this.type, tag = this.tagName.toLowerCase();
			if (tag == 'form')
				return $(':input', this).tribe_clear_form();
			if (type == 'text' || type == 'password' || tag == 'textarea')
				this.value = '';
			else if (type == 'checkbox' || type == 'radio')
				this.checked = false;
			else if (tag == 'select')
				this.selectedIndex = 0;
		});
	};
	/**
	 * @function external:"jQuery.fn".tribe_has_attr
	 * @since 3.0
	 * @desc Check if a given element has an attribute.
	 * @example if($('#myLink').tribe_has_attr('data-cats')) {true} else {false}
	 */
	$.fn.tribe_has_attr = function (name) {
		return this.attr(name) !== undefined;
	};
	/**
	 * @function external:"jQuery.fn".tribe_spin
	 * @since 3.0
	 * @desc Shows loading spinners for events ajax interactions.
	 * @example $('#myElement').tribe_spin();
	 */
	$.fn.tribe_spin = function () {
		var $loadingImg = $('.tribe-events-ajax-loading:first').clone().addClass('tribe-events-active-spinner');
		$loadingImg.prependTo('#tribe-events-content');
		$(this).addClass('tribe-events-loading').css('opacity', .25)
	};

	if ( "undefined" !== typeof $.fn.datepicker ) {
		var datepicker = $.fn.datepicker.noConflict();
		$.fn.bootstrapDatepicker = datepicker;
	}

	if ( "undefined" !== typeof tribe_bootstrap_datepicker_strings && tribe_bootstrap_datepicker_strings.dates != null )
		$.fn.bootstrapDatepicker.dates['en'] = tribe_bootstrap_datepicker_strings.dates;

})(jQuery);

(function (window, document, $, dbug, undefined) {
	/**
	 * @namespace tribe_ev
	 * @since 3.0
	 * @desc tribe_ev.fn namespace stores all the custom functions used throughout the core events plugin.
	 */
	tribe_ev.fn = {
		/**
		 * @function tribe_ev.fn.current_date
		 * @since 3.0
		 * @desc tribe_ev.fn.current_date simply gets the current date in javascript and formats it to yyyy-mm-dd for use were needed.
		 * @example var right_now = tribe_ev.fn.current_date();
		 */
		current_date: function () {
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth() + 1;
			var yyyy = today.getFullYear();
			if (dd < 10) {
				dd = '0' + dd
			}
			if (mm < 10) {
				mm = '0' + mm
			}
			return yyyy + '-' + mm + '-' + dd;
		},
		/**
		 * @function tribe_ev.fn.disable_inputs
		 * @since 3.0
		 * @desc tribe_ev.fn.disable_inputs disables all inputs of a specified type inside a parent element, and also disables select2 selects if it discovers any.
		 * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of inputs you would like disabled.
		 * @example <caption>Disable all inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.disable_inputs( '#myForm', 'input, select' );
		 */
		disable_inputs: function (parent, type) {
			$(parent).find(type).prop('disabled', true);
			if ($(parent).find('.select2-container').length) {
				$(parent).find('.select2-container').each(function () {
					var s2_id = $(this).attr('id');
					var $this = $('#' + s2_id);
					$this.select2("disable");
				});
			}
		},
		/**
		 * @function tribe_ev.fn.disable_empty
		 * @since 3.0
		 * @desc tribe_ev.fn.disable_empty disables all empty inputs of a specified type inside a parent element.
		 * @param {String} parent The top level element you would like all empty child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of empty inputs you would like disabled.
		 * @example <caption>Disable all empty inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.disable_empty( '#myForm', 'input, select' );
		 */
		disable_empty: function (parent, type) {
			$(parent).find(type).each(function () {
				if ($(this).val() === '') {
					$(this).prop('disabled', true);
				}
			});
		},
		/**
		 * @function tribe_ev.fn.enable_inputs
		 * @since 3.0
		 * @desc tribe_ev.fn.enable_inputs enables all inputs of a specified type inside a parent element, and also enables select2 selects if it discovers any.
		 * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of inputs you would like enabled.
		 * @example <caption>Enable all inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.enable_inputs( '#myForm', 'input, select' );
		 */
		enable_inputs: function (parent, type) {
			$(parent).find(type).prop('disabled', false);
			if ($(parent).find('.select2-container').length) {
				$(parent).find('.select2-container').each(function () {
					var s2_id = $(this).attr('id');
					var $this = $('#' + s2_id);
					$this.select2("enable");
				});
			}
		},
		/**
		 * @function tribe_ev.fn.get_base_url
		 * @since 3.0
		 * @desc tribe_ev.fn.get_base_url can be used on any events view to get the base_url for that view, even when on a category subset for that view.
		 * @returns {String} Either an empty string or base url if data-baseurl is found on #tribe-events-header.
		 * @example var base_url = tribe_ev.fn.get_base_url();
		 */
		get_base_url: function () {
			var base_url = '',
				$event_header = $('#tribe-events-header');
			if ($event_header.length){
				base_url = $event_header.data('baseurl');
			}
			return base_url;
		},
		/**
		 * @function tribe_ev.fn.get_category
		 * @since 3.0
		 * @desc tribe_ev.fn.get_category can be used on any events view to get the category for that view.
		 * @returns {String} Either an empty string or category slug if data-category is found on #tribe-events.
		 * @example var cat = tribe_ev.fn.get_category();
		 */
		get_category: function () {
			if (tribe_ev.fn.is_category())
				return $('#tribe-events').data('category');
			else
				return '';
		},
		/**
		 * @function tribe_ev.fn.get_day
		 * @since 3.0
		 * @desc tribe_ev.fn.get_day can be used to check the event bar for a day value that was set by the user when using the datepicker.
		 * @returns {String|Number} Either an empty string or day number if #tribe-bar-date-day has a val() set by user interaction.
		 * @example var day = tribe_ev.fn.get_day();
		 */
		get_day: function () {
			var dp_day = '';
			if ($('#tribe-bar-date').length) {
				dp_day = $('#tribe-bar-date-day').val();
			}
			dbug && debug.info('TEC Debug: tribe_ev.fn.get_day returned this date: "' + dp_day + '".');
			return dp_day;
		},
		/**
		 * @function tribe_ev.fn.get_params
		 * @since 3.0
		 * @desc tribe_ev.fn.get_params returns the params of the current document.url.
		 * @returns {String} any url params sans "?".
		 * @example var params = tribe_ev.fn.get_params();
		 */
		get_params: function () {
			return location.search.substr(1);
		},
		/**
		 * @function tribe_ev.fn.get_url_param
		 * @since 3.0
		 * @desc tribe_ev.fn.get_url_param returns the value of a passed param name if set.
		 * @param {String} name The name of the url param value desired.
		 * @returns {String|Null} the value of a parameter if set or null if not.
		 * @example var param = tribe_ev.fn.get_url_param('category');
		 */
		get_url_param: function (name) {
			return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
		},
		/**
		 * @function tribe_ev.fn.in_params
		 * @since 3.0
		 * @desc tribe_ev.fn.in_params returns the value of a passed param name if set.
		 * @param {String} params The parameter string you would like to search for a term.
		 * @param {String} term The name of the url param value you are checking for.
		 * @returns {Number} Returns index if term is present in params, or -1 if not found.
		 * @example
		 * if (tribe_ev.fn.in_params(tribe_ev.data.params, "tabby") >= 0)){
         *     // tabby is in params
         * } else {
         *     // tabby is not in params
         * }
		 */
		in_params: function (params, term) {
			return params.toLowerCase().indexOf(term);
		},
		/**
		 * @function tribe_ev.fn.is_category
		 * @since 3.0
		 * @desc tribe_ev.fn.is_category test for whether the view is a category subpage in the pretty permalink system.
		 * @returns {Boolean} Returns true if category page, false if not.
		 * @example if (tribe_ev.fn.is_category()){ true } else { false }
		 */
		is_category: function () {
			var $tribe_events = $('#tribe-events');
			return ($tribe_events.length && $tribe_events.tribe_has_attr('data-category') && $tribe_events.data('category') !== '') ? true : false;
		},
		/**
		 * @function tribe_ev.fn.parse_string
		 * @since 3.0
		 * @desc tribe_ev.fn.parse_string converts a string to an object.
		 * @param {String} string The string to be converted.
		 * @returns {Object} Returns mapped object.
		 * @example if (tribe_ev.fn.is_category()){ true } else { false }
		 */
		parse_string: function (string) {
			var map = {};
			string.replace(/([^&=]+)=?([^&]*)(?:&+|$)/g, function (match, key, value) {
				(map[key] = map[key] || []).push(value);
			});
			dbug && debug.info('TEC Debug: tribe_ev.fn.parse_string returned this map:', map);
			return map;
		},
		/**
		 * @function tribe_ev.fn.pre_ajax
		 * @since 3.0
		 * @desc tribe_ev.fn.pre_ajax allows for functions to be executed before ajax begins.
		 * @param {Function} callback The callback function, expected to be an ajax function for one of our views.
		 */
		pre_ajax: function (callback) {
			if (callback && typeof( callback ) === "function") {
				callback();
			}
		},
		/**
		 * @function tribe_ev.fn.serialize
		 * @since 3.0
		 * @desc tribe_ev.fn.serialize serializes the passed input types. Enable/disable stack in place to protect inputs during process, especially for live ajax mode.
		 * @param {String} form The form element.
		 * @param {String} type The input types to be serialized.
		 * @returns {String} Returns a param string of populated inputs.
		 * @example tribe_ev.fn.serialize('#myForm', 'input, select');
		 */
		serialize: function (form, type) {
			tribe_ev.fn.enable_inputs(form, type);
			tribe_ev.fn.disable_empty(form, type);
			var params = $(form).serialize();
			tribe_ev.fn.disable_inputs(form, type);
			dbug && params && debug.info('TEC Debug: tribe_ev.fn.serialize returned these params: "' + params);
			return params;
		},
		/**
		 * @function tribe_ev.fn.set_form
		 * @since 3.0
		 * @desc tribe_ev.fn.set_form takes a param string and sets a forms inputs to the values received. Extended in the Query Filters plugin.
		 * @param {String} params The params to be looped over and applied to the named input. Needed for back button browser history when forms are outside of the ajax area.
		 * @example <caption>Set all inputs in a form(s) to the values in a param string retrieved from the history object on popstate.</caption>
		 * $(window).on('popstate', function (event) {
		 *		var state = event.originalEvent.state;
		 *		if (state) {
		 *		 	tribe_ev.state.params = state.tribe_params;
		 *		 	// do something magical to restore query state like ajax, then set the forms to match the history state like so:
		 *			tribe_ev.fn.set_form(tribe_ev.state.params);
		 *		}
		 *	});
		 */
		set_form: function (params) {
			var $body = $('body'),
				$tribe_bar = $('#tribe-bar-form');

			$body.addClass('tribe-reset-on');

			if ($tribe_bar.length) {
				$tribe_bar.tribe_clear_form();
			}

			params = tribe_ev.fn.parse_string(params);

			$.each(params, function (key, value) {
				if (key !== 'action') {
					var name = decodeURI(key),
						$target = '';
					if (value.length === 1) {
						if ($('[name="' + name + '"]').is('input[type="text"], input[type="hidden"]')) {
							$('[name="' + name + '"]').val(value);
						} else if ($('[name="' + name + '"][value="' + value + '"]').is(':checkbox, :radio')) {
							$('[name="' + name + '"][value="' + value + '"]').prop("checked", true);
						} else if ($('[name="' + name + '"]').is('select')) {
							$('select[name="' + name + '"] option[value="' + value + '"]').attr('selected', true);
						}
					} else {
						for (var i = 0; i < value.length; i++) {
							$target = $('[name="' + name + '"][value="' + value[i] + '"]');
							if ($target.is(':checkbox, :radio')) {
								$target.prop("checked", true);
							} else {
								$('select[name="' + name + '"] option[value="' + value[i] + '"]').attr('selected', true);
							}
						}
					}
				}
			});

			$body.removeClass('tribe-reset-on');

			dbug && debug.info('TEC Debug: tribe_ev.fn.set_form fired these params: "' + params);
		},
		/**
		 * @function tribe_ev.fn.setup_ajax_timer
		 * @since 3.0
		 * @desc tribe_ev.fn.setup_ajax_timer is a simple function to add a delay to the execution of a passed callback function, in our case ajax hence the name.
		 * @param {Function} callback Used to delay ajax execution when in live ajax mode.
		 * @example <caption>Run some crazy ajax.</caption>
		 * tribe_ev.fn.setup_ajax_timer( function() {
		 *		run_some_crazy_ajax();
		 * });
		 */
		setup_ajax_timer: function (callback) {
			var timer = 500;
			clearTimeout(tribe_ev.state.ajax_timer);
			if (!tribe_ev.tests.reset_on()) {
				tribe_ev.state.ajax_timer = setTimeout(function () {
					callback();
				}, timer);
				dbug && debug.info('TEC Debug: tribe_ev.fn.setup_ajax_timer fired with a timeout of "' + timer + '" ms');
			}
		},
		/**
		 * @function tribe_ev.fn.snap
		 * @since 3.0
		 * @desc tribe_ev.fn.snap uses jquery to bind a handler to a trigger_parent which uses bubbling of a click event from the trigger to position the document to the passed container. Has an offset of -120 px to get some breathing room.
		 * @param {String} container the jquery selector to send the document to.
		 * @param {String} trigger_parent the persistent element to bind the handler to.
		 * @param {String} trigger the trigger for the click event
		 * @example <caption>"Snap" the document 120 px above the tribe bar when a footer nav link is clicked.</caption>
		 * 		tribe_ev.fn.snap('#tribe-bar-form', '#tribe-events', '#tribe-events-footer a');
		 */
		snap: function (container, trigger_parent, trigger) {
			$(trigger_parent).on('click', trigger, function (e) {
				e.preventDefault();
				$('html, body').animate({scrollTop: $(container).offset().top - 120}, {duration: 0});
			});
		},
		/**
		 * @function tribe_ev.fn.tooltips
		 * @since 3.0
		 * @desc tribe_ev.fn.tooltips binds the event handler that covers all tooltip hover events for the various views. Extended in tribe-events-pro.js for the pro views. One of the reasons both these files must load FIRST in the tribe events js stack at all times.
		 * @example <caption>It's really not that hard... Get yourself inside a doc ready and...</caption>
		 * 		tribe_ev.fn.tooltips();
		 */
		tooltips: function () {

			$('#tribe-events').on('mouseenter', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring',function () {

				var bottomPad = 0,
					$this = $(this),
					$body = $('body');

				if ($body.hasClass('events-gridview')) { // Cal View Tooltips
					bottomPad = $this.find('a').outerHeight() + 18;
				} else if ($body.is('.single-tribe_events, .events-list, .tribe-events-day')) { // Single/List View Recurring Tooltips
					bottomPad = $this.outerHeight() + 12;
				} else if ($body.is('.tribe-events-photo')) { // Photo View
					bottomPad = $this.outerHeight() + 10;
				}

				// Widget Tooltips
				if ($this.parents('.tribe-events-calendar-widget').length) {
					bottomPad = $this.outerHeight() - 6;
				}
				if (!$body.hasClass('tribe-events-week')) {
					$this.find('.tribe-events-tooltip').css('bottom', bottomPad).show();
				}

			}).on('mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function () {
					$(this).find('.tribe-events-tooltip').stop(true, false).fadeOut(200);
				});
		},
		/**
		 * @function tribe_ev.fn.update_picker
		 * @since 3.0
		 * @desc tribe_ev.fn.update_picker Updates the custom bootstrapDatepicker if it and the event bar is present, or only the event bar input if it is present.
		 * @param {String} date The date string to update picker or input with.
		 * @example <caption>Bind a handler that updates the datepicker if present with the date, in this case harvested from a data attribute on the link.</caption>
		 * $('#tribe-events').on('click', '.tribe-events-nav-previous a', function (e) {
		 *     e.preventDefault();
		 *     var $this = $(this);
		 *     tribe_ev.state.date = $this.attr("data-day");
		 *     tribe_ev.fn.update_picker(tribe_ev.state.date);
		 * });
		 */
		update_picker: function (date) {
			var $bar_date = $("#tribe-bar-date");
			if ($().bootstrapDatepicker && $bar_date.length) {
				// for ie8 and under
				if (window.attachEvent && !window.addEventListener) {
					$bar_date.bootstrapDatepicker("remove");
					$bar_date.val('');
					$bar_date.bootstrapDatepicker(tribe_ev.data.datepicker_opts);
				}
				$bar_date.bootstrapDatepicker("setDate", date);
				dbug && debug.info('TEC Debug: tribe_ev.fn.update_picker sent "' + date + '" to the boostrapDatepicker');
			} else if ($bar_date.length) {
				$bar_date.val(date);
				dbug && debug.warn('TEC Debug: tribe_ev.fn.update_picker sent "' + date + '" to ' + $bar_date);
			} else {

				dbug && debug.warn('TEC Debug: tribe_ev.fn.update_picker couldnt send "' + date + '" to any object.');
			}
		},
		/**
		 * @function tribe_ev.fn.url_path
		 * @since 3.0
		 * @desc tribe_ev.fn.url_path strips query vars from a url passed to it using js split on the ? character.
		 * @param {String} url The url to remove all vars from.
		 * @returns {String} Returns a url devoid of any query vars.
		 * @example <caption>Get the query var free version of an href attribute.</caption>
		 * $('#tribe-events').on('click', '.tribe-events-nav-next', function (e) {
		 *		e.preventDefault();
		 *		tribe_ev.data.cur_url = tribe_ev.fn.url_path($(this).attr('href'));
		 * });
		 */
		url_path: function (url) {
			return url.split("?")[0];
		}
	};

	/**
	 * @namespace tribe_ev
	 * @since 3.0
	 * @desc tribe_ev.tests namespace stores all the custom tests used throughout the core events plugin.
	 */

	tribe_ev.tests = {
		/**
		 * @function tribe_ev.tests.live_ajax
		 * @since 3.0
		 * @desc tribe_ev.tests.live_ajax tests if live ajax is enabled in the events settings tab by checking the data attribute data-live_ajax on #tribe-events in the front end.
		 * @example <caption>Very easy test to use. In a doc ready:</caption>
		 * if (tribe_ev.tests.live_ajax()) {
		 *		// live ajax is on
		 * ) else {
		 *     // live ajax is off
		 * }
		 */
		live_ajax: function () {
			var $tribe_events = $('#tribe-events');
			return ($tribe_events.length && $tribe_events.tribe_has_attr('data-live_ajax') && $tribe_events.data('live_ajax') == '1') ? true : false;
		},
		/**
		 * @function tribe_ev.tests.map_view
		 * @since 3.0
		 * @desc tribe_ev.tests.map_view test if we are on map view.
		 * @example <caption>Test if we are on map view</caption>
		 * if (tribe_ev.tests.map_view()) {
		 *		// we are on map view
		 * )
		 */
		map_view: function () {
			return ( typeof GeoLoc !== 'undefined' && GeoLoc.map_view ) ? true : false;
		},
		/**
		 * @function tribe_ev.tests.no_bar
		 * @since 3.0.4
		 * @desc tribe_ev.tests.has_bar tests if the events bar is enabled on the front end.
		 * @example <caption>Very easy test to use. In a doc ready:</caption>
		 * if (tribe_ev.tests.no_bar()) {
		 *		// no event bar
		 * ) else {
		 *     // has event bar
		 * }
		 */
		no_bar: function(){
			return $('body').is('.tribe-bar-is-disabled');
		},
		/**
		 * @type Boolean tribe_ev.tests.pushstate
		 * @since 3.0
		 * @desc tribe_ev.tests.pushstate checks if the history object is available safely and returns true or false.
		 * @example <caption>Execute an if else on the presence of pushstate</caption>
		 * if (tribe_ev.tests.pushstate) {
		 *		// pushstate is available
		 * ) else {
		 *     // pushstate is not available
		 * }
		 */
		pushstate: !!(window.history && history.pushState),
		/**
		 * @function tribe_ev.tests.reset_on
		 * @since 3.0
		 * @desc tribe_ev.tests.reset_on tests if any other function is currently disabling a tribe ajax function.
		 * @example <caption>In another handler that will be triggering a tribe ajax function:</caption>
		 * if (!tribe_ev.tests.reset_on()) {
		 *		// reset is not occuring so lets run some other ajax
		 * )
		 */
		reset_on: function () {
			return $('body').is('.tribe-reset-on');
		},
		/**
		 * @function tribe_ev.tests.starting_delim
		 * @since 3.0
		 * @desc tribe_ev.tests.starting_delim is used by events url forming functions to determine if "?" is already present. It then sets the delimiter for the next part of the url concatenation to "?" if not found and "&" if it is.
		 * @example <caption>Test and set delimiter during url string concatenation.</caption>
		 * 		tribe_ev.state.cur_url += tribe_ev.tests.starting_delim + tribe_ev.state.url_params;
		 */
		starting_delim: function () {
			return tribe_ev.state.cur_url.indexOf('?') != -1 ? '&' : '?';
		}
	};

	/**
	 * @namespace tribe_ev
	 * @since 3.0
	 * @desc tribe_ev.data stores information that is sometimes used internally and also contains useful data for themers.
	 */

	tribe_ev.data = {
		ajax_response: {},
		base_url: '',
		cur_url: tribe_ev.fn.url_path(document.URL),
		cur_date: tribe_ev.fn.current_date(),
		datepicker_opts: {},
		initial_url: tribe_ev.fn.url_path(document.URL),
		params: tribe_ev.fn.get_params()
	};

	/**
	 * @namespace tribe_ev
	 * @since 3.0
	 * @desc tribe_ev.events is an empty object used to attach all tribe custom events to.
	 */

	tribe_ev.events = {};

	/**
	 * @namespace tribe_ev
	 * @since 3.0
	 * @desc tribe_ev.state is mainly used in events ajax operations, though a few variables are set on doc ready.
	 */

	tribe_ev.state = {
		ajax_running: false,
		ajax_timer: 0,
		category: '',
		date: '',
		do_string: false,
		filters: false,
		filter_cats: false,
		initial_load: true,
		paged: 1,
		page_title: '',
		params: {},
		popping: false,
		pushstate: true,
		pushcount: 0,
		recurrence: false,
		url_params: {},
		view: '',
		view_target: ''
	};

})(window, document, jQuery, tribe_debug);

(function (window, document, $, td, te, tf, ts, tt, dbug) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */



	$(document).ready(function () {

		dbug && debug.info('TEC Debug: Tribe Events JS init, Init Timer started from tribe-events.js.');

		var $tribe_events = $('#tribe-events'),
			$tribe_events_header = $('#tribe-events-header');

		$tribe_events.removeClass('tribe-no-js');
		ts.category = tf.get_category();
		td.base_url = tf.get_base_url();
		ts.page_title = document.title;

		var tribe_display = tf.get_url_param('tribe_event_display');

		if (tribe_display) {
			ts.view = tribe_display;
		} else if ($tribe_events_header.length && $tribe_events_header.tribe_has_attr('data-view')) {
			ts.view = $tribe_events_header.data('view');
		}

		ts.view && dbug && debug.time('Tribe JS Init Timer');

		/* Let's hide the widget calendar if we find more than one instance */
		$(".tribe-events-calendar-widget").not(":eq(0)").hide();

		tf.tooltips();

		//remove border on list view event before month divider
		function list_find_month_last_event() {
			if ($('.tribe-events-list').length) {
				$('.tribe-events-list-separator-month').prev('.vevent').addClass('tribe-event-end-month');
			}
		}
		list_find_month_last_event();
		// remove events header subnav pagination if no results
		if ($('.tribe-events-list .tribe-events-notices').length) {
			$('#tribe-events-header .tribe-events-sub-nav').empty();
		}

		//remove border on list view event before month divider
		if ($('.tribe-events-list').length) {
			$('.tribe-events-list-separator-month').prev('.vevent').addClass('tribe-event-end-month');
		}

		// ajax complete function to remove active spinner
		$(te).on( 'tribe_ev_ajaxSuccess', function() {
			$('.tribe-events-active-spinner').remove();
			list_find_month_last_event();
		});

		if(dbug){
			debug.groupCollapsed('TEC Debug: Browser and events settings information:');
			debug.log('User agent reported as: "' + navigator.userAgent);
			debug.log('Live ajax returned its state as: "' + tt.live_ajax());
			ts.view && debug.log('Tribe js detected the view to be: "' + ts.view);
			debug.log('Supports pushstate: "' + tt.pushstate);
			debug.groupEnd();
			debug.info('TEC Debug: tribe-events.js successfully loaded');
		}
	});
})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);
