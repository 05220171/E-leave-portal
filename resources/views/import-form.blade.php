<!DOCTYPE html>
<html>
<head>
    <title>Import Users</title>
    {{-- Optional: Add some basic styling --}}
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .alert-danger ul { margin-top: 10px; margin-bottom: 0; padding-left: 20px; list-style-position: inside; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { margin-bottom: 15px; padding: 5px; border: 1px solid #ccc; border-radius: 3px; }
        button { padding: 10px 18px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button:hover { background-color: #0056b3; }
        form div { margin-bottom: 15px; }
    </style>
</head>
<body>

    <h1>Import Users from Excel</h1>

    {{-- Display Success Message --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Display General Error Message (from session('error')) --}}
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
            {{-- Display detailed validation failures if they were added to the general error message --}}
             @if (is_array(session('validation_failures')))
                <ul style="margin-top: 10px;">
                    @foreach (session('validation_failures') as $failure)
                        <li>{{ $failure }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    {{-- Display Specific Validation Failures (if passed separately) --}}
    {{-- This checks for the custom key 'validation_failures' we used in the controller example --}}
     {{-- Note: The previous check inside session('error') might already display these if you passed the formatted string --}}
    @if (session('validation_failures') && !session('error')) {{-- Avoid double display if included in general error --}}
        <div class="alert alert-danger" role="alert">
            <strong>Import Validation Errors:</strong>
            <ul style="margin-top: 10px;">
                @foreach (session('validation_failures') as $failure)
                    <li>{{ $failure }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Display Standard Laravel Validation Errors (from ->validate() in controller, or ->back()->withErrors()) --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Whoops! There were some problems:</strong>
            <ul style="margin-top: 10px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- The action attribute now correctly uses the named route 'import.users.store' --}}
    <form action="{{ route('import.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file">Choose Excel File (.xlsx, .xls, .csv):</label>
            <input type="file" name="file" id="file" required accept=".xlsx, .xls, .csv"> {{-- Added accept attribute --}}
        </div>
        <button type="submit">Import</button>
    </form>

</body>
</html>