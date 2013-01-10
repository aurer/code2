<?php

/*
    Main XML class
	
---------------------------------------------------------------------------------- */

class Xml
{
	protected $_config_file;		// Path to main config file
	protected $_compile_filename;	// Name of compiled config file
	protected $_compile_dir;		// Directory of compiled config file
	public $compiled_config;		// Path to compiled config used by the system
	public $compile_errors=array();	// Any errors when compiling config
	
	public function __construct()
	{
		$this->_config_file = defined('CONFIG')? CONFIG : APPPATH.'config/config.xml';
		$this->_compile_dir = defined('CONFIG_COMPILE_DIR')? CONFIG_COMPILE_DIR : APPPATH.'cache';
		$this->_compile_filename = str_ireplace(".xml", ".compiled.xml", end(explode("/", $this->_config_file)));
		$this->compiled_config = "$this->_compile_dir/$this->_compile_filename";
	}
	
	/* 
		Creates the system config file in the directory $_compile_dir
		Uses Xml::_fetch_includes to build a compiled version of the xml
	*/
	public function compile_config($minify=false)
	{		
		$file_data = $this->_fetch_includes($minify) or die('Xml::_fetch_includes failed to build config');
		log_message('debug', "Attempting to recompile the config");
		
		// Create comile dir if doesn't exist
		if(!is_dir($this->_compile_dir)){
			if(!@mkdir($this->_compile_dir, 0777)){
				log_message('error', "Failed to create config compile directory, check file permissions");
				return false;
			}
		}
		// Try to make it writable if it's not
		if(!is_writable($this->_compile_dir)){
			if(!@chmod($this->_compile_dir, 0777)){
				log_message('error', "Compile directory is not writable, failed to make it so");
				return false;
			}
		}
		
		// Validate the compiled xml
		if(!$this->validate($file_data)){
			log_message('error', "Config file is invalid XML, Config will not be reloaded", 'ERROR');
			return false;
		}
		
		// Create a back of the curent config if it exists already
		if(is_file($this->compiled_config)){
			$backup = file_get_contents($this->compiled_config);
			if(!@file_put_contents("$this->_compile_dir/previous.$this->_compile_filename", $backup)){
				log_message('error', "Failed to backup config file, check file permissions", 'ERROR');
			}
		}
		// Create the compiled config file
		if(!@file_put_contents($this->compiled_config, $file_data)){
			log_message('error', "Failed to compile config file '$this->compiled_config', check file permissions",'ERROR');
			return false;
		}
		log_message('debug', "The config was successfully recompiled");
		return true;
	}
	
	/*
		Used in Xml::compile_config()
		Returns a string of compiled version of the config with all includes merged in
		$minify will try to strip all whitespace
	*/
	protected function _fetch_includes($file=false, $minify=false)
	{
		$level=0; // Holds depth of includes
		static $includes=array();
		static $errors=array();
		$result=''; // Holds the resulting XML
		
		if($level > 10) return; // Stop at 10 levels of includes
		
		// Check if config file exists
		if(!file_exists($this->_config_file)){
			die("Config file not found");
		}
		
		$xml = (!$file)? file($this->_config_file) : file($file);
		
		// Read through each line
		foreach($xml as $key=>$line){
			if(trim($line) != ''){
				if( preg_match("/<include[\s\S][^\n<]+[\/>]/", $line, $matches) ){
					$xml = simplexml_load_string($line);
					if($xml){
						foreach($xml->attributes() as $key=>$val)
						{
							if($key == 'file')
							{
								if(file_exists(APPPATH.$val))
								{
									if(trim($val) == trim($file))
									{
										$line = "<!-- Error: $file is trying to include itself! -->\n";
										array_push($this->compile_errors, $line);
									}
									elseif( end(explode('.', $val)) != 'xml' )
									{
										$line = "<!-- Error: Not incuding '$val', only XML files will be included -->\n";
										array_push($this->compile_errors, $line);
									}
									else
									{
										$include_count += 1;
										array_push($includes, APPPATH.$val);
										$line = "<!-- Start $val -->\n";
										$line .= $this->_fetch_includes(APPPATH.$val, $minify)."\n";
									}
								}
								else
								{								
									$line = "<!-- Error: Failed to include ".APPPATH.$val.", file not found -->\n";
									array_push($this->compile_errors, $line);
								}
							}
						}
					}
				}
				if($minify) $result .= trim($line);
				else $result .= $line;
			}
		}
		if(!$file) $result = $result . "\n".
			"<!-- XML Compile Data\n\n".
			"Includes: " . count($includes) . "\n".
			"Errors: " . count($this->compile_errors) . "\n".
			"\n-->\n";
		return $result;
	}
	
	
	/*
		Attempt to validate xml using libxml
	*/
	public function validate($xml, $filename=null)
	{	
		$filename = (!$filename)? "XML" : $filename; 
		if (!simplexml_load_string($xml)){
			echo "Error in ".$filename."\n";
			foreach(libxml_get_errors() as $error){
				echo "\t", $error->message;
			}
			return false;
		}
		else
			return true;
	}
	
}



/*
	Config class
	
---------------------------------------------------------------------------------- */

class XMLConfig extends Xml
{
	public $xml;  			// Holds the simplexml object
	public $config_attributes=array();	// Array containing config attributes
	
	function __construct()
	{
		libxml_use_internal_errors(true);
		parent::__construct();
		
		if(!is_writable($this->_compile_dir)){
			if(!@chmod($this->_compile_dir, 0755)){
				echo "Please make sure the application/cache folder is writable by the server<br>\n";
				echo "Either use your program of choice to make the change or run the following command in the terminal:<br/>\n";
				echo "<pre>chmod o+w ".FCPATH.$this->_compile_dir."</pre>";
				exit;
			}
		}

		if(!is_file($this->compiled_config))
			$this->compile_config();
		
		$this->xml = simplexml_load_file($this->compiled_config);
		
		foreach($this->xml->attributes() as $key=>$val){
			$this->attributes[$key] = (string)$val;
		}
	}
	
	
	/*
		Returns an array of attributes for a selected field element
		
		TODO - rename to field_attributes
	*/
	public function field_info($section, $field)
	{

		$data=array();

		// Look up the section ID as an array key to use below
		$section_id = $this->_section_id($section);
		$field_id = $this->_field_id($this->xml->section[$section_id]->table->field, $field );
		
		if(!is_int($section_id)){
			return false;
		}
		if(!is_int($field_id)){
			return false;
		}
		
		$attr_array = $this->xml->section[$section_id]->table->field[$field_id]->attributes();

		foreach ($attr_array as $key=>$val) {
			$data[$key] = (string)$val;
		}

		if ($data) return $data;
	}
	
	
	/*
		Returns two level array of fields and attributes for a given section name
		If $associative is true each field array will have the name of the field as a key

		TODO - rename to fields_for_section
	*/
	public function section_fields($section, $associative=false)
	{
		$section = $this->get_section($section);
		
		if(!$section)
			return false;
		
		if(!$associative){
			foreach($section->table->field as $val){
				$attributes[] = $this->_attributes_to_array( $val->attributes() );
			}
		}
		else{
			foreach($section->table->field as $val){
				$attr = $this->_attributes_to_array( $val->attributes() );
				$attributes[ $attr['name'] ] = $attr;
			}
		}
		
		return $attributes;
	}
	
	
	/*
		Returns true if section exists, false if not#
	*/
	public function is_section($section)
	{
		$id = $this->_section_id($section);

		if ($id === false)
			return false;
		else 
			return true;
	}

	/*
		TODO - Add a is_table funciton similar to above
	*/
    
    /*
    	Returns true if view exists, false if not
	*/
	public function is_view($theview)
	{
		$is_view=false;
        $views = $this->views();
        foreach($views as $view){            
            if($theview == $view['name']){
                $is_view=true;
                break;
            }
        }
        return $is_view;
	}
		
	
	/*
		returns a simplexml object of a specific section

		TODO - rename to _get_section_object and make private
	*/
	public function get_section($section)
	{
		$id = $this->_section_id($section);
		
		if ($id !== false)
			return $this->xml->section[$id];
		else {
			trigger_error("Config error getting section '$section'");
			return false;
		}
	}
		
	
	/*
		Returns an array of section names extracted from the config
	*/
	public function section_names()
	{
		$section_names = array();
		foreach ($this->xml->section as $section) {
			$name = (string)$section['name'];
			array_push($section_names, $name);
		}
		return $section_names;
	}
	
	/*
		Returns the primary key for a given section
	*/
	public function section_pk($section)
	{	
		if( !$this->is_section($section) )
			return false;
			
		$fields = $this->section_fields($section);
		$pk=null;
		foreach($fields as $field){
			if( isset($field['pk']) ){
				$pk = $field['name'];
				break;
			}
		}
		return $pk;
	}
	
	/*
		Returns an array of view names extracted from the config
		Or a single one if $name is supplied and it exists
	*/
	public function views($name=false)
	{
		foreach ($this->xml->view as $key=>$section){
			$view['name'] = (string)$section['name'];
			$view['statement'] = trim((string)($section));
			$view['table'] = trim((string)($section['table']));
			$views[$view['name']] = $view;
		}
        if($name){
        	if(array_key_exists($name, $views)){
        		return $views[$name];	
        	} else {
        		return false;
        	}  
        }
		return $views;
	}
	
	
	/*
		Returns names for all the required fields in a section
	*/
	public function required_fields($section)
	{
		$required_fieldnames = array();
		
		$fields = $this->section_fields($section);
		foreach($fields as $field){
			if( isset($field['required']) && $field['required'] == 'true'){
				array_push($required_fieldnames, $field['name']);
			}
		}
		return $required_fieldnames;
	}
	
	
	/* 
		Returns fields array with values to be used in a form
		Creates field 'options' array using 'values' and 'labels'
	*/
	public function fields_for_form($section)
	{
		$fields = $this->section_fields($section);
		
		$sanitised_fields = array(); // Holds clean version of fields;
		
		if( !is_array($fields) )
			return false;
		
		foreach($fields as $field)
		{
			/*
				Sanitise the 'type'
			*/
			// Textarea
			if($field['type'] == 'text')
				$field['type'] = 'textarea';
			
			// Checkbox
			elseif($field['type'] == boolean)
				$field['type'] = 'checkbox';
			
			// Select
			elseif( isset($field['values']) )
				$field['type'] = 'select';
			
			// Password
			elseif( strstr($field['name'], 'password') )
				$field['type'] = 'checkbox';
			
			// Hidden
			elseif( $field['hidden'] == 'true')
				$field['type'] = 'hidden';
			
			// Email
			elseif( strstr($field['name'], 'email') )
				$field['type'] = 'email';
			
			// Default to text
			else
				$field['type'] = 'text';
			
			/*
				Sanitise Other attributes
			*/
			if( !isset( $field['label'] ) )
				$field['label'] = $field['name'];
			
				
			/*
				Build select field options array
			*/
			if( isset($field['values']) && isset($field['labels']) ){
				$values = explode(',', $field['values']);
				$labels = explode(',', $field['labels']);
				foreach($values as $key=>$val){
					$options[$val] = $labels[$key];
				}
				$field['options'] = $options;
			} else {
				$field['options'] = null;
			}
			array_push($sanitised_fields, $field);
		}
		
		return $sanitised_fields;
	}
	
	
	/*************************************
		START PROTECTED FUNCTIONS
	*************************************/
	
	/*
		Returns the array key for a specified section
	*/
	protected function _section_id($name)
	{
		$id = false;
		$found = false;
		
		// Loop sections and look for matching name attribute
		$i=0;
		foreach ($this->xml->section as $section) {

			$att = $this->_attributes_to_array($section->attributes());
			if ($att['name'] == $name) {
				$id = $i;
				$found = true;
				break;
			}
			$i++;
		}
		if ($found)
			return $id;
		else
			return;
	}
	
	/*
		Returns the array key for a field within specified section->table
		Expects $field_array to be a SimpleXML object
	*/
	protected function _field_id($field_array, $name)
	{
		$id = false;
		$found = false;
		
		// Loop fields which should be a simpleXML object
		$i=0;
		foreach ($field_array as $field) {
			$attr = $this->_attributes_to_array($field->attributes());
			if ($attr['name'] == $name) {
				$id = $i; // Set ID
				$found = true;
				break; // Stop loop
			}
			$i++;
		}
		if ($found)
			return $id;
		else
			return;
	}
	
	/*
		Convert SimpleXMLObject attributes to a one dimentional array
	*/
	protected function _attributes_to_array($object)
	{
		$result = (array)$object;
		return $result['@attributes'];
	}
}



/*
	Database manipulation class
	
---------------------------------------------------------------------------------- */

class DB_Manager extends XMLConfig
{
	private $CI;
	private $db;				// Database connection link
	public $errors=array();		// Errors!
	
	function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->db =& $this->CI->db;
		$this->CI->load->dbforge();
	}
	
	/*
		Recompiles the config
	*/
	public function reload_config($drop_tables=false)
	{		
		$sections = $this->section_names();
		$config_views = $this->views();
		
		$this->compile_config();
		
		$tables = $this->_list_tables();
		$views  = $this->_list_views();
		
		// Loop over sections and try to create any tables that don't exist yet
		foreach($sections as $section){
			if(!in_array($section, $tables)){
				$this->create_table($section);
			}
		}
		
		// Loop over views from config and create any that don't exist yet
		foreach($config_views as $view=>$view_data){
			if( !in_array($view, $views) ){
				$query = $this->db->query("CREATE VIEW $view AS ".$view_data['statement']);
			}
		}
		
		// Drop tables and views that are not in the config if $drop_tables is true
		if($drop_tables){
			foreach($tables as $table){
				if( !in_array($table, $sections) ){
					$result = $this->db->query("DROP TABLE $table");
				}
			}
			foreach($views as $view){
				if( !array_key_exists($view, $config_views) ){
					$result = $this->db->query("DROP VIEW $view");
				}
			}
		}
		return true;
		
	}
	
	
	/*
		Creates a table based on a section
		Requires only the section name
	*/
	public function create_table($section)
	{	
		if( !$this->is_section($section))
			return false;
			
		$field_args = $this->fields_for_database($section);
		
		$qry = "CREATE TABLE IF NOT EXISTS $section(\n\t" . implode(",\n\t", $field_args) . "\n)\n";
			
		$result = $this->db->query($qry);
		if($result)
			return true;
		else
			return false;
		
	}
	
	
	/*
		Takes the name of a section and returns an array of strings ready to be used in create/alter table functions
	*/
	public function fields_for_database($section)
	{
		if( !$this->is_section($section))
			return false;
			
		$fields = $this->section_fields($section);
		
		if(!$fields)
			return false;
		
		foreach($fields as $field){
			$field_name = $field['name'];
			$field_type = $field['type'];
			
			// Make tinyint if boolean
			if($field_type == 'boolean')
					$field_type = 'tinyint';
			
			// Add length if varchar
			if($field_type == 'varchar')
					$field_type = isset($field['length'])? "$field_type($field[length])" : "$field_type(255)";
			
			// Check for default
			if(isset($field['default'])){
				if($field['default'] == 'now()' OR $field['default'] == 'CURRENT_TIMESTAMP' OR is_numeric($field['default'])){
					$field_default = " default $field[default]";
				} else $field_default = " default '$field[default]'";
			} else $field_default = null;
			
			// Check  for required attribute
			if(isset($field['required'])){
				if($field['required'] == 'true') $field_required = ' NOT NULL';
				else $field_required = null;	
			} else $field_required = null;
			
			// Check for unique attribute
			if(isset($field['unique'])){
				if($field['unique'] == 'true') $field_unique = ' UNIQUE';
				else $field_unique = null;
			} else $field_unique = null;
			
			// Check for primary key
			if(isset($field['pk'])){
				if($field['pk'] == 'true') $field_pk = ' PRIMARY KEY';
				else $field_pk = null;
			} else $field_pk = null;
			
			// Check for primary key
			if(isset($field['increment'])){
				if($field['increment'] == 'true') $field_increment = ' AUTO_INCREMENT';
				else $field_increment = null;
			} else $field_increment = null;
			
			// Build field arguments for mysql
			$field_args[] = $field_name." ".$field_type.$field_default.$field_required.$field_unique.$field_index.$field_pk.$field_increment;
		}
		return $field_args;
	}
	
	
	/*
		Checks a section against the database
		Returns true if the match, false if not
		$error var contains the error message
	*/	
	public function check_section($section)
	{
		if( !$this->is_section($section))
			return false;
			
		// Fetch an array of tables in the database and comapare against config		
		$tables = $this->_list_tables();
		
		if(!in_array($section, $tables)){
			$this->_error("Table '$section' is missing");
			return false;
		}
		
		// Fetch fields from the config and the database so we can compare them
		$config_fields = $this->section_fields($section); // Fields from the config
		$query = $this->db->query("SHOW COLUMNS FROM $section"); // Get fields from the database
		foreach($query->result_array() as $row){
			$table = array_values($row); 
			$database_fields[] = $row;
		}
		
		// Loop through fields from config and clean up
		foreach($config_fields as $field){
			$name = $field['name'];
			$type = ($field['type'] == 'boolean')? 'tinyint' : $field['type'];
			$length = ($type == 'varchar')? "($field[length])" : null;
            //if( !in_array($type, array('date time', 'timestamp', 'date', 'time')) ) $null = $field['required'] == 'true'? 'NO' : 'YES';
            //else $null = null;
            $pk = $field['pk']? 'pk' : null;
			$increment = $field['increment']? 'auto_increment' : null;
			$config_fields_array[] = trim("$name $type $length $pk $increment");
		}
		
		// Loop through fields from database and clean up
		foreach($database_fields as $field){
			$name = $field['Field'];
			$type = array_shift( explode(' ', $field['Type']) );
			$type = preg_replace('/[^a-z]/', '', $type);
			$length = ($type == 'varchar')? "(".preg_replace('/[^0-9]/', '', $field['Type']).")" : null;
			//if( !in_array($type, array('date time', 'timestamp', 'date', 'time')) ) $null = $field['Null'];
			//else $null = null;
            $pk = ($field['Key'] == 'PRI')? 'pk' : null;
			$increment = $field['Extra'] == "auto_increment" ? "auto_increment" : null;
			$database_fields_array[] = trim("$name $type $length $pk $increment");
		}
		
		sort($config_fields_array);
		sort($database_fields_array);
		
		// http://en.wikipedia.org/wiki/Complement_(set_theory)
		$diff = array_diff( array_merge($config_fields_array, $database_fields_array), array_intersect($config_fields_array, $database_fields_array) );
		if( count($diff) > 0 ){
			foreach($diff as $field){
				$this->_error("Field mismatch: $section -> $field");	
			}
			return false;
		}
		return true;
				
	}
    
    public function check_view($view){
        // View definition does not exist
        if( !$this->is_view($view['name']) ){
            $this->_error("View is not defined");
            return false;
        }
        // View does not exist in database
        if( !in_array($view['name'], $this->db->list_tables()) ){
            $this->_error("View does not exist");
            return false;
        }
        
        $errors=0;
        $check = $this->db->query("CHECK TABLE ".$view['name']);
        foreach( $check->result_array() as $row){
        	if( strtolower($row['Msg_type']) == 'error'){
        		$this->_error($row['Msg_text']);
        		$errors++;
        	}
        }
        if($errors > 0){
        	return false;
        }
         
        return true;        
    }
	
	
	/*
		Updates the database to match the config
	*/
	public function update_section($section)
	{
		if( !$this->is_section($section))
			return false;
			
		log_message('debug', "Updating '$section' section");
		
		// Get fields from the config
		$config_fields = $this->section_fields($section); // Fields from xml as array for given section
		$fields_sql = $this->fields_for_database($section); // Formatted for use in alter table command
		
		// Check the table exists in the database i.e if we haveb't made it yet
		if(!in_array($section, $this->db->list_tables())){
			$this->create_table($section);
			return true;
		}


		// Get fields from the database
		$db_fieldnames = array();
		$query = $this->db->query("SHOW COLUMNS FROM $section");
		foreach($query->result_array() as $row){
			array_push($db_fieldnames, $row['Field']);
		}		
				
		// Look for the primary key
		$query = $this->db->query("SHOW INDEX FROM $section");
		foreach($query->result_array() as $row){
			if($row['Key_name'] == 'PRIMARY'){
				$pk = $row['Column_name'];
			}
		}
		// If we have a primary key - drop it, we will recreate it below
		if(isset($pk)){
			$query = $this->db->query("ALTER TABLE $section MODIFY $pk int");
			$query = $this->db->query("ALTER TABLE $section DROP PRIMARY KEY");
		}
		
		// Build up query for each field to add, modify or drop it
		foreach($config_fields as $id=>$field){
			$name = $field['name'];
			$position = $id==0 ? 'FIRST' : "AFTER $previous_name";
			$previous_name = $name; // Holds name of this field for the next loop ^
			$config_fieldnames[] = $name;
			if( !in_array($name, $db_fieldnames) ){
				$sql[] = "ALTER TABLE $section ADD $fields_sql[$id] $position";
				log_message('debug', "Adding column '$name' to '$section'");
			}
			if( in_array($name, $db_fieldnames) ){
				$sql[] = "ALTER TABLE $section MODIFY $fields_sql[$id] $position";
			}
		}
		foreach($db_fieldnames as $db_field){
			if( !in_array($db_field, $config_fieldnames) ){
				$sql[] = "ALTER TABLE $section DROP $db_field";
				log_message('debug', "Dropping field '$db_field' from '$section'");
			}	
		}
		
		// Run each sql command and check for errors 
		foreach($sql as $command){
			$result = $this->db->query($command);
			log_message('debug', $command);
			$affected += $this->db->affected_rows();
		}
		return $affected;
	}
    
    /*
    	Refresh a view using it's definition
    */
    public function update_view($view){
        if( !$this->is_view($view) ){
            $this->_error("View is not defined: $view");
            return false;
        }
        
        // Get the view create statement
        $view_definition = $this->views($view);
        
        // Create if it doesn't exist, alter if it does
        if( in_array($view, $this->db->list_tables()) ){
            $command = "ALTER VIEW $view AS " . $view_definition['statement'];
        } else {
            $command = "CREATE VIEW $view AS " . $view_definition['statement'];
        }
        // Run the query
    	$result = $this->db->query($command);
    	return $result;
    }
    
    /*
    	Returns a detailed list of tables in the database
    */
    public function detailed_list_tables(){
    	$database = (string)$this->db->database;
    	$query = $this->db->query("SHOW TABLE STATUS FROM $database WHERE Comment != 'VIEW' AND Engine IS NOT null ");
    	$tables = $query->result_array();
    	return $tables;
    }
    
    /*
    	Returns a simple list of tables in the database
    */
    private function _list_tables(){
    	$database = (string)$this->db->database;
    	$query = $this->db->query("SHOW FULL TABLES from $database WHERE Table_type='BASE TABLE' ");
    	foreach($query->result_array() as $table){
    		$tables[] = $table['Tables_in_'.$this->db->database];
    	}
    	return $tables;
    }
    
    
    /*
    	Returns a list of views from the database
    */
    private function _list_views(){
    	$database = (string)$this->db->database;
    	$query = $this->db->query("SHOW FULL TABLES from $database WHERE Table_type='VIEW' ");
    	foreach($query->result_array() as $view){
    		$views[] = $view['Tables_in_'.$this->db->database];
    	}
    	return $views;
    }
    
    /*
    	Records an error for displaying later
    */
    private function _error($message, $log=true){
    	array_push($this->errors, $message);
    	if($log) log_message('error', $message);
    }

}