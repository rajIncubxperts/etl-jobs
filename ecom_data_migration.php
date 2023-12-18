<?php
$sourceDBConfig = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'CraajDec@2022%',
    'database' => 'ecommerce_db'
);

$destDBConfig = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'CraajDec@2022%',
    'database' => 'ecommerce_destination_db'
);

$sourceConnection = new mysqli($sourceDBConfig['host'], $sourceDBConfig['user'], $sourceDBConfig['password'], $sourceDBConfig['database']);
if ($sourceConnection->connect_error) {
    die("Connection to source database failed: " . $sourceConnection->connect_error);
}

$destConnection = new mysqli($destDBConfig['host'], $destDBConfig['user'], $destDBConfig['password'], $destDBConfig['database']);
if ($destConnection->connect_error) {
    die("Connection to destination database failed: " . $destConnection->connect_error);
}

// Begin a transaction
$destConnection->begin_transaction();

// Set autocommit to false to handle transactions manually
$destConnection->autocommit(FALSE);

// Create tables in the destination database (if they don't exist)
$createCategoriesTableQuery = "
    CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(50) NOT NULL
    )
";

$createCustomersTableQuery = "
    CREATE TABLE IF NOT EXISTS customers (
        customers_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE 
    )
";

$createProductsTableQuery = "
    CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(100) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        category_id INT,
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
    )
";

$createOrdersTableQuery = "
    CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        customers_id INT,
        FOREIGN KEY (customers_id) REFERENCES customers(customers_id)
    )
";

$createOrderDetailsTableQuery = "
    CREATE TABLE IF NOT EXISTS order_details (
        order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT AUTO_INCREMENT,
        quantity INT,
        subtotal DECIMAL(10, 2),
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )
";

// Execute the table creation queries
$destConnection->query($createCategoriesTableQuery);
$destConnection->query($createCustomersTableQuery);
$destConnection->query($createProductsTableQuery);
$destConnection->query($createOrdersTableQuery);
$destConnection->query($createOrderDetailsTableQuery);

// Merge categories first
$mergeCategoriesQuery = "
    INSERT INTO ecommerce_destination_db.categories (category_name)
    SELECT category_name FROM ecommerce_db.categories
";

if ($destConnection->query($mergeCategoriesQuery) === TRUE) {
    echo "Merged categories into destination_database!\n";

    // Merge products after categories
    $mergeProductsQuery = "
        INSERT INTO ecommerce_destination_db.products (product_name, price, category_id)
        SELECT product_name, price, category_id FROM ecommerce_db.products
    ";

    if ($destConnection->query($mergeProductsQuery) === TRUE) {
        echo "Merged products into destination_database!\n";

        // Merge customers after products
        $mergeCustomersQuery = "
            INSERT INTO ecommerce_destination_db.customers (first_name, last_name, email)
            SELECT first_name, last_name, email FROM ecommerce_db.customers
            ON DUPLICATE KEY UPDATE email=VALUES(email)
        ";

        if ($destConnection->query($mergeCustomersQuery) === TRUE) {
            echo "Merged customers into destination_database!\n";

            // Merge orders after customers
            $mergeOrdersQuery = "
                INSERT INTO ecommerce_destination_db.orders (order_date, customers_id)
                SELECT order_date, customers_id FROM ecommerce_db.orders
            ";

            if ($destConnection->query($mergeOrdersQuery) === TRUE) {
                echo "Merged orders into destination_database!\n";

                // Merge order details after orders
                $mergeOrderDetailsQuery = "
                    INSERT INTO ecommerce_destination_db.order_details (order_id, product_id, quantity, subtotal)
                    SELECT order_id, product_id, quantity, subtotal FROM ecommerce_db.order_details
                ";

                if ($destConnection->query($mergeOrderDetailsQuery) === TRUE) {
                    echo "Merged order_details into destination_database!\n";
                } else {
                    echo "Error merging order_details: " . $destConnection->error;
                    // Rollback on failure
                    $destConnection->rollback();
                }
            } else {
                echo "Error merging orders: " . $destConnection->error;
                // Rollback on failure
                $destConnection->rollback();
            }
        } else {
            echo "Error merging customers: " . $destConnection->error;
            // Rollback on failure
            $destConnection->rollback();
        }
    } else {
        echo "Error merging products: " . $destConnection->error;
        // Rollback on failure
        $destConnection->rollback();
    }
} else {
    echo "Error merging categories: " . $destConnection->error;
    // Rollback on failure
    $destConnection->rollback();
}
// Commit the transaction if all queries executed successfully
$destConnection->commit();

// Close connections
$sourceConnection->close();
$destConnection->close();
?>