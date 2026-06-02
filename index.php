<?php
session_start();

// Get table number
$tableNo = isset($_GET['table']) ? (int)$_GET['table'] : 0;

// validate table number
if ($tableNo < 1 || $tableNo > 5) {
    die("<h1 style='color:white; text-align:center; margin-top:100px; background:#0f172a; min-height:100vh;'>Invalid QR Code. Please scan again.</h1>");
}

// Store table number
$_SESSION['current_table'] = $tableNo;
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>ကေတုအလင်္ကာ Smart Restraunt </title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --accent: #ff6b3d;
        }

        body {
            font-family: 'Inter', system-ui, Arial, sans-serif;
            min-height: 100vh;
            color: #fff;
        }

        /* Hero Section with Background Image */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1600');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        /* Dark Overlay */
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.75));
        }

        .hero-inner {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 650px;
            width: 100%;
            margin: 0 auto;
        }

        /* Badge */
        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Title */
        h1 {
            font-size: clamp(1.8rem, 5vw, 2.8rem);
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        h3 {
            font-size: clamp(1rem, 4vw, 1.3rem);
            font-weight: 500;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Table Badge */
        .table-badge {
            display: inline-block;
            background: #10b981;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.3);
        }

        .lead {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Buttons */
        .ctas {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-order {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 10px 25px rgba(255, 107, 61, 0.3);
        }

        .btn-order:hover {
            transform: translateY(-3px);
            background: #ff8555;
            box-shadow: 0 15px 30px rgba(255, 107, 61, 0.4);
        }

        /* Promo Strip */
        .promo {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 24px;
            padding: 12px 20px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            flex-wrap: wrap;
            z-index: 100;
        }

        .promo-item {
            font-size: 0.85rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .promo-item i {
            color: #10b981;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 30px 16px;
            }
            .btn {
                padding: 10px 24px;
                font-size: 0.9rem;
            }
            .table-badge {
                font-size: 1rem;
                padding: 6px 20px;
            }
            .promo {
                gap: 12px;
                padding: 8px 12px;
            }
            .promo-item {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>

<section class="hero" role="banner">
    <div class="hero-inner">
        <div class="badge">
            <i class="fa fa-star"></i> Certified 5★
        </div>

        <h2>
            <strong>ကေတုအလင်္ကာ Smart Restaurant</strong><br>
        </h2>

        <h3>
            မှ နွေးထွေးစွာ ကြိုဆိုပါသည်။
        </h3>

        <div class="table-badge">
            <i></i> စားပွဲအမှတ်: <?php echo $tableNo; ?>
        </div>

        <p class="lead">
            လူကြီးမင်း မှာယူလိုသော အစားအစာများကို <br>
            Order မှာ၍ ရယူနိုင်ပါပြီ။
        </p>

        <div class="ctas">
            <a class="btn btn-order" href="menu.php?table=<?php echo $tableNo; ?>">
                <i class="fa fa-shopping-cart"></i> Order Now
            </a>
        </div>
    </div>
</section>

<!-- Promo Strip -->
<div class="promo" role="note">
    <div class="promo-item">
        <i class="fa fa-star"></i> သောကြာနေ့တိုင်း အထူးဟင်းလျာများရပါပြီ။
    </div>
    <div class="promo-item">
        <i class="fa fa-gift"></i> ကျပ် ၁၀၀,၀၀၀ နှင့်အထက် မှာယူပါက အခမဲ့အချိုပွဲ ရယူနိုင်ပါသည်။
    </div>
    <div class="promo-item">
        <i class="fa fa-clock-o"></i> ဆိုင်ဖွင့်ချိန်: နံနက် ၉:၀၀ မှ ည ၁၁:၀၀ အထိ။
    </div>
</div>

</body>
</html>