<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

function fn_optipic_dispatch_before_send_response() {
    if(fn_optipic_is_client_area()) {
        ob_start();
    }
}

function fn_optipic_is_client_area() {
    return (defined('AREA') && AREA=='C' && PHP_SAPI != "cli" && PHP_SAPI != "cli-server");
}

function fn_optipic_complete() {
    if(fn_optipic_is_client_area()) {
        
        $content = ob_get_contents();
        ob_end_clean();
        
        //var_dump(AREA);
        $settings = optipic_get_settings();
        
        if($settings['autoreplace_active'] && $settings['site_id']) {
            $imgAttrs = $settings['img_attrs'];
            //var_dump($imgAttrs);exit;
            
            //var_dump($attrs);exit;
            if(count($imgAttrs)) {
                
                foreach($imgAttrs as $attr) {
                    
                    //$imgAttrs = implode("|", $imgAttrs);
                    
                    $patterns = array(
                        array(
                            'pattern' => '/<img([^>]+?)('.$attr.'?)="([^"]+\.(png|jpg|jpeg)?)"([^>]*?)>/simS',
                            'quote' => '"',
                        ),
                        array(
                            'pattern' => "/<img([^>]+?)(".$attr."?)='([^']+\.(png|jpg|jpeg)?)'([^>]*?)>/simS",
                            'quote' => "'",
                        ),
                    );
                    
                    foreach($patterns as $pattern) {
                        //var_dump($pattern);
                        $qoute = $pattern['quote'];
                        $content = preg_replace_callback(
                            $pattern['pattern'], 
                            function($matches) use ($dir, $settings, $qoute) {
                                //var_dump($matches);exit;
                                $quoteSymbol = '"';
                                if($qoute) {
                                    $quoteSymbol = $qoute;
                                }
                                
                                $url = $matches[3];
                                //var_dump($matches);
                                
                                $optipicUrl = optipic_build_img_url($url, array('site_id'=>$settings['site_id']));
                                //var_dump($optipicUrl);exit;
                                
                                if($optipicUrl==$url) {
                                    return $matches[0];
                                }
                                else {
                                    return '<img'.$matches[1].$matches[2].'='.$quoteSymbol.$optipicUrl.$quoteSymbol.$matches[5].'>';
                                }
                            }, 
                            $content
                        );
                    }
                }
                //exit;
            }
        }
        
        echo $content;
    }
}

function optipic_get_settings() {
    
    $optipicSiteID = Registry::get('addons.optipic.cdn_site_id');
    $autoreplaceActive = Registry::get('addons.optipic.cdn_autoreplace_active');
    $imgAttrs = Registry::get('addons.optipic.cdn_autoreplace_img_attrs');
    
    $attrs = [];
    foreach(explode(",", $imgAttrs) as $attr) {
        $attr = trim($attr);
        if($attr) {
            $attrs[] = preg_quote($attr, '/');
        }
    }
    
    return array(
        'site_id' => $optipicSiteID,
        'autoreplace_active' => ($autoreplaceActive=='Y'),
        'img_attrs' => $attrs,
    );
}

function optipic_build_img_url($localUrl, $params=array()) {
    $dir = optipic_get_current_url_dir();
    
    $schema = "//";
    
    if(!isset($params['site_id'])) {
        $settings = optipic_get_settings();
        $params['site_id'] = $settings['site_id'];
    }
    
    if(isset($params['url_schema'])) {
        if($params['url_schema']=='http') {
            $schema = "http://";
        }
        elseif($params['url_schema']=='https') {
            $schema = "https://";
        }
    }
    
        
    if($params['site_id']) {
        if(!fn_strlen(trim($localUrl)) || stripos($localUrl, 'cdn.optipic.io')!==false) {
            return $localUrl;
        }
        /*elseif(stripos($localUrl, 'http://')===0) {
            return $localUrl;
        }
        elseif(stripos($localUrl, 'https://')===0) {
            return $localUrl;
        }
        elseif(stripos($localUrl, '//')===0) {
            return $localUrl;
        }*/
        else {
            
            // убираем адрес сайта из начала URL (для http)
            if(stripos($localUrl, Registry::get('config.http_location'))===0) {
                //$protocol = defined('HTTPS') ? 'https' : 'http';
                $localUrl = fn_substr($localUrl, fn_strlen(Registry::get('config.http_location')));
            }
            // убираем адрес сайта из начала URL (для https)
            elseif(stripos($localUrl, Registry::get('config.https_location'))===0) {
                //$protocol = defined('HTTPS') ? 'https' : 'http';
                $localUrl = fn_substr($localUrl, fn_strlen(Registry::get('config.http_location')));
            }
            
            // если URL не абсолютный - приводим его к абсолютному
            if(substr($localUrl, 0, 1)!='/') {
                $localUrl = $dir.$localUrl;
            }
            
            $url = $schema.'cdn.optipic.io/site-'.$params['site_id'];
            if(isset($params['q'])) {
                $url .= '/optipic-q='.$params['q'];
            }
            if(isset($params['maxw'])) {
                $url .= '/optipic-maxw='.$params['maxw'];
            }
            if(isset($params['maxh'])) {
                $url .= '/optipic-maxh='.$params['maxh'];
            }
            
            $url .= $localUrl;
            
            return $url;
            
            //return '<img'.$matches[1].'src='.$quoteSymbol.'//cdn.optipic.io/site-'.$settings['site_id'].$url.$quoteSymbol.$matches[3].'>';
        }
    }
    // Если URL 
    else {
        return $localUrl;
    }
    
    
}

function optipic_get_current_url_dir() {
    return fn_substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/')+1);
}