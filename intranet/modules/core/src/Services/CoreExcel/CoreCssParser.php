<?php

namespace Rikkei\Core\Services\CoreExcel;

use Maatwebsite\Excel\Parsers\CssParser;

/**
 * Description of CoreCssParser
 *
 * @author lamnv
 */
class CoreCssParser extends CssParser
{
    /**
     * Transform the found css to inline styles
     */
    public function transformCssToInlineStyles($html)
    {
        $css = '';

        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        // Loop through all stylesheets
        foreach($this->links as $link)
        {
            $css .= file_get_contents($link, false, stream_context_create($arrContextOptions));
        }

        return $this->cssInliner->convert($html, $css);
    }

    /**
     * Get css from link
     * @param  string $link
     * @return string|boolean
     */
    protected function getCssFromLink($link)
    {
        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        return file_get_contents($link, false, stream_context_create($arrContextOptions));
    }
}
