<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .admin-sidebar {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
        }
        .admin-sidebar a:hover {
            background: rgba(108, 99, 255, 0.2);
        }
        .admin-sidebar a.active {
            background: rgba(108, 99, 255, 0.3);
            border-left: 3px solid #6C63FF;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5A52D5 0%, #4A42C5 100%);
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>