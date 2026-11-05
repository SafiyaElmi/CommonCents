<?php

require_once __DIR__ . '/../config/database.php';

class Review
{
    public function create(
        $rating,
        $comment,
        $userID,
        $listingID
    ) {
        global $conn;

        $reviewDate = date("Y-m-d H:i:s");

        $sql = "INSERT INTO review
                (rating, comment, reviewDate, userID, listingID)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "issii",
            $rating,
            $comment,
            $reviewDate,
            $userID,
            $listingID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT r.*,
                       u.firstName,
                       u.lastName,
                       l.title
                FROM review r
                INNER JOIN `user` u
                    ON r.userID = u.userID
                INNER JOIN listing l
                    ON r.listingID = l.listingID
                ORDER BY r.reviewDate DESC";

        return $conn->query($sql);
    }

    public function getById($reviewID)
    {
        global $conn;

        $sql = "SELECT r.*,
                       u.firstName,
                       u.lastName,
                       l.title
                FROM review r
                INNER JOIN `user` u
                    ON r.userID = u.userID
                INNER JOIN listing l
                    ON r.listingID = l.listingID
                WHERE r.reviewID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reviewID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByListing($listingID)
    {
        global $conn;

        $sql = "SELECT r.*,
                       u.firstName,
                       u.lastName
                FROM review r
                INNER JOIN `user` u
                    ON r.userID = u.userID
                WHERE r.listingID = ?
                ORDER BY r.reviewDate DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $listingID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update(
        $reviewID,
        $rating,
        $comment
    ) {
        global $conn;

        $sql = "UPDATE review
                SET rating = ?,
                    comment = ?
                WHERE reviewID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isi",
            $rating,
            $comment,
            $reviewID
        );

        return $stmt->execute();
    }

    public function delete($reviewID)
    {
        global $conn;

        $sql = "DELETE FROM review
                WHERE reviewID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reviewID);

        return $stmt->execute();
    }
}

?>
