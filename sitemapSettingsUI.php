<?php

class SitemapSettingsUI
{

    public function htmlSettings(){
        require_once('sitemapOptions.php');
        $options = new SitemapOptions();

        $pingSearchEngine = $options->getOptions('sitemap_ping_search_engine');
        $addToRobotsTxt = $options->getOptions('sitemap_add_to_robotstxt');
        $dispCategories = $options->getOptions('sitemap_disp_categories');
        $dispTags = $options->getOptions('sitemap_disp_tags');
        $dispAuthors = $options->getOptions('sitemap_disp_authors');
        $excludedCategories = $options->getOptions('sitemap_excluded_categories');
        $excludedPosts = $options->getOptions('sitemap_excluded_posts');
        $customPages = $options->getOptions('sitemap_custom_pages');


        $option_group = settings_fields('sitemap-group');
//        var_dump($option_group);die;
        $custom_pages_row = '';
        if(count($customPages) > 0){
            foreach ($customPages as $page){
                $custom_pages_row .= <<<HTML
                    <tr>
                        <td><input name="sitemap_custom_page_url[]" type="text" value="{$page['url']}" style="width: 100%;"></td>
                        <td><input name="sitemap_custom_page_date[]" type="datetime-local" value="{$page['date']}" style="width: 100%;"></td>
                        <td><a href="javascript:void(0)" onclick="jQuery(this).parent().parent().remove()" style="color: red">X</a></td>
                    </tr>
                 HTML;
            }
        } else{
            $custom_pages_row = <<<HTML
                    <tr>
                        <td><input name="sitemap_custom_page_url[]" type="text" style="width: 100%;"></td>
                        <td><input name="sitemap_custom_page_date[]" type="datetime-local" style="width: 100%;"></td>
                        <td><a href="javascript:void(0)" onclick="jQuery(this).parent().parent().remove()" style="color: red">X</a></td>
                    </tr>
                 HTML;
        }

        $categories = get_categories();

        $cat_options = <<<HTML
                <select name="sitemap_excluded_categories[]" multiple="multiple" id="excluded_categories">
            HTML;
        if(count($categories) > 0){
            foreach ($categories as $cat){
                if(!empty($excludedCategories) > 0){
                    $selected = in_array($cat->cat_ID,$excludedCategories) ? 'selected' : '';
                } else{
                    $selected = '';
                }
                $cat_options .= <<<HTML
                    <option {$selected} value="{$cat->cat_ID}">{$cat->name}</option>
                HTML;
            }
        }

        $cat_options .= <<<HTML
                </select>
            HTML;

        $posts = get_posts(array('numberposts' => 5000));

        $post_options = <<<HTML
                <select name="sitemap_excluded_posts[]" multiple="multiple" id="excluded_posts">
            HTML;
        if(count($posts) > 0){
            foreach ($posts as $post){
                if(!empty($excludedPosts) > 0){
                    $selected = in_array($post->ID,$excludedPosts) ? 'selected' : '';
                } else{
                    $selected = '';
                }
                $post_options .= <<<HTML
                    <option {$selected} value="{$post->ID}">{$post->post_name}</option>
                HTML;
            }
        }

        $post_options .= <<<HTML
                </select>
            HTML;

        $content = <<<HTML

<h1>Sitemap Settings</h1>

<form method="post" action="options-general.php?page=sitemapSettings">
{$option_group}

<div class="postbox">
    <div class="postbox-header"><h2 class="hndle ui-sortable-handle">General settings</h2></div>
    <div class="inside">
        <table class="form-table">
            <tbody >
                <tr>
                    <th scope="row">Ping search engines(Google and Bing)</th>
                    <td><input name="sitemap_ping_search_engine" type="checkbox" {$pingSearchEngine} value="1"></td>
                </tr>
                <tr>
                    <th scope="row">Add sitemap URL to the robots.txt file</th>
                    <td><input name="sitemap_add_to_robotstxt" type="checkbox" {$addToRobotsTxt} value="1"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_role">Exclude categories(comma separated)</label></th>
                    <td>{$cat_options}</td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_role">Exclude posts(comma separated)</label></th>
                    <td>{$post_options}</td>
                </tr>
            </tbody>
        </table>
    </div> 
</div>

<div class="postbox">
    <div class="postbox-header"><h2 class="hndle ui-sortable-handle">Extra pages</h2></div>
    <div class="inside">
        <table class="form-table">
            <thead>
                <tr>
                    <th scope="row" style="text-align: center;">URL to the page</th>
                    <th scope="row" style="text-align: center;">Last change</th>
                    <th scope="row">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="custom_page_body">
                {$custom_pages_row}
            </tbody>
            <tfoot>
                <tr>
                    <td scope="row" colspan="3"><input type="button" class="button" id="custom_page_add_row" value="Add row"></td>
                </tr>
            </tfoot>
            
        </table>
    </div> 
</div>

<div class="postbox">
    <div class="postbox-header"><h2 class="hndle ui-sortable-handle">Show pages in sitemap</h2></div>
    <div class="inside">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Categories</th>
                    <td><input name="sitemap_disp_categories" type="checkbox" {$dispCategories} value="1"></td>
                </tr>
                <tr>
                    <th scope="row">Tags</th>
                    <td><input name="sitemap_disp_tags" type="checkbox" {$dispTags} value="1"></td>
                </tr>
                <tr>
                    <th scope="row">Authors</th>
                    <td><input name="sitemap_disp_authors" type="checkbox" {$dispAuthors} value="1"></td>
                </tr>

            </tbody>
        </table>
    </div> 
</div>
<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
</form>

HTML;
return $content;
    }
}
