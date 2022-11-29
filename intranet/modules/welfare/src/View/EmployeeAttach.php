<?php

namespace Rikkei\Welfare\View;

use Rikkei\Welfare\Model\WelAttachFee;

class EmployeeAttach
{
    public $items         = null;
    public $numberFeeFree = 0;
    public $numberFee50   = 0;
    public $numberFee100  = 0;

    public function __construct($old)
    {
        if ($old) {
            $this->items         = $old->items;
            $this->numberFeeFree = $old->numberFeeFree;
            $this->numberFee50   = $old->numberFee50;
            $this->numberFee100  = $old->numberFee100;
        }
    }

    /**
     * Add object to $items
     *
     * @param array $item
     * @param int $id
     */
    public function add($item, $id)
    {
        $storedItem = [
            'id'               => $item['id'],
            'welfare_id'       => $item['welid'],
            'employee_id'      => $item['employee_id'],
            'name'             => $item['name'],
            'gender'           => $item['gender'],
            'card_id'          => $item['card_id'],
            'birthday'         => $item['birthday'],
            'phone'            => $item['phone'],
            'relation_name_id' => $item['relation_name_id'],
            'support_cost'     => $item['support_cost'],
        ];

        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                if ($this->items[$id]['support_cost'] == WelAttachFee::Fee_0) {
                    $this->numberFeeFree--;
                }
                if ($this->items[$id]['support_cost'] == WelAttachFee::Fee_50) {
                    $this->numberFee50--;
                }
                if ($this->items[$id]['support_cost'] == WelAttachFee::Fee_100) {
                    $this->numberFee100--;
                }
            }
        }

        if ($item['support_cost'] == WelAttachFee::Fee_0) {
            $this->numberFeeFree++;
        }
        if ($item['support_cost'] == WelAttachFee::Fee_50) {
            $this->numberFee50++;
        }
        if ($item['support_cost'] == WelAttachFee::Fee_100) {
            $this->numberFee100++;
        }

        $this->items[$id] = $storedItem;
    }
}
