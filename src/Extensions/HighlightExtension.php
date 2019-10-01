<?php

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HighlightExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('highlight', array($this, 'highlightText')),
        );
    }

    public function highlightText($text, $array)
    {
        $highlighted = $text;
        foreach ($array as $string => $class) {
            $highlighted = str_replace($string, '<span class="'.$class.'">'.$string.'</span>', $highlighted);
        }

        return $highlighted;
    }

    public function getName()
    {
        return self::class;
    }
}
