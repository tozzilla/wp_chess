<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="scacchitrack-login-wrapper">
    <div class="scacchitrack-login">
        <h2><?php _e('Accesso Richiesto', 'scacchitrack'); ?></h2>
        
        <?php if (isset($_GET['login_error'])): ?>
            <div class="scacchitrack-message error">
                <?php _e('Password non corretta. Riprova.', 'scacchitrack'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="scacchitrack-login-form">
            <?php wp_nonce_field('scacchitrack_login', 'scacchitrack_login_nonce'); ?>
            
            <div class="form-row">
                <label for="scacchitrack_password">
                    <?php _e('Password', 'scacchitrack'); ?>
                </label>
                <input type="password" 
                       name="scacchitrack_password" 
                       id="scacchitrack_password" 
                       required>
            </div>

            <div class="form-row">
                <button type="submit" 
                        name="scacchitrack_login_submit" 
                        class="button button-primary">
                    <?php _e('Accedi', 'scacchitrack'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.scacchitrack-login-wrapper {
    max-width: 400px;
    margin: 40px auto;
    padding: 20px;
}

.scacchitrack-login {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.scacchitrack-login h2 {
    margin: 0 0 20px 0;
    text-align: center;
    color: #333;
}

.scacchitrack-message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.scacchitrack-message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.scacchitrack-login-form .form-row {
    margin-bottom: 20px;
}

.scacchitrack-login-form .form-row:last-child {
    margin-bottom: 0;
}

.scacchitrack-login-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.scacchitrack-login-form input[type="password"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.scacchitrack-login-form button {
    width: 100%;
    padding: 10px;
    font-size: 16px;
}

@media (max-width: 480px) {
    .scacchitrack-login-wrapper {
        padding: 10px;
    }
    
    .scacchitrack-login {
        padding: 20px;
    }
}
</style>