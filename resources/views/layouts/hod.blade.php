<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- If you're using Laravel Mix -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding-top: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            padding: 15px 20px;
        }

        .sidebar li a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
        }

        .sidebar li:hover {
            background-color: #34495e;
        }

        .content {
            flex-grow: 1;
            padding: 30px;
            background-color: #ffffff;
            overflow-y: auto;
        }

        .logout-button {
            margin-top: 20px;
            display: block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="{{ route('hod.dashboard') }}">Approve/Reject Leave</a></li>
                <li><a href="{{ route('hod.leave-history') }}">View Leave History</a></li>
            </ul>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>
