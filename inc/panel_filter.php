<?php
// calculate LIMIT and OFFSET for SQL
if(!isset($_SESSION['limit'])) {
	$_SESSION['limit'] = $cfg['max_elements_per_page'];
}
if(!isset($_SESSION['offset']) || isset($_POST['limit'])) {
	$_SESSION['offset']
		= array('address' => -1, 'regexp' => -1, 'domain' => -1, 'mbox' => -1,
			'addr_page' => 0, 'regx_page' => 0, 'dom_page' => 0, 'mbox_page' => 0);
}
if(isset($_POST['limit'])) {
	if(is_numeric($_POST['limit'])) {
		$_SESSION['limit']	= intval($_POST['limit']);
	} else {
		$_SESSION['limit']	= -1;
	}
}
if(isset($_GET['addr_page']) && is_numeric($_GET['addr_page'])) {
	$_SESSION['offset']['address'] = max(-1, (intval($_GET['addr_page']) - 1) * $_SESSION['limit']);
	$_SESSION['offset']['addr_page'] = intval($_GET['addr_page']);
	unset($_GET['addr_page']);
}
if(isset($_GET['regx_page']) && is_numeric($_GET['regx_page'])) {
	$_SESSION['offset']['regexp'] = max(-1, (intval($_GET['regx_page']) - 1) * $_SESSION['limit']);
	$_SESSION['offset']['regx_page'] = intval($_GET['regx_page']);
	unset($_GET['regx_page']);
}
if(isset($_GET['dom_page']) && is_numeric($_GET['dom_page'])) {
	$_SESSION['offset']['domain'] = max(-1, (intval($_GET['dom_page']) - 1) * $_SESSION['limit']);
	$_SESSION['offset']['dom_page'] = intval($_GET['dom_page']);
	unset($_GET['dom_page']);
}
if(isset($_GET['mbox_page']) && is_numeric($_GET['mbox_page'])) {
	$_SESSION['offset']['mbox'] = max(-1, (intval($_GET['mbox_page']) - 1) * $_SESSION['limit']);
	$_SESSION['offset']['mbox_page'] = intval($_GET['mbox_page']);
	unset($_GET['mbox_page']);
}
if(isset($_SESSION['limit']) && $_SESSION['limit']) {
	$_POST['limit'] = $_SESSION['limit'];
}
// now on creating additional WHERE
if(isset($_POST['filtr']) && !isset($_POST['filtr_addr'])) {
	$_SESSION['filter']['active'] = false;
	$_SESSION['filter']['str'] = array('address' => '', 'regexp' => '', 'domain' => '', 'mbox' => '');
} else if((isset($_SESSION['filter']['active']) && $_SESSION['filter']['active']) || (isset($_POST['filtr_addr']) && $_POST['filtr_addr'] == 1)) {
	$_SESSION['filter']['active'] = true; $_POST['filtr_addr'] = 1;
}
if(isset($_POST['filtr']) && isset($_POST['filtr_addr']) && $_POST['filtr'] == 'set' && trim($_POST['cont']) != '') {
	$filtr_post = '';
	$_SESSION['filter']['str'] = array('address' => '', 'regexp' => '', 'domain' => '', 'mbox' => '');
	switch($_POST['cond']) {
		case 'has':
			$filtr_post = $db->qstr('%'.str_replace(txt('5'), $oma->current_user->mbox, $_POST['cont']).'%');
			break;
		case 'begins':
			$filtr_post = $db->qstr(str_replace(txt('5'), $oma->current_user->mbox, $_POST['cont']).'%');
			break;
		case 'ends':
			$filtr_post = $db->qstr('%'.str_replace(txt('5'), $oma->current_user->mbox, $_POST['cont']));
			break;
	}
	switch($_POST['what']) {
		case 'addr':
			$_SESSION['filter']['str']['address'] = ' AND address LIKE '.$filtr_post;
			break;
		case 'target':
			$_SESSION['filter']['str']['address'] = ' AND dest LIKE '.$filtr_post;
			$_SESSION['filter']['str']['regexp'] = ' AND dest LIKE '.$filtr_post;
			break;
		case 'domain':
			$_SESSION['filter']['str']['address'] = ' AND SUBSTRING_INDEX(address, "@", -1) LIKE '.$filtr_post;
			$_SESSION['filter']['str']['domain'] = ' AND domain LIKE '.$filtr_post;
			break;
		case 'mbox':
			$_SESSION['filter']['str']['mbox'] = ' AND mbox LIKE '.$filtr_post;
			$_SESSION['filter']['str']['domain'] = ' AND owner LIKE '.$filtr_post;
			break;
	}

	$_SESSION['filter']['active'] = true;
	$_SESSION['filter']['what'] = $_POST['what'];
	$_SESSION['filter']['cond'] = $_POST['cond'];
	$_SESSION['filter']['cont'] = $_POST['cont'];
}
if(isset($_SESSION['filter']['active'])) {
	if($_SESSION['filter']['active']) $_POST['filtr_addr'] = 1;
	$_POST['what'] = isset($_SESSION['filter']['what']) ? $_SESSION['filter']['what'] : '';
	$_POST['cond'] = isset($_SESSION['filter']['cond']) ? $_SESSION['filter']['cond'] : '';
	$_POST['cont'] = isset($_SESSION['filter']['cont']) ? $_SESSION['filter']['cont'] : '';
}
if(!isset($_SESSION['filter'])) {
	$_SESSION['filter']['str'] = array('address' => '', 'regexp' => '', 'domain' => '', 'mbox' => '');
}
// DISPLAY
include('./templates/'.$cfg['theme'].'/filter_panel.tpl');

?>