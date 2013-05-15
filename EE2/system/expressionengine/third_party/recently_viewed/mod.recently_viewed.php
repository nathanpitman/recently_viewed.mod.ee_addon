<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recently_viewed {

	public $return_data;

	private $_module_table = 'recently_viewed';
	private $_limit        = 5;
	private $_cookie       = NULL;
	private $_channel;
	private $_entry_id;
	private $_distinct;

	public function __construct() {
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

		// Get tag parameters
		$this->_channel  = $this->EE->TMPL->fetch_param('channel');
		$this->_entry_id = $this->EE->TMPL->fetch_param('entry_id');
		$this->_distinct = $this->EE->TMPL->fetch_param('distinct');
		$this->_limit    = $this->EE->TMPL->fetch_param('limit')
			? $this->EE->TMPL->fetch_param('limit') : $this->_limit;

		// Get cookie
		$this->_cookie = $this->EE->input->cookie('recently_viewed_cookie');
	}


	public function add_entry()
	{
		// Get channel_id
		$channel_id = $this->_get_channel_id();

		// Check to see if user has a cookie
		if ($this->_cookie)
		{
			$session_id = $this->_cookie;
		}
		// Set cookie
		else
		{
			$session_id = md5(microtime());
			$this->EE->functions->set_cookie(
				'recently_viewed_cookie', $session_id, time() + 60*60*24*354
			);
		}
		
		// Add new entry view to db
		$insert_data = array(
			'session_id' => $this->EE->db->escape_str($session_id),
			'channel_id' => $this->EE->db->escape_str($channel_id),
			'entry_id'   => $this->EE->db->escape_str($this->_entry_id)
		);
		$this->EE->db->insert($this->_module_table, $insert_data);
		
		// Get any existing entry ids
		$this->EE->db->select('view_id');
		$this->EE->db->order_by('datetime', 'desc');
		$query = $this->EE->db->get_where($this->_module_table, array(
			'session_id' => $this->EE->db->escape_str($session_id),
			'channel_id' => $this->EE->db->escape_str($channel_id)
			)
		);

		// Return entry ids
		if ($query->num_rows() > 0)
		{
			$count = 1;
			$result = $query->result();
			foreach ($result as $r)
			{
				if ($count > $this->_limit)
				{
					$this->EE->db->delete($this->_module_table, array(
						'view_id' => $r->view_id
					));
				}
				$count++;
			}
		}
		return TRUE;
	}

	public function get_entries()
	{
		// Get channel_id
		$channel_id = $this->_get_channel_id();

		if (!is_numeric($channel_id)
			OR empty($channel_id)
			OR empty($this->_channel))
		{
			return '1';
		}

		if ($this->_cookie)
		{
			$session_id = $this->_cookie;
		}
		else
		{
			return '1';
		}

		// Get entry ids
		if ($this->_distinct == 'yes')
		{
			$this->EE->db->distinct();
		}
		$this->EE->db->select('entry_id');
		$this->EE->db->order_by('datetime', 'desc');
		$query = $this->EE->db->get_where($this->_module_table, array(
			'session_id' => $this->EE->db->escape_str($session_id),
			'channel_id' => $this->EE->db->escape_str($channel_id)
		));

		// Return entry ids
		if ($query->num_rows() > 0)
		{
			$result = $query->result();
			$q = array();
			foreach ($result as $r)
			{
				$q[] = $r->entry_id;
			}
			return implode('|', $q);
		}
		else
		{
			return '1';
		}
	}

	private function _get_channel_id()
	{
		$channel_id = "";

		$this->EE->db->select('channel_id');
		$query = $this->EE->db->get_where('channels', array(
			'channel_name' => $this->_channel
		));

		$result = $query->result();
		if ($query->num_rows() > 0)
		{
			$channel_id = $result[0]->channel_id;
		}

		return $channel_id;
	}
}
/* End of file mod.recently_viewed.php */
/* Location: ./system/expressionengine/third_party/recently_viewed/mod.recently_viewed.php */