<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Under Maintenance :(</title>
</head>

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

    .maintenance-box {
        text-align: center;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 40px 20px;
    }

    .maintenance-box h1 {
        font-size: 3rem;
        color: #2563eb;
        margin-bottom: 1rem;
    }

    .maintenance-box p {
        font-size: 1.2rem;
        color: #6b7280;
    }

    .maintenance-box img {
        width: 480px;
        margin-bottom: 20px;
    }

    /* responsive */
    @media (max-width: 768px) {
        .maintenance-box {
            padding: 20px 10px;
        }

        .maintenance-box h1 {
            font-size: 2rem;
        }

        .maintenance-box p {
            font-size: 1rem;
        }

        .maintenance-box img {
            width: 100%;
            height: auto;
        }
    }
</style>

<body>
    <div class="maintenance-box">
        <img src="{{ asset('assets/img/maintenance.png') }}" alt="Maintenance">
        <h1>We'll be back soon!</h1>
        <p>Sorry for the inconvenience, but we're performing some maintenance at the moment.</p>
        <p>Please check back later.</p>
    </div>
</body>

</html>
