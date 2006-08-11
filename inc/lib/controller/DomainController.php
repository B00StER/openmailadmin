<?php
class DomainController
	extends AOMAController
	implements INavigationContributor
{
	public function get_navigation_items() {
		$oma = $this->oma;
		if($this->oma->authenticated_user->a_admin_domains >= 1 || $this->oma->user_get_number_domains($this->oma->current_user->mbox) > 0) {
			return array('link'		=> 'domains.php'.($this->oma->current_user->mbox != $this->oma->authenticated_user->mbox ? '?cuser='.$this->oma->current_user->mbox : ''),
					'caption'	=> txt('54'),
					'active'	=> stristr($_SERVER['PHP_SELF'], 'domains.php'));
		}
		return false;
	}

	public function controller_get_shortname() {
		return 'domain';
	}

/* ******************************* domains ********************************** */
	public $editable_domains;	// How many domains can the current user change?
	/*
	 * Returns a long list with all domains (from table 'domains').
	 */
	public function get_domains() {
		$this->editable_domains = 0;
		$domains = array();

		$query  = 'SELECT * FROM '.$this->tablenames['domains'];
		if($this->authenticated_user->a_super > 0) {
			$query .= ' WHERE 1=1 '.$_SESSION['filter']['str']['domain'];
		} else {
			$query .= ' WHERE (owner='.$this->db->qstr($this->current_user->mbox).' or a_admin LIKE '.$this->db->qstr('%'.$this->current_user->mbox.'%').')'
				 .$_SESSION['filter']['str']['domain'];
		}
		$query .= ' ORDER BY owner, length(a_admin), domain';

		$result = $this->db->SelectLimit($query, $_SESSION['limit'], $_SESSION['offset']['mbox']);
		if(!$result === false) {
			while(!$result->EOF) {
				$row	= $result->fields;
				if($row['owner'] == $this->authenticated_user->mbox
				   || find_in_set($this->authenticated_user->mbox, $row['a_admin'])) {
					$row['selectable']	= true;
					++$this->editable_domains;
				} else {
					$row['selectable']	= false;
				}
				$domains[] = $row;
				$result->MoveNext();
			}
		}

		$this->current_user->n_domains = $this->user_get_number_domains($this->current_user->mbox);

		return $domains;
	}
	/**
	 * May the new user only select from domains which have been assigned to
	 * the reference user? If so, return true.
	 *
	 * @param	reference	Instance of User
	 * @param	tobechecked	Mailbox-name.
	 */
	public function domain_check(User $reference, $tobechecked, $domain_key) {
		if(!isset($reference->domain_set)) {
			$reference->domain_set = $this->get_domain_set($reference->mbox, $reference->domains);
		}
		// new domain-key must not lead to more domains than the user already has to choose from
		// A = Domains the new user will be able to choose from.
		$dom_a = $this->get_domain_set($tobechecked, $domain_key, false);
		// B = Domains the creator may choose from (that is $reference['domain_set'])?
		// Okay, if A is part of B. (Thus, no additional domains are added for user "A".)
		// Indication: A <= B
		if(count($dom_a) == 0) {
			// This will be only a warning.
			$this->ErrorHandler->add_error(txt('80'));
		} else if(count($dom_a) > count($reference->domain_set)
			   && count(array_diff($dom_a, $reference->domain_set)) > 0) {
			// A could have domains which the reference cannot access.
			return false;
		}

		return true;
	}
	/*
	 * Adds a new domain into the corresponding table.
	 * Categories are for grouping domains.
	 */
	public function domain_add($domain, $props) {
		$props['domain'] = $domain;
		if(!$this->validator->validate($props, array('domain', 'categories', 'owner', 'a_admin'))) {
			return false;
		}

		if(!stristr($props['categories'], 'all'))
			$props['categories'] = 'all,'.$props['categories'];
		if(!stristr($props['a_admin'], $this->current_user->mbox))
			$props['a_admin'] .= ','.$this->current_user->mbox;

		$this->db->Execute('INSERT INTO '.$this->tablenames['domains'].' (domain, categories, owner, a_admin) VALUES (?, ?, ?, ?)',
				array($domain, $props['categories'], $props['owner'], $props['a_admin']));
		if($this->db->Affected_Rows() < 1) {
			$this->ErrorHandler->add_error(txt('134'));
		} else {
			$this->user_invalidate_domain_sets();
			return true;
		}

		return false;
	}
	/*
	 * Not only removes the given domains by their ids,
	 * it deactivates every address which ends in that domain.
	 */
	public function domain_remove($domains) {
		// We need the old domain name later...
		if(is_array($domains) && count($domains) > 0) {
			if($this->cfg['admins_delete_domains']) {
				$result = $this->db->SelectLimit('SELECT ID, domain'
					.' FROM '.$this->tablenames['domains']
					.' WHERE (owner='.$this->db->qstr($this->authenticated_user->mbox).' OR a_admin LIKE '.$this->db->qstr('%'.$this->authenticated_user->mbox.'%').') AND '.db_find_in_set($this->db, 'ID', $domains),
					count($domains));
			} else {
				$result = $this->db->SelectLimit('SELECT ID, domain'
					.' FROM '.$this->tablenames['domains']
					.' WHERE owner='.$this->db->qstr($this->authenticated_user->mbox).' AND '.db_find_in_set($this->db, 'ID', $domains),
					count($domains));
			}
			if(!$result === false) {
				while(!$result->EOF) {
					$del_ID[] = $result->fields['ID'];
					$del_nm[] = $result->fields['domain'];
					$result->MoveNext();
				}
				if(isset($del_ID)) {
					$this->db->Execute('DELETE FROM '.$this->tablenames['domains'].' WHERE '.db_find_in_set($this->db, 'ID', $del_ID));
					if($this->db->Affected_Rows() < 1) {
						if($this->db->ErrorNo() != 0) {
							$this->ErrorHandler->add_error($this->db->ErrorMsg());
						}
					} else {
						$this->ErrorHandler->add_info(txt('52').'<br />'.implode(', ', $del_nm));
						// We better deactivate all aliases containing that domain, so users can see the domain was deleted.
						foreach($del_nm as $domainname) {
							$this->db->Execute('UPDATE '.$this->tablenames['virtual'].' SET active = 0, neu = 1 WHERE address LIKE '.$this->db->qstr('%'.$domainname));
						}
						// We can't do such on REGEXP addresses: They may catch more than the given domains.
						$this->user_invalidate_domain_sets();
						return true;
					}
				} else {
					$this->ErrorHandler->add_error(txt('16'));
				}
			} else {
				$this->ErrorHandler->add_error(txt('16'));
			}
		} else {
			$this->ErrorHandler->add_error(txt('11'));
		}

		return false;
	}
	/*
	 * Every parameter is an array. $domains contains IDs.
	 */
	public function domain_change($domains, $change, $data) {
		$toc = array();		// to be changed

		if(!$this->validator->validate($data, $change)) {
			return false;
		}

		if(!is_array($change)) {
			$this->ErrorHandler->add_error(txt('53'));
			return false;
		}
		if($this->cfg['admins_delete_domains'] && in_array('owner', $change))
			$toc[] = 'owner='.$this->db->qstr($data['owner']);
		if(in_array('a_admin', $change))
			$toc[] = 'a_admin='.$this->db->qstr($data['a_admin']);
		if(in_array('categories', $change))
			$toc[] = 'categories='.$this->db->qstr($data['categories']);
		if(count($toc) > 0) {
			$this->db->Execute('UPDATE '.$this->tablenames['domains']
				.' SET '.implode(',', $toc)
				.' WHERE (owner='.$this->db->qstr($this->authenticated_user->mbox).' or a_admin LIKE '.$this->db->qstr('%'.$this->authenticated_user->mbox.'%').') AND '.db_find_in_set($this->db, 'ID', $domains));
			if($this->db->Affected_Rows() < 1) {
				if($this->db->ErrorNo() != 0) {
					$this->ErrorHandler->add_error($this->db->ErrorMsg());
				} else {
					$this->ErrorHandler->add_error(txt('16'));
				}
			}
		}
		// changing ownership if $this->cfg['admins_delete_domains'] == false
		if(!$this->cfg['admins_delete_domains'] && in_array('owner', $change)) {
			$this->db->Execute('UPDATE '.$this->tablenames['domains']
				.' SET owner='.$this->db->qstr($data['owner'])
				.' WHERE owner='.$this->db->qstr($this->authenticated_user->mbox).' AND '.db_find_in_set($this->db, 'ID', $domains));
		}
		$this->user_invalidate_domain_sets();
		// No domain be renamed?
		if(! in_array('domain', $change)) {
			return true;
		}
		// Otherwise (and if only one) try adapting older addresses.
		if(count($domains) == 1) {
			// Grep the old name, we will need it later for replacement.
			$domain = $this->db->GetRow('SELECT ID, domain AS name FROM '.$this->tablenames['domains'].' WHERE ID = '.$this->db->qstr($domains[0]).' AND (owner='.$this->db->qstr($this->authenticated_user->mbox).' or a_admin LIKE '.$this->db->qstr('%'.$this->authenticated_user->mbox.'%').')');
			if(!$domain === false) {
				// First, update the name. (Corresponding field is marked as unique, therefore we will not receive doublettes.)...
				$this->db->Execute('UPDATE '.$this->tablenames['domains'].' SET domain = '.$this->db->qstr($data['domain']).' WHERE ID = '.$domain['ID']);
				// ... and then, change every old address.
				if($this->db->Affected_Rows() == 1) {
					// dest
					$this->db->Execute('UPDATE '.$this->tablenames['virtual'].' SET neu = 1, dest = REPLACE(dest, '.$this->db->qstr('@'.$domain['name']).', '.$this->db->qstr('@'.$data['domain']).') WHERE dest LIKE '.$this->db->qstr('%@'.$domain['name'].'%'));
					$this->db->Execute('UPDATE '.$this->tablenames['virtual_regexp'].' SET neu = 1, dest = REPLACE(dest, '.$this->db->qstr('@'.$domain['name']).', '.$this->db->qstr('@'.$data['domain']).') WHERE dest LIKE '.$this->db->qstr('%@'.$domain['name'].'%'));
					// canonical
					$this->db->Execute('UPDATE '.$this->tablenames['user'].' SET canonical = REPLACE(canonical, '.$this->db->qstr('@'.$domain['name']).', '.$this->db->qstr('@'.$data['domain']).') WHERE canonical LIKE '.$this->db->qstr('%@'.$domain['name']));
				} else {
					$this->ErrorHandler->add_error($this->db->ErrorMsg());
				}
				return true;
			} else {
				$this->ErrorHandler->add_error(txt('91'));
			}
		} else {
			$this->ErrorHandler->add_error(txt('53'));
		}

		return false;
	}

}
?>