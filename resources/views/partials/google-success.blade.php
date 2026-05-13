<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging in...</title>
    <style>
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#f8f5f5;
            font-family:Arial, Helvetica, sans-serif;
            color:#2a0f10;
        }
        .card{
            width:100%;
            max-width:420px;
            background:#fff;
            border:1px solid #ead7d8;
            border-radius:18px;
            box-shadow:0 16px 40px rgba(42,15,16,.08);
            padding:32px 24px;
            text-align:center;
        }
        .spinner{
            width:44px;
            height:44px;
            margin:0 auto 16px;
            border:4px solid #f1d8da;
            border-top-color:#9E363A;
            border-radius:50%;
            animation:spin 1s linear infinite;
        }
        h2{
            margin:0 0 10px;
            font-size:24px;
        }
        p{
            margin:0;
            color:#6f6263;
        }
        @keyframes spin{
            to{ transform:rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2>{{ $message ?? 'Login successful' }}</h2>
        <p>Redirecting to dashboard...</p>
    </div>

    <script>
        (function () {
            const token = @json($token);
            const user = @json($user);
            const role = @json($role ?? (($user->role ?? 'student')));
            const keep = @json($keep ?? false);
            const redirectUrl = @json($redirectUrl ?? url('/dashboard'));

            try {
                sessionStorage.setItem('token', token);
                sessionStorage.setItem('role', role);

                if (keep) {
                    localStorage.setItem('token', token);
                    localStorage.setItem('role', role);
                } else {
                    localStorage.removeItem('token');
                    localStorage.removeItem('role');
                }
            } catch (e) {}

            window.location.replace(redirectUrl);
        })();
    </script>
</body>
</html>