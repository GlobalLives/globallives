<div class="wrap ga-wrap" id="ga-stats-container">
	<?php if ( ! empty( $chart ) ) : ?>
    <div class="ga-panel ga-panel-default">
        <div class="ga-panel-heading"><strong><?php _e( "Pageviews - Last 7 days vs previous 7 days" ) ?></strong></div>
        <div class="ga-panel-body ga-chart">
            <div id="chart_div" style="width: 100%;"></div>
            <div class="ga-loader-wrapper stats-page">
                <div class="ga-loader stats-page-loader"></div>
            </div>
        </div>
    </div>
	<?php endif; ?>

	<?php if ( ! empty( $boxes ) ) : ?>
    <div class="ga-panel ga-panel-default">
        <div class="ga-panel-heading"><strong><?php _e( "Comparison - Last 7 days vs previous 7 days" ) ?></strong>
        </div>
        <div class="ga-panel-body">
            <div class="ga-row">
					<?php foreach ( $boxes as $box ) : ?>
                        <div class="ga-box">
                            <div class="ga-panel ga-panel-default">
                                <div class="ga-panel-body ga-box-centered">
                                    <div class="ga-box-label"><?php echo $box['label'] ?></div>
                                    <div class="ga-box-diff"
                                         style="color: <?php echo $box['color'] ?>;"><?php echo Ga_Helper::format_percent( $box['diff'] ); ?></div>
                                    <div class="ga-box-comparison"><?php echo $box['comparison'] ?></div>
                                </div>
                            </div>
                        </div>
					<?php endforeach; ?>
            </div>
        </div>
    </div>
	<?php endif; ?>

	<?php if ( ! empty( $sources ) ) : ?>
        <div class="ga-panel ga-panel-default">
            <div class="ga-panel-heading"><strong><?php _e( "Top 5 Traffic Sources for the past 7 days" ) ?></strong>
            </div>
            <div class="ga-panel-body">

                <div id="table-container">
                    <table class="ga-table">
                        <tr>
                            <td colspan="2">
                            </td>
                            <th style="text-align: right;">
								<?php _e( 'Pageviews' ); ?>
                            </th>
                            <th style="text-align: right;">
								<?php echo '%'; ?>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td class="ga-col-pageviews" style="text-align: right">
                                <div style="font-size: 16px;"><?php echo $sources['total'] ?></div>
                                <div style="color: grey; font-size: 10px;">% of
                                    Total: <?php echo Ga_Helper::format_percent( ( ! empty( $sources['total'] ) ) ? number_format( $sources['sum'] / $sources['total'] * 100,
										2, '.', ' ' ) : 100 );
									?>
                                    (<?php echo $sources['sum'] ?>)
                                </div>
                            </td>
                            <td class="ga-col-progressbar" style="text-align: right">
                                <div style="font-size: 16px;"><?php echo $sources['total'] ?></div>
                                <div style="color: grey; font-size: 10px;">% of
                                    Total: <?php echo Ga_Helper::format_percent( ( ! empty( $sources['total'] ) ) ? number_format( $sources['sum'] / $sources['total'] * 100,
										2, '.', ' ' ) : 100 );
									?>
                                    (<?php echo $sources['sum'] ?>)
                                </div>
                            </td>
                        </tr>
						<?php foreach ( $sources['rows'] as $key => $source ): ?>
                            <tr>
                                <td style="width: 5%;text-align: right"><?php echo $key ?>.</td>
                                <td class="ga-col-name">
									<?php if ( $source['name'] != '(direct) / (none)' ) : ?>
                                        <a class="ga-source-name" href="<?php echo $source['url'] ?>"
                                           target="_blank"><?php echo $source['name'] ?></a>
									<?php else: ?>
										<?php echo $source['name'] ?>
									<?php endif; ?>
                                </td>
                                <td style="text-align: right"><?php echo $source['number'] ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar"
                                             aria-valuenow="<?php echo $source['percent'] ?>" aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: <?php echo $source['percent'] ?>%;"></div>
                                        <span style="margin-left: 10px;"><?php echo Ga_Helper::format_percent( $source['percent'] ); ?></span>
                                    </div>
                                </td>
                            </tr>
						<?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
	<?php endif; ?>

	<?php if ( ! empty( $chart ) ) : ?>
        <script type="text/javascript">

            ga_charts.init(function () {

                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Day');
                    data.addColumn('number', '<?php echo $labels['thisWeek'] ?>');
                    data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                    data.addColumn('number', '<?php echo $labels['lastWeek'] ?>');
                    data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});

					<?php foreach ( $chart as $row ) : ?>
                    data.addRow(['<?php echo $row['day'] ?>', <?php echo $row['current'] ?>, ga_charts.createTooltip('<?php echo $row['day'] ?>', '<?php echo $row['current'] ?>'), <?php echo $row['previous'] ?>, ga_charts.createTooltip('<?php echo $row['previous-day'] ?>', '<?php echo $row['previous'] ?>')]);
					<?php endforeach; ?>
                    ga_charts.events(data);
                    ga_charts.drawChart(data);
                    ga_loader.hide();
                }
            );
        </script>
	<?php endif; ?>
</div>
