<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $mail;
    private $error;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configure();
    }

    private function configure() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = SMTP_HOST;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = SMTP_USERNAME;
            $this->mail->Password   = SMTP_PASSWORD;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port       = SMTP_PORT;
            $this->mail->CharSet    = 'UTF-8';
            
            // From email address and name
            $this->mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
        } catch (Exception $e) {
            $this->error = "Mailer Error: " . $e->getMessage();
            error_log($this->error);
        }
    }

    public function sendVerificationEmail($to, $name, $verificationLink) {
        try {
            $subject = 'Verify Your Email Address';
            
            $message = '
                <h2>Welcome to ' . htmlspecialchars(SMTP_FROM_NAME) . '!</h2>
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                <p>Thank you for registering. Please click the button below to verify your email address:</p>
                <p style="text-align: center; margin: 25px 0;">
                    <a href="' . htmlspecialchars($verificationLink) . '" 
                       style="background-color: #4CAF50; color: white; padding: 12px 25px; 
                              text-decoration: none; border-radius: 4px; font-weight: bold;">
                        Verify Email Address
                    </a>
                </p>
                <p>If the button doesn\'t work, copy and paste this link into your browser:</p>
                <p><a href="' . htmlspecialchars($verificationLink) . '">' . 
                   htmlspecialchars($verificationLink) . '</a></p>
                <p>If you didn\'t create an account, you can safely ignore this email.</p>
                <p>Best regards,<br>' . htmlspecialchars(SMTP_FROM_NAME) . '</p>';

            return $this->sendEmail($to, $name, $subject, $message);
            
        } catch (Exception $e) {
            $this->error = "Error sending verification email: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function sendPasswordResetEmail($to, $name, $resetLink) {
        try {
            $subject = 'Password Reset Request';
            
            $message = '
                <h2>Password Reset</h2>
                <p>Hello ' . htmlspecialchars($name) . ',</p>
                <p>We received a request to reset your password. Click the button below to set a new password:</p>
                <p style="text-align: center; margin: 25px 0;">
                    <a href="' . htmlspecialchars($resetLink) . '" 
                       style="background-color: #2196F3; color: white; padding: 12px 25px; 
                              text-decoration: none; border-radius: 4px; font-weight: bold;">
                        Reset Password
                    </a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn\'t request this, you can safely ignore this email.</p>
                <p>Best regards,<br>' . htmlspecialchars(SMTP_FROM_NAME) . '</p>';

            return $this->sendEmail($to, $name, $subject, $message);
            
        } catch (Exception $e) {
            $this->error = "Error sending password reset email: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    public function sendWelcomeEmail($to, $name) {
        try {
            $subject = 'Welcome to ' . SMTP_FROM_NAME . '!';
            
            $message = '
                <h2>Welcome, ' . htmlspecialchars($name) . '!</h2>
                <p>Thank you for verifying your email address. Your account is now active and ready to use.</p>
                <p>You can now log in to your account and start using our services.</p>
                <p>If you have any questions, feel free to contact our support team.</p>
                <p>Best regards,<br>' . htmlspecialchars(SMTP_FROM_NAME) . '</p>';

            return $this->sendEmail($to, $name, $subject, $message);
            
        } catch (Exception $e) {
            $this->error = "Error sending welcome email: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    private function sendEmail($to, $name, $subject, $htmlBody) {
        try {
            // Reset all addresses and attachments for a new email
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearReplyTos();
            
            // Recipients
            $this->mail->addAddress($to, $name);
            $this->mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlBody;
            $this->mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n"], $htmlBody));
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            $this->error = "Message could not be sent. Mailer Error: " . $this->mail->ErrorInfo;
            error_log($this->error);
            return false;
        }
    }

    public function getError() {
        return $this->error;
    }
}

// Helper function to get email instance
function getEmailer() {
    static $emailer = null;
    if ($emailer === null) {
        $emailer = new EmailHelper();
    }
    return $emailer;
}
?>