<?php
/**
 * Here will all checks take place.
 */
class InputValidatorSuite
	extends ErrorHandler
{
	private	$invalid	= array();
	private	$missing	= array();

	private	$inputs		= array();
	private	$validate	= array();

	/**
	 * @param	oma	Openmailadmin; the caller
	 * @param	cfg	Array with configuration options.
	 * @param	data	data to be tested typically $_POST
	 * @param	which	array of fields' names from data to be checked
	 */
	public function validate(openmailadmin $oma, array $cfg, $data, $which) {
		// Fieldname as key, cap as its caption and def as its default value.
		$this->inputs['mbox']		= array('cap'	=> txt('83'),
					);
		$this->inputs['pate']		= array('cap'	=> txt('9'),
					'def'	=> $oma->current_user['mbox'],
					);
		$this->inputs['person']	= array('cap'	=> txt('84'),
					);
		$this->inputs['domains']	= array('cap'	=> txt('86'),
					'def'	=> $oma->current_user['domains'],
					);
		$this->inputs['canonical']	= array('cap'	=> txt('7'),
					);
		$this->inputs['quota']	= array('cap'	=> txt('87'),
					);
		$this->inputs['max_alias']	= array('cap'	=> txt('88'),
					);
		$this->inputs['max_regexp']	= array('cap'	=> txt('89'),
					'def'	=> 0,
					);
		$this->inputs['reg_exp']	= array('cap'	=> txt('34'),
					'def'	=> '',
					);
		$this->inputs['a_super']	= array('cap'	=> txt('68'),
					'def'	=> 0,
					);
		$this->inputs['a_admin_domains']	= array('cap'	=> txt('50'),
					'def'	=> 0,
					);
		$this->inputs['a_admin_user']	= array('cap'	=> txt('70'),
					'def'	=> 0,
					);
		// domains
		$this->inputs['domain']	= array('cap'	=> txt('55'),
					);
		$this->inputs['owner']	= array('cap'	=> txt('56'),
					'def'	=> $oma->current_user['mbox'],
					);
		$this->inputs['a_admin']	= array('cap'	=> txt('57'),
					'def'	=> implode(',', array_unique(array($oma->current_user['mbox'], $oma->authenticated_user['mbox']))),
					);
		$this->inputs['categories']	= array('cap'	=> txt('58'),
					);

		// Hash with tests vor sanity and possible error-messages on failure.
		// These will only be processed if a value is given. (I.e. not on the default values from above)
		// If a test fails the next won't be invoked.
		$this->validate['mbox']	= array(array(	'val'	=> 'strlen(~) >= $cfg[\'mbox\'][\'min_length\'] && strlen(~) <= $cfg[\'mbox\'][\'max_length\'] && preg_match(\'/^[a-zA-Z0-9]*$/\', ~)',
							'error'	=> sprintf(txt('62'), $cfg['mbox']['min_length'], $cfg['mbox']['max_length']) ),
						);
		$this->validate['pate']	= array(array(	'val'	=> '$oma->authenticated_user[\'a_super\'] > 0 || $oma->user_is_descendant(~, $oma->authenticated_user[\'mbox\'])',
							),
						);
		$this->validate['person']	= array(array(	'val'	=> 'strlen(~) <= 100 && strlen(~) >= 4 && preg_match(\'/^[\w\s0-9-_\.\(\)]*$/\', ~)',
							),
						);
		$this->validate['domains']	= array(array(	'val'	=> '(~ = trim(~)) && preg_match(\'/^((?:[\w]+|[\w]+\.[\w]+),\s*)*([\w]+|[\w]+\.[\w]+)$/i\', ~)',
							),
						array(	'val'	=> '$oma->domain_check($oma->current_user, $oma->current_user[\'mbox\'], ~)',
							'error'	=> txt('81')),
						);
		$this->validate['canonical']	= array(array(	'val'	=> 'preg_match(\'/\'.openmailadmin::regex_valid_email.\'/i\', ~)',
							'error'	=> txt('64')),
						);
		$this->validate['quota']	= array(array(	'val'	=> 'is_numeric(~) && settype(~, \'int\') && ~ >= 0',
							'error'	=> txt('63')),
						);
		$this->validate['max_alias']	= array(array(	'val'	=> 'is_numeric(~) && settype(~, \'int\') && ~ >= 0',
							'error'	=> txt('63')),
						);
		$this->validate['max_regexp']	= array(array(	'val'	=> 'is_numeric(~) && settype(~, \'int\') && ~ >= 0',
							'error'	=> txt('63')),
						);
		$this->validate['a_super']	= array(array(	'val'	=> 'is_numeric(~) && settype(~, \'int\') && ~ < 3 && ~ >= 0',
							),
						array(	'val'	=> '~ == 0 || $oma->authenticated_user[\'#\'] >= 2 || $oma->authenticated_user[\'a_super\'] > ~ || $oma->authenticated_user[\'#\'] > ~',
							'error'	=> txt('16')),
						);
		$this->validate['a_admin_domains']	= $this->validate['a_super'];
		$this->validate['a_admin_user']	= $this->validate['a_super'];
		// domains
		$this->validate['domain']	= array(array(	'val'	=> 'preg_match(\'/^\'.openmailadmin::regex_valid_domain.\'$/i\', ~)',
							'error'	=> txt('51')),
						);
		$this->validate['owner']	= array(array(	'val'	=> 'strlen(~) >= $cfg[\'mbox\'][\'min_length\'] && strlen(~) <= $cfg[\'mbox\'][\'max_length\'] && preg_match(\'/^[a-zA-Z0-9]*$/\', ~)',
							),
						);
		$this->validate['a_admin']	= array(array(	'val'	=> 'preg_match(\'/^([a-z0-9]+,\s*)*[a-z0-9]+$/i\', ~)',
							),
						);
		$this->validate['categories']	= array(array(	'val'	=> '(~ = trim(~)) && preg_match(\'/^((?:[\w]+|[\w]+\.[\w]+),\s*)*([\w]+|[\w]+\.[\w]+)$/i\', ~)',
							),
						);

		// Now we can set error-messages.
		$error_occured	= $this->iterate_through_fields($oma, $cfg, $data, $which, $this->inputs, $this->validate);
		if($error_occured) {
			if(count($this->invalid) > 0) {
				$this->add_error(sprintf(txt('130'), implode(', ', $this->invalid)));
			}
			if(count($this->missing) > 0) {
				$this->add_error(sprintf(txt('129'), implode(', ', $this->missing)));
			}
		}
		return(!$error_occured);
	}

	/**
	 * To invoke all necessary checks.
	 */
	private function iterate_through_fields(openmailadmin $oma, array $cfg, $data, $which, $inputs, $validate) {
		$error_occured	= false;
		$this->invalid	= array();
		$this->missing	= array();
		foreach($which as $fieldname) {
			// Do we have to care about that field?
			if(isset($inputs[$fieldname])) {
				// Did the user provide it?
				if(isset($data[$fieldname]) && $data[$fieldname] != '') {
					// If so and if we have a rule to check for validity, we have to validate this field.
					if(isset($validate[$fieldname])) {
						foreach($validate[$fieldname] as $test) {
							if(!eval('return ('.str_replace(array('~', '#'), array('$data[\''.$fieldname.'\']', $fieldname), $test['val']).');')) {
								// The given value is invalid.
								$error_occured = true;
								if(isset($test['error'])) {
									$this->add_error($test['error']);
								} else {
									$this->invalid[] = $inputs[$fieldname]['cap'];
								}
								break;
							}
						}
					}
					// $data[$fieldname] = mysql_real_escape_string($data[$fieldname]);
				} else {
					// Assign it a valid value, if possible.
					if(isset($inputs[$fieldname]['def'])) {
						$data[$fieldname]	= $inputs[$fieldname]['def'];
					} else {
						// No value was given and we cannot assign it a default value.
						$error_occured = true;
						$this->missing[] = $inputs[$fieldname]['cap'];
					}
				}
			}
		}
		return $error_occured;
	}

}
?>