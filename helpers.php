<?php
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function read_json_body(): array
{
    $input = file_get_contents('php://input');
    $decoded = json_decode($input === false ? '' : $input, true);
    return is_array($decoded) && array_is_list($decoded) === false ? $decoded : [];
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function format_datetime($date): string
{
    if ($date === null || $date === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable((string) $date))->format('M j, Y g:i A');
    } catch (Exception $exception) {
        return '';
    }
}

function slug(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
}

function field_error(array $errors, string $field): void
{
    if (isset($errors[$field])) {
        echo '<p class="field-error" role="alert">' . e($errors[$field]) . '</p>';
    }
}
