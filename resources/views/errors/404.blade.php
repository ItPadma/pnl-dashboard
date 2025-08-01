{{-- filepath: d:\alvif\projects\app-pajak\resources\views\errors\404.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: #111827;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .center-box {
            text-align: center;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px 20px;
        }

        .center-box h1 {
            font-size: 3rem;
            color: #2563eb;
            margin-bottom: 1rem;
        }

        .center-box p {
            font-size: 1.2rem;
            color: #6b7280;
        }

        .btn-home {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .btn-home:hover {
            background: #1d4ed8;
        }

        .center-box img {
            width: 480px;
        }

        /* responsive */
        @media (max-width: 768px) {
            .center-box {
                padding: 20px 10px;
            }

            .center-box h1 {
                font-size: 2rem;
            }

            .center-box p {
                font-size: 1rem;
            }

            .center-box img {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="center-box">
        <img src="{{ asset('assets/img/not-found.png') }}" alt="not-found" width="480">
        <p>Maaf, halaman yang kamu cari tidak tersedia, sudah dipindahkan, atau mungkin sudah dihapus.<br>
        Silakan kembali ke halaman utama.</p>
        <a href="{{ url('/') }}" class="btn-home">Kembali ke Beranda</a>
    </div>
</body>
