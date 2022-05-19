<?php

class SitemapSettingsValidation{

    public function validateCustomPages($input){
        $arr = array();
        $errors = 0;

        if(count($input) > 0){
            foreach ($input['urls'] as $i => $url) {
                $url = $this->sanitizeUrl($url);
                $date = $input['dates'][$i];
                if (!empty($url) && empty($date)){
                    $errors++;
                    add_settings_error(
                        'sitemap_custom_pages',
                        'custom-page',
                        __('Input the date for: '.$url),
                        'error'
                    );
                } elseif (empty($url) && !empty($date)){
                    $errors++;
                    add_settings_error(
                        'sitemap_custom_pages',
                        'custom-page',
                        __('Input the URL for: '.$date),
                        'error'
                    );
                } else
                if (!empty($url) && !empty($date)) {
                    $arr[] = array('url' => $url, 'date' => $date);
                }
            }
        }

        if($errors > 0){
            settings_errors( 'sitemap_custom_pages' );
        }

        return $arr;
    }

    private function sanitizeUrl ($url) {
        return esc_url(trim($url));
    }
}
