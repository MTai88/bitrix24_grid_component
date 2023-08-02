<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Error;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Iblock\Elements\ElementMyElementTable;
use Bitrix\Main\UserTable;

class MyElementFilterListComponent extends \CBitrixComponent implements Controllerable, Errorable
{
    protected $gridId = 'myelement_filter_grid_list';
    protected ErrorCollection $errors;
    protected int $iblockId;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->errors = new ErrorCollection();

        Loader::includeModule("iblock");

        $this->iblockId = (int)ElementMyElementTable::getEntity()->getIblock()->getId();
    }

    public function configureActions()
    {
        return [
            'executeComponent' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ],
                'postfilters' => []
            ]
        ];
    }

    public function getErrors()
    {
        return $this->errors->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errors->getErrorByCode($code);
    }

    public function canEdit(): bool
    {
        $iblockPermission = CIBlock::GetPermission($this->iblockId);
        return ($iblockPermission >= 'U');
    }

    public function executeComponent()
    {
        $pageNav = $this->getPageNavigation();

        $this->arResult['GridId'] = $this->gridId;
        $this->arResult['GridColumns'] = $this->getGridColumns();
        $this->arResult['GridFilter'] = $this->getFilterFields();

        $filter = $this->prepareFilter($this->arResult['GridFilter']);
        $this->arResult['GridRows'] = $this->getGridRows($pageNav, $filter);

        $this->arResult['PageNavigation'] = $pageNav;
        $this->arResult['PageSizes'] = $this->getPageSizes();

        return $this->includeComponentTemplate();
    }

    protected function getGridColumns(): array
    {
        return [
            ['id' => 'NAME', 'name' => Loc::getMessage('MTH_COLUMN_NAME'), 'default' => true],
            ['id' => 'CODE', 'name' => Loc::getMessage('MTH_COLUMN_CODE'), 'default' => true],
            ['id' => 'DATE_CREATE', 'name' => Loc::getMessage('MTH_COLUMN_CREATED_DATE'), 'default' => true],
            ['id' => 'STATUS_VALUE', 'name' => Loc::getMessage('MTH_COLUMN_STATUS'), 'default' => true],
            ['id' => 'CREATED_BY', 'name' => Loc::getMessage('MTH_COLUMN_CREATED_BY'), 'default' => true],
        ];
    }

    protected function getGridRows(PageNavigation $pageNavigation, array $filter): array
    {
        $canEdit = $this->canEdit();
        $rows = [];
        $order = ['ID' => 'desc'];

        $query = ElementMyElementTable::query()
            ->setSelect([
                '*',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'CREATED_BY_NAME' => 'AUTHOR.NAME',
                'CREATED_BY_LAST_NAME' => 'AUTHOR.LAST_NAME',
                'CREATED_BY_SECOND_NAME' => 'AUTHOR.SECOND_NAME',
                'CREATED_BY_LOGIN' => 'AUTHOR.LOGIN'
            ])
            ->where('ACTIVE', 'Y')
            ->setFilter($filter)
            ->setOrder($order)
            ->setLimit($pageNavigation->getLimit())
            ->setOffset($pageNavigation->getOffset());

        $userRelation = new Reference(
            'AUTHOR',
            UserTable::class,
            Join::on('this.CREATED_BY', 'ref.ID'),
        );
        $query->registerRuntimeField($userRelation);

        $pageNavigation->setRecordCount($query->queryCountTotal());

        $resRows = $query->fetchAll();
        foreach ($resRows as $row) {
            $rowActions = [];

            if ($canEdit) {
                $rowActions[] = [
                    'text' => Loc::getMessage('MTH_ACTION_DELETE'),
                    'onclick' => sprintf('BX.MyElementList.Instance.deleteQueue(%d)', $row['ID'])
                ];
            }

            $rows[] = [
                'data' => $row,
                'actions' => $rowActions
            ];
        }

        return $rows;
    }

    protected function getPageNavigation(): PageNavigation
    {
        $gridOptions = new Options($this->gridId);
        $navParams = $gridOptions->GetNavParams();

        $pageNavigation = new PageNavigation($this->gridId);
        $pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

        return $pageNavigation;
    }

    protected function getPageSizes(): array
    {
        return [
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ];
    }

    protected function getFilterFields(): array
    {
        return [
            [
                'id' => 'NAME',
                'name' => Loc::getMessage('MTH_COLUMN_NAME'),
                'default' => true
            ],
            [
                'id' => 'CODE',
                'name' => Loc::getMessage('MTH_COLUMN_CODE'),
                'default' => true
            ],
            [
                'id' => 'STATUS_VALUE',
                'name' => Loc::getMessage('MTH_COLUMN_STATUS'),
                'type' => 'list',
                'items' => $this->getStatuses(),
                'params' => [
                    'multiple' => 'Y'
                ],
                'default' => true
            ],
            [
                'id' => 'DATE_CREATE',
                'name' => Loc::getMessage('MTH_COLUMN_CREATED_DATE'),
                'type' => 'date',
                'default' => true
            ]
        ];
    }

    protected function prepareFilter(array $gridFilter): array
    {
        $filter = [];

        $filterOption = new \Bitrix\Main\UI\Filter\Options($this->gridId);
        $filterData = $filterOption->getFilter();

        foreach ($filterData as $k => $v) {
            $filter[$k] = $v;
        }

        $filterPrepared = \Bitrix\Main\UI\Filter\Type::getLogicFilter($filter, $gridFilter);

        if (!empty($filter['FIND'])) {
            $findFilter = [
                'LOGIC' => 'OR',
                [
                    '%NAME' => $filter['FIND']
                ]
            ];

            if (!empty($filterPrepared)) {
                $filterPrepared[] = $findFilter;
            } else {
                $filterPrepared = $findFilter;
            }
        }

        return $filterPrepared;
    }

    protected function getStatuses(): array
    {
        $statuses = [];
        $propertyEnums = CIBlockPropertyEnum::GetList(
            ["DEF" => "DESC", "SORT" => "ASC"],
            ["IBLOCK_ID" => $this->iblockId, "CODE" => "STATUS"]
        );
        while ($enumFields = $propertyEnums->GetNext()) {
            $statuses[$enumFields["ID"]] = $enumFields["VALUE"];
        }
        return $statuses;
    }

    public function deleteAction(int $queueId): ?array
    {
        if (!$this->canEdit()) {
            $this->errors[] = new Error(Loc::getMessage('MTH_ACCESS_DENIED'));
            return null;
        }

        ElementMyElementTable::delete($queueId);

        return [
            "result" => true
        ];
    }
}