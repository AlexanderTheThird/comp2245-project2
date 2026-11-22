<?php
function find_ticket(PDO $pdo, int $id): ?array
{
    $sql = 'SELECT t.id, t.title, t.description, t.priority, t.status,
                   t.created_by, t.assigned_to, t.created_at, t.updated_at,
                   CONCAT(c.firstname, " ", c.lastname) AS created_by_name,
                   CONCAT(a.firstname, " ", a.lastname) AS assigned_to_name
            FROM tickets AS t
            JOIN users AS c ON c.id = t.created_by
            LEFT JOIN users AS a ON a.id = t.assigned_to
            WHERE t.id = :id';
    $statement = $pdo->prepare($sql);
    $statement->execute(['id' => $id]);
    $ticket = $statement->fetch(PDO::FETCH_ASSOC);
    return $ticket === false ? null : $ticket;
}

// Used for ticket assignee selects and for checking that a chosen user exists.
function all_users(PDO $pdo): array
{
    $statement = $pdo->query('SELECT id, firstname, lastname,
                                    CONCAT(firstname, " ", lastname) AS name
                             FROM users
                             ORDER BY firstname, lastname, id');
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}
