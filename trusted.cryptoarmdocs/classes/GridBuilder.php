<?php

namespace Trusted\CryptoARM\Docs;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Main\Grid;

class GridBuilder
{

    public $gridId = '';
    public $options = null;
    public $columns = array();
    public $filterStructure = array();
    public $filterOption = null;
    public $filterData = null;
    public $filter = array();
    public $sortData = null;
    public $sort = array();
    public $pagination = null;
    public $navigation = null;
    public $onchange = null;
    public $actionPanel = array();
    public $reloadGridJs = '';
    public $pageSizes = array();

    // grid component options
    public $showPagination = true;
    public $showPageSize = true;
    public $showTotalCounter = true;
    public $allowSort = true;

    public function __construct($schema) {
        UI\Extension::load('ui.buttons');

        $this->gridId = $schema['GRID_ID'];

        $this->options = new Grid\Options($this->gridId);

        foreach ($schema['STRUCTURE'] as $id => $values) {
            $this->columns[] = array(
                'id' => $id,
                'name' => $values['NAME'],
                'sort' => $values['SORT'] ? $id : false,
                'type' => $values['TYPE'],
                'default' => $values['DEFAULT'],
            );

            if ($values['FILTER_TYPE']) {
                $this->filterStructure[] = array(
                    'id' => $id,
                    'name' => $values['NAME'],
                    'default' => $values['DEFAULT'],
                    'type' => $values['FILTER_TYPE'],
                    'items' => $values['FILTER_ITEMS'],
                    'params' =>  $values['FILTER_PARAMS'],
                );
            }
        }

        $this->filterOption = new Ui\Filter\Options($this->gridId);
        $this->filterData = $this->filterOption->getFilter(array());

        if ($this->filterData['FILTER_APPLIED']) {
            foreach ($this->filterData as $k => $v) {
                // When date filter is detected
                if (strpos($k, '_from') !== false) {
                    $this->filter[str_replace('_from' , '', $k)]['FROM'] = ConvertDateTime($v, TR_CA_DB_TIME_FORMAT);
                    continue;
                }
                if (strpos($k, '_to') !== false) {
                    $this->filter[str_replace('_to' , '', $k)]['TO'] = ConvertDateTime($v, TR_CA_DB_TIME_FORMAT);
                    continue;
                }
                if ($k == 'ID') {
                    $this->filter['DOC'] = $v;
                    continue;
                }
                if ($k == 'NAME') {
                    $this->filter['FILE_NAME'] = $v;
                    continue;
                } elseif ($k == 'FIND' && !$this->filter['FILE_NAME']) {
                    $this->filter['FILE_NAME'] = $v;
                    continue;
                }
                if ($k == 'TYPE') {
                    if (count($v) === 1) {
                        $this->filter['TYPE'] = $v['0'];
                    }
                    continue;
                }
                if ($k == 'OWNER') {
                    $this->filter['OWNER'] = $v;
                    continue;
                }
                // Regular filter values
                if (array_key_exists($k, $schema['STRUCTURE'])) {
                    $this->filter[$k] = $v;
                    continue;
                }
            }
        }

        $this->sortData = $this->options->getSorting();
        $this->sort = $this->sortData['sort'];

        if (empty($this->sort)) {
            $this->sort = array('DOC' => 'asc');
        } elseif ($this->sort['NAME']) {
            $this->sort = array ('FILE_NAME' => $this->sort['NAME']);
        } elseif ($this->sort['ID']) {
            $this->sort = array ('DOC' => $this->sort['ID']);
        }
        $this->pagination = $this->options->getNavParams();

        $this->onchange = new Grid\Panel\Snippet\Onchange();

        $snippet = new \Bitrix\Main\Grid\Panel\Snippet();
        $this->actionPanel = array(
            'GROUPS' => array(
                'TYPE' => array(
                    'ITEMS' => array(
                        $snippet ->getRemoveButton( array(
                            'ONCHANGE'  =>  array(
                               array (
                                  'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                                  'DATA' => array(
                                     array(
                                        'JS' => "BX.adminUiList.SendSelected('" . $this->gridId . "');"
                                    )
                                 )
                              )
                           )
                        )),
                    ),
                )
            ),
        );

        $this->reloadGridJs = "trustedCA.reloadGrid.bind(this,'" . $this->gridId . "')";

        $sizes = $schema['PAGE_SIZES'] ? $schema['PAGE_SIZES'] : array(5, 10, 20, 50, 100);
        foreach ($sizes as $size) {
            $this->pageSizes[] = array(
                'NAME' => (string)$size,
                'VALUE' => (int)$size,
            );
        }

        if (array_key_exists('SHOW_PAGINATION', $schema)) {
            $this->showPagination = $schema['SHOW_PAGINATION'];
        }
        if (array_key_exists('SHOW_PAGESIZE', $schema)) {
            $this->showPageSize = $schema['SHOW_PAGESIZE'];
        }
        if (array_key_exists('SHOW_TOTAL_COUNTER', $schema)) {
            $this->showTotalCounter = $schema['SHOW_TOTAL_COUNTER'];
        }
        if (array_key_exists('ALLOW_SORT', $schema)) {
            $this->allowSort = $schema['ALLOW_SORT'];
        }
    }


    public function showFilter() {
        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            'bitrix:main.ui.filter',
            '',
            array(
                'FILTER_ID' => $this->gridId,
                'GRID_ID' => $this->gridId,
                'FILTER' => $this->filterStructure,
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL' => true,
                'DISABLE_SEARCH' => false,
            )
        );
    }


    public function showGrid($rows) {
        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            '',
            array(
                'GRID_ID' => $this->gridId,
                'COLUMNS' => $this->columns,
                'ROWS' => $rows,
                'SHOW_ROW_CHECKBOXES' => true,
                'NAV_OBJECT' => $this->navigation,
                'AJAX_MODE' => 'Y',
                'AJAX_ID' => \CAjax::getComponentID(
                    'bitrix:main.ui.grid',
                    '.default',
                    ''
                ),
                'PAGE_SIZES' => $this->pageSizes,
                'AJAX_OPTION_JUMP' => 'N',
                'SHOW_CHECK_ALL_CHECKBOXES' => true,
                'SHOW_ROW_ACTIONS_MENU' => true,
                'SHOW_GRID_SETTINGS_MENU' => true,
                'SHOW_NAVIGATION_PANEL' => true,
                'SHOW_PAGINATION' => $this->showPagination,
                'SHOW_SELECTED_COUNTER' => true,
                'SHOW_TOTAL_COUNTER' => $this->showTotalCounter,
                'SHOW_PAGESIZE' => $this->showPageSize,
                'SHOW_ACTION_PANEL' => true,
                'ACTION_PANEL' => $this->actionPanel,
                'ALLOW_COLUMNS_SORT' => true,
                'ALLOW_COLUMNS_RESIZE' => true,
                'ALLOW_HORIZONTAL_SCROLL' => true,
                'ALLOW_SORT' => $this->allowSort,
                'ALLOW_PIN_HEADER' => true,
                'AJAX_OPTION_HISTORY' => 'N',
            )
        );
    }

}

