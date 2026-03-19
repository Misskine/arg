<?php
session_start();
$heure = 'Indisponible';
$ch = curl_init('https://time.now/developer/api/ip');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$heureInitiale = '00:00:00';
if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['datetime'])) {
        $dt = new DateTime($data['datetime']);
        $heure = $dt->format('H\hi');
        $heureInitiale = $dt->format('H:i:s'); // pour JS
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heure actuelle</title>
    <style>
        @import url('./fonts/ds_digital/DS-DIGI.TTF');
        body {
            background: #1a1a2e;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'DS-Digital', sans-serif;
        }

        h1 {
            color: #aaa;
            font-size: 1.2rem;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .digital-clock {
            background: #0f0f23;
            border: 2px solid #00d4ff;
            border-radius: 16px;
            padding: 30px 50px;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.3),
                        inset 0 0 30px rgba(0, 212, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .digit-group {
            display: flex;
            gap: 4px;
        }

        .digit {
            font-size: 80px;
            font-weight: bold;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.8),
                         0 0 30px rgba(0, 212, 255, 0.4);
            line-height: 1;
            min-width: 50px;
            text-align: center;
        }

        .separator {
            font-size: 80px;
            font-weight: bold;
            color: #00d4ff;
            line-height: 1;
            animation: blink 1s step-end infinite;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.8);
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0; }
        }

        .label {
            color: #555;
            font-size: 11px;
            letter-spacing: 3px;
            text-align: center;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .digit-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <h1>Il est <?php echo htmlspecialchars($heure); ?></h1>

    <div class="digital-clock">
        <div class="digit-wrapper">
            <div class="digit-group">
                <span class="digit" id="h1">0</span>
                <span class="digit" id="h2">0</span>
            </div>
            <div class="label">heures</div>
        </div>

        <span class="separator">:</span>

        <div class="digit-wrapper">
            <div class="digit-group">
                <span class="digit" id="m1">0</span>
                <span class="digit" id="m2">0</span>
            </div>
            <div class="label">minutes</div>
        </div>

        <span class="separator">:</span>

        <div class="digit-wrapper">
            <div class="digit-group">
                <span class="digit" id="s1">0</span>
                <span class="digit" id="s2">0</span>
            </div>
            <div class="label">secondes</div>
        </div>
    </div>

    <script>
        // Heure initiale synchronisée avec l'API via PHP
        const heureAPI = '<?php echo $heureInitiale; ?>';
        const [h, m, s] = heureAPI.split(':').map(Number);

        // On crée une date avec l'heure de l'API
        const base = new Date();
        base.setHours(h, m, s, 0);
        const offset = base - new Date(); // décalage éventuel

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function updateClock() {
            const now = new Date(new Date().getTime() + offset);
            const hh = pad(now.getHours());
            const mm = pad(now.getMinutes());
            const ss = pad(now.getSeconds());

            document.getElementById('h1').textContent = hh[0];
            document.getElementById('h2').textContent = hh[1];
            document.getElementById('m1').textContent = mm[0];
            document.getElementById('m2').textContent = mm[1];
            document.getElementById('s1').textContent = ss[0];
            document.getElementById('s2').textContent = ss[1];
        }

        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>