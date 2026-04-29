<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'db',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'companyhub'
);
$pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'companyhub', $_ENV['DB_PASS'] ?? 'companyhub', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "Wiping existing data...\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach (['notes', 'messages', 'files', 'links', 'password_resets', 'users'] as $t) {
    $pdo->exec("TRUNCATE TABLE {$t}");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

echo "Seeding users...\n";
$users = [
    ['admin@companyhub.local',   'admin123',   'Avery Admin',     'IT',           'admin'],
    ['alice@companyhub.local',   'password1',  'Alice Anderson',  'Engineering',  'user'],
    ['bob@companyhub.local',     'qwerty',     'Bob Brown',       'Sales',        'user'],
    ['carol@companyhub.local',   'sunshine',   'Carol Carter',    'HR',           'user'],
    ['dave@companyhub.local',    'letmein',    'Dave Davis',      'Engineering',  'user'],
];
$insUser = $pdo->prepare(
    'INSERT INTO users (email, password_md5, display_name, department, role) VALUES (?, ?, ?, ?, ?)'
);
foreach ($users as [$email, $pw, $name, $dept, $role]) {
    // V13: passwords stored as raw MD5
    $insUser->execute([$email, md5($pw), $name, $dept, $role]);
}

$ids = $pdo->query('SELECT id, email FROM users')->fetchAll(PDO::FETCH_KEY_PAIR);
$idByEmail = array_flip($ids);
$adminId = $idByEmail['admin@companyhub.local'];
$aliceId = $idByEmail['alice@companyhub.local'];
$bobId   = $idByEmail['bob@companyhub.local'];
$carolId = $idByEmail['carol@companyhub.local'];

echo "Seeding notes...\n";
$insNote = $pdo->prepare(
    'INSERT INTO notes (user_id, title, body, is_public) VALUES (?, ?, ?, ?)'
);
$notes = [
    [$adminId, 'Server credentials',     "prod-db root: hunter2\nstaging-db root: hunter3", 0],
    [$adminId, 'Pinned: company values', 'Trust. Excellence. Customer focus.',              1],
    [$aliceId, 'Sprint planning notes',  'Need to ship the new dashboard by Friday.',       0],
    [$aliceId, 'Fav coffee shops',       "1. Blue Bottle\n2. Verve\n3. Sightglass",         1],
    [$bobId,   'Q2 prospects',           "Acme Corp, Initech, Massive Dynamic",             0],
    [$carolId, 'Onboarding checklist',   'Badge, laptop, slack invite, payroll form.',      1],
    [$carolId, 'Salary review notes',    'Confidential — see HRIS export.',                 0],
    [$aliceId, 'Random snippet',         'TODO: refactor the link parser.',                 0],
    [$bobId,   'Demo script',            "Open dashboard, show search, close with pricing.",1],
    [$adminId, 'Maintenance window',     'DB upgrade Sat 02:00-04:00 UTC.',                 1],
];
foreach ($notes as $n) { $insNote->execute($n); }

echo "Seeding messages...\n";
$insMsg = $pdo->prepare(
    'INSERT INTO messages (sender_id, recipient_id, body) VALUES (?, ?, ?)'
);
$messages = [
    [$aliceId, $bobId,   'Hey, can you forward me the Q2 deck?'],
    [$bobId,   $aliceId, "Sure, I'll send it after lunch."],
    [$adminId, $aliceId, 'Heads up: maintenance window this Saturday.'],
    [$carolId, $adminId, 'Need access to the HRIS export folder.'],
    [$adminId, $carolId, 'Granted. Use the shared folder at /uploads/hr.'],
];
foreach ($messages as $m) { $insMsg->execute($m); }

echo "Seeding links...\n";
$insLink = $pdo->prepare(
    'INSERT INTO links (user_id, url, title) VALUES (?, ?, ?)'
);
$links = [
    [$adminId, 'https://example.com',        'Example Home'],
    [$aliceId, 'https://news.ycombinator.com','Hacker News'],
    [$bobId,   'https://github.com',         'GitHub'],
];
foreach ($links as $l) { $insLink->execute($l); }

echo "Done. Sample logins:\n";
foreach ($users as [$email, $pw,,, $role]) {
    printf("  %-30s %-10s (%s)\n", $email, $pw, $role);
}
