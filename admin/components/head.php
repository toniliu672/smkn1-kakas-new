<?php
// admin/components/head.php

if (!isset($page_title)) {
    $page_title = 'SMKN 1 Kakas';
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="../../public/css/tailwind.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../public/logo.jpg" type="image/x-icon">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toast -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .bg-image {
            background-image: url('../../public/logo2.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            height: 100%;
            max-height: 1200px;
            opacity: 0.15;
            z-index: -1;
        }

        .glassmorphism {
            background: rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 20px;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            @apply border border-gray-300 rounded-md shadow-sm py-2 px-3;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            @apply bg-blue-500 text-white border-blue-500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            @apply bg-blue-100 text-blue-600 border-blue-200;
        }
    </style>
</head>