<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de réinitialisation - Flotteq</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            color: #18A8A5;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .code-box {
            background: #f8f9fa;
            border: 2px solid #18A8A5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #18A8A5;
            letter-spacing: 4px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🚗 Flotteq</div>
            <h1>Code de réinitialisation de mot de passe</h1>
        </div>

        <p>Bonjour,</p>

        <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Flotteq associé à l'adresse email : <strong>{{ $userEmail }}</strong></p>

        <p>Voici votre code de vérification :</p>

        <div class="code-box">
            <div class="code">{{ $resetCode }}</div>
        </div>

        <p>Pour réinitialiser votre mot de passe :</p>
        <ol>
            <li>Retournez sur la page de réinitialisation</li>
            <li>Saisissez ce code de vérification</li>
            <li>Définissez votre nouveau mot de passe</li>
        </ol>

        <div class="warning">
            <strong>⚠️ Important :</strong>
            <ul>
                <li>Ce code expire dans <strong>15 minutes</strong></li>
                <li>N'utilisez ce code que si vous avez demandé cette réinitialisation</li>
                <li>Ne partagez jamais ce code avec qui que ce soit</li>
            </ul>
        </div>

        <p>Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email en toute sécurité.</p>

        <div class="footer">
            <p>Cordialement,<br>L'équipe Flotteq</p>
            <p>
                <em>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</em>
            </p>
        </div>
    </div>
</body>
</html>
