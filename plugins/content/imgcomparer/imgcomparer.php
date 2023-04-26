<?php
/* 
* @license    GNU General Public License version 2 or later; see LICENSE.txt
* @copyright 2022 Kevin's Guides, Kevin Olson
* For help visit KevinsGuides.com
* If you like, please leave a tip. I'm poor. \_(ツ)_/¯
*/
//kill direct access
defined('_JEXEC') || die;

//required to make the plugin work
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgContentImgComparer extends CMSPlugin
{


    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {

        //getters
        $doc = Factory::getApplication()->getDocument();
        //used to add scripts/styles
        $wa = $doc->getWebAssetManager();
        //in case you need it, the path to this plugin
        $pluginPath = 'plugins/content/' . $this->_name;
        //the current view, if you need it
        $view = JFactory::getApplication()->input->get('view');


        if($this->params->get('slideonhover', 0) == 1){
            $optionHoverStart = 'hoverStart: true,';
        }
        else{
            $optionHoverStart = 'hoverStart: false,';
        }

        if($this->params->get('usecircleslider', 0) == 1){
            $optionCircleSlider = 'addCircle: true,';
        }
        else{
            $optionCircleSlider = '';
        }

        $vieweridx = 0;

        $slidercolor = $this->params->get('slidercolor', '#ffffff');
        $settingControlColor = 'controlColor: "'.$slidercolor.'",';
        
       

        //find each image comparer block based on the RegEx within $article->text and store results in $matches array
        
        //find every instance starting from {imgcomparer and ending with /imgcomparer}

        preg_match_all('/{imgcomparer.*?\/imgcomparer}/s', $article->text, $matches);

        //for each match in matches[0] - it's a 2d array but the first dimension isn't doing much
        foreach ($matches[0] as $value) {

            $output = '';

            //find each img tag
            preg_match_all('/<img.*?\/>/s', $value, $imgMatches);

            //skip this loop iteration if there are not exactly 2 img tags
            if (count($imgMatches[0]) != 2) {
                $output = '<p style="color:red;">ERROR: Image Compare Viewer requires exactly 2 images. Please check your code/editor.</p>';
                $article->text = str_replace($value, $output, $article->text);

                continue;
            }

            //look for {imgcomparer vertical}
            if (strpos($value, 'imgcomparer vertical') !== false) {
                $vertical = 'verticalMode: true,';
            } else {
                $vertical = '';
            }
         



            //get the src attribute of the first img tag
            preg_match('/src="(.*?)"/', $imgMatches[0][0], $img1);

            //get the src attribute of the second img tag
            preg_match('/src="(.*?)"/', $imgMatches[0][1], $img2);

            //get the alt attribute of the first img tag
            preg_match('/alt="(.*?)"/', $imgMatches[0][0], $alt1);

            //get the alt attribute of the second img tag
            preg_match('/alt="(.*?)"/', $imgMatches[0][1], $alt2);

            //get width of first image
            preg_match('/width="(.*?)"/', $imgMatches[0][0], $width1);
            //and second
            preg_match('/width="(.*?)"/', $imgMatches[0][1], $width2);

            //get height of first image
            preg_match('/height="(.*?)"/', $imgMatches[0][0], $height1);
            //and second
            preg_match('/height="(.*?)"/', $imgMatches[0][1], $height2);

            // get title of first and second image
            preg_match('/title="(.*?)"/', $imgMatches[0][0], $title1);
            preg_match('/title="(.*?)"/', $imgMatches[0][1], $title2);

            //check for the text "vertical" in the first imgcomparer shortcode
            //eg {imgcomparer vertical}


            if($this->params->get('alwayslabel', 0) == 1){
                $showLabels = true;
            }
            else{
                $showLabels = false;
            }
           

            //see if the img1 and img2 src attributes are there
            if (isset($img1[1]) && isset($img2[1])) {

                $vieweridx++;


                if (isset($title1[1]))
                {
                    $showLabels = true;
                    $title1 = 'before: "'.$title1[1].'",';
                }
                else{
                    $title1 = 'before: "Before",';
                }
    
                if (isset($title2[1]))
                {
                    $showLabels = true;
                    $title2 = 'after: "'.$title2[1].'",';
                }
                else{
                    $title2 = 'after: "After",';
                }

                if ($showLabels == true){
                    $optionLabels = '
                    showLabels: true,
                    labelOptions: {
                        '.$title1.'
                        '.$title2.'
                    },
                    ';
                }
                else{
                    $optionLabels = '';
                }


                
                $img1 = $img1[1];
                $img2 = $img2[1];

                $wa->registerAndUseScript('imgcomparer-scripts',$pluginPath.'/vendor/image-compare-viewer/image-compare-viewer.min.js');
                $wa->registerAndUseStyle('imgcomparer-styles',$pluginPath.'/vendor/image-compare-viewer/image-compare-viewer.min.css');

                $optionsconst = '
                const options =
                {
                '.$settingControlColor.'
                '.$optionCircleSlider.'
                '.$optionLabels.' 
                '.$optionHoverStart.'
                '.$vertical.'
                }';


                $wa->addInlineScript('
                document.addEventListener("DOMContentLoaded", function(event) {
                    '.$optionsconst.'
                    const viewer = new ImageCompare(document.getElementById("imgcompareridx-'.$vieweridx.'"), options).mount();
                  });
                ', ['position' => 'after', [], ['imgcomparer-scripts']]);

            }

            if (isset($alt1[1]))
            {
                $alt1 = $alt1[1];
            }
            else{
                $alt1 = '';
            }

            if (isset($alt2[1]))
            {
                $alt2 = $alt2[1];
            }
            else{
                $alt2 = '';
            }



            if (isset($width1[1]))
            {
                $width1 = 'width="' . $width1[1] . '"';
            }
            else{
                $width1 = '';
            }

            if (isset($width2[1]))
            {
                $width2 = 'width="' . $width2[1] . '"';
            }
            else{
                $width2 = '';
            }

            if (isset($height1[1]))
            {
                $height1 = 'height="' . $height1[1] . '"';
            }
            else{
                $height1 = '';
            }

            if (isset($height2[1]))
            {
                $height2 = 'height="' . $height2[1] . '"';
            }
            else{
                $height2 = '';
            }

            // build output
            $output = '<div class="imgcomparer-container" tabindex="0" id="imgcompareridx-'.$vieweridx.'">';
            $output .= '<img src="' . $img1 . '" alt="' . $alt1 . '" '.$width1.' '.$height1.'>';
            $output .= '<img src="' . $img2 . '" alt="' . $alt2 . '" '.$width2.' '.$height2.'>';
            $output .= '</div>';
 
            //replace the original card $value with the new $output in article->text
            $article->text = str_replace($value, $output, $article->text);

        }

        //all done

    }
}