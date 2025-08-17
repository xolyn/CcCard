<?php
/**
 * 在你的文章末尾自动插入 Creative Commons 版权声明卡片
 * 
 * @package CcCard
 * @author uygnil
 * @version 1.0.2
 * @link https://zhoulingyu.net
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class CcCard_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('CcCard_Plugin', 'injectAtArticleEnd');
        Typecho_Plugin::factory('Widget_Archive')->header = array('CcCard_Plugin', 'addStylesheet');
    }

    public static function deactivate(){}

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $licenses = array(
            'CC BY 4.0'       => 'https://creativecommons.org/licenses/by/4.0/',
            'CC BY-SA 4.0'    => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'CC BY-NC 4.0'    => 'https://creativecommons.org/licenses/by-nc/4.0/',
            'CC BY-ND 4.0'    => 'https://creativecommons.org/licenses/by-nd/4.0/',
            'CC BY-NC-SA 4.0' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
            'CC BY-NC-ND 4.0' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/'
        );
        $license = new Typecho_Widget_Helper_Form_Element_Select(
            'license', $licenses, 'CC BY 4.0', _t('选择 Creative Commons 协议')
        );
        
        $license->description(_t('你选择的是：<a id="cc-license-preview" href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a>'));

        $showTitle  = new Typecho_Widget_Helper_Form_Element_Checkbox('showTitle',  array('1' => '显示文章名'),   array('1'), _t('显示项'));
        $showAuthor = new Typecho_Widget_Helper_Form_Element_Checkbox('showAuthor', array('1' => '显示默认作者名'), array('1'), null);
        $customAuthor = new Typecho_Widget_Helper_Form_Element_Text('customAuthor', null, '', _t('自定义作者名（留空则使用默认作者）'));
        $showLink   = new Typecho_Widget_Helper_Form_Element_Checkbox('showLink',   array('1' => '显示文章链接'), array('1'), null);

        $strictArticle = new Typecho_Widget_Helper_Form_Element_Radio(
            'strictArticle', array('1' => '是', '0' => '否'), '1', _t('严格插入到 &lt;/article&gt; 前')

        );

        $form->addInput($license);
        $form->addInput($showTitle);
        $form->addInput($showAuthor);
        $form->addInput($customAuthor);
        $form->addInput($showLink);
        $form->addInput($strictArticle);
        
        echo '<script type="text/javascript">
        (function() {
            var licenseData = {
                "CC BY 4.0": "https://creativecommons.org/licenses/by/4.0/",
                "CC BY-SA 4.0": "https://creativecommons.org/licenses/by-sa/4.0/",
                "CC BY-NC 4.0": "https://creativecommons.org/licenses/by-nc/4.0/",
                "CC BY-ND 4.0": "https://creativecommons.org/licenses/by-nd/4.0/",
                "CC BY-NC-SA 4.0": "https://creativecommons.org/licenses/by-nc-sa/4.0/",
                "CC BY-NC-ND 4.0": "https://creativecommons.org/licenses/by-nc-nd/4.0/"
            };
            
            function updateLicensePreview() {
                var select = document.querySelector("select[name=\"license\"]");
                var preview = document.getElementById("cc-license-preview");
                if (select && preview) {
                    var selectedLicense = select.value;
                    preview.textContent = selectedLicense;
                    preview.href = licenseData[selectedLicense] || "#";
                }
            }
            
            document.addEventListener("DOMContentLoaded", function() {
                var select = document.querySelector("select[name=\"license\"]");
                if (select) {
                    updateLicensePreview();
                    select.addEventListener("change", updateLicensePreview);
                }
            });
        })();
        </script>';
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 在头部添加样式表
     */
    public static function addStylesheet()
    {
        $cssUrl = Helper::options()->pluginUrl . '/CcCard/assets/styles.css';
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />' . "\n";
    }

    /**
     * 内容增强：在文章末尾插入 CC 卡片
     * @param string $content 原始内容
     * @param Widget_Archive $archive 当前归档对象
     * @return string
     */
    public static function injectAtArticleEnd($content, $archive)
    {
        if (!$archive->is('single')) return $content;

        $opts = Helper::options()->plugin('CcCard');
        
        $licenses = array(
            'CC BY 4.0'       => 'https://creativecommons.org/licenses/by/4.0/',
            'CC BY-SA 4.0'    => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'CC BY-NC 4.0'    => 'https://creativecommons.org/licenses/by-nc/4.0/',
            'CC BY-ND 4.0'    => 'https://creativecommons.org/licenses/by-nd/4.0/',
            'CC BY-NC-SA 4.0' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
            'CC BY-NC-ND 4.0' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/'
        );
        
        $license = isset($opts->license) ? $opts->license : 'CC BY 4.0';
        $licenseUrl = isset($licenses[$license]) ? $licenses[$license] : '#';
        
        $authorName = $archive->author->screenName;
        if (!empty($opts->customAuthor)) {
            $authorName = $opts->customAuthor;
        }

        $cardItems = array();
        if (!empty($opts->showTitle))  $cardItems[] = '<li>本文标题：'.htmlspecialchars($archive->title).'</li>';
        if (!empty($opts->showAuthor)) $cardItems[] = '<li>本文作者：'.htmlspecialchars($authorName).'</li>';
        if (!empty($opts->showLink))   $cardItems[] = '<li>本文链接：<a href="'.htmlspecialchars($archive->permalink).'">'.htmlspecialchars($archive->permalink).'</a></li>';
        
        $cardItems[] = '<li>版权声明：本文采用 <a href="'.htmlspecialchars($licenseUrl).'">'.htmlspecialchars($license).'</a> 协议进行许可</li>';

        $card = '<div class="cc-card" cc-card-info="CcCard Plugin v1.0.1, powered by uygnil: zhoulingyu.net"><ul>'.implode('', $cardItems).'</ul></div>';

        $strict = isset($opts->strictArticle) ? ($opts->strictArticle === '1') : true;

        if ($strict && false !== stripos($content, '</article>')) {
            $content = preg_replace('/<\/article>\s*$/i', $card . '</article>', $content, 1);
        } else {
            $content .= $card;
        }
        return $content;
    }
}
