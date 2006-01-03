<div class="setup_outer">
<div class="setup_head_outer"><div class="setup_head">
	<h1>Openmailadmin installation</h1>
	<h2>Step 3 - table creation and configuration file</h2>
</div></div>
<div class="setup_body">
	<h3>connection tests</h3>
	<?php if(isset($failure)) { ?>
		<p class="bad"><?= $failure ?></p>
	<?php } else { ?>
		<p class="good">Success.</p>

		<h3>table and index creation</h3>
		<table class="settings">
		<tr><th>tablename</th><th>creation</th></tr>
		<?php foreach($status as $row) { ?>
		<tr>
			<td><?= $row[0] ?></td>
			<?php if($row[1] == 2) { ?>
				<td class="good">successfull</td>
			<?php } else if($row[1] == 1) { ?>
				<td class="tolerated">partially failed - does table already exist?</td>
			<?php } else { ?>
				<td class="bad">failed</td>
			<?php } ?>
		</tr>
		<?php } ?>
		</table>
	<?php } ?>
	<h3>configuration file</h3>
	<p>This has been written as your new configuration file, <cite>./inc/config.local.inc.php</cite></p>
	<p><pre><?php print_r($_POST); ?></pre></p>
</div>