<script>
(function() {
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://google-analytics.com/analytics.js', 'ga');

    ga('create', '<?php echo esc_attr( $data[ Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME ] ); ?>', 'auto');
    ga('send', 'pageview');
	})();
</script>
