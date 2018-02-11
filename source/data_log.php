<?php

	require_once(__DIR__.'/data_account.php');
	
	// tbl_account_role
	class mendeleev_class_log_data extends mendeleev_class_account_data
	{
		private
			$update_id		= NULL,
			$update_time	= NULL;
			
		// Accessors
		public function get_update_time()
		{
			return $this->update_time;
		}
		
		public function get_update_id()
		{
			return $this->update_id;
		}
		
		// Mutators
		public function set_update_time($value)
		{
			$this->update_time = $value;
		}
		
		public function set_update_id($value)
		{
			$this->update_id = $value;
		}
	}
?>