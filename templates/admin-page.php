<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione delle tab
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
?>

<div class="wrap scacchitrack-admin">
    <h1 class="wp-heading-inline">
        <?php _e('ScacchiTrack', 'scacchitrack'); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=scacchipartita')); ?>" class="page-title-action">
        <?php _e('Aggiungi Nuova Partita', 'scacchitrack'); ?>
    </a>
    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper wp-clearfix">
    <a href="?page=scacchitrack&tab=dashboard" 
       class="nav-tab <?php echo $current_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
        <?php _e('Dashboard', 'scacchitrack'); ?>
    </a>
    <a href="?page=scacchitrack&tab=stats" 
       class="nav-tab <?php echo $current_tab === 'stats' ? 'nav-tab-active' : ''; ?>">
        <?php _e('Statistiche', 'scacchitrack'); ?>
    </a>
    <a href="?page=scacchitrack&tab=tournaments" 
       class="nav-tab <?php echo $current_tab === 'tournaments' ? 'nav-tab-active' : ''; ?>">
        <?php _e('Tornei', 'scacchitrack'); ?>
    </a>
    <a href="?page=scacchitrack&tab=import" 
       class="nav-tab <?php echo $current_tab === 'import' ? 'nav-tab-active' : ''; ?>">
        <?php _e('Importa/Esporta', 'scacchitrack'); ?>
    </a>
    <a href="?page=scacchitrack&tab=settings" 
       class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
        <?php _e('Impostazioni', 'scacchitrack'); ?>
    </a>
</nav>

    <div class="tab-content">
        <?php
       switch ($current_tab) {
        case 'dashboard':
            include SCACCHITRACK_DIR . 'templates/admin/dashboard.php';
            break;
        case 'stats':
            include SCACCHITRACK_DIR . 'templates/admin/stats.php';
            break;
        case 'tournaments':
            include SCACCHITRACK_DIR . 'templates/admin/tournaments.php';
            break;
        case 'import':
            include SCACCHITRACK_DIR . 'templates/admin/import.php';
            break;
        case 'settings':
            include SCACCHITRACK_DIR . 'templates/admin/settings.php';
            break;
    }
        ?>
    </div>
</div>

<style>
.scacchitrack-admin .nav-tab-wrapper {
    margin-bottom: 20px;
}

.scacchitrack-admin .tab-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.scacchitrack-admin .dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.scacchitrack-admin .dashboard-widget {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.scacchitrack-admin .dashboard-widget h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
</style>