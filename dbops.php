<?PHP
function simpleQuerySqlite($dbPath, $queryString) {
    try {
        // Open SQLite database connection
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Execute query
        $stmt = $pdo->query($queryString);
        
        // Fetch results as an associative array
        $result = $stmt->fetchAll();
        
        // Close connection
        $pdo = null;
        
        return $result;
    } catch (PDOException $e) {
        return ["error" => $e->getMessage()];
    }
}