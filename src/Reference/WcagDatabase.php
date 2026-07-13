<?php

namespace YakNet\AccessibilityConsole\Reference;

class WcagDatabase
{
    /** @var array<string, WcagCriterion> */
    private static array $criteria = [];

    public static function load(): void
    {
        if (!empty(self::$criteria)) {
            return;
        }

        self::add(new WcagCriterion(
            id: 'WCAG_2_2_2_BLINK',
            title: '2.2.2 Pause, Stop, Hide',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/pause-stop-hide.html',
            description: 'The <blink> tag causes flashing text, presenting significant cognitive and visual accessibility barriers.',
            badExample: '<blink>Blinking Text</blink>',
            goodExample: '<span class="attention-style">Styled Text</span>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_1_DUPLICATE_H1',
            title: '1.3.1 Info and Relationships',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html',
            description: 'Pages should contain exactly one h1 element to present a clear hierarchy and entry point for screen readers.',
            badExample: '<h1>Title</h1><h1>Another Title</h1>',
            goodExample: '<h1>Title</h1><h2>Subtitle</h2>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_5_AUTOCOMPLETE',
            title: '1.3.5 Identify Input Purpose',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/identify-input-purpose.html',
            description: 'Inputs collecting user personal info must have autocomplete attributes to assist users with cognitive impairments.',
            badExample: '<input type="email" name="email">',
            goodExample: '<input type="email" name="email" autocomplete="email">'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_1_1_SCROLLABLE_FOCUS',
            title: '2.1.1 Keyboard',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html',
            description: 'Scrollable regions must be focusable using keyboard navigation (tabindex="0") so keyboard-only users can scroll them.',
            badExample: '<div style="overflow: scroll; height: 100px;">Scrollable content</div>',
            goodExample: '<div style="overflow: scroll; height: 100px;" tabindex="0">Scrollable content</div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_LABELLEDBY',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: 'aria-labelledby and aria-describedby attributes must refer to existing element IDs to prevent screen readers from reading empty contexts.',
            badExample: '<button aria-labelledby="missing_id">Submit</button>',
            goodExample: '<div id="btn_lbl">Submit Form</div><button aria-labelledby="btn_lbl">Submit</button>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_1_1_ALT_PLACEHOLDER',
            title: '1.1.1 Non-text Content',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html',
            description: 'Alt attributes must not contain placeholder words or file names. They should provide a meaningful description of the image content.',
            badExample: '<img src="logo.png" alt="logo">',
            goodExample: '<img src="logo.png" alt="YakNet Company Logo">'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_1_1_SVG',
            title: '1.1.1 Non-text Content',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html',
            description: 'Inline SVGs must either contain alternative descriptions or be hidden from assistive technologies using aria-hidden="true".',
            badExample: '<svg><circle cx="5" cy="5" r="5"></svg>',
            goodExample: '<svg aria-hidden="true"><circle cx="5" cy="5" r="5"></svg>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_1_TABLE_SUMMARY',
            title: '1.3.1 Info and Relationships',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html',
            description: 'The summary attribute on <table> is deprecated in HTML5. Use <caption> elements for tables description instead.',
            badExample: '<table summary="Table Summary">',
            goodExample: '<table><caption>Table Description</caption>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_1_PRESENTATION_TAGS',
            title: '1.3.1 Info and Relationships',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html',
            description: 'Obsolete presentation tags like <center> and <strike> are banned. Use standard CSS classes or styling.',
            badExample: '<center>Centered Text</center>',
            goodExample: '<span style="text-align: center; display: block;">Centered Text</span>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_4_3_AUTOFOCUS',
            title: '2.4.3 Focus Order',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/focus-order.html',
            description: 'autofocus attribute can cause keyboard traps and disorient blind/low-vision users on page load. Focus should be managed contextually.',
            badExample: '<input name="search" autofocus>',
            goodExample: '<input name="search">'
        ));
    }

    public static function add(WcagCriterion $criterion): void
    {
        self::$criteria[$criterion->id] = $criterion;
    }

    public static function get(string $ruleId): ?WcagCriterion
    {
        self::load();
        return self::$criteria[$ruleId] ?? null;
    }

    /**
     * @return array<string, WcagCriterion>
     */
    public static function all(): array
    {
        self::load();
        return self::$criteria;
    }
}
