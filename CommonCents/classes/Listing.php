<?php

require_once __DIR__ . '/../config/database.php';

class Listing
{
    public function create(
        $title,
        $description,
        $price,
        $type,
        $imageURL,
        $status,
        $userID,
        $categoryID
    ) {
        global $conn;

        $datePosted = date("Y-m-d H:i:s");

        $sql = "INSERT INTO listing
                (title, description, price, type, imageURL,
                 datePosted, status, userID, categoryID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssdssssii",
            $title,
            $description,
            $price,
            $type,
            $imageURL,
            $datePosted,
            $status,
            $userID,
            $categoryID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT l.*,
                       u.firstName,
                       u.lastName,
                       c.categoryName
                FROM listing l
                INNER JOIN `user` u
                    ON l.userID = u.userID
                INNER JOIN category c
                    ON l.categoryID = c.categoryID
                ORDER BY l.datePosted DESC";

        return $conn->query($sql);
    }

    public function getById($listingID)
    {
        global $conn;

        $sql = "SELECT l.*,
                       u.firstName,
                       u.lastName,
                       c.categoryName
                FROM listing l
                INNER JOIN `user` u
                    ON l.userID = u.userID
                INNER JOIN category c
                    ON l.categoryID = c.categoryID
                WHERE l.listingID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $listingID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUser($userID)
    {
        global $conn;

        $sql = "SELECT *
                FROM listing
                WHERE userID = ?
                ORDER BY datePosted DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update(
        $listingID,
        $title,
        $description,
        $price,
        $type,
        $imageURL,
        $status,
        $categoryID
    ) {
        global $conn;

        $sql = "UPDATE listing
                SET title = ?,
                    description = ?,
                    price = ?,
                    type = ?,
                    imageURL = ?,
                    status = ?,
                    categoryID = ?
                WHERE listingID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssdssssii",
            $title,
            $description,
            $price,
            $type,
            $imageURL,
            $status,
            $categoryID,
            $listingID
        );

        return $stmt->execute();
    }

    public function updateStatus($listingID, $status)
    {
        global $conn;

        $sql = "UPDATE listing
                SET status = ?
                WHERE listingID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $listingID);

        return $stmt->execute();
    }

    public function delete($listingID)
    {
        global $conn;

        $sql = "DELETE FROM listing
                WHERE listingID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $listingID);

        return $stmt->execute();
    }
}

?>
