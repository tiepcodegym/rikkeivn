<?php
namespace Rikkei\Welfare\View;

use Rikkei\Team\Model\Team;
use Lang;

class TableView
{
    /**
     *
     * @param PartnerGroup $partGroup
     * @return \Illuminate\Support\HtmlString
     */
    public static function actionPartnerGroup($partGroup)
    {
        return '<td style="padding-left: 37px;">
                    <input class="hidden" name="choose" type="radio" value="'. $partGroup->id .'">
                    <button type="button" class="edit-modal-group btn-edit" data-id="'. $partGroup->id .'" data-name="'. $partGroup->name .'">
                        <span class="glyphicon glyphicon-edit"></span>
                    </button>
                    <button type="button" class="delete-modal-group btn btn-danger" data-id="'. $partGroup->id .'"data-name="'. $partGroup->name .'">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>';
    }

    /**
     *
     * @param Partner $partner
     * @return \Illuminate\Support\HtmlString
     */
    public static function actionPartner($partner)
    {
        return '<td>
                    <input class="hidden" name="choose" type="radio" value="'. $partner->id .'">
                    <button type="button" class="edit-modal-partner btn-edit" id="edit-modal-partner"
                        data-id="'. $partner->id .'" data-name="'. $partner->name .'">
                        <span class="glyphicon glyphicon-edit"></span>
                    </button>
                    <button type="button" class="delete-modal-partner btn btn-danger"
                    data-id="'. $partner->id .'" data-name="'. $partner->name .'">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>';
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Support\HtmlString
     */
    public static function checkBoxHtml($id)
    {
        return '<input class="hidden" name="choose" type="radio" value="'. $id .'">';
    }
}

