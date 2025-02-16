<?php
class DatabaseUploader
{
    private $dbConnection = null;
    private $host;
    private $username;
    private $password;
    private $dbname = "moodle_users"; // Default database name
    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }
    public function connect()
    {
        try {
            $dsn = "pgsql:host=$this->host;dbname=$this->dbname";
            $this->dbConnection = new PDO($dsn, $this->username, $this->password);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Successfully connected to the database.\n";
            return true;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    public function createTable()
    {
        try {
            $sql = "DROP TABLE IF EXISTS users;
                    CREATE TABLE users (
                        id SERIAL PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        surname VARCHAR(100) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE
                    )";
            $this->dbConnection->exec($sql);
            echo "Table 'users' created successfully.\n";
            return true;
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
// Parse command line arguments
$options = getopt("h:u:p:", ["file:", "create_table", "dry_run", "help"]);
// Display help if requested
if (isset($options['help'])) {
    echo "Available options:\n";
    echo "-h - PostgreSQL host\n";
    echo "-u - PostgreSQL username\n";
    echo "-p - PostgreSQL password\n";
    echo "--create_table - Create the PostgreSQL users table\n";
    echo "--file [csv file name] - Name of the CSV file to be parsed\n";
    echo "--dry_run - Run the script without database insertions\n";
    echo "--help - Display this help message\n";
    exit(0);
}
// Check for required database connection parameters
if (!isset($options['h']) || !isset($options['u']) || !isset($options['p'])) {
    echo "Error: Database connection parameters (-h, -u, -p) are required.\n";
    exit(1);
}
// Initialize database connection
$uploader = new DatabaseUploader(
    $options['h'],
    $options['u'],
    $options['p']
);
// Connect to database
if (!$uploader->connect()) {
    exit(1);
}
// Create table if requested
if (isset($options['create_table'])) {
    $uploader->createTable();
    exit(0);
}
echo "Script completed.\n";
