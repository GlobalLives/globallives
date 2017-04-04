/**
 * Processes bulk smushing
 *
 * @author Umesh Kumar <umeshsingla05@gmail.com>
 *
 */
/**@todo: Use Element tag for all the class selectors **/

var WP_Smush = WP_Smush || {};

/**
 * Show/hide the progress bar for Smushing/Restore/SuperSmush
 *
 * @param cur_ele
 * @param txt Message to be displayed
 * @param state show/hide
 */
var progress_bar = function (cur_ele, txt, state) {

    //Update Progress bar text and show it
    var progress_button = cur_ele.parents().eq(1).find('.wp-smush-progress');

    if ('show' == state) {
        progress_button.find('span').html(txt);
        progress_button.removeClass('hidden');
    } else {
        progress_button.find('span').html(wp_smush_msgs.all_done);
        progress_button.hide();
    }
};

var dash_offset = function (percent) {
    //Get the dasharray value
    var dasharray = jQuery('.wp-smush-svg-circle-progress').attr('stroke-dasharray');
    return dasharray - ( dasharray * percent );
}

var update_dashoffset = function (stats) {
    var total = stats.total.length;
    if (total > 0) {
        var dashoffset = dash_offset(stats.smushed / total);
        var circle_progress = jQuery('.wp-smush-svg-circle-progress');
        if (typeof dashoffset != 'undefined' && circle_progress.length) {
            circle_progress.css({'stroke-dashoffset': dashoffset});
        }
    }
};

var membership_validity = function (data) {
    var member_validity_notice = jQuery('#wp-smush-invalid-member');

    //Check for Membership warning
    if ('undefined' != typeof ( data.show_warning ) && member_validity_notice.length > 0) {
        if (data.show_warning) {
            member_validity_notice.show();
        } else {
            member_validity_notice.hide();
        }
    }
};
/**
 * Resize Background width
 */
var resize_width = function () {
    var width = jQuery('.wp-smush-pro-for-free').width();
    if ('undefined' != typeof ( width ) && 500 < width) {
        jQuery('.wpmud .wp-smush-pro-adv').css({'background-size': '500px'});
    } else {
        jQuery('.wpmud .wp-smush-pro-adv').css({'background-size': '90%'});
    }
};

var remove_element = function (el, timeout) {
    if (typeof timeout == 'undefined') {
        timeout = 100;
    }
    el.fadeTo(timeout, 0, function () {
        el.slideUp(timeout, function () {
            el.remove();
        });
    });
};

jQuery(function ($) {
    var smushAddParams = function (url, data) {
        if (!$.isEmptyObject(data)) {
            url += ( url.indexOf('?') >= 0 ? '&' : '?' ) + $.param(data);
        }

        return url;
    };
    // url for smushing
    WP_Smush.errors = [];
    WP_Smush.timeout = wp_smushit_data.timeout;
    /**
     * Checks for the specified param in URL
     * @param arg
     * @returns {*}
     */
    WP_Smush.geturlparam = function (arg) {
        var $sPageURL = window.location.search.substring(1);
        var $sURLVariables = $sPageURL.split('&');

        for (var i = 0; i < $sURLVariables.length; i++) {
            var $sParameterName = $sURLVariables[i].split('=');
            if ($sParameterName[0] == arg) {
                return $sParameterName[1];
            }
        }
    };

    WP_Smush.Smush = function ($button, bulk, smush_type) {
        var self = this;
        var skip_resmush = $button.data('smush');
        //If smush attribute is not defined, Need not skip resmush ids
        skip_resmush = ( ( typeof skip_resmush == typeof undefined ) || skip_resmush == false ) ? false : true;

        this.init = function () {
            this.$button = $($button[0]);
            this.is_bulk = typeof bulk ? bulk : false;
            this.url = ajaxurl;
            this.$log = $(".smush-final-log");
            this.deferred = jQuery.Deferred();
            this.deferred.errors = [];

            //If button has resmush class, and we do have ids that needs to resmushed, put them in the list
            this.ids = wp_smushit_data.resmush.length > 0 && !skip_resmush ? ( wp_smushit_data.unsmushed.length > 0 ? wp_smushit_data.resmush.concat(wp_smushit_data.unsmushed) : wp_smushit_data.resmush ) : wp_smushit_data.unsmushed;

            this.is_bulk_resmush = wp_smushit_data.resmush.length > 0 && !skip_resmush ? true : false;

            this.$status = this.$button.parent().find('.smush-status');

            //Added for NextGen support
            this.smush_type = typeof smush_type ? smush_type : false;
            this.single_ajax_suffix = this.smush_type ? 'smush_manual_nextgen' : 'wp_smushit_manual';
            this.bulk_ajax_suffix = this.smush_type ? 'wp_smushit_nextgen_bulk' : 'wp_smushit_bulk';
            this.url = this.is_bulk ? smushAddParams(this.url, {action: this.bulk_ajax_suffix}) : smushAddParams(this.url, {action: this.single_ajax_suffix});
        };

        /** Send Ajax request for smushing the image **/
        WP_Smush.ajax = function (is_bulk_resmush, $id, $send_url, $getnxt, nonce) {
            "use strict";
            var param = {
                is_bulk_resmush: is_bulk_resmush,
                attachment_id: $id,
                get_next: $getnxt,
                _nonce: nonce
            };
            param = jQuery.param(param);
            return $.ajax({
                type: "GET",
                data: param,
                url: $send_url,
                timeout: WP_Smush.timeout,
                dataType: 'json'
            });
        };

        //Show loader in button for single and bulk smush
        this.start = function () {

            this.$button.attr('disabled', 'disabled');
            this.$button.addClass('wp-smush-started');

            this.bulk_start();
            this.single_start();
        };

        this.bulk_start = function () {
            if (!this.is_bulk) return;

            //Hide the Bulk Div
            $('.wp-smush-bulk-wrapper').hide();

            //Show the Progress Bar
            $('.bulk-smush-wrapper .wp-smush-bulk-progress-bar-wrapper').show();

            //Remove any Global Notices if there
            $('.wp-smush-notice.wp-smush-resmush-message').remove();
        };

        this.single_start = function () {
            if (this.is_bulk) return;
            this.show_loader();
            this.$status.removeClass("error");
        };

        this.enable_button = function () {
            this.$button.prop("disabled", false);
            //For Bulk process, Enable other buttons
            $('button.wp-smush-all').removeAttr('disabled');
            $('button.wp-smush-scan').removeAttr('disabled');
        };

        this.show_loader = function () {
            progress_bar(this.$button, wp_smush_msgs.smushing, 'show');
        };

        this.hide_loader = function () {
            progress_bar(this.$button, wp_smush_msgs.smushing, 'hide');
        };

        this.single_done = function () {
            if (this.is_bulk) return;

            this.hide_loader();
            this.request.done(function (response) {
                if (typeof response.data != 'undefined') {
                    //Append the smush stats or error
                    self.$status.html(response.data);

                    //Check whether to show membership validity notice or not
                    membership_validity(response.data);

                    if (response.success && response.data !== "Not processed") {
                        self.$status.removeClass('hidden');
                        self.$button.parent().removeClass('unsmushed').addClass('smushed');
                        self.$button.remove();
                    } else {
                        self.$status.addClass("error");
                        self.$status.html(response.data.error_msg);
                        self.$status.show();
                    }
                    if (response.data.status) {
                        self.$status.html(response.data.status);
                    }
                    //Check if stats div exists
                    var parent = self.$status.parent();
                    var stats_div = parent.find('.smush-stats-wrapper');
                    if ('undefined' != stats_div && stats_div.length) {
                        stats_div.replaceWith(response.data.stats);
                    } else {
                        parent.append(response.data.stats);
                    }
                }
                self.enable_button();
            }).error(function (response) {
                self.$status.html(response.data);
                self.$status.addClass("error");
                self.enable_button();
            });

        };

        /** After the Bulk Smushing has been Finished **/
        this.bulk_done = function () {
            if (!this.is_bulk) return;

            //Enable the button
            this.enable_button();

            //Show Notice
            if (self.ids.length == 0) {
                $('.bulk-smush-wrapper .wp-smush-all-done').show();
                $('.wp-smush-bulk-wrapper').hide();
            } else {
                if ($('.bulk-smush-wrapper .wp-smush-resmush-notice').length > 0) {
                    $('.bulk-smush-wrapper .wp-smush-resmush-notice').show();
                } else {
                    $('.bulk-smush-wrapper .wp-smush-remaining').show();
                }
                $('.wp-smush-bulk-wrapper').show();
            }

            //Hide the Progress Bar
            $('.wp-smush-bulk-progress-bar-wrapper').hide();

            //Enable Resmush and scan button
            $('.wp-resmush.wp-smush-action, .wp-smush-scan').removeAttr('disabled');
        };

        this.is_resolved = function () {
            "use strict";
            return this.deferred.state() === "resolved";
        };

        this.free_exceeded = function () {
            //Hide the Progress bar and show the Bulk smush wrapper
            $('.wp-smush-bulk-progress-bar-wrapper').hide();

            if (self.ids.length > 0) {
                //Show Bulk wrapper
                $('.wp-smush-bulk-wrapper ').show();
            } else {
                $('.wp-smush-notice.wp-smush-all-done').show();
            }
        };

        this.update_remaining_count = function () {
            if (this.is_bulk_resmush) {
                //ReSmush Notice
                if ($('.wp-smush-resmush-notice .wp-smush-remaining-count').length && 'undefined' != typeof self.ids) {
                    $('.wp-smush-resmush-notice .wp-smush-remaining-count').html(self.ids.length);
                }
            } else {
                //Smush Notice
                if ($('.bulk-smush-wrapper .wp-smush-remaining-count').length && 'undefined' != typeof self.ids) {
                    $('.bulk-smush-wrapper .wp-smush-remaining-count').html(self.ids.length);
                }
            }
        }

        this.update_progress = function (_res) {
            //If not bulk
            if (!this.is_bulk_resmush && !this.is_bulk) {
                return;
            }

            var progress = '';

            if (!this.is_bulk_resmush) {
                if (_res && ( 'undefined' == typeof _res.data || 'undefined' == typeof _res.data.stats )) {
                    return;
                }
                //handle progress for normal bulk smush
                progress = ( _res.data.stats.smushed / _res.data.stats.total.length ) * 100;
            } else {
                //If the Request was successful, Update the progress bar
                if (_res.success) {
                    //Handle progress for Super smush progress bar
                    if (wp_smushit_data.resmush.length > 0) {
                        //Update the Count
                        $('.wp-smush-images-remaining').html(wp_smushit_data.resmush.length);
                    } else if (wp_smushit_data.resmush.length == 0 && this.ids.length == 0) {
                        //If all images are resmushed, show the All Smushed message

                        //Show All Smushed
                        $('.bulk-resmush-wrapper .wp-smush-all-done').removeClass('hidden');

                        //Hide Everything else
                        $('.wp-smush-resmush-wrap, .wp-smush-bulk-progress-bar-wrapper').hide();
                    }
                }

                //handle progress for normal bulk smush
                //Set Progress Bar width
                if ('undefined' !== typeof self.ids && 'undefined' !== typeof wp_smushit_data.count_total && wp_smushit_data.count_total > 0) {
                    progress = ( ( wp_smushit_data.count_total - self.ids.length ) / wp_smushit_data.count_total ) * 100;
                }
            }

            //Show Bulk Wrapper and Smush Notice
            if (self.ids.length == 0) {
                //Hide the bulk wrapper
                $('.wp-smush-bulk-wrapper').hide();
                //Show All done notice
                $('.wp-smush-notice.wp-smush-all-done').show();
            }

            //Update Total Images Tooltip
            if ('undefined' !== typeof _res.data.stats.tooltip_text && '' != _res.data.stats.tooltip_text) {
                $('.wp-smush-current-progress').attr('tooltip', _res.data.stats.tooltip_text);
            }

            //Update remaining count
            self.update_remaining_count();

            //if we have received the progress data, update the stats else skip
            if ('undefined' != typeof _res.data.stats) {

                //Update Progress on Circle
                update_dashoffset(_res.data.stats);

                //Update stats
                $('.wp-smush-savings .wp-smush-stats-percent').html(_res.data.stats.percent);
                $('.wp-smush-savings .wp-smush-stats-human').html(_res.data.stats.human);

                $('.wp-smush-images-smushed, .wp-smush-optimised').html(_res.data.stats.smushed);
                if ($('.super-smush-attachments .smushed-count').length && 'undefined' != typeof _res.data.stats.super_smushed) {
                    $('.super-smush-attachments .smushed-count').html(_res.data.stats.super_smushed);
                }

                var smush_conversion_savings = $('.smush-conversion-savings');
                //Update Conversion Savings
                if (smush_conversion_savings.length > 0 && 'undefined' != typeof ( _res.data.stats.conversion_savings ) && _res.data.stats.conversion_savings != '') {
                    var conversion_savings = smush_conversion_savings.find('.wp-smush-stats');
                    if (conversion_savings.length > 0) {
                        conversion_savings.html(_res.data.stats.conversion_savings);
                    }
                }
                var smush_resize_savings = $('.smush-resize-savings');
                //Update Resize Savings
                if (smush_resize_savings.length > 0 && 'undefined' != typeof ( _res.data.stats.resize_savings ) && _res.data.stats.resize_savings != '') {
                    var resize_savings = smush_resize_savings.find('.wp-smush-stats');
                    if (resize_savings.length > 0) {
                        resize_savings.html(_res.data.stats.resize_savings);
                    }
                }
                // increase the progress bar
                this._update_progress(_res.data.stats.smushed, progress);
            }
        };

        this._update_progress = function (count, width) {
            "use strict";
            if (!this.is_bulk && !this.is_bulk_resmush) {
                return;
            }
            //Update the Progress Bar Width
            // get the progress bar
            var $progress_bar = jQuery('.bulk-smush-wrapper .wp-smush-progress-inner');
            if ($progress_bar.length < 1) {
                return;
            }
            // increase progress
            $progress_bar.css('width', width + '%');

        };

        //Whether to send the ajax requests further or not
        this.continue = function () {
            var continue_smush = self.$button.attr('continue_smush');

            if (typeof continue_smush == typeof undefined) {
                continue_smush = true;
            }

            if ('false' == continue_smush || !continue_smush) {
                continue_smush = false;
            }

            return continue_smush && this.ids.length > 0 && this.is_bulk;
        };

        this.increment_errors = function (id) {
            WP_Smush.errors.push(id);
        };

        //Send ajax request for smushing single and bulk, call update_progress on ajax response
        this.call_ajax = function () {
            var nonce_value = '';
            this.current_id = this.is_bulk ? this.ids.shift() : this.$button.data("id"); //remove from array while processing so we can continue where left off

            //Remove the id from respective variable as well
            this.update_smush_ids(this.current_id);

            var nonce_field = this.$button.parent().find('#_wp_smush_nonce');
            if (nonce_field) {
                nonce_value = nonce_field.val();
            }

            this.request = WP_Smush.ajax(this.is_bulk_resmush, this.current_id, this.url, 0, nonce_value)
                .error(function () {
                    self.increment_errors(self.current_id);
                }).done(function (res) {
                    //Increase the error count if any
                    if (typeof res.success === "undefined" || ( typeof res.success !== "undefined" && res.success === false && res.data.error !== 'bulk_request_image_limit_exceeded' )) {
                        self.increment_errors(self.current_id);
                    }
                    //If no response or success is false, do not process further
                    if (typeof res == 'undefined' || !res || !res.success) {
                        if ('undefined' !== typeof res && 'undefined' !== typeof res.data && typeof res.data.error_msg !== 'undefined') {
                            //Print the error on screen
                            self.$log.append(res.data.error_msg);
                            self.$log.removeClass('hidden');
                        }
                    }

                    //Check whether to show the warning notice or not
                    membership_validity(res.data);

                    if (typeof res.data !== "undefined" && res.data.error == 'bulk_request_image_limit_exceeded' && !self.is_resolved()) {
                        //Add a data attribute to the smush button, to stop sending ajax
                        self.$button.attr('continue_smush', false);

                        self.free_exceeded();

                        //Reinsert the current id
                        wp_smushit_data.unsmushed.push(self.current_id);

                        //Update the remaining count to length of remaining ids + 1 (Current id)
                        self.update_remaining_count();
                    } else {

                        if (self.is_bulk && res.success) {
                            self.update_progress(res);
                        }
                    }
                    self.single_done();
                }).complete(function () {
                    if (!self.continue() || !self.is_bulk) {
                        //Calls deferred.done()
                        self.deferred.resolve();
                    } else {
                        self.call_ajax();
                    }
                });

            self.deferred.errors = WP_Smush.errors;
            return self.deferred;
        };

        this.init(arguments);

        //Send ajax request for single and bulk smushing
        this.run = function () {

            // if we have a definite number of ids
            if (this.is_bulk && this.ids.length > 0) {
                this.call_ajax();
            }

            if (!this.is_bulk)
                this.call_ajax();

        };

        //Show bulk smush errors, and disable bulk smush button on completion
        this.bind_deferred_events = function () {

            this.deferred.done(function () {

                self.$button.removeAttr('continue_smush');

                if (WP_Smush.errors.length) {
                    var error_message = '<div class="wp-smush-ajax-error">' + wp_smush_msgs.error_in_bulk.replace("{{errors}}", WP_Smush.errors.length) + '</div>';
                    //Remove any existing notice
                    $('.wp-smush-ajax-error').remove();
                    self.$log.prepend(error_message);
                }

                self.bulk_done();

                //Re enable the buttons
                $('.wp-smush-button:not(.wp-smush-finished), .wp-smush-scan').removeAttr('disabled');
            });

        };
        /** Handles the Cancel button Click
         *
         * Update the UI, and enables the bulk smush button
         *
         **/
        this.cancel_ajax = function () {
            $('.wp-smush-cancel-bulk').on('click', function () {
                //Add a data attribute to the smush button, to stop sending ajax
                self.$button.attr('continue_smush', false);

                self.request.abort();
                self.enable_button();
                self.$button.removeClass('wp-smush-started');
                $('.wp-smush-bulk-wrapper').show();

                //Hide the Progress Bar
                $('.wp-smush-bulk-progress-bar-wrapper').hide();
            });
        };
        /**
         * Remove the current id from unsmushed/resmush variable
         * @param current_id
         */
        this.update_smush_ids = function (current_id) {
            if ('undefined' !== typeof wp_smushit_data.unsmushed && wp_smushit_data.unsmushed.length > 0) {
                var u_index = wp_smushit_data.unsmushed.indexOf(current_id);
                if (u_index > -1) {
                    wp_smushit_data.unsmushed.splice(u_index, 1);
                }
            }
            //remove from the resmush list
            if ('undefined' !== typeof wp_smushit_data.resmush && wp_smushit_data.resmush.length > 0) {
                var index = wp_smushit_data.resmush.indexOf(current_id);
                if (index > -1) {
                    wp_smushit_data.resmush.splice(index, 1);
                }
            }
        }

        this.start();
        this.run();
        this.bind_deferred_events();

        //Handle Cancel Ajax
        this.cancel_ajax();

        return this.deferred;
    };

    /**
     * Handle the Bulk Smush/ Bulk Resmush button click
     */
    $('body').on('click', 'button.wp-smush-all', function (e) {

        // prevent the default action
        e.preventDefault();

        $('.wp-smush-notice.wp-smush-settings-updated').remove();

        //Disable Resmush and scan button
        $('.wp-resmush.wp-smush-action, .wp-smush-scan, .wp-smush-button').attr('disabled', 'disabled');

        //Check for ids, if there is none (Unsmushed or lossless), don't call smush function
        if (typeof wp_smushit_data == 'undefined' ||
            ( wp_smushit_data.unsmushed.length == 0 && wp_smushit_data.resmush.length == 0 )
        ) {

            return false;

        }

        $(".wp-smush-remaining").hide();

        new WP_Smush.Smush($(this), true);


    });

    /** Disable the action links **/
    var disable_links = function (c_element) {

        var parent = c_element.parent();
        //reduce parent opacity
        parent.css({'opacity': '0.5'});
        //Disable Links
        parent.find('a').attr('disabled', 'disabled');
    };

    /** Enable the Action Links **/
    var enable_links = function (c_element) {

        var parent = c_element.parent();

        //reduce parent opacity
        parent.css({'opacity': '1'});
        //Disable Links
        parent.find('a').removeAttr('disabled');
    };
    /**
     * Restore image request with a specified action for Media Library / NextGen Gallery
     * @param e
     * @param current_button
     * @param smush_action
     * @returns {boolean}
     */
    var process_smush_action = function (e, current_button, smush_action, action) {

        //If disabled
        if ('disabled' == current_button.attr('disabled')) {
            return false;
        }

        e.preventDefault();

        //Remove Error
        $('.wp-smush-error').remove();

        //Hide stats
        $('.smush-stats-wrapper').hide();

        //Get the image ID and nonce
        var params = {
            action: smush_action,
            attachment_id: current_button.data('id'),
            _nonce: current_button.data('nonce')
        };

        //Reduce the opacity of stats and disable the click
        disable_links(current_button);

        progress_bar(current_button, wp_smush_msgs[action], 'show');

        //Restore the image
        $.post(ajaxurl, params, function (r) {

            progress_bar(current_button, wp_smush_msgs[action], 'hide');

            //reset all functionality
            enable_links(current_button);

            if (r.success && 'undefined' != typeof( r.data.button )) {
                //Show the smush button, and remove stats and restore option
                current_button.parents().eq(1).html(r.data.button);
            } else {
                if (r.data.message) {
                    //show error
                    current_button.parent().append(r.data.message);
                }
            }
        })
    };

    /**
     * Validates the Resize Width and Height against the Largest Thumbnail Width and Height
     *
     * @param wrapper_div jQuery object for the whole setting row wrapper div
     * @param width_only Whether to validate only width
     * @param height_only Validate only Height
     * @returns {boolean} All Good or not
     *
     */
    var validate_resize_settings = function (wrapper_div, width_only, height_only) {
        var resize_checkbox = wrapper_div.find('#wp-smush-resize');
        if (!height_only) {
            var width_input = wrapper_div.find('#wp-smush-resize_width');
            var width_error_note = wrapper_div.find('.wp-smush-size-info.wp-smush-update-width');
        }
        if (!width_only) {
            var height_input = wrapper_div.find('#wp-smush-resize_height');
            var height_error_note = wrapper_div.find('.wp-smush-size-info.wp-smush-update-height');
        }

        var width_error = false;
        var height_error = false

        //If resize settings is not enabled, return true
        if (!resize_checkbox.is(':checked')) {
            return true;
        }

        //Check if we have localised width and height
        if ('undefined' == typeof (wp_smushit_data.resize_sizes) || 'undefined' == typeof (wp_smushit_data.resize_sizes.width)) {
            //Rely on server validation
            return true;
        }

        //Check for width
        if (!height_only && 'undefined' != typeof width_input && parseInt(wp_smushit_data.resize_sizes.width) > parseInt(width_input.val())) {
            width_input.addClass('error');
            width_error_note.show('slow');
            width_error = true;
        } else {
            //Remove error class
            width_input.removeClass('error');
            width_error_note.hide();
            if (height_input.hasClass('error')) {
                height_error_note.show('slow');
            }
        }

        //Check for height
        if (!width_only && 'undefined' != typeof height_input && parseInt(wp_smushit_data.resize_sizes.height) > parseInt(height_input.val())) {
            height_input.addClass('error');
            //If we are not showing the width error already
            if (!width_error) {
                height_error_note.show('slow');
            }
            height_error = true;
        } else {
            //Remove error class
            height_input.removeClass('error');
            height_error_note.hide();
            if (width_input.hasClass('error')) {
                width_error_note.show('slow');
            }
        }

        if (width_error || height_error) {
            return false;
        }
        return true;

    };
    //Stackoverflow: http://stackoverflow.com/questions/1726630/formatting-a-number-with-exactly-two-decimals-in-javascript
    var precise_round = function (num, decimals) {
        var sign = num >= 0 ? 1 : -1;
        return (Math.round((num * Math.pow(10, decimals)) + (sign * 0.001)) / Math.pow(10, decimals));
    };

    /**
     * Update the progress bar width if we have images that needs to be resmushed
     * @param unsmushed_count
     * @returns {boolean}
     */
    var update_progress_bar_resmush = function (unsmushed_count) {

        if ('undefined' == typeof unsmushed_count) {
            return false;
        }

        var smushed_count = wp_smushit_data.count_total - unsmushed_count;

        //Update the Progress Bar Width
        // get the progress bar
        var $progress_bar = jQuery('.bulk-smush-wrapper .wp-smush-progress-inner');
        if ($progress_bar.length < 1) {
            return;
        }

        var width = ( smushed_count / wp_smushit_data.count_total ) * 100;

        // increase progress
        $progress_bar.css('width', width + '%');
    };

    var run_re_check = function (button, process_settings) {
        var spinner = button.parent().find('.spinner');

        //Check if type is set in data attributes
        var scan_type = button.data('type');
        scan_type = 'undefined' == typeof scan_type ? 'media' : scan_type;

        //Show spinner
        spinner.addClass('is-active');

        //Remove the Skip resmush attribute from button
        $('button.wp-smush-all').removeAttr('data-smush');

        //remove notices
        var el = $('.wp-smush-notice.wp-smush-resmush-message, .wp-smush-notice.wp-smush-settings-updated');
        el.slideUp(100, function () {
            el.remove();
        });

        //Disable Bulk smush button and itself
        button.attr('disabled', 'disabled');
        $('.wp-smush-button').attr('disabled', 'disabled');

        //Hide Settings changed Notice
        $('.wp-smush-settings-changed').hide();

        //Show Loading Animation
        jQuery('.bulk-resmush-wrapper .wp-smush-progress-bar-wrap').removeClass('hidden');

        //Ajax Params
        var params = {
            action: 'scan_for_resmush',
            type: scan_type,
            get_ui: true,
            process_settings: process_settings,
            wp_smush_options_nonce: jQuery('#wp_smush_options_nonce').val()
        };

        //Send ajax request and get ids if any
        $.get(ajaxurl, params, function (r) {
            //Check if we have the ids,  initialize the local variable
            if ('undefined' != typeof r.data) {
                //Update Resmush id list
                if ('undefined' != typeof r.data.resmush_ids) {
                    wp_smushit_data.resmush = r.data.resmush_ids;

                    //Get the Smushed image count
                    var smushed_count = wp_smushit_data.count_smushed - r.data.resmush_ids.length;

                    //Update it in stats bar
                    $('.wp-smush-images-smushed, .wp-smush-optimised').html(smushed_count);

                    //Hide the Existing wrapper
                    var notices = $('.bulk-smush-wrapper .wp-smush-notice');
                    if (notices.length > 0) {
                        notices.hide();
                    }
                    //remove existing Re-Smush notices
                    $('.wp-smush-resmush-notice').remove();

                    //Show Bulk wrapper
                    $('.wp-smush-bulk-wrapper').show();

                    if ('undefined' !== typeof r.data.count) {
                        //Update progress bar
                        update_progress_bar_resmush(r.data.count);
                    }
                }
                //If content is received, Prepend it
                if ('undefined' != typeof r.data.content) {
                    $('.bulk-smush-wrapper .box-container').prepend(r.data.content);
                }
                //If we have any notice to show
                if ('undefined' != typeof r.data.notice) {
                    $('.wp-smush-page-header').after(r.data.notice);
                }
                //Hide errors
                $('.smush-final-log').hide();

                //Hide Super Smush notice if it's enabled in media settings
                if ('undefined' != typeof r.data.super_smush && r.data.super_smush) {
                    var enable_lossy = jQuery('.wp-smush-enable-lossy');
                    if (enable_lossy.length > 0) {
                        enable_lossy.remove();
                    }
                    if ('undefined' !== r.data.super_smush_stats) {
                        $('.super-smush-attachments .wp-smush-stats').html(r.data.super_smush_stats);
                    }
                }
            }

        }).always(function () {

            //Hide the progress bar
            jQuery('.bulk-smush-wrapper .wp-smush-bulk-progress-bar-wrapper').hide();

            //Enable the Bulk Smush Button and itself
            button.removeAttr('disabled');

            //Hide Spinner
            spinner.removeClass('is-active');
            $('.wp-smush-button').removeAttr('disabled');

            //If wp-smush-re-check-message is there, remove it
            if ($('.wp-smush-re-check-message').length) {
                remove_element($('.wp-smush-re-check-message'));
            }
        });
    }

    /**
     * Get directory list using Ajax
     *
     * @param param
     * @returns {string}
     *
     */
    var getDirectoryList = function (param) {
        param.action = 'smush_get_directory_list';
        param.list_nonce = jQuery('input[name="list_nonce"]').val();
        var res = '';
        $.ajax({
            type: "GET",
            url: ajaxurl,
            data: param,
            success: function (response) {
                res = response;
            },
            async: false
        });
        return res;
    }
    /**
     * Hide the popup and reset the opacity for the button
     *
     */
    var close_dialog = function () {
        //Hide the dialog
        $('.wp-smush-list-dialog').hide();
        $('.wp-smush-select-dir, button.wp-smush-browse, button.wp-smush-resume').removeAttr('disabled');

        //Remove the spinner
        $('div.dir-smush-button-wrap span.spinner').removeClass('is-active');

        //Reset the opacity for content and scan button
        $('.wp-smush-select-dir, .wp-smush-list-dialog .box .content').css({'opacity': '1'});
        $('.wp-smush-select-button-wrap .spinner').removeClass('is-active');
    }

    /**
     * Initialize accordion
     *
     */
    var set_accordion = function () {
        //Accordion On WP Smush All Page
        var acc = document.getElementsByClassName("wp-smush-li-path");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].onclick = function () {
                var parent = $(this).parent();
                if (parent.hasClass('active')) {
                    parent.removeClass('active');
                    parent.find('.wp-smush-image-list-inner').removeClass("show");
                } else {
                    parent.addClass("active");
                    $('.wp-smush-image-ul.active .wp-smush-image-list-inner').addClass("show");
                }
            }
        }
    }

    /**
     * Appends the waiting message to child elements for a directory
     * @param ele
     */
    var update_dir_ele_status = function (ele) {
        //Get the parent element
        var parent = ele.parents('li.wp-smush-image-ul');

        //Spinner
        var spinner = $('div.wp-smush-scan-result span.spinner:first').clone();

        if (!parent.length) {
            return;
        }
        //Check if the selected element is under expandable li
        parent.removeClass('partial complete').addClass('active in-progress');

        //Append a spinner, if parent doesn't have it
        if (!parent.find('span.wp-smush-li-path span.spinner').length) {
            parent.find('span.wp-smush-li-path').prepend(spinner.clone());
        }

        var list = parent.find('.wp-smush-image-list-inner');
        list.addClass('show');

        //Check if first image, Add a loader against directory path
        var progress_wrap = parent.find('div.wp-smush-dir-progress-wrap');

        var waiting_message = $('content').find('span.waiting-message').clone();

        if (ele.is(':first-child')) {
            progress_wrap.css({'display': 'inline-block'});
            parent.find('a.wp-smush-exclude-dir').remove();
        }

        var child = parent.find('ul.wp-smush-image-list-inner li');

        //Mark all the images inside a directory path, as waiting. Copy and append a single span from the page
        var unsmushed = child.filter(":not('.optimised')");
        if (unsmushed.length > 0) {
            //Append a waiting message
            unsmushed.append(waiting_message);
            unsmushed.find(waiting_message).show();
        }
    }

    /**
     * Check if all the elements in the directory are smushed or not
     *
     * @param parent directory selector
     *
     * @returns {boolean}
     *
     */
    var is_last_element = function(parent) {
        var elements = parent.find('li.wp-smush-image-ele:not(.optimised,.error)');
        if( elements.length <= 0 ) {
            return true;
        }
        return false;
    };

    /**
     * Update directory optimisation progress if the element has a parent
     *
     * @param ele
     *
     */
    var update_dir_progress = function (ele) {
        //Get the parent element
        var parent = ele.parents('li.wp-smush-image-ul');

        if (!parent.length) {
            return;
        }
        var child = parent.find('ul.wp-smush-image-list-inner li');

        //Check if first image, Add a loader against directory path
        var progress_wrap = parent.find('div.wp-smush-dir-progress-wrap');

        //Update the percentage, Check the total number of images inside dir, smushed images count
        var total = child.length;
        var smushed = child.filter('.optimised').length;
        var smush_progress = progress_wrap.find('.wp-smush-dir-progress');
        if (smushed > 0 && total > 0) {
            var percent = ( smushed / total ) * 100;
            percent = precise_round(percent, 1);
            progress_wrap.find('.smush-percent').html(percent + '%');
            smush_progress.css({'width': percent + '%'});
        }

        //Add the class in-progress, to show the respective icon for parent
        if (0 != $('input[name="wp-smush-continue-ajax"]').val() && !parent.hasClass('in-progress') && smushed != total) {
            parent.addClass('in-progress').removeClass('partial');
            //Append a spinner
            var spinner = $('div.wp-smush-scan-result span.spinner:first').clone();
            if (spinner) {
                parent.find('span.wp-smush-li-path').prepend(spinner);
            }
        }

        var parent_class = '';
        //Check if last image, and if all the images are not smushed under the specified directory path, add a generic warning message
        if (is_last_element(parent)) {
            if (smushed < total) {
                var unsmushed = total - smushed;
                var message = '<div class="wp-smush-dir-notice"><i class="dev-icon wdv-icon wdv-icon-fw wdv-icon-exclamation-sign"></i>' + unsmushed + ' ' + ( 1 == unsmushed ? wp_smush_msgs.unfinished_smush_single : wp_smush_msgs.unfinished_smush ) + '</div>';

                //If the notice is already displayed, remove it
                var notice = parent.find('div.wp-smush-dir-notice');
                if( notice.length  ) {
                    notice.remove();
                }

                //Append message to 2nd parent i.e li
                parent.find('ul.wp-smush-image-list-inner').after(message);

                //Check If all the images are smushed, remove the class in-progress and add the class complete, else add class partial
                parent_class = 'partial';

                //Remove the Spinner
                parent.find('span.wp-smush-li-path span.spinner').remove();
            } else {
                parent_class = 'complete';
                smush_progress.removeClass('partial').addClass('complete');
            }
            //Remove Spinner
            parent.find('span.wp-smush-li-path span.spinner').remove();

            //Remove In progress class for the element and add partial/complete class
            parent.removeClass('in-progress active').addClass(parent_class);

            //Remove active class from parent
            parent.removeClass('active').find('.wp-smush-image-list-inner').removeClass("show");
        }

    }

    /**
     * Add choose directory button at the top
     *
     */
    var add_dir_browser_button = function () {
        //Get the content div length, if less than 1500, Skip
        if( $('div.wp-smush-scan-result div.content').height() < 1500 || $('div.dir-smush-button-wrap.top').length >= 1 ) {
            return;
        }

        var choose_button = $('div.dir-smush-button-wrap').clone();
        choose_button.addClass('top');
        $('div.wp-smush-scan-result div.content').prepend(choose_button);
    };

    var add_smush_button = function() {
        //Get the content div length, if less than 1500, Skip
        if( $('div.wp-smush-scan-result div.content').height() < 1500 || $('div.wp-smush-all-button-wrap.top').length >= 1 ) {
            return;
        }

        var smush_button = $('div.wp-smush-all-button-wrap.bottom').clone();
        smush_button.addClass('top').removeClass('bottom');
        $('div.wp-smush-scan-result div.content').prepend(smush_button);

    };

    /**
     * Add smush notice after directory smushing is finished
     *
     * @param notice_type
     *  all_done -  If all the images were smushed else warning
     *  smush_limit - If Free users exceeded limit
     *
     */
    var add_smush_dir_notice = function ( notice_type ) {
        //Get the content div length, if less than 1500, Skip
        if( $('div.wp-smush-scan-result div.content').height() < 1500 || $('div.wp-smush-scan-result div.wp-smush-notice.top').length >= 1 ) {
            return;
        }
        var notice = '';
        //Clone and append the notice
        if( 'all_done' == notice_type ) {
            notice = $('div.wp-smush-notice.wp-smush-dir-all-done').clone();
        }else if( 'smush_limit' == notice_type ){
            notice = $('div.wp-smush-notice.wp-smush-dir-limit').clone();
        }else{
            notice = $('div.wp-smush-notice.wp-smush-dir-remaining').clone();
        }

        //Add class top
        notice.addClass('top');

        //Append the notice
        $('div.wp-smush-scan-result div.dir-smush-button-wrap').after( notice );
    };

    var update_smush_progress = function() {
        var in_progress_path = $('ul.wp-smush-image-list li.in-progress');
        in_progress_path.removeClass('in-progress active');
        if( in_progress_path.length > 0 ) {
            in_progress_path.each( function( index, ele ) {
                if ($(ele).hasClass('wp-smush-image-ul')) {
                    //Remove Spinner
                    $(ele).find('span.spinner').remove();

                    //Check if images are pending
                    var in_progress_ele = $(ele).find('li.wp-smush-image-ele');

                    //If there are elements
                    if (in_progress_ele.length > 0) {
                        var optimised = in_progress_ele.filter('.optimised').length;
                        var error = in_progress_ele.filter('.error').length;
                        //if all the elements are optimised
                        if (optimised == in_progress_ele.length) {
                            $( ele ).addClass('complete');
                        } else if (0 < optimised || 0 < error) {
                            //If there are images that needs to be smushed, add the class partial
                            $( ele ).addClass('partial');
                        }
                    }

                }else{
                    //Remove spinner for the element
                    $(ele).find('span.spinner').remove();
                }
            });
        }
    };

    /**
     * Update the progress and show notice when smush completes
     */
    var directory_smush_finished = function( notice_type ) {
        //If there are no images left
        $('div.wp-smush-all-button-wrap span.spinner').remove();
        $('button.wp-smush-pause').hide().attr('disabled', 'disabled');

        //Hide Bulk Smush button if smush was stopped for error or finished
        if ('' == notice_type) {
            $('button.wp-smush-start').parent().hide();
        } else {
            $('button.wp-smush-start').show().removeAttr('disabled');
        }

        //Enable Choose directory button
        $('button.wp-smush-browse').show().removeAttr('disabled', 'disabled');

        //Clone Choose Directory Button and add at the top
        add_dir_browser_button();

        //Clone and add Smush button
        add_smush_button();

        if( '' == notice_type ) {
            //Get the Total and Optimised image count
            var image_ele = $('li.wp-smush-image-ele')
            var total = image_ele.length;
            var remaning = image_ele.filter(':not(.optimised)').length;
            var smushed = total - remaning;
            if (remaning > 0) {

                //Append the count
                $('span.wp-smush-dir-remaining').html(remaning);
                $('span.wp-smush-dir-total').html(total);
                $('span.wp-smush-dir-smushed').html(smushed);

                //Show remaining image notice
                $('.wp-smush-notice.wp-smush-dir-remaining').show();

                //Show notice on top if required
                add_smush_dir_notice();
            } else {
                //Show All done notice
                $('.wp-smush-notice.wp-smush-dir-all-done').show();

                //Show notice on top if required
                add_smush_dir_notice('all_done');
            }
        }else{
            //Show Bulk Limit Notice
            $('.wp-smush-notice.wp-smush-dir-limit').show();
            //Show notice on top if required
            add_smush_dir_notice('smush_limit');
        }

        //Update Directory progress and remove any loaders still in there
        update_smush_progress();

    }

    /**
     * Start Optimising all the images listed in last directory scan
     *
     */
    var smush_all = function () {

        var spinner = $('div.smush-page-wrap span.spinner:first').clone();
        spinner.addClass('is-active');
        //Update the Optimising status for the image
        var first_child = $('ul.wp-smush-image-list li.wp-smush-image-ele:not(".optimised, .processed"):first');

        var parent = first_child.parents('li.wp-smush-image-ul');

        //Check if the selected element is under expandable li
        if (parent.length == 1) {
            parent.addClass('active in-progress').removeClass('partial');
            parent.find('.wp-smush-image-list-inner').addClass('show');
            if (!parent.find('span.wp-smush-li-path span.spinner').length) {
                parent.find('span.wp-smush-li-path').prepend(spinner.clone());
            }
        }

        //Append and show spinner
        first_child.addClass('in-progress processed');
        if (!first_child.find('spam.spinner').length) {
            first_child.prepend(spinner.clone());
        }

        //If all the elements are optimised, No need to send ajax request
        if( first_child.length == 0 ) {
            directory_smush_finished('');
            return;
        }

        /** Ajax Request to optimise directory images */
        var param = {
            action: 'optimise',
            image_id: first_child.attr('id'),
            nonce: $('#wp-smush-all').val()
        };

        //Send Ajax request
        $.get(ajaxurl, param, function (res) {

            //Check, if limit is exceeded for free version
            if (typeof res.data !== "undefined" && res.data.error == 'dir_smush_limit_exceeded') {
                //Show error, Bulk Smush limit exceeded
                directory_smush_finished( 'wp-smush-dir-limit' );
                return;
            }

            //append stats, remove loader, add loader to next image, loop
            var data = 'undefined' != typeof ( res.data ) ? res.data : '';

            //If image element is there
            if ('undefined' != typeof(data.image)) {
                //Mark Optimised
                var ele = jQuery(document.getElementById(data.image.id));

                //Remove the spinner
                ele.find('span.spinner').remove();
                ele.removeClass('in-progress');

                if (res.success) {

                    ele.addClass('optimised');

                    //Show the Optimisation status
                    ele.find('span.wp-smush-image-ele-status').show();

                    //Update Directory progress
                    update_dir_progress(ele);
                } else {
                    //If there was an error optimising the image
                    ele.addClass('error');
                    //Update Directory progress
                    update_dir_progress(ele);
                }
            }

            //If user haven't paused the Smushing
            if ( 1 == $('input[name="wp-smush-continue-ajax"]').val() ) {
                //Loop
                smush_all(false);
            } else {
                //Reset the Ajax flag
                $('input.wp-smush-continue-ajax').val(1);
            }

        });
    }

    //Scroll the element to top of the page
    var goToByScroll = function (selector) {
        // Scroll
        $('html,body').animate({
                scrollTop: selector.offset().top
            },
            'slow');
    };

    var disable_buttons = function (self) {
        self.attr('disabled', 'disabled');
        $('.wp-smush-browse').attr('disabled', 'disabled');
    };

    var update_cummulative_stats = function (stats) {
        //Update Directory Smush Stats
        if ('undefined' != typeof ( stats.dir_smush )) {
            var stats_human = $('div.smush-dir-savings span.wp-smush-stats span.wp-smush-stats-human');
            var stats_percent = $('div.smush-dir-savings span.wp-smush-stats span.wp-smush-stats-percent');

            //Update Savings in bytes
            if (stats_human.length > 0) {
                stats_human.html(stats.dir_smush.human);
            } else {
                var span = '<span class="wp-smush-stats-human">' + stats.dir_smush.bytes + '</span>';
            }

            //Update Optimisation percentage
            if (stats_percent.length > 0) {
                stats_percent.html(stats.dir_smush.percent + '%');
            } else {
                var span = '<span class="wp-smush-stats-percent">' + stats.dir_smush.percent + '%' + '</span>';
            }
        }

        //Update Combined stats
        if ('undefined' != typeof ( stats.combined_stats ) && stats.combined_stats.length > 0) {
            var c_stats = stats.combined_stats;

            //Update Circle Progress
            if (c_stats.dash_offset) {
                $('circle.wp-smush-svg-circle-progress').css({'stroke-dashoffset': c_stats.dash_offset});
            }
            //Update Tooltip Text
            if (c_stats.tooltip_text) {
                $('div.wp-smush-current-progress').attr('tooltip', c_stats.tooltip_text);
            }
            //Update Smushed count
            if (c_stats.smushed_count) {
                $('div.wp-smush-count-total span.wp-smush-optimised').html(c_stats.smushed_count);
            }
            //Update Total Attachment Count
            if (c_stats.total_count) {
                $('div.wp-smush-count-total div.wp-smush-smush-stats-wrapper span:last-child').html(c_stats.total_count);
            }
            //Update Savings and Percent
            if (c_stats.savings) {
                $('div.wp-smush-savings span.wp-smush-stats-human').html(c_stats.savings);
            }
            if (c_stats.percent) {
                $('div.wp-smush-savings span.wp-smush-stats-percent').html(c_stats.percent);
            }
        }
    };

    /**
     * Handle the Smush Stats link click
     */
    $('body').on('click', 'a.smush-stats-details', function (e) {

        //If disabled
        if ('disabled' == $(this).attr('disabled')) {
            return false;
        }

        // prevent the default action
        e.preventDefault();
        //Replace the `+` with a `-`
        var slide_symbol = $(this).find('.stats-toggle');
        $(this).parents().eq(1).find('.smush-stats-wrapper').slideToggle();
        slide_symbol.text(slide_symbol.text() == '+' ? '-' : '+');


    });

    /** Handle smush button click **/
    $('body').on('click', '.wp-smush-send:not(.wp-smush-resmush)', function (e) {

        // prevent the default action
        e.preventDefault();
        new WP_Smush.Smush($(this), false);
    });

    /** Handle NextGen Gallery smush button click **/
    $('body').on('click', '.wp-smush-nextgen-send', function (e) {

        // prevent the default action
        e.preventDefault();
        new WP_Smush.Smush($(this), false, 'nextgen');
    });

    /** Handle NextGen Gallery Bulk smush button click **/
    $('body').on('click', '.wp-smush-nextgen-bulk', function (e) {

        // prevent the default action
        e.preventDefault();

        //Check for ids, if there is none (Unsmushed or lossless), don't call smush function
        if (typeof wp_smushit_data == 'undefined' ||
            ( wp_smushit_data.unsmushed.length == 0 && wp_smushit_data.resmush.length == 0 )
        ) {

            return false;

        }

        jQuery('.wp-smush-button, .wp-smush-scan').attr('disabled', 'disabled');
        $(".wp-smush-notice.wp-smush-remaining").hide();
        new WP_Smush.Smush($(this), true, 'nextgen');

    });

    /** Restore: Media Library **/
    $('body').on('click', '.wp-smush-action.wp-smush-restore', function (e) {
        var current_button = $(this);
        var smush_action = 'smush_restore_image';
        process_smush_action(e, current_button, smush_action, 'restore');
    });

    /** Resmush: Media Library **/
    $('body').on('click', '.wp-smush-action.wp-smush-resmush', function (e) {
        var current_button = $(this);
        var smush_action = 'smush_resmush_image';
        process_smush_action(e, current_button, smush_action, 'smushing');
    });

    /** Restore: NextGen Gallery **/
    $('body').on('click', '.wp-smush-action.wp-smush-nextgen-restore', function (e) {
        var current_button = $(this);
        var smush_action = 'smush_restore_nextgen_image';
        process_smush_action(e, current_button, smush_action, 'restore');
    });

    /** Resmush: NextGen Gallery **/
    $('body').on('click', '.wp-smush-action.wp-smush-nextgen-resmush', function (e) {
        var current_button = $(this);
        var smush_action = 'smush_resmush_nextgen_image';
        process_smush_action(e, current_button, smush_action, 'smushing');
    });

    //Scan For resmushing images
    $('body').on('click', '.wp-smush-scan', function (e) {

        e.preventDefault();

        //Run the Re-check
        run_re_check($(this), false);

    });

    //Dismiss Welcome notice
    $('#wp-smush-welcome-box .smush-dismiss-welcome').on('click', function (e) {
        e.preventDefault();
        var $el = $(this).parents().eq(1);
        remove_element($el);

        //Send a ajax request to save the dismissed notice option
        var param = {
            action: 'dismiss_welcome_notice'
        };
        $.post(ajaxurl, param);
    });

    //Remove Notice
    $('body').on('click', '.wp-smush-notice .dev-icon-cross', function (e) {
        e.preventDefault();
        var $el = $(this).parent();
        remove_element($el);
    });

    //On Click Update Settings. Check for change in settings
    $('input#wp-smush-save-settings').on('click', function (e) {
        e.preventDefault();

        var setting_type = '';
        var setting_input = $('input[name="setting-type"]');
        //Check if setting type is set in the form
        if (setting_input.length > 0) {
            setting_type = setting_input.val();
        }

        //Show the spinner
        var self = $(this);
        self.parent().find('span.spinner').addClass('is-active');

        //Save settings if in network admin
        if ('' != setting_type && 'network' == setting_type) {
            //Ajax param
            var param = {
                action: 'save_settings',
                nonce: $('#wp_smush_options_nonce').val()
            };

            param = jQuery.param(param) + '&' + jQuery('form#wp-smush-settings-form').serialize();

            //Send ajax, Update Settings, And Check For resmush
            jQuery.post(ajaxurl, param).done(function () {
                jQuery('form#wp-smush-settings-form').submit();
                return true;
            });
        } else {

            //Check for all the settings, and scan for resmush
            var wrapper_div = self.parents().eq(1);

            //Get all the main settings
            var keep_exif = document.getElementById("wp-smush-keep_exif");
            var super_smush = document.getElementById("wp-smush-lossy");
            var smush_original = document.getElementById("wp-smush-original");
            var resize_images = document.getElementById("wp-smush-resize");
            var smush_pngjpg = document.getElementById("wp-smush-png_to_jpg");

            var update_button_txt = true;

            $('.wp-smush-hex-notice').hide();

            //If Preserve Exif is Checked, and all other settings are off, just save the settings
            if (keep_exif.checked && !super_smush.checked && !smush_original.checked && !resize_images.checked && !smush_pngjpg.checked) {
                update_button_txt = false;
            }

            //Update text
            self.attr('disabled', 'disabled').addClass('button-grey');

            if (update_button_txt) {
                self.val(wp_smush_msgs.checking)
            }

            //Check if type is set in data attributes
            var scan_type = self.data('type');
            scan_type = 'undefined' == typeof scan_type ? 'media' : scan_type;

            //Ajax param
            var param = {
                action: 'scan_for_resmush',
                wp_smush_options_nonce: jQuery('#wp_smush_options_nonce').val(),
                scan_type: scan_type
            };

            param = jQuery.param(param) + '&' + jQuery('form#wp-smush-settings-form').serialize();

            //Send ajax, Update Settings, And Check For resmush
            jQuery.post(ajaxurl, param).done(function () {
                jQuery('form#wp-smush-settings-form').submit();
                return true;
            });
        }
    });

    //On Resmush click
    $('body').on('click', '.wp-smush-skip-resmush', function (e) {
        e.preventDefault();
        var self = jQuery(this);
        var container = self.parents().eq(1);

        //Remove Parent div
        var $el = self.parent();
        remove_element($el);

        //Remove Settings Notice
        $('.wp-smush-notice.wp-smush-settings-updated').remove();

        //Set button attribute to skip re-smush ids
        container.find('.wp-smush-all').attr('data-smush', 'skip_resmush');

        //Update Stats
        if (wp_smushit_data.count_smushed == wp_smushit_data.count_total) {

            //Show all done notice
            $('.wp-smush-notice.wp-smush-all-done').show();

            //Hide Smush button
            $('.wp-smush-bulk-wrapper ').hide()

        }
        //Remove Re-Smush Notice
        $('.wp-smush-resmush-notice').remove();

        var type = $('.wp-smush-scan').data('type');
        type = 'undefined' == typeof type ? 'media' : type;

        var smushed_count = 'undefined' != typeof wp_smushit_data.count_smushed ? wp_smushit_data.count_smushed : 0
        $('.wp-smush-images-smushed, .wp-smush-optimised').html(smushed_count);

        //Update the Progress Bar Width
        // get the progress bar
        var $progress_bar = jQuery('.bulk-smush-wrapper .wp-smush-progress-inner');
        if ($progress_bar.length < 1) {
            return;
        }

        var width = ( smushed_count / wp_smushit_data.count_total ) * 100;

        // increase progress
        $progress_bar.css('width', width + '%');

        //Show the default bulk smush notice
        $('.wp-smush-bulk-wrapper .wp-smush-notice').show();

        var params = {
            action: 'delete_resmush_list',
            type: type
        }
        //Delete resmush list
        $.post(ajaxurl, params);

    });

    //Enable Super Smush
    $('.wp-smush-lossy-enable').on('click', function (e) {
        e.preventDefault();

        //Enable Super Smush
        $('#wp-smush-lossy').prop('checked', true);
        //Induce Setting button save click
        $('#wp-smush-save-settings').click();
    });

    //Enable Resize
    $('.wp-smush-resize-enable').on('click', function (e) {
        e.preventDefault();

        //Enable Super Smush
        $('#wp-smush-resize').prop('checked', true);
        //Induce Setting button save click
        $('#wp-smush-save-settings').click();
    });

    //Trigger Bulk
    $('body').on('click', '.wp-smush-trigger-bulk', function (e) {
        e.preventDefault();
        //Induce Setting button save click
        $('button.wp-smush-all').click();

    });

    //Allow the checkboxes to be Keyboard Accessible
    $('.wp-smush-setting-row .toggle-checkbox').focus(function () {
        //If Space is pressed
        $(this).keypress(function (e) {
            if (e.keyCode == 32) {
                e.preventDefault();
                $(this).find('.toggle-checkbox').click();
            }
        });
    });

    //Re-Validate Resize Width And Height
    $('.wp-smush-resize-input').blur(function () {

        var self = $(this);

        var wrapper_div = self.parents().eq(2);

        //Initiate the check
        validate_resize_settings(wrapper_div, false, false); // run the validation

    });

    //Handle Resize Checkbox toggle, to show/hide width, height settings
    $('#wp-smush-resize').click(function () {
        var self = $(this);
        var settings_wrap = $('.wp-smush-resize-settings-wrap');

        if (self.is(':checked')) {
            settings_wrap.show();
        } else {
            settings_wrap.hide();
        }
    });

    //Handle PNG to JPG Checkbox toggle, to show/hide Transparent image conversion settings
    $('#wp-smush-png_to_jpg').click(function () {
        var self = $(this);
        var settings_wrap = $('.wp-smush-png_to_jpg-wrap');

        if (self.is(':checked')) {
            settings_wrap.show();
        } else {
            settings_wrap.hide();
        }
    });

    //Handle, Change event in Enable Networkwide settings
    $('#wp-smush-networkwide').on('click', function (e) {
        if ($(this).is(':checked')) {
            $('.network-settings-wrapper').show();
        } else {
            $('.network-settings-wrapper').hide();
        }
    });

    //Handle Twitter Share
    $('#wp-smush-twitter-share').on('click', function (e) {
        e.preventDefault();
        var width = 550,
            height = 420,
            left = ($(window).width() - width) / 2,
            top = ($(window).height() - height) / 2,
            url = this.href,
            opts = 'status=1' +
                ',width=' + width +
                ',height=' + height +
                ',top=' + top +
                ',left=' + left;

        window.open(url, 'twitter', opts);

        return false;
    });

    //Handle Facebook Share
    $('#wp-smush-facebook-share').on('click', function (e) {
        e.preventDefault();
        var width = 550,
            height = 420,
            left = ($(window).width() - width) / 2,
            top = ($(window).height() - height) / 2,
            url = this.href,
            opts = 'status=1' +
                ',width=' + width +
                ',height=' + height +
                ',top=' + top +
                ',left=' + left;

        window.open(url, 'facebook', opts);

        return false;
    });

    //Adjust background image size if required
    if ($('.wp-smush-pro-for-free').length) {
        //On Page load
        resize_width();
        //Adjust background image
        $(window).resize(function () {
            resize_width();
        });
    }
    //Handle Re-check button functionality
    $("#wp-smush-revalidate-member").on('click', function (e) {
        e.preventDefault();
        //Ajax Params
        var params = {
            action: 'smush_show_warning',
        };
        var link = $(this);
        var parent = link.parents().eq(1);
        parent.addClass('loading-notice');
        $.get(ajaxurl, params, function (r) {
            //remove the warning
            parent.removeClass('loading-notice').addClass("loaded-notice");
            if (0 == r) {
                parent.attr('data-message', wp_smush_msgs.membership_valid);
                remove_element(parent, 1000);
            } else {
                parent.attr('data-message', wp_smush_msgs.membership_invalid);
                setTimeout(function remove_loader() {
                    parent.removeClass('loaded-notice');
                }, 1000)
            }
        });
    });

    //Initiate Re-check if the variable is set
    if ('undefined' != typeof (wp_smush_run_re_check) && 1 == wp_smush_run_re_check && $('.wp-smush-scan').length > 0) {
        //Run the Re-check
        run_re_check($('.wp-smush-scan'), false);
    }

    //WP Smush all : Scan Images
    $('div.row').on('click', 'button.wp-smush-browse', function (e) {

        e.preventDefault();

        //Hide all the notices
        $('div.wp-smush-scan-result div.wp-smush-notice').hide();

        //If disabled, do not process
        if ($(this).attr('disabled')) {
            return;
        } else {
            //Disable Buttons
            $(this).attr('disabled', 'disabled');
            $('button.wp-smush-resume').attr('disabled', 'disabled');
            $('div.dir-smush-button-wrap span.spinner').addClass('is-active');

        }

        //Remove Notice
        $('div.wp-smush-info').remove();

        //Shows the directories available
        $('.wp-smush-list-dialog').show();

        //Display the loader
        $('button.dir-smush-button-wrap span.spinner').addClass('is-active');

        $(".wp-smush-list-dialog .content").fileTree({
            script: getDirectoryList,
            //folderEvent: 'dblclick',
            multiFolder: false
            //onlyFolders: true
        });

    });

    //WP Smush all: Close button functionality
    $('.wp-smush-list-dialog').on('click', '.close', function (e) {
        e.preventDefault();
        close_dialog();
    });

    //Image Directories: On Select button click
    $('.wp-smush-select-dir').on('click', function (e) {
        e.preventDefault();

        //If disabled, do not process
        if ($(this).attr('disabled')) {
            return;
        }

        var button = $(this);

        button.css({'opacity': '0.5'});
        $('div.wp-smush-list-dialog div.box div.content').css({'opacity': '0.8'});
        $('div.wp-smush-list-dialog div.box div.content a').unbind('click');

        //Remove resume button
        $('button.wp-smush-resume').remove();

        //Disable Button
        button.attr('disabled', 'disabled');

        //Display the spinner
        button.parent().find('.spinner').addClass('is-active');

        //Get the Selected directory path
        var path = $('.jqueryFileTree .selected a').attr('rel');
        path = 'undefined' == typeof (path) ? '' : path;

        //Absolute path
        var abs_path = $('input[name="wp-smush-base-path"]').val();

        //Fill in the input field
        $('.wp-smush-dir-path').val(abs_path + path);

        //Send a ajax request to get a list of all the image files
        var param = {
            action: 'image_list',
            smush_path: $('.wp-smush-dir-path').val(),
            image_list_nonce: $('input[name="image_list_nonce"]').val()
        };

        //Get the List of images
        $.get(ajaxurl, param, function (res) {
            if( !res.success && 'undefined' !== typeof ( res.data.message ) ) {
                $('div.wp-smush-scan-result div.content').html(res.data.message );
            }else {
                $('div.wp-smush-scan-result div.content').html(res.data );
                wp_smush_dir_image_ids = res.data.ids;
            }
            set_accordion();
            close_dialog();

            //Show Scan result
            $('.wp-smush-scan-result').removeClass('hidden');
        }).done(function (res) {

            //If there was no image list, return
            if( !res.success ) {
                //Hide the smush button
                $('div.wp-smush-all-button-wrap.bottom').hide();
                return;
            }

            //Show the smush button
            $('div.wp-smush-all-button-wrap.bottom').show();

            //Remove disabled attribute for the button
            $('button.wp-smush-start').removeAttr('disabled');

            //Append a Directory browser button at the top
            add_dir_browser_button();

            //Clone and add Smush button
            add_smush_button();

        });
    });

    /**
     * Handle the Smush Now button click
     */
    $('div.wp-smush-scan-result').on('click', 'button.wp-smush-start', function (e) {
        e.preventDefault();

        //Check if we have images to be optimised
        if (!$('.wp-smush-image-list li').length) {
            return;
        }

        //Disable this button
        var button = $('.wp-smush-start');
        var parent = button.parent();

        //Hide all the notices
        $('div.wp-smush-scan-result div.wp-smush-notice').hide();

        //Set the button status to 0, to cancel next ajax request
        $('input[name="wp-smush-continue-ajax"]').val(1);

        //Hide Directory browser button
        $('button.wp-smush-browse').hide();

        //Hide Exclude directory button link
        $('a.wp-smush-exclude-dir').hide();

        /** All the Styling changes **/
        button.attr('disabled', 'disabled');
        parent.find('span.spinner').addClass('is-active');
        parent.find('button.wp-smush-pause').show().removeClass('disabled').removeAttr('disabled');

        //Disable Select Directory button
        $('button.wp-smush-browse').attr('disabled', 'disabled');

        //Initialize the optimisation
        smush_all(true);

    });

    //Handle the Pause button click
    $('div.wp-smush-scan-result').on('click', 'button.wp-smush-pause', function (e) {
        e.preventDefault();

        var pause_button = $('button.wp-smush-pause');
        //Return if the link is disabled
        if (pause_button.hasClass('disabled')) {
            return false;
        }

        //Set the button status to 0, to cancel next ajax request
        $('input[name="wp-smush-continue-ajax"]').val(0);

        //Enable the smush button, disable Pause button
        pause_button.attr('disabled', 'disabled');

        //Enable the smush button, hide the spinner
        $('button.wp-smush-start, button.wp-smush-browse').show().removeAttr('disabled');
        $('div.wp-smush-all-button-wrap span.spinner').removeClass('is-active');

        //Show directory exclude option
        $('a.wp-smush-exclude-dir').show();

        //Remove the loaders
        update_smush_progress();


    });

    //Exclude Directory from list - Handle Click
    $('div.wp-smush-scan-result').on('click', 'a.wp-smush-exclude-dir', function (e) {
        e.preventDefault();

        var self = $(this);
        var parent = self.parent();

        //Hide the link
        self.hide();

        //Append the loader
        parent.find('span.wp-smush-li-path').after($('div.wp-smush-scan-result span.spinner:first').clone());

        //Store the spinner in a element
        var loader = parent.find('span.spinner:first');

        loader.removeClass('is-active');

        var path = self.data('path');
        var param = {
            action: 'smush_exclude_path',
            path: path,
            nonce: $('input[name="exclude-path-nonce"]').val()
        };

        //Send Ajax request to remove image for the given path from db
        $.post(ajaxurl, param, function (res) {
            loader.remove();
            //Remove the whole li element on success
            if (res.success) {
                //Check if immediate sibling is ul, add a hr tag to it
                if (parent.is("li.wp-smush-image-ul:first")) {
                    //Add a hr tag for the next element
                    parent.siblings('li.wp-smush-image-ul:first').prepend('<hr />');
                }
                parent.remove();
            }
        });
    });

    //Handle Click for Resume Last scan button
    $('button.wp-smush-resume').on('click', function () {

        var self = $(this);

        //Disable buttons
        disable_buttons(self);

        //Show Spinner
        $('div.dir-smush-button-wrap span.spinner').addClass('is-active');

        var params = {
            action: 'resume_scan',
        };

        //Send Ajax request to load a list of images
        $.get(ajaxurl, params, function (r) {

            //Hide the buttons
            $('button.wp-smush-resume').remove();
            //Remove the loader for choose directory button
            $('div.dir-smush-button-wrap span.spinner').remove();
            // Allow to select a new directory
            $('button.wp-smush-browse').removeAttr('disabled');
            //Append the results
            if (!r.success) {
                //Append the error message before the buttons
                $('div.wp-smush-dir-desc').after(r.data.message);
            } else {
                //Append the image markup after the buttons
                $('div.wp-smush-scan-result div.content').html(r.data);
                $('div.wp-smush-scan-result').removeClass('hidden');

                set_accordion();
            }
        }).done(function () {
            //Add Choose dir browser button
            add_dir_browser_button();

            //Clone and add Smush button
            add_smush_button();
        });

    });

    if ($('div.smush-dir-savings').length > 0) {
        //Update Directory Smush, as soon as the page loads
        var stats_param = {
            action: 'get_dir_smush_stats'
        }
        $.get(ajaxurl, stats_param, function (r) {

            //Hide the spinner
            $('div.smush-dir-savings span.spinner').hide();

            //If there are no errors, and we have a message to display
            if (!r.success && 'undefined' != typeof ( r.data.message )) {
                $('div.wp-smush-scan-result div.content').prepend(r.data.message);
                return;
            }

            //If there is no value in r
            if ('undefined' == typeof ( r.data) || 'undefined' == typeof ( r.data.dir_smush )) {
                //Append the text
                $('div.smush-dir-savings span.wp-smush-stats').append(wp_smush_msgs.ajax_error);
                $('div.smush-dir-savings span.wp-smush-stats span').hide();
                return;
            } else {
                //Update the stats
                update_cummulative_stats(r.data);
            }

        });
    }
    //Close Directory smush modal, if pressed esc
    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            var modal = $('div.dev-overlay.wp-smush-list-dialog');
            //If the Directory dialog is not visible
            if (!modal.is(':visible')) {
                return;
            }
            modal.find('div.close').click();

        }
    });

});
(function ($) {
    var Smush = function (element, options) {
        var elem = $(element);

        var defaults = {
            isSingle: false,
            ajaxurl: '',
            msgs: {},
            msgClass: 'wp-smush-msg',
            ids: []
        };
    };
    $.fn.wpsmush = function (options) {
        return this.each(function () {
            var element = $(this);

            // Return early if this element already has a plugin instance
            if (element.data('wpsmush'))
                return;

            // pass options to plugin constructor and create a new instance
            var wpsmush = new Smush(this, options);

            // Store plugin object in this element's data
            element.data('wpsmush', wpsmush);
        });
    };

})(jQuery);
