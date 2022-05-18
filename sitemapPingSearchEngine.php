<?php

class SitemapPingSearchEngine{
    private $googleURL;
    private $bingURL;

    public function __construct () {
        $this->googleURL = "http://www.google.com/webmasters/sitemaps/ping?sitemap=%s";
        $this->bingURL = "http://www.bing.com/webmaster/ping.aspx?siteMap=%s";
    }

    public function pingGoogle(){
        $url = str_replace( '%s', rawurlencode( get_home_url() . (substr(get_home_url(), -1) === '/' ? '' : '/') . 'sitemap.xml' ), $this->googleURL);
        wp_remote_get($url);
    }

    public function pingBing(){
        $url = str_replace( '%s', rawurlencode( get_home_url() . (substr(get_home_url(), -1) === '/' ? '' : '/') . 'sitemap.xml' ), $this->bingURL);
        wp_remote_get($url);
    }
}
