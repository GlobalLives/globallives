<div class="wrap ga-wrap">

    <div class="form-group">
        <select id="range-selector" autocomplete="off">
            <option value="7daysAgo">Last 7 Days</option>
            <option value="30daysAgo" selected="selected">Last 30 Days</option>
            <option value="90daysAgo">Last 90 Days</option>
        </select>

        <select id="metrics-selector" autocomplete="off">
            <option value="pageviews">Pageviews</option>
            <option value="sessions">Visits</option>
            <option value="users">Users</option>
            <option value="organicSearches">Organic Search</option>
            <option value="visitBounceRate">Bounce Rate</option>
        </select>

        <div class="ga-loader-wrapper">
            <div class="ga-loader"></div>
        </div>
    </div>

    <div>
        <div id="chart_div" style="width: 100%;"></div>
        <div>
            <div id="boxes-container">
                <div class="ga-box-row">
	                <?php if ( ! empty( $boxes ) ) : ?>
	                <?php $iter = 1; ?>
	                <?php foreach ( $boxes as $k => $v ) : ?>
                    <div class="ga-box-column ga-box-dashboard">
                        <div style="color: grey; font-size: 13px;"
                             id="ga_box_dashboard_label_<?php echo $k; ?>"><?php echo $v['label'] ?></div>
                        <div style="font-size: 15px;"
                             id="ga_box_dashboard_value_<?php echo $k; ?>"><?php echo $v['value'] ?></div>
                    </div>
	                <?php if ( ( ( $iter ++ ) % 3 ) == 0 ) : ?>
                </div>
                <div class="ga-box-row">
	                <?php endif; ?>
	                <?php endforeach; ?>
	                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 5px;"><?php echo sprintf( '<a href="%s">' . __( 'Show more details' ) . '</a>',
			$more_details_url ); ?></div>
</div>

<script type="text/javascript">
	<?php if ( ! empty( $chart ) ) : ?>
    dataArr = [['Day', 'Pageviews'],<?php
	    $arr = "";
	    foreach ( $chart as $row ) {
		    if ( $arr ) {
			    $arr .= ",";
		    }
		    $arr .= "['" . $row['day'] . "'," . $row['current'] . "]";
	    }

	    echo $arr;
	    ?>];

    ga_dashboard.init(dataArr);
    ga_dashboard.events(dataArr);
	<?php endif; ?>
</script>
