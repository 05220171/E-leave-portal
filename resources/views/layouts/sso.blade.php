<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSO Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #34495e;
            color: white;
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
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar li:hover {
            background-color: #2c3e50;
        }

        .content {
            flex-grow: 1;
            padding: 30px;
            background-color: #f4f6f8;
        }

        .logout-button {
            margin-top: 20px;
            display: block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            cursor: pointer;
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
                <li><a href="{{ route('sso.dashboard') }}">Approve/Reject Leave</a></li>
                <li><a href="{{ route('sso.leave-history') }}">View Leave History</a></li>
            </ul>

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
