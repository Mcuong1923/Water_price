<?php
// payment_functions.php

function getUserDetails($conn, $user_id) {
    $sql = "SELECT name, email, address FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createInvoice($conn, $user_id, $total_amount) {
    $sql = "INSERT INTO invoices (id_user, total_amount) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('id', $user_id, $total_amount);
    return $stmt->execute();
}
