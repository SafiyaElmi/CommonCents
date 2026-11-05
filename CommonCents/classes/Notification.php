<?php

require_once __DIR__ . '/../config/database.php';

class Notification
{
    public function create($message, $userID)
    {
        global $conn;

        $notificationDate = date("Y-m-d H:i:s");

        $sql = "INSERT INTO notification
                (message, notificationDate, userID)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssi",
            $message,
            $notificationDate,
            $userID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT n.*,
                       u.firstName,
                       u.lastName
                FROM notification n
                INNER JOIN `user` u
                    ON n.userID = u.userID
                ORDER BY n.notificationDate DESC";

        return $conn->query($sql);
    }

    public function getById($notificationID)
    {
        global $conn;

        $sql = "SELECT *
                FROM notification
                WHERE notificationID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $notificationID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUser($userID)
    {
        global $conn;

        $sql = "SELECT *
                FROM notification
                WHERE userID = ?
                ORDER BY notificationDate DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update($notificationID, $message)
    {
        global $conn;

        $sql = "UPDATE notification
                SET message = ?
                WHERE notificationID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $message,
            $notificationID
        );

        return $stmt->execute();
    }

    public function delete($notificationID)
    {
        global $conn;

        $sql = "DELETE FROM notification
                WHERE notificationID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $notificationID);

        return $stmt->execute();
    }
}

?>
