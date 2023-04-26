<?php
// @copyright 2022 Kevin's Guides, Kevin Olson
// License: MIT

//kill direct access
defined('_JEXEC') || die;

//required to make the plugin work
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

//the class name should be PlgTYPEYourPluginName and it must extend CMSPlugin
class PlgContentContentPlugDemo extends CMSPlugin
{

    //the onContentPrepare happens after Joomla loads the content from the database, but before it reaches the user.
    //making changes in this file will not affect the original article content at all - it's only changing the output.
    //$context - article, category, etc. where it's being accessed from
    //$article - the article object, used to access things like $article->text (all text)
    //         - you can also try out $article->introtext and $article->fulltext if you use read more breaks
    //$params  - params from the page/menu item (show title, show tags, link titles, show author, etc...)
    //$page    - the page # you're on, if on a multi-page article with pagebreaks
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

        //adding sample styles and scripts
        //remove comments if you actually want to use
        //$wa->registerAndUseStyle('contentplugdemo.mainstyle',$pluginPath.'/css/style.css');
        //$wa->registerAndUseScript('contentplugdemo.mainscript',$pluginPath.'/js/script.js');


        //getting some options from the config
        $whiteBodybg = $this->params->get('white-body','0');
        $cardColor = $this->params->get('card-color','bg-dark');
        
        //how we handle the card body background color (white or not)
        //by default, don't change anything
        $cardBodyClass = '';
        if($whiteBodybg == '1'){
            //if 1, change classes
            $cardBodyClass = 'bg-white text-dark link-dark';
        }


        //find each card based on the RegEx within $article->text and store results in $matches array
        preg_match_all('/{card.*?\/card}/s', $article->text, $matches);


        //for each match in matches[0] - it's a 2d array but the first dimension isn't doing much
        foreach ($matches[0] as $value) {

            //this searches between the card tags to find the body
            //this would be in group 2 of this match.. see RegEx101.com for more info
            preg_match('/(?<={card)(.*?})(.*?)(?={\/card})/s', $value, $cardMatcher);
            //take the second group match - it's the card body between the curly braces of opening and closing {card}{/card}s
            $cardBody = $cardMatcher[2];

            //the first part of the match above contains everything between {card and }, 
            //so it could be nothing if {card} but it could have attributes, like {card title=''}
            //Match the title attribute based on the first group in the previous match
            preg_match('/(?<=title=").*?(?=")/s', $cardMatcher[1], $titleMatch);

            //the title is empty until we prove otherwise
            $title = '';
            //if this isn't null, the title attribute must be there
            if ($titleMatch) {
                //we found the title set by title=""
                $title = $titleMatch[0];
            }

            //generate the output using bootstrap classes and throwing out variables in
            if ($title == '') {
                //make a card div with no title
                $output = '<div class="card '.$cardColor.' text-light"><div class="card-body '.$cardBodyClass.'">'.$cardBody.'</div></div>';
            } else {
                //make a card div with a span title and a card body div, remember to close both divs.
                $output = '<div class="card '.$cardColor.' text-light"><span class="card-header">' .
                    $title . '</span><div class="card-body '.$cardBodyClass.'">' . $cardBody . '</div></div>';
            }

            //replace the original card $value with the new $output in article->text
            $article->text = str_replace($value, $output, $article->text);

        }

        //all done

    }
}