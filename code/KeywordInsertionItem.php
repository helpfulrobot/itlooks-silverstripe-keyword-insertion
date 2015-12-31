<?php

/**
 * Class provides a simple keyword element, only contains the keyword as 
 * key of a keyword insertion request.
 * You can extend this Element with your own data and fields.
 */
class KeywordInsertionItem extends DataObject
{

    /**
     * @var array
     */
    private static $db = array(
        'Keyword' => 'Varchar(200)',
        'Value' => 'Varchar(200)',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'KeywordInsertionPage' => 'KeywordInsertionPage',
    );

    /**
     * @var string
     */
    public static $singular_name = 'Wildcard Element';

    /**
     * @var string
     */
    public static $plural_name = 'Wildcard Element';

    /**
     * Returns the fieldset for cms backend
     * 
     * @return \FieldList
     */
    public function getCMSFields()
    {
        $fields = new FieldList(
            $rootTab = new TabSet('Root',
                $tabMain = new Tab('Main')
            )
        );

        $fields->addFieldsToTab(
            'Root.Main',
            array(
                new TextField(
                    'Keyword',
                    _t('KeywordInsertionPage.KEYWORD_LABEL', 'Keyword')
                ),
                new TextField(
                    'Value',
                    _t('KeywordInsertionPage.VALUE_LABEL', 'Value')
                )
            )
        );
        
        return $fields;
    }
    
    /**
     * Method returns all values based on a specific keyword
     *
     * @return array
     */
    public function allKeywordValues()
    {
        $aAllKeywordValues = array();
        $aAllKeywords = self::allKeywords();
        
        foreach ($aAllKeywords as $sKeyword) {
            $aAllKeywordValues[$sKeyword] = $this->{$sKeyword};
        }

        return $aAllKeywordValues;
    }

    /**
     * Return keywords for wildcard insertion.
     *
     * @return array
     */
    public static function allKeywords()
    {
        return array(
            'Value',
        );
    }
}
