<?php

if (!defined( 'ABSPATH' )) { die(); }

class SitemapBuilder
{
    private $home = null;
    private $xml = true;
    private $posts = '';
    private $pages = '';
    private $url;
    private $tags;
    private $homeUrl;
    private $authors;
    private $categories;
    private $blockedUrls;
    private $excludedCategories;
    private $excludedPosts;


    // Constructor
    public function __construct () {
        $this->url = esc_url(plugins_url() . '/sitemap');
        $this->homeUrl = esc_url(get_home_url() . (substr(get_home_url(), -1) === '/' ? '' : '/'));
        $this->categories = get_option('sitemap_disp_categories') ? array(0 => 0) : false;
        $this->tags = get_option('sitemap_disp_tags') ? array(0 => 0) : false;
        $this->authors = get_option('sitemap_disp_authors') ? array(0 => 0) : false;
        $this->excludedCategories = get_option('sitemap_excluded_categories');
        $this->excludedPosts = get_option('sitemap_excluded_posts');
    }

    // Generates the sitemaps and returns the content
    public function getContent ($type) {
        if ($type === 'xml') {
            $this->$type = true;
            $this->setUpBlockedUrls();
            $this->generateContent();
            $this->mergeAndPrint();
        }
    }

    // Returns other urls user has submitted
    private function getCustomPages () {
        $xml = '';

        if ($options = get_option('sitemap_custom_pages')) {
            foreach ($options as $option) {
                if ($option && is_array($option)) {
                    $xml .= $this->getXml(esc_url($option['url']), esc_html($option['date']));
                }
            }
        }
        return $xml;
    }

    // Sets up blocked urls into an array
    private function setUpBlockedUrls () {
        $blocked = get_option('sitemap_block_urls');

        if ($blocked && is_array($blocked)) {
            $this->blockedUrls = array();
            foreach ($blocked as $block) {
                $this->blockedUrls[$block['url']] = 'blocked';
            }
        }
        else {
            $this->blockedUrls = null;
        }
    }

    // Matches url against blocked ones that shouldn't be displayed
    private function isBlockedUrl($url) {
        return $this->blockedUrls && isset($this->blockedUrls[$url]);
    }


    // Returns an xml string
    private function getXml ($link, $date) {
        if ($this->xml) {
            return "<url>\n\t<loc>$link</loc>\n\t<lastmod>$date</lastmod>\n</url>\n";
        }
    }

    // Querys the database and gets the actual sitemaps content
    private function generateContent () {
        $q = new WP_Query(array('post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => 50000, 'has_password' => false));

        global $post;
        $localPost = $post;
        if(empty($this->excludedPosts)){
            $this->excludedPosts = array(0 => 0);
        }
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();

                $link = esc_url(get_permalink());
                $date = esc_html(get_the_modified_date('Y-m-d\TH:i:sP'));

                $this->getCategoriesTagsAndAuthor($date);

                if (!$this->isBlockedUrl($link)) {
                    if (!$this->home && $link === $this->homeUrl) {
                        $this->home = $this->getXml($link, $date);
                    } elseif ('page' === get_post_type()) {
                        $this->pages .= $this->getXml($link, $date);
                    } elseif(!in_array(get_the_ID(),$this->excludedPosts)) { // posts (also all custom post types are added here)
                        $this->posts .= $this->getXml($link, $date);
                    }
                }
            }
        }
        wp_reset_postdata();
        $post = $localPost; // reset global post to its value before the loop
    }

    // Gets a posts categories, tags and author, and compares for last modified date
    private function getCategoriesTagsAndAuthor ($date) {
        if ($this->categories && ($postCats = get_the_category())) {
            if(empty($this->excludedCategories)){
                $this->excludedCategories = array(0 => 0);
            }
            foreach ($postCats as $category) {
                $id = $category->term_id;
                if ((!isset($this->categories[$id]) || $this->categories[$id] < $date) && !in_array($category->name,$this->excludedCategories)) {
                    $this->categories[$id] = $date;
                }
            }
        }
        if ($this->tags && ($postTags = get_the_tags())) {
            foreach ($postTags as $tag) {
                $id = $tag->term_id;
                if (!isset($this->tags[$id]) || $this->tags[$id] < $date) {
                    $this->tags[$id] = $date;
                }
            }
        }
        if ($this->authors && ($id = get_the_author_meta('ID'))) {
            if (is_int($id) && (!isset($this->authors[$id]) || $this->authors[$id] < $date)) {
                $this->authors[$id] = $date;
            }
        }
    }

    // Merges the arrays with post data into strings and gets user submitted pages, categories, tags and author pages
    private function mergeAndPrint () {
        $xml = '';
        $name = get_bloginfo('name');
        $sections = $this->getSortedArray();
        $xml_content = '';

        foreach ($sections as $title => $content) {
            if ($content) {
                if (preg_match("/^(Categories|Tags|Authors)$/", $title)) {
                    $content = $this->stringifyCatsTagsAuths($title, $content);
//                    if ($title === 'Authors' && count($this->authors) <= 2) { // only one author
//                        $title = 'Author';
//                    }
                }

                if ($content) {
                    $xml .= $content;
                }
            }
        }
        if ($this->xml) {
            $xml_content = <<<XML
<?xml version="1.0" encoding="UTF-8"?>  
<?xml-stylesheet type="text/xsl" href="{$this->url}/css/sitemap.xsl"?>
<urlset>{$xml}</urlset>
XML;
        }

        echo $xml_content;
    }

    // Gets sorted array according to specified order
    private function getSortedArray () {
        if (!($arr = get_option('sitemap_disp_order'))) {
            $arr = array('Home' => null, 'Pages' => null, 'Custom' => null, 'Posts' => null, 'Categories' => null, 'Tags' => null, 'Authors' => null);
        }

        if (!$this->home) { // if homepage isn't found in the query (for instance if it's not a real "page" it wont be found)
            @date_default_timezone_set(get_option('timezone_string'));
            $this->home = $this->getXml($this->homeUrl, date('Y-m-d\TH:i:sP'));
        }

        // copy to array and also clear some memory (some sites have a huge amount of posts)
        $arr['Home'] = $this->home; $this->home = null;
        $arr['Pages'] = $this->pages; $this->pages = null;
        $arr['Custom'] = $this->getCustomPages();
        $arr['Posts'] = $this->posts; $this->posts = null;
        $arr['Categories'] = $this->categories;
        $arr['Tags'] = $this->tags; $this->tags = null;
        $arr['Authors'] = $this->authors; $this->authors = null;

        return $arr;
    }


    // Returns category, tag and author links as ready xml and html strings
    private function stringifyCatsTagsAuths ($type, $content) {
        $xml = '';

        foreach ($content as $id => $date) {
            if ($date) {
                $link = esc_url($this->getLink($id, $type));
                if (!$this->isBlockedUrl($link)) {
                    $xml .= $this->getXml($link, $date);
                }
            }
        }
        return $xml;
    }

    // Returns either a category, tag or an author link
    private function getLink ($id, $type) {
        switch ($type) {
            case 'Tags': return get_tag_link($id);
            case 'Categories': return get_category_link($id);
            default: return get_author_posts_url($id); // Authors
        }
    }

    // Deletes the sitemap files from old versions of the plugin
    public static function deleteFiles () {
        if (function_exists('get_home_path')) {
            $path = sprintf('%s%ssitemap.', get_home_path(), (substr(get_home_path(), -1) === '/' ? '' : '/'));
            try {
                foreach (array('xml') as $file) {
                    if (file_exists($path . $file)) {
                        unlink($path . $file);
                    }
                }
            }
            catch (Exception $ex) {
                return;
            }
        }
    }

}
