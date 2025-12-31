-- Update script to add missing columns to existing tables

-- Add amount_usd to payments table if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "payments";
SET @columnname = "amount_usd";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE payments ADD COLUMN amount_usd DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER exchange_rate;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add updated_by to daily_rates table if it doesn't exist
SET @tablename = "daily_rates";
SET @columnname = "updated_by";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE daily_rates ADD COLUMN updated_by INT AFTER source, ADD FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
