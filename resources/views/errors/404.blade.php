{{-- filepath: d:\alvif\projects\app-pajak\resources\views\errors\404.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            color: #333;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .center-box {
            max-width: 500px;
            margin: 8% auto 0 auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,72,88,0.12);
            padding: 48px 32px 40px 32px;
            text-align: center;
        }
        .center-box h1 {
            font-size: 6rem;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
        }
        .center-box h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #374151;
        }
        .center-box p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .btn-home {
            display: inline-block;
            padding: 12px 32px;
            background: #2563eb;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #1d4ed8;
        }
        @media (max-width: 600px) {
            .center-box {
                padding: 32px 10px 24px 10px;
            }
            .center-box h1 {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="center-box">
        <h1>404</h1>
        <h2>Halaman Tidak Ditemukan</h2>
        <p>Maaf, halaman yang kamu cari tidak tersedia, sudah dipindahkan, atau mungkin sudah dihapus.<br>
        Silakan kembali ke halaman utama.</p>
        <a href="{{ url('/') }}" class="btn-home">Kembali ke Beranda</a>
    </div>
</body>