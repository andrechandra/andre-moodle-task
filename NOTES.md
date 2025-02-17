# Notes

## 1. Handling Invalid Rows Individually
**Change:** If a single row of data is invalid, I decided to skip that row and insert the rest instead of rejecting the entire CSV file.

**Reasoning:**
- This approach ensures that valid data is still processed, reducing data loss.
- If a CSV contains hundreds of valid records and just a few invalid ones, rejecting the whole file would be inefficient.
- Error messages are logged for invalid rows, so issues can still be reviewed and corrected.

## 2. Dry Run Function Enhancement
**Change:** The `--dry_run` function now simulates which data can be inserted and highlights any invalid rows.

**Reasoning:**
- This provides a clearer preview of what would happen before actually modifying the database.
- Users can see which records are invalid before committing to an import.
- Helps in debugging issues with the CSV file more efficiently.

## 3. Name and Surname Validation
**Change:** Names containing `!` are now considered invalid and will not be inserted.

**Reasoning:**
- `!` is not a typical character in names and may indicate input errors or data corruption.
- This adds an extra layer of data integrity by rejecting unusual or malformed names.

These changes ensure a more robust and user-friendly CSV processing experience while maintaining data integrity and flexibility.
