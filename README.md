# User Upload Script

This PHP script processes a CSV file containing user data and uploads it to a PostgreSQL database.

## Requirements

- PHP 8.3
- PostgreSQL 13 or higher
- PHP PDO extension with PostgreSQL support

## Installation

### 1. Install PHP and PostgreSQL
Ensure you have **PHP 8.3** and **PostgreSQL (PSQL 14.16)** installed on your system.

- **Download PHP 8.3** from the official PHP website: [https://www.php.net/downloads](https://www.php.net/downloads)
- **Download PostgreSQL 14.16** from the official PostgreSQL website: [https://www.postgresql.org/download](https://www.postgresql.org/download)

- **Verify PHP version**:
  ```sh
  php -v
  ```  
- **Verify PostgreSQL version**:
  ```sh
  psql --version
  ```  

### 2. Install PHP PDO PostgreSQL Extension
If not already installed, enable the necessary extensions in your `php.ini` file:
1. Open your `php.ini` file.
2. Uncomment the following lines by removing the `;` at the beginning:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Save and restart your PHP service if needed.

### 3. Clone the Repository
```sh
git clone https://github.com/andrechandra/andrechandra-moodle-task.git
cd andrechandra-moodle-task
```

### 4. Execute the Script
Run the script using:
```sh
php user_upload.php -h <db_host> -u <db_user> -p <db_password> --file users.csv 
```

For additional options, check the help menu:
```sh
php user_upload.php --help
```

## Usage

The script supports the following command line options:

- `-h` - PostgreSQL host
- `-u` - PostgreSQL username
- `-p` - PostgreSQL password
- `--create_table` - Create the PostgreSQL users table
- `--file [csv file name]` - Name of the CSV file to be parsed
- `--dry_run` - Run the script without database insertions
- `--help` - Display help message

### Examples

1. Create the database table:
```bash
./user_upload.php -h localhost -u username -p password --create_table
```

2. Process CSV file:
```bash
./user_upload.php -h localhost -u username -p password --file users.csv
```

3. Dry run (test without database insertions):
```bash
./user_upload.php -h localhost -u username -p password --file users.csv --dry_run
```

## CSV File Format

The CSV file should have three columns in the following order:
1. name
2. surname
3. email

Example:
```csv
John,Smith,john.smith@example.com
Jane,Doe,jane.doe@example.com
```

## Error Handling

The script includes validation for:
- Email format
- Name and surname format (letters, spaces, hyphens, and apostrophes only)
- Duplicate email addresses
- File existence and readability
- Database connection issues

Errors are reported to STDOUT with line numbers