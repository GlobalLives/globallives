<div id="wrap">

<h2 class="nav-tab-wrapper">	<a href="?page=wpengine-common" class="nav-tab <?php if($_REQUEST['page'] == 'wpengine-common' || !isset($_REQUEST['page'])) { echo 'nav-tab-active'; } ?>" ><?php echo esc_html( $plugin->get_plugin_title() ) ?></a>
	<?php if( is_super_admin() ): ?>
		<a href="?page=wpengine-advanced" class="nav-tab <?php if($_REQUEST['page'] == 'wpengine-advanced') { echo 'nav-tab-active'; } ?>" >Advanced</a>	
	<?php endif; ?>
</h2>

<table>
<thead>
<th>Query</th>
<th><a href="<?php echo add_query_arg(array('show'=>'slowest')); ?>">Time</a></th>
<th><a href="<?php echo add_query_arg(array('show'=>'frequent')); ?>">Count</a></th>
<th>Trace</th>
</thead>
<tbody>
	<?php foreach($$show as $query): if(strlen($query['query'])>5): ?>
		<tr>
			<td><?php echo @$query['query']; ?></td>
			<td><?php echo @$query['time']; ?></td>
			<td><?php echo @$query['count']; ?></td>
			<td><?php echo @$query['trace']; ?></td>
		</tr>
	<?php endif; endforeach; ?>
</tbody>
</table>