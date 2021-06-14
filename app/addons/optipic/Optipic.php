<?php
use Tygh\Registry;
use \optipic\cdn\ImgUrlConverter;

class Optipic
{
    public function getSettings()
    {
        return array(
            'autoreplace_active' => Registry::get('addons.optipic.autoreplace_active'),
            'site_id' => Registry::get('addons.optipic.site_id'),
            'domains' => Registry::get('addons.optipic.domains')!='' ? explode("\n", Registry::get('addons.optipic.domains')) : array(),
            'exclusions_url' => Registry::get('addons.optipic.exclusions_url')!='' ? explode("\n", Registry::get('addons.optipic.exclusions_url')) : array(),
            'whitelist_img_urls' => Registry::get('addons.optipic.whitelist_img_urls')!='' ? explode("\n", Registry::get('addons.optipic.whitelist_img_urls')) : array(),
            'srcset_attrs' => Registry::get('addons.optipic.srcset_attrs')!='' ? explode("\n", Registry::get('addons.optipic.srcset_attrs')) : array(),
            'cdn_domain' => Registry::get('addons.optipic.cdn_domain'),
        );
    }

    public function changeContent($content)
    {
        $settings = $this->getSettings();

        if ($settings['autoreplace_active']=='Y' && $settings['site_id']!=''){
            ImgUrlConverter::loadConfig($settings);
            $content = ImgUrlConverter::convertHtml($content);
        }

        return $content;
    }
}