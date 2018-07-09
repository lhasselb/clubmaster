<?php

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;

/**
 * Adds an "Show/Hide Inactive" button to the bottom of a GridField.
 *
 * @package forms
 * @subpackage gridfield
 */
class GridFieldShowHideButton implements GridField_HTMLProvider, GridField_ActionProvider
{

    /**
     * Fragment to write the button to.
     *
     * @var string
     */
    protected $targetFragment;
    /**
     * Current state SHOW/HIDE.
     *
     * @var string
     */
    protected $state;

    /**
     * @param string $targetFragment The HTML fragment to write the button into
     */
    public function __construct($targetFragment = "after", $state = "SHOW")
    {
        $this->targetFragment = $targetFragment;
        $this->state = $state;
    }

    /**
     * Place the button in a <p> tag below the field
     *
     * @param GridField
     *
     * @return array
     */
    public function getHTMLFragments($gridField)
    {

        //SS_Log::log('getHTMLFragments() state='.$this->state,SS_Log::WARN);
        if ($this->state == 'SHOW') {
            $button = new GridField_FormAction(
                $gridField,
                'hide',
                _t('GridFieldShowHideButton.HIDE', 'Hide'),
                'hide',
                null
            );
            $button->setAttribute('data-icon', 'accept');
        } elseif ($this->state == 'HIDE') {
            $button = new GridField_FormAction(
                $gridField,
                'show',
                _t('GridFieldShowHideButton.SHOW', 'Show'),
                'show',
                null
            );
            $button->setAttribute('data-icon', 'decline');
        }

        return array(
            $this->targetFragment => '<p class="grid-show-button">' . $button->Field() . '</p>',
        );
    }

    /**
     * Show is an action button.
     *
     * @param GridField
     *
     * @return array
     */
    public function getActions($gridField)
    {
        return array('show', 'hide');
    }

    /**
     * Handle the show & hide action.
     *
     * @param GridField
     * @param string
     * @param array
     * @param array
     */
    public function handleAction($gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'show') {
            $this->setState('SHOW');
        } elseif ($actionName == 'hide') {
            $this->setState('HIDE');
            $list = $gridField->getList()->filter('Active', '1');
            $gridField->setList($list);
        }
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function toggleState()
    {
        $this->state = !$this->state;

        return $this;
    }
}
