<?php


/*
    Main XML manipulation class
	
---------------------------------------------------------------------------------- */

class Xml
{
	protected $CI;
	protected $_config_file;		// Path to main config file
	protected $_compile_filename;	// Name of compiled config file
	protected $_compile_dir;		// Directory of compiled config file
	public $compiled_config;		// Path to compiled config used by the system
	public $compile_errors=array();	// Any errors when compiling config
	
	public function __construct()
	{
		/* Instantiate CI and load the config */
		$this->CI =& get_instance();
		$this->CI->config->load('dbmanager');
		
		/* Setup paths for the compiled config */
		if(strpos($this->CI->config->item('config_xml'), '/') === FALSE){
			$this->_config_file = APPPATH."config/".$this->CI->config->item('config_xml');
		} else {
			$this->_config_file = APPPATH.$this->CI->config->item('config_xml');
		}
		$this->_compile_dir = APPPATH.$this->CI->config->item('compile_dir');
		
		$extension = end(explode('.', $this->CI->config->item('config_xml')));
		$this->_compile_filename = str_ireplace($extension, "compiled.$extension", end(explode("/", $this->_config_file)));
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
				show_error("Compile directory is not writable, failed to make it so");
				return false;
			}
		}
		
		// Validate the compiled xml
		if(!$this->validate($file_data)){
			log_message('error', "Config file contains invalid XML, Config will not be reloaded", 'ERROR');
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
			show_error("Failed to compile config file '$this->compiled_config', check file permissions");
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
			show_error("A database config file could not be found. Please make sure the config is pointing to a valid database config file.<br>
			Currently the system is looking for <b>'$this->_config_file'</b>.", 500);
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
				show_error("Please make sure the application/cache folder is writable by the server<br>
					Either use your program of choice to make the change or run the following command in the terminal:<br/>
					<code>chmod o+w ".FCPATH.$this->_compile_dir."</code>");
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
	*/
	public function field_attributes($section, $field)
	{

		$data=array();

		// Look up the section ID as an array key to use below
		$section_id = $this->_section_id($section);
		$field_id = $this->_field_id($section, $field );
		
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
	*/
	public function section_fields($section, $associative=false)
	{
		$section = $this->_get_section_object($section);
		
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
		return in_array($section, $this->section_names());
	}
    
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
	
	public function admin($section){
		if(!$this->is_section($section)) return false;
		
		$attributes = array(
			'name'=>'',
			'table'=>'',
			'heading'=>'',
			'info'=>'',
			'hidden'=>'',
			'singular'=>'',
			'plural'=>'',
			'columns'=>'',
		);
		
		$section_obj = $this->_get_section_object($section);
		$attributes['name'] = (string)$section_obj->attributes()->name;
		$attributes['table'] = (string)$section_obj->table->attributes()->name;
			
		// Admin details
		$admin = $this->_attributes_to_array($section_obj->admin->attributes());
		foreach ($admin as $name=>$value) {
			$attributes[$name] = $value;
		}
		$attributes['heading'] = (string)$section_obj->admin->heading;
		$attributes['info'] = (string)$section_obj->admin->info;		
		
		// Add default singular/plural for section 
		if(empty($attributes['singular'])){
			$this->CI->load->helper('inflector');
			$attributes['singular'] = singular($attributes['name']);
		}
		if(empty($attributes['plural'])){
			$this->CI->load->helper('inflector');
			$attributes['plural'] = plural($attributes['name']);
		}
		
		// default hidden to false
		$attributes['hidden'] = empty($attributes['hidden'])? 'false' : $attributes['hidden'];
		
		return $attributes;
	}
	
	/*
		Returns an array of section names extracted from the config
	*/
	public function section_names()
	{
		$names = array();
		foreach ($this->xml->section as $section) {
			$names[]  = (string)$section->attributes()->name;
		}
		return $names;
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
		$views=array();
		foreach ($this->xml->view as $key=>$section){
			$view['name'] = (string)$section['name'];
			$view['statement'] = trim((string)($section));
			$view['dependancies'] = trim((string)($section['dependancies']));
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
			elseif($field['type'] == 'boolean')
				$field['type'] = 'checkbox';
			
			// Select
			elseif( isset($field['values']) )
				$field['type'] = 'select';
			
			// Password
			elseif( strstr($field['name'], 'password') )
				$field['type'] = 'checkbox';
			
			// Hidden
			elseif( isset($field['hidden']) && $field['hidden'] == 'true')
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
			
			// Add in rules fallback
			if(!isset($field['rules'])){
				$field['rules'] = '';
			}
			
			array_push($sanitised_fields, $field);
		}
		
		return $sanitised_fields;
	}
	
	/* 
		Returns fields array with values to be used in a Codeigniter form
		Creates field 'options' array using 'values' and 'labels'
	*/
	public function fields_for_ci_form($section)
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
			elseif($field['type'] == 'boolean')
				$field['type'] = 'checkbox';
			
			// Select
			elseif( isset($field['values']) )
				$field['type'] = 'select';
			
			// Password
			elseif( strstr($field['name'], 'password') )
				$field['type'] = 'checkbox';
			
			// Hidden
			elseif( isset($field['hidden']) && $field['hidden'] == 'true')
				$field['type'] = 'hidden';
			
			
			// Default to text
			else
				$field['type'] = 'input';
			
			// Sanitise Other attributes
			if( !isset( $field['label'] ) )
				$field['label'] = $field['name'];
			
			// Remove SQL specific defaults
			if( isset($field['default']) ){
				if($field['default']=='CURRENT_TIMESTAMP' || $field['default']=='now()'){
					$field['default'] = '';
				}
			} else {
				$field['default'] = '';
			}
			
			// Extract rules
			if( isset($field['rules']) ){
				foreach(explode('|', $field['rules']) as $rule){
					if( strpos($rule, '[') ){
						$rulename = preg_replace('/(?<=.)\[.+\]/', '', $rule);
						$ruleval = preg_replace('/]|\[|\w+\[/', '', $rule);
					} else {
						$rulename = $rule;
					}
					if($rulename=='min_length'){
						$field['min_length'] = $ruleval;
					}
					if($rulename=='max_length'){
						$field['max_length'] = $ruleval;
					}
				}
			}
				
			// Build select field options array
			if( isset($field['values']) && isset($field['labels']) ){
				$values = explode(',', $field['values']);
				$labels = explode(',', $field['labels']);
				foreach($values as $key=>$val){
					$options[$val] = $labels[$key];
				}
				$field['options'] = $options;
			}
			
			// Remove used attributes
			unset($field['rules']);
			unset($field['increment']);
			
			array_push($sanitised_fields, $field);
		}
		
		return $sanitised_fields;
	}
	
	
	/*************************************
		START PROTECTED FUNCTIONS
	*************************************/
	
	/*
		returns a simplexml object of a specific section
	*/
	private function _get_section_object($section)
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
		Returns the array key for a specified section
	*/
	protected function _section_id($section)
	{
		if(!$this->is_section($section)) return false;
		
		$count = count($this->xml->section);
		for($i=0; $i<$count; $i++){
			if( (string)$this->xml->section[$i]->attributes()->name == $section){
				return $i;
				break;
			}
		}
	}
	
	/*
		Returns the array key for a field within specified section->table
		Expects $field_array to be a SimpleXML object
	*/
	protected function _field_id($section, $name)
	{		
		if(!$this->is_section($section)) return false;
		
		$section_object = $this->_get_section_object($section);
		
		$i=0;		
		foreach($section_object->table->field as $field) {
			if( (string)$field->attributes()->name == $name){
				return $i;
				break;
			} $i++;
		}
	}
	
	/*
		Convert SimpleXMLObject attributes to a one dimensional array
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

class Dbmanager extends XMLConfig
{
	private $db;				// Database connection link
	public $errors=array();		// Errors!
	
	function __construct()
	{
		parent::__construct();
		$this->db =& $this->CI->db;
		$this->CI->load->dbforge();
		if( count($this->section_names()) == 0){
			show_error("Your database config does not appear to have any valid sections defined, please add some and <a href='".current_url()."/reload_config'>reload the config</a>.");
		}
	}
	
	/*
		Recompiles the config
	*/
	public function reload_config($optimise=false)
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
		if($optimise){
			foreach($tables as $table){
				// Run mysql optimize table command
				$this->db->simple_query("OPTIMIZE TABLE $table");
				// Drop tables that are not in the config
				if( !in_array($table, $sections) ){
					$result = $this->db->query("DROP TABLE $table");
				}
			}
			foreach($views as $view){
				// Drop views that are not in the config
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
			
		$field_args = $this->_create_field_definitions($section);
		
		$qry = "CREATE TABLE IF NOT EXISTS $section(\n\t" . implode(",\n\t", $field_args) . "\n)\n";
			
		$result = $this->db->query($qry);
		if($result)
			return true;
		else
			return false;
		
	}
	
	
	/*
		Returns an array of strings ready to be used in create/alter table functions
	*/	
	public function _create_field_definitions($section)
	{
		if( !$this->is_section($section)) return false;
			
		$fields = $this->section_fields($section);

		if(!$fields) return false;
		
		foreach($fields as $field){
			
			// Create/Reset the array
			$field_query = array();

			// Add name and type
			$field_query['name'] = $field['name'];
			$field_query['type'] = $field['type'];
			

			// Add length if varchar
			if($field['type'] == 'varchar'){
				$field_query['type'] = isset($field['length'])? "$field[type]($field[length])" : "$field[type](255)";
			}
			
			// Check for default
			if(isset($field['default'])){
				// Convert 'true' 'false' to numerical values
				if($field['default'] == 'true'){
					$field['default'] = 1;
				}
				if($field['default'] == 'false'){
					$field['default'] = 0;
				}
				// Add quotes except for special cases
				if($field['default'] == 'now()' OR $field['default'] == 'CURRENT_TIMESTAMP' OR is_numeric($field['default'])){
					$field_query['default'] = "DEFAULT ".$field['default'];
				} else {
					$field_query['default'] = "DEFAULT '".$field['default']."'";
				}
			}
			
			// Extract field rules
			if(isset($field['rules'])){
				$rules = explode('|', $field['rules']);
				
				// Check if required
				if( in_array('required', $rules) ){
					$field_query['required'] = 'NOT NULL';
				}
	
				// Check if unique
				if( in_array('is_unique', $rules) ){
					$field_query['unique'] = 'UNIQUE';
				}
			}

			// Check for primary key
			if( isset($field['pk']) && $field['pk'] == 'true'){
				$field_query['pk'] = 'PRIMARY KEY';
			}
			
			// Check for auto increment key
			if( isset($field['increment']) && $field['increment']=='true' ){
				$field_query['increment'] = 'AUTO_INCREMENT';
			}
			
			// Build field arguments for mysql
			$field_queries[] = implode(' ', $field_query);
		}
		return $field_queries;
	}
	
	/*
		Checks a section against the database
		Returns true if the match, false if not
		$error var contains the error message
	*/	
	public function check_section($section){
		
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
		$database_fields = $query->result_array();
		
		
		// Loop through fields from config and clean up
		foreach($config_fields as $field){
			$name = $field['name'];
			$type = ($field['type'] == 'boolean')? 'tinyint' : $field['type'];
			$length = ($type == 'varchar')? "($field[length])" : null;
		    $pk = isset($field['pk'])? 'pk' : null;
			$increment = isset($field['increment'])? 'auto_increment' : null;
			$config_fields_array[] = trim("$name $type$length $pk $increment");
		}
		
		// Loop through fields from database and clean up
		foreach($database_fields as $field){
			$name = $field['Field'];
			$type = array_shift( explode(' ', $field['Type']) );
			$type = preg_replace('/[^a-z]/', '', $type);
			$length = ($type == 'varchar')? "(".preg_replace('/[^0-9]/', '', $field['Type']).")" : null;
		    $pk = ($field['Key'] == 'PRI')? 'pk' : null;
			$increment = $field['Extra'] == "auto_increment" ? "auto_increment" : null;
			$database_fields_array[] = trim("$name $type$length $pk $increment");
		}
		
		sort($config_fields_array);
		sort($database_fields_array);
		
		// Check if db field is missing 
		foreach($config_fields_array as $field){
			if(!in_array($field, $database_fields_array)){
				$name = substr($field, 0, strpos($field, ' '));
				$errors[$name] = "The field <b>$section.$name</b> is mismatched";
			}
		}
		
		// Check if db field shouldn't be there 
		foreach($database_fields_array as $field){
			if(!in_array($field, $config_fields_array)){
				$name = substr($field, 0, strpos($field, ' '));
				$errors[$name] = "The field <b>$section.$name</b> is mismatched";
			}
		}
		
		
		if(is_array($errors)){
			foreach($errors as $name=>$error){
		
				$field_errors[$name]['message'] = $error;
				$field_errors[$name]['error'] = 'mismatch';
				
				// Get section definiteion for this field
				foreach($config_fields_array as $field){
					$fieldname = substr($field, 0, strpos($field, ' '));
					if($fieldname==$name){
						$field_errors[$name]['section_definition'] = $field;
					}
				}
				// Get database definiteion for this field
				foreach($database_fields_array as $field){
					$fieldname = substr($field, 0, strpos($field, ' '));
					if($fieldname==$name){
						$field_errors[$name]['table_definition'] = $field;
					}
				}
				
				// If fields are missing update error message and add empty index
				if( empty($field_errors[$name]['section_definition']) ){
					$field_errors[$name]['section_definition'] = '';
					$field_errors[$name]['message'] = "'$name' field found in '$section' table but not in config";
					$field_errors[$name]['error'] = 'undefined';
				}
				if( empty($field_errors[$name]['table_definition']) ){
					$field_errors[$name]['table_definition'] = '';
					$field_errors[$name]['message'] = "'$name' field found in config but not in '$section' table";
					$field_errors[$name]['error'] = 'missing';
				}
			}
		}
		
				
		// Add the errors to _errors
		if(!empty($field_errors)){
			foreach ($field_errors as $error) {
				if($error['error'] == 'mismatch') $this->_error($error['message'].": <b>".$error['section_definition']."</b> > <b>".$error['table_definition']."</b>");
				else $this->_error($error['message']);
			}
			return false;
		} else {
			return true;
		}
	}
    
    public function check_view($view){
        // View definition does not exist
        if( !$this->is_view($view) ){
            $this->_error("The view <b>$view</b> is not defined in the config");
            return 'f';//false;
        }
        
        // Check the create statement
        
        $view_definition = $this->views($view);
        $create = $this->db->simple_query($view_definition['statement']);
        if(!$create){
        	$this->_error("Error creating <b>$view</b>: ".$this->db->_error_message());
        	return false;
        }
        
        
        // View does not exist in database
        if( !in_array($view, $this->db->list_tables()) ){
            $this->_error("The view <b>$view</b> does not exist in the database");
            return false;
        }
        
        $errors=0;
        $check = $this->db->query("CHECK TABLE ".$view);
        foreach( $check->result_array() as $row){
        	if( $row['Msg_type'] == 'Error'){
        		$this->_error("Error creating <b>$view</b>: ".$row['Msg_text']);
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
		$fields_sql = $this->_create_field_definitions($section); // Formatted for use in alter table command
		
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
        
        // Drop the view
        $this->db->query("DROP VIEW IF EXISTS $view");
        
        // Create the view
        $result = $this->db->simple_query("CREATE VIEW $view AS " . $view_definition['statement']);
		
		if(!$result){
			$this->_error($this->db->_error_message());
			return false;
		} else {
    		return true;
    	}
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
    	$tables = array();
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
    	$views = array();
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
    
    /*
    	Generate the interface to manage the daabase
    */    
    function generate_manager_interface($regions=false){
    	
    	// Check and grab status information for the tables
    	$sections = $this->section_names();
    	foreach($sections as $section){
    		$table_check = $this->check_section($section);
    		$table_result['name'] = $section;
    		$table_result['status'] = $table_check? 'ok' : 'error';
    		$table_result['status_message'] = $table_check? 'Ok' : "Mismatched";
    		$table_stats[] = $table_result;
    	}
    	    	
    	// Insert detailed table info to the sections array
    	$details = $this->detailed_list_tables();
    	foreach($table_stats as $i=>$section){
    		foreach($details as $detail){
    			if($section['name'] === $detail['Name']){
    				foreach($detail as $key=>$val){
    					$keyname = strtolower($key);
    					$table_stats[$i]['col-'.$keyname] = $val;
    				}
    				
    			}
    		}
    	}
    	
    	// Check and grab status information for the views
    	$views = $this->views();
    	$view_stats = array();
    	foreach($views as $view){
    		$view_check = $this->check_view($view['name']);
    		$view_result['name'] = $view['name'];
    		$view_result['dependancies'] = $view['dependancies'];
    		$view_result['status'] = $view_check? 'ok' : 'error';
    		$view_result['status_message'] = $view_check? 'Ok' : "Mismatched";
    		$view_stats[] = $view_result;
    	}
    	
    	$this->check_view('clips_vw');
    	$sections = $table_stats;
    	$views = $view_stats;
    	$errors = $this->errors;
    	
    	if(is_array($regions)){
    		foreach ($regions as $key => $value) {
    			${'region_'.$key} = $value;
    		}
    	}
    	
    	?>
    	<?= $region_before_interface ?>
    	<p class="actions">
    		<a class="btn dark" href="<? echo current_url() ?>/reload_config">Reload config</a>
    		<a class="btn dark" href="<?=current_url()?>/reload_config/true" title="This will drop tables from the database that don't exist in the config">Optimise Database</a>
    		<?= $region_buttons ?>
    	</p>
    	<? if( count($errors) > 0) : ?>
    		<div id="errors" class="errors">
	    		<p class="error">There were errors found when reloading the config, try reloading the mismatched rows.</p>
	    		<ol id="error-messages" class="reset"><? foreach($errors as $error): ?>
	    			<li class="error"><?= $error ?></li>
	    		<? endforeach ?></ol>
    		</div>
    	<? endif ?>
    	<h2>Tables</h2>
    	<?= $region_before_tables ?>
    	<table class="db-manager" id="db-tables" cellpadding="0" cellspacing="0">
    		<thead>
	    		<tr>
	    			<th class="name">Table</th>
	    			<th class="info">Info</th>
	    			<th class="status">Status</th>
	    			<th class="action">Action</th>
	    		</tr>
    		</thead>
	    	<tbody>
	    	<? foreach($sections as $section): 
	    	?>	<tr class="<?= $section['status'] ?>" id="table-<?= $section['name'] ?>">
	    			<td class="name"><?= $section['name'] ?></td>
	    			<td class="info">Rows: <b><?= $section['col-rows'] ?></b> Size: <b><?= byte_format($section['col-data_length']) ?></b> Increment: <b><?= $section['col-auto_increment'] ?></b></td>
	    			<td class="status"><?= $section['status_message'] ?></td>
	    			<td class="action">
	    				<a class="btn" href="<?=current_url()?>/reload_table/<?= $section['name'] ?>" title="Reload <?= $section['name'] ?> table">Reload</a>
	    			</td>
	    		</tr>
	    	<? endforeach ?></tbody>
		</table>
    	
    	<h2>Views</h2>
    	<?= $region_before_views ?>
    	<table class="db-manager" id="db-views" cellpadding="0" cellspacing="0">
	    	<thead>
	    		<tr>
	    			<th class="name">View</th>
	    			<th class="info">Dependencies</th>
	    			<th class="status">Status</th>
	    			<th class="action">Action</th>
	    		</tr>
	    	</thead>
	    	<tbody>
	    	<? foreach($views as $view): 
	    	?>	<tr class="<?= $view['status'] ?>" id="view-<?= $view['name'] ?>">
	    			<td class="name"><?= $view['name'] ?></td>
	    			<td class="info"><?= $view['dependancies'] ?></td>
	    			<td class="status"><?= $view['status_message'] ?></td>
	    			<td class="action"><a class="btn" href="<?=current_url()?>/reload_view/<?= $view['name'] ?>">Reload</a></td>
	    		</tr>
	    	<? endforeach ?></tbody>
    	</table><?
    }

}