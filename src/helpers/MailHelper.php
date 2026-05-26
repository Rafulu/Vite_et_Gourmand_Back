<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    private static function createMailer(): PHPMailer
    {
        $config = require __DIR__ . '/../../config/mail.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$config['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($config['from'], $config['from_name']);

        return $mail;
    }

    private static function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    private static function validateEmail(string $email): bool
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private static function send(string $to, string $name, string $subject, string $body): void
    {
        if (!self::validateEmail($to)) {
            error_log('MailHelper::send - adresse invalide : ' . $to);
            return;
        }

        try {
            $mail = self::createMailer();
            $mail->addAddress(self::sanitize($to), self::sanitize($name));
            $mail->isHTML(true);
            $mail->Subject = self::sanitize($subject);
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            $mail->send();
        } catch (Exception $e) {
            error_log('MailHelper::send - ' . $e->getMessage());
        }
    }

    // 1. Mail de bienvenue
    public static function sendWelcome(string $to, string $name): void
    {
        $name = self::sanitize($name);
        $body = "
            <h1>Bonjour {$name},</h1>
            <p>Bienvenue chez <strong>Vite &amp; Gourmand</strong> !</p>
            <p>Votre compte a été créé avec succès. Vous pouvez dès maintenant commander nos menus.</p>
            <p>À bientôt,<br>L'équipe Vite &amp; Gourmand</p>
        ";
        self::send($to, $name, 'Bienvenue chez Vite & Gourmand', $body);
    }

    // 2. Mail de confirmation de commande
    public static function sendOrderConfirmation(string $to, string $name, string $orderNumber, string $deliveryDate): void
    {
        $name        = self::sanitize($name);
        $orderNumber = self::sanitize($orderNumber);
        $deliveryDate = self::sanitize($deliveryDate);
        $body = "
            <h1>Bonjour {$name},</h1>
            <p>Votre commande <strong>{$orderNumber}</strong> a bien été enregistrée.</p>
            <p>Date de livraison prévue : <strong>{$deliveryDate}</strong></p>
            <p>Vous pouvez suivre votre commande depuis votre espace client.</p>
            <p>À bientôt,<br>L'équipe Vite &amp; Gourmand</p>
        ";
        self::send($to, $name, 'Confirmation de votre commande - ' . $orderNumber, $body);
    }

    // 3. Mail reset mot de passe
    public static function sendResetPassword(string $to, string $name, string $resetLink): void
    {
        $name = self::sanitize($name);
        // Le lien n'est pas sanitizé via htmlspecialchars pour conserver l'URL
        if (!filter_var($resetLink, FILTER_VALIDATE_URL)) {
            error_log('MailHelper::sendResetPassword - lien invalide');
            return;
        }
        $resetLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
        $body = "
            <h1>Bonjour {$name},</h1>
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p><a href='{$resetLink}'>Cliquez ici pour réinitialiser votre mot de passe</a></p>
            <p>Ce lien est valable <strong>1 heure</strong>. Si vous n'êtes pas à l'origine de cette demande, ignorez ce mail.</p>
            <p>À bientôt,<br>L'équipe Vite &amp; Gourmand</p>
        ";
        self::send($to, $name, 'Réinitialisation de votre mot de passe', $body);
    }

    // 4. Mail fin de commande (invitation avis)
    public static function sendOrderCompleted(string $to, string $name, int $orderId): void
    {
        $name    = self::sanitize($name);
        $orderId = (int)$orderId;
        $body = "
            <h1>Bonjour {$name},</h1>
            <p>Votre commande a été livrée et est maintenant terminée.</p>
            <p>Nous espérons que vous avez apprécié notre prestation !</p>
            <p><a href='/my-reviews'>Cliquez ici pour donner votre avis</a></p>
            <p>À bientôt,<br>L'équipe Vite &amp; Gourmand</p>
        ";
        self::send($to, $name, 'Votre commande est terminée - Donnez votre avis !', $body);
    }

    // 5. Mail retour matériel
    public static function sendMaterialReturn(string $to, string $name, string $orderNumber): void
    {
        $name        = self::sanitize($name);
        $orderNumber = self::sanitize($orderNumber);
        $body = "
            <h1>Bonjour {$name},</h1>
            <p>Votre commande <strong>{$orderNumber}</strong> inclut un prêt de matériel.</p>
            <p>Vous disposez de <strong>10 jours ouvrés</strong> pour restituer le matériel.</p>
            <p>Sans restitution dans ce délai, des frais de <strong>600 euros</strong> vous seront facturés, conformément à nos CGV.</p>
            <p>Pour organiser le retour, contactez-nous via notre page contact.</p>
            <p>À bientôt,<br>L'équipe Vite &amp; Gourmand</p>
        ";
        self::send($to, $name, 'Retour du matériel - ' . $orderNumber, $body);
    }
}