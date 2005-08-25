<?= caption(txt('3')) ?>
<?= $table->outer_shadow_start() ?>
<table border="0" cellpadding="1" cellspacing="1">
    <?php foreach($information as $entry) { ?>
    <tr>
	<td class="std" width="180"><b><?= $entry[0] ?></b></td>
	<td class="std" width="400"><?= $entry[1] ?></td>
    </tr>
    <?php } ?>
    <tr>
	<td class="std" width="180"><b><?= txt('9') ?></b></td>
	<td class="std" width="400">
	    <?= $cpate['person'] ?> (<a href="<?= mkSelfRef(array('cuser' => $cpate['mbox'])) ?>"><?= $cpate['mbox'] ?></a>
	    <?php if($cpate['mbox'] != $authinfo['mbox'] && $cuser['mbox'] != $authinfo['mbox']) { ?>
		-&gt;<a href="<?= mkSelfRef(array('cuser' => $authinfo['mbox'])) ?>"><?= $authinfo['mbox'] ?></a>
	    <?php } ?>)
	</td>
    </tr>
</table>
<?= $table->outer_shadow_stop() ?>
<br />