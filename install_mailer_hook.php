<?php
echo "🔧 REDCap PHPMailer Installer Starting...\n";

// Step 1: Check for Composer
echo "🔍 Checking for Composer...\n";
$composerCheck = shell_exec('composer --version');
if (stripos($composerCheck, 'Composer') === false) {
    exit("❌ Composer is not installed or not in PATH.\n");
}
echo "✅ Composer is available.\n";

// Step 2: Install PHPMailer if missing
$phpMailerPath = __DIR__ . '/vendor/phpmailer/phpmailer';
if (!is_dir($phpMailerPath)) {
    echo "📦 PHPMailer not found. Installing...\n";
    echo shell_exec('composer require phpmailer/phpmailer');
    if (!is_dir($phpMailerPath)) {
        exit("❌ PHPMailer installation failed. Please check for Composer errors.\n");
    }
    echo "✅ PHPMailer installed.\n";
} else {
    echo "✅ PHPMailer already installed.\n";
}

// Step 3: Load config
$configFile = __DIR__ . '/mailer_config.php';
if (!file_exists($configFile)) {
    exit("❌ Config file 'mailer_config.php' not found.\n");
}
$config = require $configFile;

// Step 4: Generate redcap_email() injection code
$injection = <<<PHP

require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\\PHPMailer\\PHPMailer;
use PHPMailer\\PHPMailer\\Exception;

function redcap_email(\$to, \$from, \$subject, \$message, \$cc = null, \$bcc = null, \$fromName = null, \$attachments = []) {
    \$config = require __DIR__ . '/mailer_config.php';
    \$mail = new PHPMailer(true);
    try {
        \$mail->isSMTP();
        \$mail->Host = \$config['smtp_host'];
        \$mail->SMTPAuth = true;
        \$mail->Username = \$config['smtp_username'];
        \$mail->Password = \$config['smtp_password'];
        \$mail->SMTPSecure = \$config['smtp_encryption'];
        \$mail->Port = \$config['smtp_port'];
        \$mail->setFrom(\$from ?: \$config['default_from_email'], \$fromName ?: \$config['default_from_name']);
        foreach (preg_split('/[,;]/', \$to) as \$addr) if (trim(\$addr)) \$mail->addAddress(trim(\$addr));
        foreach (preg_split('/[,;]/', \$cc ?? '') as \$addr) if (trim(\$addr)) \$mail->addCC(trim(\$addr));
        foreach (preg_split('/[,;]/', \$bcc ?? '') as \$addr) if (trim(\$addr)) \$mail->addBCC(trim(\$addr));
        if (!empty(\$attachments)) foreach (\$attachments as \$att)
            if (!empty(\$att['name']) && !empty(\$att['content']))
                \$mail->addStringAttachment(\$att['content'], \$att['name']);
        \$mail->isHTML(true);
        \$mail->Subject = \$subject;
        \$mail->Body = \$message;
        \$mail->AltBody = strip_tags(\$message);
        \$mail->send();
        return true;
    } catch (Exception \$e) {
        error_log("PHPMailer error: " . \$e->getMessage());
        return false;
    }
}

PHP;

// Step 5: Update hook_functions.php
$hookFile = __DIR__ . '/hook_functions.php';
if (!file_exists($hookFile)) {
    echo "📄 Creating hook_functions.php...\n";
    file_put_contents($hookFile, "<?php\n\n");
}
$content = file_get_contents($hookFile);

// Check for existing redcap_email
if (preg_match('/function\s+redcap_email\s*\(.*?\)\s*\{.*?return\s+TRUE\s*;.*?\}/is', $content)) {
    echo "🔁 Placeholder redcap_email() found. Replacing...\n";
    $content = preg_replace('/function\s+redcap_email\s*\(.*?\)\s*\{.*?return\s+TRUE\s*;.*?\}/is', '', $content);
    file_put_contents($hookFile, $content . "\n" . $injection);
    echo "✅ Placeholder replaced with custom redcap_email().\n";
} elseif (strpos($content, 'function redcap_email(') !== false) {
    echo "⚠️  redcap_email() already exists with custom code. Skipping injection to avoid overwriting.\n";
} else {
    echo "✍️  Injecting redcap_email() into hook_functions.php...\n";
    file_put_contents($hookFile, $content . "\n" . $injection);
    echo "✅ Injection complete.\n";
}

echo "\n🎉 Installation Complete. You can now use PHPMailer for REDCap emails.\n";
