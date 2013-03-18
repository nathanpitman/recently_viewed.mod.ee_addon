<?php

class Recently_viewed_CP {

    //module vars
	private $module_name = 'Recently_viewed';
	private $module_version = '1.1';
	private $backend_bool = 'n';

    function Recently_viewed_CP() {
		global $PREFS, $DB;

		// get current installed version module version
	    $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name='".$this->module_name."'");
	   
	    // run updates
	    if ($query->num_rows > 0 && $query->row['module_version'] < $this->module_version) {
			
			$old_table = ($PREFS->core_ini['db_prefix']."_recently_viewed_viewed");
			$new_table = ($PREFS->core_ini['db_prefix']."_recently_viewed");
			
			// Rename the recently_viewed_viewed table
			$sql_rename = "ALTER TABLE ".$old_table." RENAME ".$new_table."";
			$DB->query($sql_rename);
			
			// Apply any DB updates to previously installed version
			$sql_view_id_exists = "SHOW COLUMNS FROM exp_recently_viewed LIKE 'view_id'";
			$view_id_exists = $DB->query($sql_view_id_exists);
			// if view_id column does not exist, add it
			if($view_id_exists->num_rows == 0) {
				$sql_view_id_add = "ALTER TABLE exp_recently_viewed ADD view_id int(10) unsigned NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (view_id)";
				$DB->query($sql_view_id_add);
			}
			// update module version in DB	
			$sql_update_complete = "UPDATE exp_modules 
					SET module_version = '".$this->module_version."'
					WHERE module_name = '".$this->module_name."'";
			$DB->query($sql_update_complete);
	    }
	}

    function recently_viewed_module_install() {
        global $DB;

        $sql[] = "CREATE TABLE IF NOT EXISTS exp_recently_viewed (
				 view_id INT(10) unsigned NOT NULL auto_increment,
				 session_id TEXT DEFAULT NULL,
				 weblog_id INT(10) UNSIGNED,
                 entry_id INT(10) UNSIGNED,
				 datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				 PRIMARY KEY (view_id));";

        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend)
				  VALUES ('', '".$this->module_name."', '".$this->module_version."', '".$this->backend_bool."')";

        foreach ($sql as $query) {
            $DB->query($query);
        }

        return true;
    }

    function recently_viewed_module_deinstall() {
        global $DB;

        $sql[] = "DELETE FROM exp_modules WHERE module_name = '".$this->module_name."'";
        $sql[] = "DROP TABLE IF EXISTS exp_recently_viewed";

        foreach ($sql as $query) {
            $DB->query($query);
        }

        return true;
    }

}

?>