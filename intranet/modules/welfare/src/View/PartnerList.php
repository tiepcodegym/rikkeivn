<?php

namespace Rikkei\Welfare\View;

use Rikkei\Welfare\Model\Partners;

class PartnerList
{
    public static function getTrTableHtml($partner)
    {
//        $partner = Partners::find($id);
        $html = '<tr class=item' . $partner->id . '>';
        $html .= '<td><input name="choose" type="radio" class="hidden" value="'. $partner->id .'"></td>';
        $html .= '<td>'. $partner->name .'</td>';
        $html .= '<td>'. $partner->email .'</td>';
        $html .= '<td>'. $partner->phone .'</td>';
        $html .= '<td>'. $partner->address .'</td>';
        $html .= '<td>'. $partner->website .'</td>';
        $html .= '<td>';
        $html .= '<button type="button" class="edit-modal-partner btn btn-edit" id="edit-modal-partner" data-id="'. $partner->id .'" data-name="'. $partner->name .'" style="margin-right: 10px;"><span class="glyphicon glyphicon-edit"></span></button>';
        $html .= '<button type="button" class="delete-modal-partner btn btn-danger" data-id="'. $partner->id .'" data-name="'. $partner->name .'"><span class="glyphicon glyphicon-trash"></span></button>';
        $html .= '</td></tr>';

        return $html;
    }
}
