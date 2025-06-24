# redcap_phpmailer_hook

**redcap_phpmailer_hook** is a lightweight utility that installs [PHPMailer](https://github.com/PHPMailer/PHPMailer) and integrates it into REDCap using the `redcap_email()` hook. It enables sending REDCap emails via SMTP with support for authentication, HTML formatting, attachments, and secure delivery.

---

## 🚀 Features

- Installs PHPMailer using Composer (automatically).
- Overrides REDCap’s native email system via the `redcap_email()` hook.
- Sends emails through SMTP with TLS/SSL support.
- Supports HTML messages, attachments, and sender customization.
- Simple configuration with one config file.

---

## 🛠 Requirements

- REDCap (with hooks enabled)
- PHP 7.2 or higher
- [Composer](https://getcomposer.org/) installed on your server

---

## 📁 File Structure

Place the following files in your REDCap root directory:

```yaml
/redcap/
├── index.php
├── hook_functions.php
├── installer_mailer_hook.php
└── mailer_config.php
````

---

## 🧩 Files Explained

### `installer_mailer_hook.php`

This script installs PHPMailer via Composer.

#### Run via command line:

```bash
php installer_mailer_hook.php
```

#### To run in a web browser:

```arduino
http://your-redcap-url/installer_mailer_hook.php
```

---

### `mailer_config.php`

This file stores your SMTP settings and default sender identity.

```php
<?php
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',
    'smtp_encryption' => 'tls', // or 'ssl'
    'default_from_email' => 'your-email@gmail.com',
    'default_from_name' => 'REDCap'
];
```

> ⚠️ **Note:** If you're using Gmail, you must use an [App Password](https://support.google.com/accounts/answer/185833) instead of your actual password if 2-Factor Authentication (2FA) is enabled.

---

## 📧 How It Works

Once PHPMailer is installed and `mailer_config.php` is filled out, REDCap’s email system will use PHPMailer automatically via the `redcap_email()` hook inside `hook_functions.php`.

You can customize this further to extend or filter outgoing messages (e.g., log all emails, apply conditions, or change formatting).

---

## 🔐 Security Tips

* **Do not commit** `mailer_config.php` to version control.
* Consider placing `mailer_config.php` **outside the web root**, or restrict it with `.htaccess`.

---

## 📝 License

This project is licensed under the **MIT License**.

---

## 🙌 Credits

* Uses [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* Designed for REDCap email customization via the `redcap_email()` hook

```
