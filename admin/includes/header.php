<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_reread_ui_lang = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'km') ? 'km' : 'en';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_reread_ui_lang, ENT_QUOTES, 'UTF-8'); ?>" class="lang-<?= $_reread_ui_lang === 'km' ? 'km' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="../assets/images/favicon.png">

    <!-- Embedded Admin CSS -->
    <style>
        /* ===========================
           Admin Panel Global Styles
        =========================== */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        a { text-decoration: none; color: #3498db; }
        a:hover { color: #2980b9; }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 { color: #2c3e50; margin-bottom: 15px; }
        section { margin-bottom: 30px; }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        .btn-primary { background-color: #3498db; color: #fff; border: none; }
        .btn-primary:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; color: #fff; border: none; }
        .btn-danger:hover { background-color: #c0392b; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background-color: #3498db; color: #fff; }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        table tr:hover { background-color: #f1f1f1; }

        /* Forms */
        form label { display: block; margin-top: 10px; font-weight: bold; }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form select,
        form textarea {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        form textarea { min-height: 80px; resize: vertical; }
        form button { margin-top: 15px; }

        /* Alerts */
        .alert { padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background-color: #2ecc71; color: #fff; }
        .alert-danger { background-color: #e74c3c; color: #fff; }

        /* Dashboard Cards */
        .dashboard-cards { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .dashboard-cards .card {
            flex: 1 1 200px;
            padding: 20px;
            border-radius: 8px;
            background-color: #3498db;
            color: #fff;
            text-align: center;
        }
        .dashboard-cards .card h3 { margin: 0 0 10px 0; }
        .dashboard-cards .card p { font-size: 24px; margin: 0; }

        /* Book management redesign */
        .admin-books-page {
            box-shadow: 0 18px 40px rgba(31, 45, 61, 0.08);
        }
        .admin-page-head {
            align-items: flex-start;
            display: flex;
            gap: 24px;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .admin-eyebrow {
            color: #7f8c8d;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.4px;
            margin: 0 0 8px;
            text-transform: uppercase;
        }
        .admin-page-head h1 {
            font-size: 30px;
            line-height: 1.1;
            margin: 0 0 8px;
        }
        .admin-page-head p:not(.admin-eyebrow) {
            color: #64748b;
            line-height: 1.55;
            margin: 0;
            max-width: 680px;
        }
        .admin-link-btn {
            background: #2c3e50;
            border-radius: 7px;
            color: #fff;
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 700;
            padding: 11px 15px;
        }
        .admin-link-btn:hover {
            background: #1f2c38;
            color: #fff;
        }
        .admin-stat-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 22px;
        }
        .admin-stat-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            color: #2c3e50;
            display: block;
            padding: 18px;
        }
        .admin-stat-card:hover,
        .admin-stat-card.active {
            border-color: #3498db;
            box-shadow: 0 10px 22px rgba(52, 152, 219, 0.12);
            color: #2c3e50;
        }
        .admin-stat-card span {
            color: #64748b;
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .admin-stat-card strong {
            display: block;
            font-size: 30px;
            line-height: 1;
        }
        .admin-stat-card.status-pending strong { color: #d97706; }
        .admin-stat-card.status-approved strong { color: #15803d; }
        .admin-stat-card.status-rejected strong { color: #dc2626; }
        .admin-table-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }
        .admin-table-head {
            align-items: center;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            padding: 18px 20px;
        }
        .admin-table-head h2 {
            font-size: 20px;
            margin: 0 0 4px;
        }
        .admin-table-head p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }
        .admin-table-wrap {
            overflow-x: auto;
        }
        .admin-books-table {
            margin: 0;
            min-width: 920px;
        }
        .admin-books-table th,
        .admin-books-table td {
            border: 0;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        .admin-books-table th {
            background: #fff;
            color: #64748b;
            font-size: 12px;
            letter-spacing: .8px;
            text-transform: uppercase;
        }
        .admin-books-table tr:nth-child(even) {
            background: #fff;
        }
        .admin-books-table tr:hover {
            background: #f8fafc;
        }
        .admin-book-title {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 280px;
        }
        .admin-book-title img {
            background: #eef2f7;
            border-radius: 6px;
            height: 58px;
            object-fit: cover;
            width: 46px;
        }
        .admin-book-title strong {
            color: #1f2937;
            display: block;
            line-height: 1.35;
            max-width: 320px;
        }
        .admin-book-title span {
            color: #94a3b8;
            display: block;
            font-size: 12px;
            margin-top: 3px;
        }
        .admin-status {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 10px;
        }
        .admin-status-approved {
            background: #dcfce7;
            color: #166534;
        }
        .admin-status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .admin-status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        .admin-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            min-width: 250px;
        }
        .admin-action {
            border-radius: 6px;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            padding: 7px 9px;
        }
        .admin-action:hover {
            color: #fff;
            opacity: .88;
        }
        .admin-action.view { background: #2563eb; }
        .admin-action.approve { background: #16a34a; }
        .admin-action.reject { background: #ea580c; }
        .admin-action.delete { background: #334155; }
        .admin-empty {
            color: #64748b;
            display: grid;
            gap: 6px;
            padding: 30px;
            text-align: center;
        }
        .admin-empty strong {
            color: #2c3e50;
            font-size: 18px;
        }
        @media (max-width: 850px) {
            .admin-page-head {
                flex-direction: column;
            }
            .admin-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 540px) {
            .admin-stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/khmer-overrides.css">
</head>
<body>

<header style="background:#2c3e50;color:#fff;padding:15px 20px;">
    <h1 style="margin:0;font-size:24px;">Admin Panel</h1>
</header>

</nav>
