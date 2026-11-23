<?php
// Shared by index.php and api/tickets.php so the page and the API always match.
// $filter is 'all', 'open' or 'mine'. Dates come back already formatted.

// Fetches tickets based on the filter and user ID.
function fetch_tickets(PDO $pdo, string $filter, int $userId): array
{
    // The query selects ticket details along with the names of the creator and assignee.
    $sql = 'SELECT t.id, t.title, t.priority, t.status, t.created_at,
                   CONCAT(c.firstname, " ", c.lastname) AS created_by_name,
                   CONCAT(a.firstname, " ", a.lastname) AS assigned_to_name
            FROM tickets AS t
            JOIN users AS c ON c.id = t.created_by
            LEFT JOIN users AS a ON a.id = t.assigned_to'; // LEFT JOIN keeps unassigned tickets

    // Prepare parameters for the query based on the filter.
    $params = [];
    if ($filter === 'open') {
        $sql .= " WHERE t.status <> 'Closed'";
    } elseif ($filter === 'mine') {
        $sql .= ' WHERE t.assigned_to = :uid';
        $params['uid'] = $userId;
    }
    // 'all' adds no WHERE clause.

    // Seed tickets share one timestamp, so id breaks the tie.
    $sql .= ' ORDER BY t.created_at DESC, t.id DESC';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    // Fetch all tickets and format them into an array.
    $tickets = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $tickets[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'priority' => $row['priority'],
            'status' => $row['status'],
            'created_by_name' => $row['created_by_name'],
            // CONCAT gives NULL when nobody is assigned, which is what the API must send
            'assigned_to_name' => $row['assigned_to_name'],
            'created_at' => format_datetime($row['created_at']),
        ];
    }
    return $tickets;
}
