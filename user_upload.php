<?php
class DatabaseUploader
{
    private $dryRun = false;

    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
    }

    private function validateEmail($email)
    {
        return !empty($email) && strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function validateName($name)
    {
        return !empty($name) && strlen($name) <= 100 && preg_match('/^[a-zA-Z\s\'-]+$/', $name);
    }

    public function processCSV($filename)
    {
        if (!file_exists($filename)) {
            echo "Error: File '$filename' does not exist.\n";
            return false;
        }

        $file = fopen($filename, 'r');

        if (!$file) {
            echo "Error: Unable to open file '$filename'.\n";
            return false;
        }

        $lineNumber = 0;
        $successCount = 0;
        $errorCount = 0;

        while (($data = fgetcsv($file)) !== FALSE) {
            $lineNumber++;

            if (count($data) !== 3) {
                echo "Error on line $lineNumber: Invalid number of columns.\n";
                $errorCount++;
                continue;
            }

            $name = ucfirst(strtolower(trim($data[0])));
            $surname = ucfirst(strtolower(trim($data[1])));
            $email = strtolower(trim($data[2]));

            if (!$this->validateName($name) || !$this->validateName($surname)) {
                if (!$this->validateName($name) && !$this->validateName($surname)) {
                    echo "Error on line $lineNumber: Invalid name and surname format ('$name', '$surname').\n";
                } else if (!$this->validateName($name)) {
                    echo "Error on line $lineNumber: Invalid name format '$name'.\n";
                } else {
                    echo "Error on line $lineNumber: Invalid surname format '$surname'.\n";
                }
                $errorCount++;
                continue;
            }

            if (!$this->validateEmail($email)) {
                echo "Error on line $lineNumber: Invalid email format '$email'.\n";
                $errorCount++;
                continue;
            }

            if (!$this->dryRun) {
                try {
                    $nextId = $this->dbConnection->query("SELECT COALESCE(MAX(id), 0) + 1 FROM users")->fetchColumn();
                    $sql = "INSERT INTO users (id, name, surname, email) VALUES (?, ?, ?, ?)";
                    $stmt = $this->dbConnection->prepare($sql);
                    $stmt->execute([$nextId, $name, $surname, $email]);
                    $successCount++;
                } catch (PDOException $e) {
                    if ($e->getCode() == '23505') {
                        echo "Error on line $lineNumber: Email '$email' already exists.\n";
                    } else {
                        echo "Error on line $lineNumber: " . $e->getMessage() . "\n";
                    }
                    $errorCount++;
                }
            } else {
                echo "DRY RUN: Would insert: $name $surname ($email)\n";
                $successCount++;
            }
        }

        fclose($file);

        echo "\nProcessing complete:\n";
        echo "Successful records: $successCount\n";
        echo "Failed records: $errorCount\n";
        return true;
    }

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
                    );
                    CREATE INDEX idx_email ON users(email);";
            $this->dbConnection->exec($sql);
            echo "Table 'users' created successfully with email index.\n";
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

// Check if file parameter is provided
if (!isset($options['file'])) {
    echo "Error: No input file specified. Use --file option.\n";
    exit(1);
}

// Set dry run mode if specified
if (isset($options['dry_run'])) {
    $uploader->setDryRun(true);
    echo "Running in dry run mode - no database changes will be made.\n";
}

// Process the CSV file
$uploader->processCSV($options['file']);
