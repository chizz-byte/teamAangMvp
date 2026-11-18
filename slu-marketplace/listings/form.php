<?php
// Shared upload handler. Returns ['path'=>?string, 'error'=>?string].
// Stores file in uploads/listings/ and returns DB-friendly relative path.
function handle_listing_image(array $file): array {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Image upload failed.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['path' => null, 'error' => 'Image must be 5MB or smaller.'];
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['path' => null, 'error' => 'Only JPG, PNG, or WebP images are allowed.'];
    }
    // Verify it is a real image.
    if (!@getimagesize($file['tmp_name'])) {
        return ['path' => null, 'error' => 'Uploaded file is not a valid image.'];
    }
    $dir = __DIR__ . '/../uploads/listings';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = 'lst_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return ['path' => null, 'error' => 'Could not save uploaded image.'];
    }
    return ['path' => 'uploads/listings/' . $name, 'error' => null];
}

// Validate + normalize listing form POST data. Returns [data, errors].
function validate_listing_post(array $post): array {
    $errors = [];
    $title = trim($post['title'] ?? '');
    $description = trim($post['description'] ?? '');
    $category = $post['category'] ?? '';
    $department = trim($post['department'] ?? '');
    $course_code = trim($post['course_code'] ?? '');
    $edition = trim($post['edition'] ?? '');
    $price = $post['price'] ?? '';
    $condition = $post['condition'] ?? '';

    if ($title === '' || mb_strlen($title) > 200) $errors[] = 'Title is required (max 200 chars).';
    if (mb_strlen($description) > 5000) $errors[] = 'Description is too long.';
    if (!in_array($category, Listing::CATEGORIES, true)) $errors[] = 'Please select a valid category.';
    if ($department !== '' && !in_array($department, Listing::DEPARTMENTS, true)) $errors[] = 'Invalid department.';
    if ($course_code !== '' && mb_strlen($course_code) > 40) $errors[] = 'Course code too long.';
    if ($edition !== '' && mb_strlen($edition) > 60) $errors[] = 'Edition too long.';
    if (!is_numeric($price) || (float)$price <= 0 || (float)$price > 1000000) {
        $errors[] = 'Price must be a number greater than 0.';
    }
    if (!in_array($condition, Listing::CONDITIONS, true)) $errors[] = 'Please select a valid condition.';

    if ($errors) return [null, $errors];
    return [[
        'title' => $title,
        'description' => $description !== '' ? $description : null,
        'category' => $category,
        'department' => $department !== '' ? $department : null,
        'course_code' => $course_code !== '' ? $course_code : null,
        'edition' => $edition !== '' ? $edition : null,
        'price' => (float)$price,
        'condition' => $condition,
    ], []];
}
