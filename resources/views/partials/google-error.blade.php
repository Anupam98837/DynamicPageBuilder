<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Failed</title>
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
            padding:20px;
        }
        .card{
            width:100%;
            max-width:460px;
            background:#fff;
            border:1px solid #ead7d8;
            border-radius:18px;
            box-shadow:0 16px 40px rgba(42,15,16,.08);
            padding:32px 24px;
            text-align:center;
        }
        .icon{
            width:52px;
            height:52px;
            margin:0 auto 16px;
            border-radius:50%;
            background:#fff1f1;
            color:#b42318;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            font-weight:700;
            border:1px solid #f3c2c2;
        }
        h2{
            margin:0 0 10px;
            font-size:24px;
        }
        p{
            margin:0 0 18px;
            color:#6f6263;
            line-height:1.55;
        }
        .btn{
            display:inline-block;
            text-decoration:none;
            background:#9E363A;
            color:#fff;
            border:none;
            border-radius:12px;
            padding:12px 18px;
            font-weight:700;
            box-shadow:0 10px 24px rgba(158,54,58,.18);
        }
        .meta{
            margin-top:14px;
            font-size:13px;
            color:#8a7a7b;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">!</div>
        <h2>Unable to sign in</h2>
        <p>{{ $message ?? 'Something went wrong during Google login.' }}</p>
        <a class="btn" href="{{ $loginUrl ?? url('/') }}">Back to Login</a>
        <div class="meta" id="countdownText">
            You will be redirected automatically in {{ $redirectAfter ?? 4 }} seconds.
        </div>
    </div>

    <script>
        (function () {
            const loginUrl = @json($loginUrl ?? url('/'));
            let secondsLeft = Math.max(1, Number(@json($redirectAfter ?? 4)));
            const countdownText = document.getElementById('countdownText');

            try {
                sessionStorage.removeItem('token');
                sessionStorage.removeItem('role');
                sessionStorage.removeItem('user');
                sessionStorage.removeItem('keep_login');
            } catch (e) {}

            function renderCountdown() {
                if (!countdownText) return;
                countdownText.textContent =
                    'You will be redirected automatically in ' +
                    secondsLeft +
                    ' second' +
                    (secondsLeft === 1 ? '' : 's') +
                    '.';
            }

            renderCountdown();

            const interval = setInterval(function () {
                secondsLeft--;

                if (secondsLeft <= 0) {
                    clearInterval(interval);
                    window.location.replace(loginUrl);
                    return;
                }

                renderCountdown();
            }, 1000);
        })();
    </script>
</body>
</html>