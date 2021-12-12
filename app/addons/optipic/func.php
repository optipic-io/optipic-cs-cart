<?php
use Tygh\Registry;
//use Optipic;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Создаём функцию, которая подключится к хуку.
function fn_optipic_dispatch_before_send_response($status, $area, $controller, $mode, $action) {
    ob_start();
}

function fn_optipic_complete() {

    $content = ob_get_clean();

    if (ACCOUNT_TYPE != 'admin'){
        $optipic = new Optipic();
        $content = $optipic->changeContent($content);
    }

    echo $content;
}

function fn_optipic_settings_field_info() {
    
    require_once dirname(__FILE__) . '/ImgUrlConverter.php';
    require_once dirname(__FILE__) . '/Optipic.php';
    
    $optipic = new Optipic();
    $currentHost = \optipic\cdn\ImgUrlConverter::getCurrentDomain();
            
    $settings = $optipic->getSettings();
    
    $srcJs = 'https://optipic.io/api/cp/stat?domain='.$currentHost.'&sid='.$settings['site_id'].'&cms=cscart&stype=cdn&append_to=%23content_optipic_generic_settings&version=1.25.0';
    
    $html = '';
    
    $html .= '<div class="muted" style="margin: 20px 0;">'.__('optipic.cdn_domain_description').'</div>';
    
    //$html .= var_export(\optipic\cdn\ImgUrlConverter::getDefaultSettings(), true);
    $defaultSettings = \optipic\cdn\ImgUrlConverter::getDefaultSettings();
    $defaultDomains = implode("\\n", $defaultSettings['domains']);
    $defaultSrcsetAttrs = implode("\\n", $defaultSettings['srcset_attrs']);
    
    if($currentHost) {
        $html .= '<script src="'.$srcJs.'"></script>';
    }
    
    $html .= <<<JS

<script>
window.optipicAfterInitExternalCallback = function($) {
    var optipicDomainsTextarea = $(".control-group.optipic textarea[id^='addon_option_optipic_domains']");
    if(optipicDomainsTextarea.length==1 && optipicDomainsTextarea.val().length==0) {
        optipicDomainsTextarea.val("$defaultDomains");
        console.log("auto set Domains");
    }
    
    var optipicSrcsetAttrsTextarea = $(".control-group.optipic textarea[id^='addon_option_optipic_srcset_attrs']");
    if(optipicSrcsetAttrsTextarea.length==1 && optipicSrcsetAttrsTextarea.val().length==0) {
        optipicSrcsetAttrsTextarea.val("$defaultSrcsetAttrs");
        console.log("auto set Srcset");
    }
}
</script>

JS
;
    
    return $html;
    //return '<script>alert("Z");</script>';
}