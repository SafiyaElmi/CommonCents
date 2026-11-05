<?php

require_once __DIR__ . '/../config/database.php';

class Category
{
    public function create($categoryName)
    {
        global $conn;

        $sql = "INSERT INTO category (categoryName)
                VALUES (?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $categoryName);

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT *
                FROM category
                ORDER BY categoryName ASC";

        return $conn->query($sql);
    }

    public function getById($categoryID)
    {
        global $conn;

        $sql = "SELECT *
                FROM category
                WHERE categoryID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function update($categoryID, $categoryName)
    {
        global $conn;

        $sql = "UPDATE category
                SET categoryName = ?
                WHERE categoryID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $categoryName, $categoryID);

        return $stmt->execute();
    }

    public function delete($categoryID)
    {
        global $conn;

        $sql = "DELETE FROM category
                WHERE categoryID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryID);

        return $stmt->execute();
    }
}

?>
