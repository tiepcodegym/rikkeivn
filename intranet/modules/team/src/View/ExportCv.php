<?php

namespace Rikkei\Team\View;

use Illuminate\Support\Facades\Lang;

/**
 * Description of ExportCv
 *
 * @author lamnv
 */
class ExportCv
{
    private $rows; // array contains tag html

    // const TYPE_EXPORT_CUSTOMER = 1;
    // const TYPE_EXPORT_UPDATE = 2;

    /**
     * get list/tag names
     * @param array|integer $aryTags
     * @param array $tagList
     * @param boolean $isBreakLine
     * @return mixed|string|null
     */
    public static function getTagNames($aryTags, $tagList, $isBreakLine = false)
    {
        if (!$aryTags) {
            return null;
        }
        if (is_array($aryTags)) {
            $names = [];
            foreach ($aryTags as $itemAry) {
                if ($itemAry['text']) {
                    $text = htmlentities($itemAry['text']);
                    $names[] = $isBreakLine ? "{$text}<br>" : $text;
                    continue;
                }
                if (isset($tagList[$itemAry['id']])) {
                    $text = htmlentities($tagList[$itemAry['id']]);
                    $names[] = $isBreakLine ? "{$text}<br>" : $text;
                }
            }
            return $isBreakLine ? (string)substr(implode('', $names), 0, -4) : implode(', ', $names);
        }
        return isset($tagList[$aryTags]) ? $tagList[$aryTags] : null;
    }

    /**
     * render skill row table
     * @param object $skillItem
     * @param array $tagData
     * @param array $ranking
     * @param integer $rightColspan
     * @param string $locale
     * @return string string
     */
    public static function renderSkillRow($skillItem, $tagData, $ranking, $rightColspan, $locale, $isLast)
    {
        $tdHtml = '';
        if ($skillItem->text) {
            $tdHtml .= '<td colspan="' . $rightColspan . '" class="sl-bd md-bdl md-bdr bg-type">' . e($skillItem->text) . '</td>';
        } else {
            $classBdb = $isLast ? ' md-bdb' : '';
            $tdHtml .= '<td class="sl-bd md-bdl'. $classBdb .'">' . self::getTagNames($skillItem->tag_id, $tagData) . '</td>';
            foreach ($ranking as $noRank => $rankName) {
                $tdHtml .= '<td align="center" class="sl-bd '. $classBdb .'">' . ($noRank == $skillItem->level ? '&nbsp;●&nbsp;' : '') . '</td>';
            }
            $tdHtml .= '<td align="right" class="sl-bd md-bdr'. $classBdb .'">' . $skillItem->exp_y . ' ' . e(Lang::get('team::cv.Y', [], $locale)) . ' - '
                . $skillItem->exp_m . ' ' . e(Lang::get('team::cv.M', [], $locale)) . '</td>';
        }
        return $tdHtml;
    }

    public static function renderBankTdRow($ranking)
    {
        $blankTdRank = '<td></td>';
        foreach ($ranking as $noRank => $rankName) {
            $blankTdRank .= '<td></td>';
        }
        return $blankTdRank .= '<td></td>';
    }

    public static function renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, $keySkill)
    {
        $skillItem = isset($skillPersonIds[$keySkill]) ? $skillPersonIds[$keySkill] : null;
        if ($skillItem) {
            return self::renderSkillRow($skillItem, $tagData, $ranking, $rightColspan, $locale, ($keySkill === $countSkill - 1));
        }
        return self::renderBankTdRow($ranking);
    }

    public static function convertFullName($name)
    {
        $slug = str_slug($name, '_');
        $arrSlug = explode('_', $slug);
        $result = [];
        foreach ($arrSlug as $text) {
            $result[] = ucfirst($text);
        }
        return trim(implode('_', $result));
    }

    /*
     * get value in array
     */
    public static function getEavAttr($eav, $name)
    {
        if (isset($eav[$name])) {
            return $eav[$name];
        }
        return null;
    }

    /*
     * get column name by ascii key
     */
    public static function getColNameByIndex($index)
    {
        $num = $index - ord('A');
        return \PHPExcel_Cell::stringFromColumnIndex($num);
    }

    /**
     * generate some tag <td> to use many times
     *
     * @param integer
     * @return null
     */
    public function generateEmptyCols()
    {
        $aryRows = [];
        for ($i = 3; $i < 10; $i++) {
            $aryRows[$i] = str_repeat('<td></td>', $i);
        }
        $this->rows = $aryRows;
    }

    /**
     * get n tag <td>
     *
     * @param integer $nCol
     * @return string
     */
    public function renderEmptyCols($nCol)
    {
        return isset($this->rows[$nCol]) ? $this->rows[$nCol] : str_repeat('<td></td>', $nCol);
    }
}
