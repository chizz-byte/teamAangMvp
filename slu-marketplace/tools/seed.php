<?php
// Seed script: creates demo users, listings, and sample images.
// Run once AFTER importing database/slu_marketplace.sql:
//   C:\wamp64\bin\php\php8.2.26\php.exe tools\seed.php
require_once __DIR__ . '/../config/database.php';

$pdo = db();

// ---- Users ----
$users = [
    ['2020-0001', 'Maria Santos',   'admin@slu.edu.ph',         'password123', 'admin'],
    ['2021-1001', 'Juan Dela Cruz', 'juan.delacruz@slu.edu.ph', 'password123', 'student'],
    ['2022-2002', 'Ana Reyes',      'ana.reyes@slu.edu.ph',      'password123', 'student'],
    ['2023-3003', 'Pedro Lim',      'pedro.lim@slu.edu.ph',      'password123', 'student'],
];
$ids = [];
$check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
$ins = $pdo->prepare('INSERT INTO users (student_number, full_name, email, password_hash, role) VALUES (?,?,?,?,?)');
foreach ($users as [$num, $name, $email, $pass, $role]) {
    $check->execute([$email]);
    if ($existing = $check->fetchColumn()) { $ids[$email] = (int)$existing; continue; }
    $ins->execute([$num, $name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
    $ids[$email] = (int)$pdo->lastInsertId();
}
echo "Users ready.\n";

// ---- Sample images via GD ----
$imgDir = __DIR__ . '/../uploads/listings';
if (!is_dir($imgDir)) mkdir($imgDir, 0775, true);
function make_img(string $path, string $label, array $rgb): void {
    $w = 640; $h = 480;
    $im = imagecreatetruecolor($w, $h);
    $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
    imagefill($im, 0, 0, $bg);
    $white = imagecolorallocate($im, 255, 255, 255);
    // simple "book" rectangle
    imagefilledrectangle($im, 220, 90, 420, 390, $white);
    imagefilledrectangle($im, 235, 105, 405, 375, imagecolorallocate($im, 240, 244, 250));
    imagestring($im, 5, 250, 220, $label, imagecolorallocate($im, 40, 60, 110));
    imagejpeg($im, $path, 85);
    imagedestroy($im);
}
$palette = [
    ['Textbooks', [31,58,107]], ['Reviewers', [47,111,237]], ['Laboratory Manuals', [20,120,90]],
    ['Course Materials', [150,90,30]], ['Uniforms and Academic Attire', [120,60,120]], ['Other Academic Items', [80,80,90]],
];
$imgFiles = [];
foreach ($palette as $i => [$cat, $rgb]) {
    $fn = 'seed_' . ($i + 1) . '.jpg';
    make_img($imgDir . '/' . $fn, strtoupper(substr($cat, 0, 12)), $rgb);
    $imgFiles[$cat] = 'uploads/listings/' . $fn;
}
echo "Images generated.\n";

// ---- Listings ----
$dept = ['Information Technology','Accountancy','Engineering','Nursing','Medicine','Business','Education'];
$courses = ['ITE 28','ACC 211','ECE 101','NUR 204','MED 301','BUS 105','EDU 150','CS 201','MATH 140','PHY 101'];
$conds = ['Brand New','Like New','Good','Fair'];
$titles = [
    'Calculus Early Transcendentals 8th Ed', 'Financial Accounting Textbook',
    'ITE 28 Data Structures Reviewer', 'Anatomy & Physiology Lab Manual',
    'Organic Chemistry Lab Guide', 'SLU Nursing Uniform Set (Complete)',
    'Engineering Drawing Instruments Kit', 'Philippine History Reviewer 2024',
    'Biology Cell Biology Modules', 'Statistics for Business Workbook',
    'Medical Terminology Dictionary', 'Introduction to Programming Textbook',
];
$sellers = ['juan.delacruz@slu.edu.ph','ana.reyes@slu.edu.ph','pedro.lim@slu.edu.ph'];

$exists = $pdo->query('SELECT COUNT(*) FROM listings')->fetchColumn();
if ($exists == 0) {
    $insL = $pdo->prepare(
        'INSERT INTO listings (seller_id, title, description, category, department, course_code, edition, price, `condition`, status, image_path)
         VALUES (?,?,?,?,?,?,?,?,?,"available",?)'
    );
    foreach ($titles as $i => $t) {
        $cat = $palette[$i % count($palette)][0];
        $seller = $ids[$sellers[$i % count($sellers)]];
        $price = rand(150, 1200) + (rand(0,99)/100);
        $desc = "Secondhand academic material for SLU students. Condition as described. "
              . "Meet on campus to inspect before buying. Payment via cash or GCash.";
        $insL->execute([
            $seller, $t, $desc, $cat, $dept[$i % count($dept)], $courses[$i % count($courses)],
            rand(0,1) ? (rand(1,5).'th Edition') : null,
            $price, $conds[$i % count($conds)], $imgFiles[$cat]
        ]);
    }
    echo "Listings seeded.\n";
} else {
    echo "Listings already exist, skipping.\n";
}

echo "\nDone!\n";
echo "Admin login:    admin@slu.edu.ph / password123  (change before demo if desired)\n";
echo "Student logins:  juan.delacruz@slu.edu.ph, ana.reyes@slu.edu.ph, pedro.lim@slu.edu.ph / password123\n";
