<?php

class DbblogDbCategoryModuleFrontController extends ModuleFrontController
{
    public $home;
    public $parents = [];

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        $id_lang = Context::getContext()->language->id;

        parent::initContent();

        // Rewrite
        $rewrite = Tools::getValue('rewrite');
        $category = DbBlogCategory::getCategory($rewrite);

        // Redireccionamos a 404
        if((int)$category['id'] == 0 || $category['active'] == 0){
            Tools::redirect('index.php?controller=404');
        }

        // Cabecera
        $title_blog = Configuration::get('DBBLOG_TITLE', $id_lang);
        $categories = DbBlogCategory::getCategories($id_lang, true, false);
        $subcategories = DbBlogCategory::getCategories($id_lang, true, $category['id']);
        $url_home = Context::getContext()->link->getModuleLink('dbblog', 'dbhome', array());

        // Descripciones
        $short_desc = Configuration::get('DBBLOG_HOME_SHORT_DESC', $id_lang);
        $large_desc = Configuration::get('DBBLOG_HOME_LARGE_DESC', $id_lang);

        // Listado de posts
        $list_cat = DbBlogCategory::getPosts($rewrite, $id_lang);
        $total_posts = DbBlogCategory::totalPosts($rewrite, $id_lang);
        $posts_per_page = Configuration::get('DBBLOG_POSTS_PER_PAGE');
        $pagination = 1;
        if($total_posts <= $posts_per_page){
            $pagination = 0;
            $posts_per_page = $total_posts;
        }
        if($total_posts > 0) {
            $percent_view = round($posts_per_page * 100 / $total_posts, 0);
        } else {
            $percent_view = 100;
        }

        // Posts views
        $post_extract = Configuration::get('DBBLOG_POST_EXTRACT');
        $post_readmore = Configuration::get('DBBLOG_POST_READMORE');

        // Authors
        //$authors = DbBlogPost::getAuthors($category['id']);
        $authors = DbBlogCategory::getAuthors(0, $category['id']);

        // Redes sociales
        $rrss = 0;
        $twitter = Configuration::get('DBBLOG_TWITTER');
        $facebook = Configuration::get('DBBLOG_FACEBOOK');
        $linkedin = Configuration::get('DBBLOG_LINKEDIN');
        $youtube = Configuration::get('DBBLOG_YOUTUBE');
        $instagram = Configuration::get('DBBLOG_INSTAGRAM');
        if(!empty($twitter) || !empty($facebook) || !empty($linkedin) || !empty($youtube)
            || !empty($instagram)){
                $rrss = 1;
        }

        // Mas vistos Sidebar
        $more_views = DbBlogCategory::getPostsViews($id_lang, $rewrite, NULL, NULL, Configuration::get('DBBLOG_SIDEBAR_VIEWS'));

        // Ultimos Sidebar
        $last_posts = DbBlogCategory::getPostsLast($id_lang, $rewrite, NULL, NULL, Configuration::get('DBBLOG_SIDEBAR_LAST'));

        $this->context->smarty->assign(array(
            'title_blog'    => $title_blog,
            'categories'    => $categories,
            'subcategories' => $subcategories,
            'isHome'        => 0,
            'isCategory'    => 1,
            'isAuthors'     => 0,
            'isAuthor'      => 0,
            'isPost'        => 0,
            'url_home'      => $url_home,

            'category'          => $category,
            'list_cat'          => $list_cat,
            'total_posts'       => $total_posts,
            'posts_per_page'    => $posts_per_page,
            'pagination'        => $pagination,
            'percent_view'      => $percent_view,
            'rewrite'           => $rewrite,
            'post_extract'      => $post_extract,
            'post_readmore'     => $post_readmore,

            'authors'       => $authors,
            'rrss'          => $rrss,
            'twitter'       => $twitter,
            'facebook'      => $facebook,
            'linkedin'      => $linkedin,
            'youtube'       => $youtube,
            'instagram'     => $instagram,
            'path_img'      => _MODULE_DIR_.'dbblog/views/img/',
            'more_views'    => $more_views,
            'last_posts'    => $last_posts,
        ));

        $this->setTemplate('module:dbblog/views/templates/front/category.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $id_lang = Context::getContext()->language->id;
        $title_blog = Configuration::get('DBBLOG_TITLE', $id_lang);
        $url_home = Context::getContext()->link->getModuleLink('dbblog', 'dbhome', array());

        $breadcrumb['links'][] = [
            'title' => $title_blog,
            'url' => $url_home,
        ];

        // Categoria
        $rewrite = Tools::getValue('rewrite');
        $category = DbBlogCategory::getCategory($rewrite);

        // Categorias superiores
        $parents = $this->getParentCategories((int)$category['id_parent']);
        if(!empty($parents) && is_array($parents)) {
            foreach ($parents as $parent) {
                $breadcrumb['links'][] = $parent;
            }
        }

        $breadcrumb['links'][] = [
            'title' => $category['title'],
            'url'   => $category['url'],
        ];

        return $breadcrumb;
    }

    public function getParentCategories($id_parent)
    {
        if($id_parent > 0){
            $category = DbBlogCategory::getCategoryById($id_parent);
            $parents[] = [
                'title' => $category['title'],
                'url'   => $category['url'],
            ];
            if((int)$category['id_parent'] > 0){
                $this->getParentCategories((int)$category['id_parent']);
            } else {
                return $parents;
            }
        } else {
            return;
        }
    }
}