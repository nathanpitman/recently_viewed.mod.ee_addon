<?php

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

class Recently_viewed {

    var $prefs;

    function Recently_viewed() {
		$prefs['limit'] = 5;
		$this->prefs = $prefs;
    }

    function add_entry() {
    	global $limit, $TMPL, $DB, $SESS;

		//get parameters
		$weblog = $TMPL->fetch_param('weblog');
		$entry_id = $TMPL->fetch_param('entry_id');

		//get weblog_id
		$query = $DB->query("SELECT weblog_id FROM exp_weblogs
							 WHERE blog_name = '".$weblog."'");
		$weblog_id = $query->result[0]['weblog_id'];

		//check to see if user has a cookie
		if (isset($_COOKIE['recently_viewed_cookie'])) {
			$session_id = $_COOKIE['recently_viewed_cookie'];
		}
		//set cookie
		else {
			$session_id = md5(microtime());
			$this->set_cookie('recently_viewed_cookie', $session_id, time() + 60*60*24*354, '/', 0, 0);
		}
		
		//add new entry view to db
		$DB->query("INSERT INTO exp_recently_viewed(session_id, weblog_id, entry_id)
					VALUES ('".$DB->escape_str($session_id)."', '".$DB->escape_str($weblog_id)."', '".$DB->escape_str($entry_id)."')");

		//get any existing entry ids
		$query = $DB->query("SELECT view_id FROM exp_recently_viewed
							 WHERE session_id = '".$DB->escape_str($session_id)."'
							 AND weblog_id = '".$DB->escape_str($weblog_id)."'
							 ORDER BY datetime DESC");

		//return entry ids
		if ($query->num_rows > 0) {
			$count = 1;
			$result = $query->result;
			foreach ($result as $r) {
				if ($count > $this->prefs['limit']) {
					$DB->query("DELETE FROM exp_recently_viewed WHERE view_id='".$r['view_id']."'");
				}
				$count++;
			}
		}
		return;
    }

    function get_entries() {
		global $TMPL, $DB;

		//get parameters
		$weblog = $TMPL->fetch_param('weblog');
		$distinct = $TMPL->fetch_param('distinct');

		//get weblog_id
		$query = $DB->query("SELECT weblog_id FROM exp_weblogs
							 WHERE blog_name = '".$weblog."'");
		$weblog_id = $query->result[0]['weblog_id'];

		if (!is_numeric($weblog_id) || empty($weblog_id) || empty($weblog)) {
			return '1';
		}

		if (isset($_COOKIE['recently_viewed_cookie'])) {
			$session_id = $_COOKIE['recently_viewed_cookie'];
		}
		else {
			return '1';
		}

		//get entry ids
		if ($distinct == 'on') {
			$query = $DB->query("SELECT DISTINCT entry_id FROM exp_recently_viewed
								 WHERE session_id = '".$DB->escape_str($session_id)."'
								 AND weblog_id = '".$DB->escape_str($weblog_id)."'
								 ORDER BY datetime DESC");
		}
		else if ($distinct == 'off') {
			$sql = "SELECT entry_id FROM exp_recently_viewed
					WHERE session_id = '".$DB->escape_str($session_id)."'
					AND weblog_id = '".$DB->escape_str($weblog_id)."'
					ORDER BY datetime DESC";
			$query = $DB->query($sql);
		}

		//return entry ids
		if ($query->num_rows > 0) {
			$result = $query->result;
			$q = array();
			foreach ($result as $r) {
				$q[] = $r['entry_id'];
			}
			return implode('|', $q);
		}
		else {
			return '1';
		}
    }

    function set_cookie($name, $value = '', $expires=0, $path='', $domain='', $secure=false, $http_only=false) {
		 header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
	         .(empty($expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s \\G\\M\\T', $expires))
	         .(empty($path)    ? '' : '; path=' . $path)
	         .(empty($domain)  ? '' : '; domain=' . $domain)
	         .(!$secure        ? '' : '; secure')
	         .(!$http_only    ? '' : '; HttpOnly'), false);
	}
}

?>