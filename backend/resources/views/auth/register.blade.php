
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Peminjam</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
        }

        .register-card {
            width: 100%;
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        h1 {
            margin: 0 0 30px;
            text-align: center;
            color: #1f2937;
            font-size: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #1f2937;
        }

        input {
            width: 100%;
            height: 48px;
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #2563eb;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            margin: -12px 0 16px;
            color: #dc2626;
            font-size: 14px;
        }

        .footer {
            margin-top: 22px;
            text-align: center;
            color: #4b5563;
        }

        .footer a {
            color: #2563eb;
            font-weight: bold;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <h1>Register Peminjam</h1>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf

            <label for="name">Nama Lengkap</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Masukkan nama lengkap"
                autocomplete="name"
                required
            >

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email"
                autocomplete="email"
                required
            >

            <label for="no_hp">Nomor HP</label>
            <input
                type="tel"
                id="no_hp"
                name="no_hp"
                value="{{ old('no_hp') }}"
                placeholder="Contoh: 081234567890"
                autocomplete="tel"
                inputmode="numeric"
                pattern="[0-9]{10,15}"
                maxlength="15"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                minlength="8"
                required
            >

            <label for="password_confirmation">Konfirmasi Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Ulangi password"
                autocomplete="new-password"
                required
            >

            <button type="submit">Buat Akun</button>
        </form>

        <div class="footer">
            Sudah memiliki akun?
            <a href="{{ route('login') }}">Masuk</a>
        </div>
    </div>

</body>
</html>
