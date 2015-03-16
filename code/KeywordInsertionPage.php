<?php

/**
 * This module adds a page type to provides keyword insertion into a normal text   
 * using wildcards.
 */
class KeywordInsertionPage extends Page {

    /**
     * @var array 
     */
    private static $has_many = array(
        'KeywordInsertionItems' => 'KeywordInsertionItem'
    );

    /**
     * @var array 
     */
    public static $defaults = array(
        'ShowInMenus' => 0,
    );

    /**
     * @var string
     */
    public static $description = 'Seite mit Keyword Insertion funktion.';

    /**
     * @var string
     */
    private static $icon = 'mysite/images/sitetree/KeywordInsertionPage.png';

    /**
     * @var array
     */
    private $allKeywordData = array();

    /**
     * Set the fields for cms.
     * 
     * @return \FieldList
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $sKeywordInsertionItem = $this->KeywordInsertionItems()->DataQuery()->dataClass();
        $aKeywords = $sKeywordInsertionItem::allKeywords();
        $sKeywordInfoString = "";
        
        foreach($aKeywords as $sKeyword) {
            $sKeywordInfoString .= "{{".$sKeyword."}}<br/>";
        }

        $oGridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldAddNewButton('toolbar-header-right'),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldPaginator(50),
            new GridFieldEditButton(),
            new GridFieldDeleteAction(),
            new GridFieldDetailForm()
        );

        $oDataColumns = $oGridFieldConfig->getComponentByType('GridFieldDataColumns');

        $oDataColumns->setDisplayFields(
            array(
                'Keyword' => 'Keyword'
            )
        );

        $fields->addFieldsToTab(
            'Root.Main',
            array(
                new GridField(
                    "KeywordInsertionItems",
                    _t(
                        'KeywordInsertionPage.KEYWORD_ITEMS_LABEL',
                        'Wildcard Elements'
                    ),
                    $this->KeywordInsertionItems(),
                    $oGridFieldConfig
                ),
                new LiteralField(
                    'WildcardInfo',
                    '<div class="field">'.
                    '<h2>'._t('KeywordInsertionPage.WILDCARD_HEADLINE', 'Wildcard Content').'</h2>'.
                    '<p class="green-text">'.
                    _t(
                        'KeywordInsertionPage.WILDCARD_INFO',
                        'You can add a wildcard value into your content using <strong>{{</strong> and <strong>}}</strong> brackets.'
                    ).
                    '<br/>'.
                    _t(
                        'KeywordInsertionPage.WILDCARD_INFO_EXAMPLE',
                        'E.g. you want to insert the value of a wildcard named "mywildcard", you have to write {{mywildcard}} '.
                        'into content field below.'
                    ).
                    '<br/><br/>'.
                    _t(
                        'KeywordInsertionPage.WILDCARD_INFO_KEYWORDS_AVAILABLE',
                        'Available keywords are:'
                    ).
                    '<br/><strong>'.
                    $sKeywordInfoString.
                    '</strong></p>'.
                    '</div>'
                )
            ),
            'Content'
        );

        return $fields;
    }

    /**
     * Parse the content of this page with insertion values based on given keyword
     *
     * @param string $sKeyword
     *
     * @return string rendered text
     */
    public function renderContent($sKeyword) {
        // Pattern to replace wildcards opened by {{ and closed by }}
        $sPattern = "/{{([\w]+)}}/";
        
        // Find matching item to given keyword
        $oItemList = $this->getInsertionItemList();
        $oItem = $oItemList->find('Keyword', $sKeyword);
        
        if ($oItem) {
            $aValues = $oItem->allKeywordValues();
            $sContent =  $this->getInsertionContent();

            preg_match_all($sPattern, $sContent, $aMatches, PREG_SET_ORDER);

            $aMatchedPatterns = array_column($aMatches, 0);
            $aMatchedKeys = array_column($aMatches, 1);
            $aInsertionvalues = array();

            foreach($aMatchedKeys as $sKey) {
                $aInsertionvalues[] = $aValues[$sKey];
            }

            $sRenderedContent = str_replace (
                $aMatchedPatterns,
                $aInsertionvalues,
                $sContent
            );
        } else {
            $sRenderedContent = "Not found";
        }

        return $sRenderedContent;
    }

    /**
     * Get text to parse
     *
     * @return string
     */
    protected function getInsertionContent() {
        return $this->Content;
    }
    
    /**
     * Get KeywordInsertionItems object
     *
     * @return object
     */
    protected function getInsertionItemList() {
        return $this->KeywordInsertionItems();
    }
}

class KeywordInsertionPage_Controller extends Page_Controller {

    /**
     * @var array
     */
    private static $url_handlers = array(
        '$Keyword' => 'renderInsertionPage',
    );

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'renderInsertionPage',
    );
    
    /**
     * @var array
     */
    protected static $aAllKeywordData = array();

    /**
     * controller init method
     */
    public function init() {
        parent::init();
    }

    /**
     * Render the KeywordInsertionPage page
     *
     * @param \SS_HTTPRequest $request
     *
     * @return string Rendered template
     */
    public function renderInsertionPage(SS_HTTPRequest $request) {
        $iPageId = $this->pageId();
        $oPage = $this->pageObject($iPageId);
        
        $sKeyword = $this->paramKeyword();
        $sContent = $oPage->renderContent($sKeyword);

        return $this->render(
            array(
                'Content' => $sContent,
            )
        );
    }

    /**
     * Returns the value of keyword param from request
     *
     * @todo Check, if Keyword param exists
     *
     * @return string
     */
    protected function paramKeyword() {
        return $this->request->param('Keyword');
    }
    
    /**
     * Get the page id
     *
     * return int
     */
    protected function pageId() {
        $aSessionData = $this->session->get_all();

        return (int)$aSessionData["CMSMain"]["currentPage"];
    }

    /**
     * Get the page object
     *
     * @return \DataObject
     */
    protected function pageObject($iPageId) {
        return DataObject::get_by_id("KeywordInsertionPage", $iPageId);
    }
}
