<?php
if (!defined( 'ABSPATH' )) { die(); }


class SitemapOptions
{
    private $homeUrl;

    // Constructor: sets homeUrl with trailing slash
    public function __construct()
    {
        $this->homeUrl = esc_url(get_home_url() . (substr(get_home_url(), -1) === '/' ? '' : '/'));
    }

    // Returns a sitemap url
    public function sitemapUrl($format)
    {
        return sprintf('%ssitemap.%s', $this->homeUrl, $format);
    }

    // Updates the settings/options
    public function setOptions ($categories, $tags, $authors,$pingSearchEngine,$addToRobotsTxt,$excludedCategories,$excludedPosts,$customPages) {
        @date_default_timezone_set(get_option('timezone_string'));
//        update_option('sitemap_other_urls', $this->addUrls($otherUrls, get_option('sitemap_other_urls')));
//        update_option('sitemap_block_urls', $this->addUrls($blockUrls));
        update_option('sitemap_disp_categories', $categories);
        update_option('sitemap_disp_tags', $tags);
        update_option('sitemap_disp_authors', $authors);
        update_option('sitemap_ping_search_engine', $pingSearchEngine);
        update_option('sitemap_add_to_robotstxt', $addToRobotsTxt);
        update_option('sitemap_excluded_categories', $excludedCategories);
        update_option('sitemap_excluded_posts', $excludedPosts);
        update_option('sitemap_custom_pages', $customPages);

    }

// Returns the options as strings to be displayed in textareas, checkbox values and orderarray (to do: refactor this messy function)
    public function getOptions ($val) {
        if (preg_match("/^sitemap_(custom_pages)$/", $val)) {
            return get_option($val);
        } elseif (preg_match("/^sitemap_(excluded_categories|excluded_posts)$/", $val)){
            return get_option($val);
        }
        elseif (preg_match("/^sitemap_(disp_categories|disp_tags|disp_authors|ping_search_engine|add_to_robotstxt)$/", $val)) {
            return get_option($val) ? 'checked' : ''; // return checkbox checked values right here and dont bother with the loop below
        }
        else {
            $val = null;
        }

        $str = '';
        if (!$this->isNullOrWhiteSpace($val)) {
            foreach ($val as $sArr) {
                $str .= $this->sanitizeUrl($sArr['url']) . "\n";
            }
        }
        return trim($str);
    }

    // Checks if string/array is empty
    private function isNullOrWhiteSpace ($word) {
        if (is_array($word)) {
            return false;
        }
        return ($word === null || $word === false || trim($word) === '');
    }

    // Sanitizes urls with esc_url and trim
    private function sanitizeUrl ($url) {
        return esc_url(trim($url));
    }

}
