<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #wrapper {
            display: flex;
            height: 100vh;
        }
        #sidebar-wrapper {
            width: 250px;
        }
        #page-content-wrapper {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        @include('superadmin.sidebar') <!-- Sidebar for superadmin -->
        <div id="page-content-wrapper">
            @yield('content') <!-- Your content will be injected here -->
        </div>
    </div>
</body>
</html>
