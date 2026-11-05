<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    public function create($firstName, $lastName, $email, $password)
    {
        global $conn;

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO `user`
                (firstName, lastName, email, password)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssss",
            $firstName,
            $lastName,
            $email,
            $hashedPassword
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT userID, firstName, lastName, email
                FROM `user`
                ORDER BY userID DESC";

        $result = $conn->query($sql);

        return $result;
    }

    public function getById($userID)
    {
        global $conn;

        $sql = "SELECT userID, firstName, lastName, email
                FROM `user`
                WHERE userID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByEmail($email)
    {
        global $conn;

        $sql = "SELECT *
                FROM `user`
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function update($userID, $firstName, $lastName, $email)
    {
        global $conn;

        $sql = "UPDATE `user`
                SET firstName = ?,
                    lastName = ?,
                    email = ?
                WHERE userID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssi",
            $firstName,
            $lastName,
            $email,
            $userID
        );

        return $stmt->execute();
    }

    public function updatePassword($userID, $password)
    {
        global $conn;

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE `user`
                SET password = ?
                WHERE userID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userID);

        return $stmt->execute();
    }

    public function delete($userID)
    {
        global $conn;

        $sql = "DELETE FROM `user`
                WHERE userID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);

        return $stmt->execute();
    }
}

?>
