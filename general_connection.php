 <?php
 class Connection {
	public $sql;
	public $conn;
	public $inserted_id;

	function createConnection () {
		$servername = "servername";
		$username = "username";
		$dbname = "database name";
		$password = "password";

		// Create connection
		$this->conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($this->conn->connect_error) {
		    die("Connection failed: " . $this->conn->connect_error);
		}
	}

	function closeConnection () {
		$this->conn->close();
	}

	function submit () {
		if ($this->conn->query($this->sql) === TRUE) { // checks to see if the query runs successfully
			return true; // shows that the query ran successfully
		}
		else {
	    	return false; // allows for other pages to have error messages appropriate to the page
		}
	}

	function getInsertedId () {
		$this->inserted_id = $this->conn->insert_id;
		return $this->inserted_id;
	}
}
?>