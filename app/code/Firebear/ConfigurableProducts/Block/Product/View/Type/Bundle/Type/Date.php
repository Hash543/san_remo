<?php

namespace Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type;

/**
 * Product options text type block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    private $fillLeadingZeros = true;

    /**
     * Catalog product option type date
     *
     * @var \Magento\Catalog\Model\Product\Option\Type\Date
     */
    private $catalogProductOptionTypeDate;

    private $bundleOption = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
     * @param \Magento\Catalog\Helper\Data                     $catalogData
     * @param \Magento\Catalog\Model\Product\Option\Type\Date  $catalogProductOptionTypeDate
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        array $data = []
    ) {
        $this->catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        parent::__construct($context, $pricingHelper, $catalogData, $data);

        if ($this->hasData('bundle_option')) {
            $this->setBundleOption($this->getData('bundle_option'));
        }
    }

    /**
     * @return null
     */
    public function getBundleOption()
    {
        return $this->bundleOption;
    }

    /**
     * @param null $bundleOption
     */
    public function setBundleOption($bundleOption)
    {
        $this->bundleOption = $bundleOption;
    }

    /**
     * Use JS calendar settings
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return $this->catalogProductOptionTypeDate->useCalendar();
    }

    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
        if ($this->useCalendar()) {
            return $this->getCalendarDateHtml();
        } else {
            return $this->getDropDownsDateHtml();
        }
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $option = $this->getOption();
        $value  = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        $yearStart = $this->catalogProductOptionTypeDate->getYearStart();
        $yearEnd   = $this->catalogProductOptionTypeDate->getYearEnd();

        $calendar = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Date'
        )->setId(
            'bundle_options_' . $this->getBundleOption() . '_' . $this->getOption()->getId() . '_date'
        )->setName(
            'bundle_options[' . $this->getBundleOption() . '][' . $this->getOption()->getId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder     = $this->catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder     = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $daysHtml   = $this->_getSelectFromToHtml('day', 1, 31);

        $yearStart = $this->catalogProductOptionTypeDate->getYearStart();
        $yearEnd   = $this->catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd);

        $translations = ['d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml];

        return strtr($fieldsOrder, $translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
        if ($this->catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart   = 0;
            $hourEnd     = 23;
            $dayPartHtml = '';
        } else {
            $hourStart   = 1;
            $hourEnd     = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                'day_part'
            )->setOptions(
                ['am' => __('AM'), 'pm' => __('PM')]
            )->getHtml();
        }
        $hoursHtml   = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * Return drop-down html with range of values
     *
     * @param string   $name  Id/name of html select element
     * @param int      $from  Start position
     * @param int      $to    End position
     * @param int|null $value Value selected
     *
     * @return string Formatted Html
     */
    private function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = [['value' => '', 'label' => '-']];
        for ($i = $from; $i <= $to; $i++) {
            $options[] = ['value' => $i, 'label' => $this->_getValueWithLeadingZeros($i)];
        }

        return $this->_getHtmlSelect($name, $value)->setOptions($options)->getHtml();
    }

    /**
     * HTML select element
     *
     * @param string   $name Id/name of html select element
     * @param int|null $value
     *
     * @return mixed
     */
    private function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        $this->setSkipJsReloadPrice(1);

        $require = '';
        $select  = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setId(
            'options_' . $this->getOption()->getId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require
        )->setExtraParams()->setName(
            'options[' . $option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($this->getOption()->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $value = $this->getProduct()->getPreconfiguredValues()->getData(
                'options/' . $option->getId() . '/' . $name
            );
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }

    /**
     * Add Leading Zeros to number less than 10
     *
     * @param int $value
     *
     * @return string|int
     */
    private function _getValueWithLeadingZeros($value)
    {
        if (!$this->fillLeadingZeros) {
            return $value;
        }

        return $value < 10 ? '0' . $value : $value;
    }
}
