<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Database\ForeignKeyConstraintViolationException;
use Nette\Database\Table\ActiveRow;

class PartnersManager
{

    const TABLE_GROUPS = 'partner_groups';
    const TABLE_PARTNERS = 'partners';

    /**
     * @var Context
     */
    private $database;


    /**
     * PartnersManager constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }


    /**
     * @param bool $onlyEnabled
     * @return array
     */
    public function getReport($onlyEnabled = true)
    {
        $groups = [];
        foreach ($this->getAll($onlyEnabled) as $item) {
            if (!isset($groups[$item->group_id])) {
                $groups[$item->group_id] = [
                    'id' => $item->group_id,
                    'name' => $item->group_name,
                    'partners' => [],
                ];
            }

            if ($item->height === null && $item->group_height) {
                $item->height = $item->group_height;
            }
            $groups[$item->group_id]['partners'][$item->id] = $item;
        }

        return $groups;
    }


    /**
     * @param bool $onlyEnabled
     * @return \Nette\Database\ResultSet
     */
    public function getAll($onlyEnabled = true)
    {
        $enabledPart = '';
        if ($onlyEnabled) {
            $enabledPart = 'WHERE `p`.`enabled` AND `pg`.`enabled`';
        }

        $sql = <<<EOT
SELECT `pg`.`id` AS `group_id`, `pg`.`name` AS `group_name`, `pg`.`height` AS `group_height`, `p`.`id`, `p`.`name`, `p`.`url`, `p`.`picture_url`, `p`.`height`
FROM `partners` AS `p`
LEFT JOIN `partner_groups` AS `pg` ON `p`.`group_id` = `pg`.`id`
$enabledPart
ORDER BY `pg`.`order`, `p`.`order`; 
EOT;

        return $this->database->query($sql);
    }


    /**
     * @param bool $onlyEnabled
     * @return \Nette\Database\Table\Selection
     */
    public function getGroups($onlyEnabled = true)
    {
        $selection = $this->database->table(self::TABLE_GROUPS)
            ->order('order');

        if ($onlyEnabled == true) {
            $selection->where('enabled', 1);
        }

        return $selection;
    }


    /**
     * @param bool $onlyEnabled
     * @return \Nette\Database\Table\Selection
     */
    public function getPartners($onlyEnabled = true)
    {
        $selection = $this->database->table(self::TABLE_PARTNERS)
            ->order('order');

        if ($onlyEnabled == true) {
            $selection->where('enabled', 1);
        }

        return $selection;
    }


    /**
     * @param int $id
     * @return false|ActiveRow
     */
    public function getPartnerById($id)
    {
        return $this->database->table(self::TABLE_PARTNERS)->get($id);
    }


    /**
     * @param \Traversable $values
     * @param int|null $id
     */
    public function insertUpdatePartner($values, $id = null)
    {
        if ($id) {
            $this->getPartnerById($id)->update($values);
        } else {
            $this->database->table(self::TABLE_PARTNERS)->insert($values);
        }
    }


    public function changePartnersOrder(ActiveRow $item = null, ActiveRow $prevItem = null, ActiveRow $nextItem = null)
    {
        $this->sort(self::TABLE_PARTNERS, $item, $prevItem, $nextItem);
    }


    /**
     * @param $id
     * @return false|ActiveRow
     */
    public function getGroupById($id)
    {
        return $this->database->table(self::TABLE_GROUPS)->get($id);
    }


    /**
     * @param \Traversable $values
     * @param int|null $id
     */
    public function insertUpdateGroup($values, $id = null)
    {
        if ($id) {
            $this->getPartnerById($id)->update($values);
        } else {
            $this->database->table(self::TABLE_GROUPS)->insert($values);
        }
    }


    public function changeGroupsOrder(ActiveRow $item = null, ActiveRow $prevItem = null, ActiveRow $nextItem = null)
    {
        $this->sort(self::TABLE_GROUPS, $item, $prevItem, $nextItem);
    }


    /**
     * @param ActiveRow $group
     * @throws ForeignKeyConstraintViolationException
     */
    public function delete(ActiveRow $group)
    {
        $group->delete();
    }


    private function sort($table, ActiveRow $item = null, ActiveRow $prevItem = null, ActiveRow $nextItem = null)
    {
        if (!in_array($table, [self::TABLE_GROUPS, self::TABLE_PARTNERS])) {
            throw new \InvalidArgumentException("Table name $table is invalid");
        }

        $itemOrder = intval($item->order);

        if ($prevItem) {
            $this->database->query(
                "UPDATE `$table` 
                SET `order` = `order` - 1
                WHERE `order` <= " . intval($prevItem->order) . "
                AND `order` > $itemOrder;"
            );
        }
        if ($nextItem) {
            $this->database->query(
                "UPDATE `$table` 
                SET `order` = `order` + 1
                WHERE `order` >= " . intval($nextItem->order) . "
                AND `order` < $itemOrder;"
            );
        }

        if ($prevItem) {
            $itemOrder = $prevItem->order;
        } elseif ($nextItem) {
            $itemOrder = $nextItem->order;
        } else {
            $itemOrder = 1;
        }

        $this->database->query(
            "UPDATE `$table` 
                SET `order` = $itemOrder
                WHERE `id` = " . $item->id . ";"
        );
    }
}
