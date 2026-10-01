<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Delicious Food, Delivered Fast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .btn-primary {
            background: linear-gradient(135deg, #6C63FF 0%, #5A52D5 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5A52D5 0%, #4A42C5 100%);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #FF4757;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }
        .food-image {
            height: 200px;
            object-fit: cover;
        }
        .restaurant-image {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>