<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recently_viewed_upd {

	private $_module_version = '1.1';
	private $_module_name    = 'Recently_viewed';
	private $_module_table   = 'recently_viewed';
	private $_cp_backend     = 'n';
	private $_publish_fields = 'n';

	public function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

		$this->EE->load->dbforge();
	}

	public function install()
	{
		$module_data = array(
			'module_name'        => $this->_module_name,
			'module_version'     => $this->_module_version,
			'has_cp_backend'     => $this->_cp_backend,
			'has_publish_fields' => $this->_publish_fields
		);

		$this->EE->db->insert('modules', $module_data);

		$fields = array(
			'view_id'    => array(
				'type'           => 'int',
				'constraint'     => '10',
				'unsigned'       => true,
				'auto_increment' => true
			),
			'session_id' => array(
				'type' => 'text',
				'null' => TRUE
			),
			'channel_id' => array(
				'type'       => 'int',
				'constraint' => '10',
				'unsigned'   => true
			),
			'entry_id'   => array(
				'type'       => 'int',
				'constraint' => '10',
				'unsigned'   => true
			),
			'datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('view_id', TRUE);
		$this->EE->dbforge->create_table($this->_module_table, TRUE);

		unset($fields);

		return TRUE;
	}

	public function uninstall()
	{
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array(
			'module_name' => $this->_module_name
		));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', $this->_module_name);
		$this->EE->db->delete('modules');

		$this->EE->dbforge->drop_table($this->_module_table);

		return TRUE;
	}

	public function update($current = '')
	{
		// Get current installed version module version
		$this->EE->db->select('module_version');
		$query = $this->EE->db->get_where('modules', array(
			'module_name' => $this->_module_name
		));

		// Run updates
		if ($query->num_rows() > 0
		&& version_compare(
			$query->row('module_version'), $this->_module_version, '<'
		))
		{
			$old_table = "recently_viewed_viewed";

			// Rename the recently_viewed_viewed table
			$this->EE->dbforge->rename_table($old_table, $this->_module_table);

			// Apply any DB updates to previously installed version
			$sql_view_id_exists = "SHOW COLUMNS FROM " . $this->_module_table
			. " LIKE 'view_id'";
			$view_id_exists = $this->EE->db->query($sql_view_id_exists);

			// If view_id column does not exist, add it
			if ($view_id_exists->num_rows() == 0)
			{
				$sql_view_id_add = "ALTER TABLE " . $this->_module_table
				. " ADD view_id int(10) unsigned NOT NULL"
				. " AUTO_INCREMENT FIRST,"
				. " ADD PRIMARY KEY (view_id)";
				$this->EE->db->query($sql_view_id_add);
			}

			// Update module version in DB
			$fields = array(
				'module_version' => $this->_module_version
			);
			$this->EE->db->where('module_name', $this->_module_name);
			$this->EE->db->update('modules', $fields);
		}

		return TRUE;
	}
}
/* End of file upd.recently_viewed.php */
/* Location: ./system/expressionengine/third_party/modules/recently_viewed/upd.recently_viewed.php */