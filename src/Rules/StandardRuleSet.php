<?php

namespace YakNet\AccessibilityConsole\Rules;

class StandardRuleSet
{
    /**
     * @return RuleInterface[]
     */
    public static function all(): array
    {
        return [
            new HtmlHasLang(),
            new ImgAltText(),
            new EmptyLink(),
            new ButtonName(),
            new FormLabel(),
            new FieldsetLegend(),
            new HeadingOrder(),
            new MetaViewport(),
            new TabindexOrder(),
            new ColorContrast(),
            new DuplicateId(),
            new AriaRole(),
            new IframeTitle(),
        ];
    }
}
