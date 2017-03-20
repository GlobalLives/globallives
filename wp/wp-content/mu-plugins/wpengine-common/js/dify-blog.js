//$ = jQuery
jQuery(document).ready(function($) { 
  $(function() {
    var $dify_posts, fetchEntries, formatDate, difyRetryCount, difyRetryLimit;
    difyRetryCount = 0;
    difyRetryLimit = 5;
    $dify_posts = $('.wpe-dify-posts');
    if ($dify_posts.length === 0) {
      return;
    }
    formatDate = function(date_string) {
      var date, day, monthIndex, monthNames, year;
      monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"];
      date = new Date(date_string);
      day = date.getDate();
      monthIndex = date.getMonth();
      year = date.getFullYear();
      return "" + monthNames[monthIndex] + " " + day + ", " + year;
    };
    fetchEntries = function() {
      var page;
      page = parseInt($dify_posts.data('page'));
      return $.ajax({
        url: 'https://my.wpengine.com/dify_posts.json',
        data: {
          show_hidden: $dify_posts.data('show-hidden').toString(),
          page: page,
          source: $dify_posts.data('source'),
          install_name: $dify_posts.data('install-name')
        },
        type: 'GET',
        dataType: 'JSON',
        success: function(json) {
          var $list, li, post, _i, _len, _ref;
          $list = $dify_posts.find('ul');
          _ref = json.posts;
          $('#wpe-dify-plugin .wpe-dify-section-title h2').show();
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            post = _ref[_i];
            li = $('<li><div class="content"></div><div class="wpe-dify-post-date"></div></li>');
            li.find('.content').html(post.content);
            li.find('.wpe-dify-post-date').html(formatDate(post.date));
            if (post.hidden) {
              li.addClass('hidden_post');
            }
            $list.append(li);
          }
          $(".wpe-dify-blog-spinner").hide();
          if (json.next_page) {
            $('.wpe-dify-show-more a').show();
            return $dify_posts.data('page', json.next_page);
          } else {
            return $('.wpe-dify-show-more').hide();
          }
        },
        error: function() {
          $(".wpe-dify-blog-spinner").hide();
          $postbox = $('#wpe-dify-widget').closest('.postbox');

            // If in the wp dashboard add a link to the wpengine blog if we can't get dify content
          if ($postbox.length > 0) {
            $list = $dify_posts.find('ul');
            $list.addClass('wpe-dify-blog-content');
            $list.append('<li><div class="wpe-dify-blog-link"><a href="http://wpengine.com/blog">Visit the WordPress blog</a> &mdash; your source for WordPress news and updates about our managed hosting service.</div></li>');
            return;
          }

          // If in the wpe plugin, keep trying to get the content every 5 seconds until reaching retry limit
          difyRetryCount++;
          if (difyRetryCount <= difyRetryLimit && $postbox.length == 0) {
            //try again
            setTimeout(fetchEntries, 5000);
          }
        },
      });
    };
    $('.wpe-dify-show-more a').on('click', function(e)
    {
        e.preventDefault();
        $('.wpe-dify-show-more a').hide();
        $(".wpe-dify-blog-spinner").show();
        fetchEntries();
    });
    fetchEntries();
  });
});