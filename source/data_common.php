<?php
	class mendeleev_class_common_data
	{
		protected
			$id					= NULL,
			$label				= NULL,
			$item_id			= NULL,	// Used to link entry to a specfic item in a seperate list.
			$details			= NULL,
			$log_update			= NULL,
			$record_deleted		= NULL,
			$time_recorded		= NULL;
		
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{		
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
							
				// If there is a request var with key matching
				// current method name, then the current method 
				// is a set mutator for this request var. Run 
				// it (the set method) with the request var. 
				if(isset($_REQUEST[$key]))
				{					
					$this->$method($_REQUEST[$key]);					
				}
			}			
		}
		
		// Accessors
		public function get_id()
		{
			return $this->id;
		}
		
		public function get_label()
		{
			return $this->label;
		}
		
		public function get_item_id()
		{
			return $this->item_id;
		}
		
		public function get_details()
		{
			return $this->details;
		}
		
		public function get_record_deleted()
		{
			return $this->record_deleted;
		}
		
		public function get_log_update()
		{
			return $this->log_update;
		}
		
		public function get_time_recorded()
		{
			return $this->time_recorded;
		}
		
		// Mutators
		public function set_id($value)
		{
			$this->id = $value;
		}
		
		public function set_label($value)
		{
			$this->label = $value;	
		}
		
		public function set_details($value)
		{
			$this->details = $value;
		}
		
		public function set_item_id($value)
		{
			$this->item_id = $value;
		}
		
		public function set_record_deleted($value)
		{
			$this->record_deleted = $value;
		}
		
		public function set_log_update($value)
		{
			$this->log_update = $value;
		}
		
		public function set_time_recorded($value)	
		{
			$this->time_recorded = $value;
		}
	}
?>
