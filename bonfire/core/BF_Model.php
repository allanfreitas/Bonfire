<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BF_Model extends CI_Model {

	/**
	 * A publicly available error string.
	 *
	 * @var string
	 */
	public $error 			= '';		// Stores custom errors 
	
	/**
	 * The database table to use.
	 *
	 * @var string
	 */ 
	protected $table 		= '';		
	
	/**
	 * The primary key for use in some
	 * functions. Defaults to 'id'.
	 *
	 * @var string
	 */
	protected $primary_key	= 'id';
	
	/**
	 * Whether to set created_on field
	 * during inserts.
	 *
	 * @var boolean
	 */
	protected $set_created	= TRUE;		// Whether or not to auto-create and fill a 'created_on' field DATETIME
	
	/**
	 * Whether to set modified_on field
	 * during inserts.
	 *
	 * @var boolean
	 */
	protected $set_modified = TRUE;		// Whether or not to auto-create and fill a 'modified_on' field DATETIME
	
	/**
	 * An array of functions to be called before
	 * a record is created.
	 *
	 * @var array
	 */
	protected $before_create = array();
	
	/**
	 * An array of functions to be called after
	 * a record is created.
	 *
	 * @var array
	 */
	protected $after_create = array();
	
	/**
	 * An array of functions to be called before
	 * a record is deleted.
	 *
	 * @var array
	 */
	protected $before_delete = array();
	
	/**
	 * An array of functions to be called after
	 * a record is created.
	 *
	 * @var array
	 */
	protected $after_delete = array();

	//---------------------------------------------------------------
	// !METHODS
	//---------------------------------------------------------------

	/**
	 * The class constructor. Currently does nothing
	 * more than calls the MODEL constructor.
	 *
	 * @return	void
	 * @author	Lonnie Ezell
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Find a single record by creating a WHERE clause with
	 * a value for your primary key.
	 *
	 * @param	string	$id	The value of your primary_key
	 * @return	object
	 * @author	Lonnie Ezell
	 */
	public function find($id='')
	{
		if ($this->check($id) === FALSE)
		{
			return false;
		}
		
		$query = $this->db->get_where($this->table, array($this->primary_key => $id));
		
		if ($query->num_rows() == 1)
		{
			return $query->row();
		}
		
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Find all records in the table. Use method chaining
	 * to limit the number of records.
	 *
	 * @return	array
	 * @author	Lonnie Ezell
	 */
	public function find_all()
	{
		if ($this->check() === FALSE)
		{
			return false;
		}
		
		$this->db->from($this->table);
		
		$query = $this->db->get();
		
		if (!empty($query) && $query->num_rows() > 0)
		{
			return $query->result();
		}
		
		$this->error = 'Invalid selection.';
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Find multiple records by creating a WHERE clause
	 * from the field(s)/value passed in. Accepts either 
	 * a string for a single value or an array for multiple
	 * values. 
	 *
	 * You can pass an array of field/value pairs in the
	 * first parameter to create multiple WHERE (AND) clauses.
	 * If this is the case, leave the value field empty.
	 *
	 * @param	string/array	$fields
	 * @param	string			$value
	 * @return	array
	 * @author	Lonnie Ezell
	 */
	public function find_by($fields=null, $value=null)
	{
		if (empty($fields) || empty($value))
		{
			$this->error = 'Not enough information to find by.';
			return false;
		}
	
		if (is_string($fields))
		{
			$this->db->where($fields, $value);
		} else if (is_array($fields))
		{
			$this->db->where($fields);	
		}
		
		$query = $this->db->get($this->table);
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Insert a single record into the database.
	 * Returns the record ID.
	 *
	 * If $this->set_created == TRUE, will automatically
	 * update the 'created_on' field.
	 *
	 * @param	array	$data	The name/value pairs of items to be stored.
	 * @returns	bool
	 * @author	Lonnie Ezell
	 */
	public function insert($data=null)
	{
		if ($this->check(FALSE, $data) === FALSE)
		{
			return FALSE;
		}
	
		// Add the created field
		if ($this->set_created === TRUE && !array_key_exists('created', $data))
		{
			$data['created_on'] = date('Y-m-d H:i:s');
		}
		
		// Before Create Hook
		$data = $this->_run_before_create($data);
		
		// Insert it
		$status = $this->db->insert($this->table, $data);
		
		// After Create Hook
		$data = $this->_run_before_create($data, $this->db->insert_id());
		
		if ($status != FALSE)
		{
			return $this->db->insert_id();
		} else
		{
			$this->error = mysql_error();
			return false;
		}

	}
	
	//---------------------------------------------------------------
	
	/**
	 * Inserts multiple rows into the database by passing an array
	 * of data to insert.
	 *
	 * @param	array	$data	Array of arrays to insert
	 * @return	array			Array of id's inserted
	 * @author	Lonnie Ezell
	 */
	public function insert_many($data=null) 
	{
		if ($this->check(FALSE, $data) === FALSE)
		{
			return FALSE;
		}
		
		$ids = array();
		
		foreach ($data as $row)
		{
			// Add the created field
			if ($this->set_created === TRUE && !array_key_exists('created', $data))
			{
				$data['created_on'] = date('Y-m-d H:i:s');
			}
			
			// Before Create Hook
			$data = $this->_run_before_create($row);
			
			// Insert it
			$status = $this->db->insert($this->table, $row);
			
			// After Create Hook
			$data = $this->_run_before_create($row, $this->db->insert_id());
			
			$ids[] = $this->db->insert_id();
		}
		
		return $ids;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Updates a single record in the database using the
	 * primary_key to identify.
	 *
	 * If $this->set_modified == TRUE, will automatically
	 * update the 'modified_on' field.
	 *
	 * @param	int		$id		The primary_key value
	 * @param	array	$data	The information to be updated.
	 */
	public function update($id=null, $data=null)
	{
		
		if ($this->check($id, $data) === FALSE)
		{
			return FALSE;
		}
		
		// Add the modified field
		if ($this->set_modified === TRUE && !array_key_exists('modified_on', $data))
		{
			$data['modified_on'] = date('Y-m-d H:i:s');
		}
	
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
		{
			return true;
		}

	}
	
	//---------------------------------------------------------------
	
	/**
	 * Update many rows from the database, by passing in an 
	 * array of id's and an array of field/value pairs.
	 *
	 * @param	array	$ids	An array of values to match against $this->primary_key
	 * @param	array	$data	An array of field/value pairs to update.
	 * @return	bool
	 * @author	Lonnie Ezell
	 */
	public function update_many($ids=null, $data=null) 
	{
		if (!is_array($ids) || !is_array($data))
		{
			$this->error = 'IDs is not an array.';
			return false;
		}
		
		$this->db->where_in($this->primary_key, $ids);
		$this->db->set($data);
		$result = $this->db->update($this->table);
		
		if ($result != false)
		{
			return true;
		}
		
		$this->error = 'DB Error: '. mysql_error();
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Updates all records in table.
	 *
	 * @param	array	$data	An array of field/value pairs to update.
	 * @return	bool
	 * @author	Lonnie Ezell
	 */
	public function update_all($data=null) 
	{
		if ($this->check(false, $data) === FALSE)
		{
			return FALSE;
		}
		
		$this->db->set($data);
		$result = $this->db->update($this->table);
		
		if ($result != false)
		{
			return true;
		}
		
		$this->error = 'DB Error: '. mysql_error();
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Delete a single record from the database based on the
	 * primary_key value.
	 *
	 * @param	int		$id		The primary_key value.
	 * @return	bool
	 * @author	Lonnie Ezell
	 */
	public function delete($id=null)
	{
		if ($this->check($id) === FALSE)
		{
			return FALSE;
		}
	
		// Before Delete Hook
		$this->_run_before_delete($id);
	
		$result = $this->db->delete($this->table, array($this->primary_key => $id));

		if ($result != false)
		{
			// After Delete Hook
			$this->_run_after_delete($id);
		
			return true;
		} 
		
		$this->error = 'DB Error: ' . mysql_error();
	
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Delete many rows from the database, by passing in an 
	 * array of id's to delete.
	 *
	 * @param	array	$ids	An array of values to match against $this->primary_key
	 * @return	bool
	 * @author	Lonnie Ezell
	 */
	public function delete_many($ids=null) 
	{
		if (!is_array($ids))
		{
			$this->error = 'IDs is not an array.';
			return false;
		}
		
		// Before Delete Hook - note we're passing an array of ids here!
		$this->_run_before_delete($ids);
		
		$this->db->where_in($this->primary_key, $ids);
		$result = $this->db->delete($this->table);
		
		if ($result != false)
		{
			// After Delete Hook - note we're passing an array of ids here!
			$this->_run_after_delete($ids);
		
			return true;
		}
		
		$this->error = 'DB Error: '. mysql_error();
		return false;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !HELPER FUNCTIONS
	//---------------------------------------------------------------
	
	/**
	 * Checks if the passed in field/value is already in the database.
	 *
	 * @param	string	$field	The field name to check.
	 * @param	string	$value	The value to check.
	 * @author	Lonnie Ezell
	 */
	public function is_unique($field='', $value='')
	{
		if (empty($field) || empty($value))
		{
			$this->error = 'Not enough information to check uniqueness.';
			return false;
		}

		$this->db->where($field, $value);			
		$query = $this->db->get($this->table);
					
		if ($query->num_rows() == 0)
		{
			return true;
		}
		
		return false;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Returns the total number of records in the database.
	 *
	 * @return	int		The total number of records.
	 * @author	Lonnie Ezell
	 */
	public function count_all()
	{
		return $this->db->count_all($this->table);
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Returns the total number of items that match the field/value
	 * combination spec'd in the parameters.
	 *
	 * @param	string	$field
	 * @param	string	$value
	 * @return	int		The total number of matching records.
	 * @author	Lonnie Ezell
	 */
	public function count_by($field='', $value='') 
	{
		if (empty($field) || empty($value))
		{
			$this->error = 'Not enough information to count results.';
			return false;
		}
		
		$this->db->where($field, $value);
		
		return $this->db->count_all_results();
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Returns the value of a single field within a record spec'd
	 * by the primary_key.
	 *
	 * @param	int		$id		The primary key value
	 * @param	string	$field	The name of the field to return.
	 * @return	mixed			The value of the field searched for.
	 */
	public function field($id=null, $field='') 
	{
		if (!is_numeric($id) || $id === 0 || empty($field))
		{
			$this->error = 'Not enough information to fetch field.';
			return false;
		}
		
		$this->db->select($field);
		$this->db->where($this->primary_key, $id);
		$query = $this->db->get($this->table);
		
		if ($query->num_rows() > 0)
		{
			return $query->row()->$field;
		}
		
		return false;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !CHAIN FUNCTIONS
	//---------------------------------------------------------------
	
	/**
	 * Chain functions help you write concise, readable queries.
	 *
	 * Method chaining in PHP5+ allows you to combine the results
	 * of multiple class methods into a final method, one after the
	 * other. 
	 *
	 * In Bonfire, Method Chaining is used to easily specify the 
	 * items to select, put a limit on the results, or specify
	 * an order_by. An example might look like this: 
	 *
	 *		$this->user->select('id, username')
	 *				   ->limit(1)
	 *				   ->find($id);
	 */
	
	/**
	 * Allows you to modify the values selected from the results.
	 *
	 * @param	string	$values	The values to select
	 * @return	void
	 * @author	Lonnie Ezell
	 */
	public function select($values='') 
	{
		if (!empty($selects))
		{
			$this->db->select($values);
		}
	
		return $this;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Allows you to limit the results from a query.
	 *
	 * @param	int		$limit	
	 * @param	int		$offset
	 * @return	void
	 * @author	Lonnie Ezell
	 */
	public function limit($limit=null, $offset=0) 
	{
		if (is_numeric($limit))
		{
			$this->db->limit($limit, $offset);
		}
	
		return $this;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Allows you to order your results. Uses the same format
	 * as CI's AR library. You can pass either a single
	 * field/dir pair, or an array of field/value pairs.
	 *
	 * @param	string/array	$fields
	 * @param	string	$dir	either 'asc', 'desc', or 'random'
	 * @return	void
	 * @author	Lonnie Ezell
	 */
	public function order_by($fields=null, $dir=null) 
	{
		if (is_string($fields) && !is_null($dir))
		{
			$this->db->order_by($fields, $dir);
		} else if (is_array($fields))
		{
			foreach ($fields as $field => $dir)
			{
				$this->db->order_by($field, $dir);
			}
		}
	
		return $this;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !HOOK POINTS
	//---------------------------------------------------------------
	
	/**
	 * Runs the before create actions.
	 *
	 * @param array $data The array of actions
	 * @return 		void
	 * @author 		Jamie Rumbelow
	 * @modified	Lonnie Ezell
	 */
	private function _run_before_create($data)
	{
		if (!is_array($data))
		{
			return;
		}
	
		foreach ($this->before_create as $method)
		{
			$data = call_user_func_array(array($this, $method), array($data));
		}
		
		return $data;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Runs the after create actions.
	 *
	 * @param array $data The array of actions
	 * @return 		void
	 * @author 		Jamie Rumbelow
	 * @modified	Lonnie Ezell
	 */
	private function _run_after_create($data, $id)
	{
		if (!is_array($data) || !is_numeric($id))
		{
			return;
		}
	
		foreach ($this->after_create as $method)
		{
			call_user_func_array(array($this, $method), array($data, $id));
		}
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Runs the before delete actions.
	 *
	 * @param array $data The array of actions
	 * @return 		void
	 * @author 		Jamie Rumbelow
	 * @modified	Lonnie Ezell 
	 */
	private function _run_before_delete($data, $id)
	{
		if (!is_array($data) || !is_numeric($id))
		{
			return;
		}
	
		foreach ($this->before_delete as $method)
		{
			$data = call_user_func_array(array($this, $method), array($data, $id));
		}
		
		return $data;
	}
	
	//---------------------------------------------------------------
	
	/**
	 * Runs the after delete actions.
	 *
	 * @param array $data The array of actions
	 * @return 		void
	 * @author 		Jamie Rumbelow
	 * @modified	Lonnie Ezell
	 */
	private function _run_after_delete($data, $id)
	{
		if (!is_array($data) || !is_numeric($id))
		{
			return;
		}
	
		foreach ($this->after_delete as $method)
		{
			call_user_func_array(array($this, $method), array($data, $id));
		}
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !PRIVATE FUNCTIONS
	//---------------------------------------------------------------
	
	/**
	 * Handles various book-keeping and error setting for many
	 * of the functions.
	 *
	 * Will also strip any 'submit' fields automatically to make
	 * working with $_POST data easier.
	 *
	 * @param	bool	$id		Whether to verify a numeric id value
	 * @param	bool	$id		Whether to verify data !empty
	 * @return	bool
	 * @author	Lonnie Ezell
	 */
	protected function check($id=FALSE, &$data=FALSE) 
	{
		// Does the model have a table set?
		if (empty($this->table))
		{
			$this->error = 'Model has unspecified database table.';
			return false;
		}
		
		// Check the ID, but only if it's a non-FALSE value
		if ($id !== FALSE)
		{
			if (!is_numeric($id) || $id == 0)
			{
				$this->error = 'Invalid ID passed to model.';
				return false;
			}
		}
		
		// Check the data
		if ($data !== FALSE)
		{
			if (!is_array($data) || count($data) == 0)
			{
				$this->error = 'No data available to insert.';
				return false;
			}
		}
		
		// Strip the 'submit' field, if set
		if (isset($data['submit']))
		{
			unset($data['submit']);
		}
		
		return true;
	}
	
	//---------------------------------------------------------------
}

// END BF_Model class

/* End of file BF_Model.php */
/* Location: ./bonfire/core/BF_Model.php */