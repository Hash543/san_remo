<?php

namespace WeltPixel\NavigationLinks\Block\Adminhtml;

class ColumnWidthTemplate extends \Magento\Backend\Block\Template
{
	protected function _toHtml()
	{
		return '<div style="margin-top: -30px; margin-bottom: 30px" class="admin__field admin__field-no-label">
                    <div class="admin__field-control">
                        <div class="admin__field admin__field-option">
                            <span>' . "Only  Applies for:<br/>
                                <strong>Full Width</strong> allowed values: pixels or %. E.g.: \"130px\" or \"30 % \"<br/>
                                <strong>Sectioned</strong> allowed value: auto only. E.g.: \"auto\"" . '<br />
                                Invalid values will be ignored!
                            </span>
                        </div>
                    </div>
                </div>';
	}
}