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
            goodExample: '<img src="logo.png" alt="YakNet Logo">'
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

        self::add(new WcagCriterion(
            id: 'WCAG_2_5_8_TARGET_SIZE',
            title: '2.5.8 Target Size (Minimum)',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html',
            description: 'The size of the target for pointer inputs is at least 24 by 24 CSS pixels.',
            badExample: '<button style="width: 16px; height: 16px;">X</button>',
            goodExample: '<button style="width: 24px; height: 24px;">X</button>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_1_DETAILS_SUMMARY',
            title: '1.3.1 Info and Relationships',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html',
            description: '<details> elements must have a <summary> element to serve as an accessible disclosure trigger.',
            badExample: '<details><p>Details content</p></details>',
            goodExample: '<details><summary>More info</summary><p>Details content</p></details>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_CURRENT',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: 'The aria-current attribute must have a valid value indicating the current item in a set.',
            badExample: '<a href="/home" aria-current="active">Home</a>',
            goodExample: '<a href="/home" aria-current="page">Home</a>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_3_3_1_ARIA_ERRORMESSAGE',
            title: '3.3.1 Error Identification',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html',
            description: 'aria-errormessage must reference a valid error element and control must have aria-invalid="true".',
            badExample: '<input name="email" aria-errormessage="email_error">',
            goodExample: '<input name="email" aria-invalid="true" aria-errormessage="email_err"><span id="email_err">Invalid email</span>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_ROLEDESCRIPTION',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: 'aria-roledescription must only be used on elements with a non-generic role.',
            badExample: '<div aria-roledescription="slide">Slide 1</div>',
            goodExample: '<div role="region" aria-roledescription="slide" aria-label="Slide 1">Slide 1</div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_PROHIBITED_ATTR',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: 'Prohibited ARIA attributes must not be used on presentation or generic roles.',
            badExample: '<div role="presentation" aria-label="decorative"></div>',
            goodExample: '<div role="presentation"></div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_2_HTML_DIR',
            title: '1.3.2 Meaningful Sequence',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/meaningful-sequence.html',
            description: 'The dir attribute must only use valid values: ltr, rtl, or auto.',
            badExample: '<p dir="left">Text</p>',
            goodExample: '<p dir="ltr">Text</p>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_1_SELECT_CHILDREN',
            title: '1.3.1 Info and Relationships',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html',
            description: '<select> elements must only contain valid child elements (<option>, <optgroup>).',
            badExample: '<select><div>Option 1</div></select>',
            goodExample: '<select><option>Option 1</option></select>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_1_1_ROLE_IMG',
            title: '1.1.1 Non-text Content',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html',
            description: 'Elements with role="img" must have an accessible name.',
            badExample: '<span role="img" class="icon-star"></span>',
            goodExample: '<span role="img" class="icon-star" aria-label="5 stars rating"></span>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_4_2_MEDIA_CONTROLS',
            title: '1.4.2 Audio Control',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/audio-control.html',
            description: 'Audio and video media elements should provide user controls for playback and volume.',
            badExample: '<audio src="podcast.mp3"></audio>',
            goodExample: '<audio src="podcast.mp3" controls></audio>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_2_2_TRACK_VALIDATION',
            title: '1.2.2 Captions (Prerecorded)',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/captions-prerecorded.html',
            description: '<track> elements must have valid kind, srclang, and label attributes.',
            badExample: '<track src="sub.vtt" kind="captions">',
            goodExample: '<track src="sub.vtt" kind="captions" srclang="en" label="English">'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_1_1_ACCESSKEY_DUPLICATE',
            title: '2.1.1 Keyboard',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html',
            description: 'accesskey attribute values must be unique across the document.',
            badExample: '<a href="/" accesskey="h">Home</a><a href="/help" accesskey="h">Help</a>',
            goodExample: '<a href="/" accesskey="h">Home</a><a href="/help" accesskey="p">Help</a>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_BUTTON_NESTED_INTERACTIVE',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: '<button> elements must not contain nested interactive elements.',
            badExample: '<button><a href="/cart">Checkout</a></button>',
            goodExample: '<a href="/cart" class="btn">Checkout</a>'
        ));
        self::add(new WcagCriterion(
            id: 'WCAG_2_4_11_FOCUS_NOT_OBSCURED',
            title: '2.4.11 Focus Not Obscured (Minimum)',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum.html',
            description: 'Fixed or sticky headers and overlays must not completely obscure keyboard-focused elements.',
            badExample: '<header style="position: fixed; top: 0; width: 100%; height: 80px;"></header>',
            goodExample: '<html style="scroll-padding-top: 80px;"><header style="position: fixed; top: 0; width: 100%; height: 80px;"></header></html>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_5_7_DRAGGING_MOVEMENTS',
            title: '2.5.7 Dragging Movements',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG22/Understanding/dragging-movements.html',
            description: 'Elements supporting drag-and-drop operations must provide a single-pointer alternative.',
            badExample: '<div draggable="true" class="list-item">Item 1</div>',
            goodExample: '<div draggable="true" class="list-item">Item 1 <button>Move Up</button> <button>Move Down</button></div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_LABEL_GENERIC',
            title: '4.1.2 Name, Role, Value',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html',
            description: 'Generic <div> or <span> elements should not use aria-label without an explicit ARIA role.',
            badExample: '<div aria-label="Important Notice">Text</div>',
            goodExample: '<div role="region" aria-label="Important Notice">Text</div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_5_AUTOCOMPLETE_APPROPRIATE',
            title: '1.3.5 Identify Input Purpose',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/identify-input-purpose.html',
            description: 'Input autocomplete value must be semantically appropriate for the input type.',
            badExample: '<input type="number" autocomplete="email">',
            goodExample: '<input type="email" autocomplete="email">'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_4_13_FOCUS_APPEARANCE',
            title: '2.4.13 Focus Appearance',
            level: 'AAA',
            url: 'https://www.w3.org/WAI/WCAG22/Understanding/focus-appearance.html',
            description: 'Focus indicators must enclose the element with at least a 2px perimeter and sufficient contrast.',
            badExample: '<style>a:focus { outline: 1px dotted #ccc; }</style>',
            goodExample: '<style>a:focus-visible { outline: 2px solid #005fcc; outline-offset: 2px; }</style>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_3_1_FLASHING_CONTENT',
            title: '2.3.1 Three Flashes or Below Threshold',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/three-flashes-or-below-threshold.html',
            description: 'Web pages must not contain anything that flashes more than three times in any one second period.',
            badExample: '<div style="animation: flash 0.15s infinite;">Alert</div>',
            goodExample: '<div style="animation: fade 1.2s ease-in-out;">Alert</div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_3_3_REDUCED_MOTION',
            title: '2.3.3 Animation from Interactions',
            level: 'AAA',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/animation-from-interactions.html',
            description: 'Motion animation triggered by interaction can be disabled unless essential.',
            badExample: '<style>@keyframes slide { from { left: 0; } to { left: 100px; } }</style>',
            goodExample: '<style>@keyframes slide { from { left: 0; } to { left: 100px; } } @media (prefers-reduced-motion: reduce) { * { animation: none !important; } }</style>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_3_4_ORIENTATION_LOCK',
            title: '1.3.4 Orientation',
            level: 'AA',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/orientation.html',
            description: 'Content does not restrict its view and operation to a single display orientation such as portrait or landscape.',
            badExample: '<style>@media (orientation: landscape) { body { display: none; } }</style>',
            goodExample: '<style>.layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }</style>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_TABLIST',
            title: '4.1.2 Name, Role, Value (APG Tabs)',
            level: 'A',
            url: 'https://www.w3.org/WAI/ARIA/apg/patterns/tabs/',
            description: 'ARIA Tabs require role="tablist", child role="tab" with aria-selected & aria-controls, and role="tabpanel" with aria-labelledby.',
            badExample: '<div role="tablist"><button role="tab">Tab 1</button></div>',
            goodExample: '<div role="tablist"><button id="t1" role="tab" aria-selected="true" aria-controls="p1">Tab 1</button></div><div id="p1" role="tabpanel" aria-labelledby="t1">Panel</div>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_4_1_2_ARIA_COMBOBOX',
            title: '4.1.2 Name, Role, Value (APG Combobox)',
            level: 'A',
            url: 'https://www.w3.org/WAI/ARIA/apg/patterns/combobox/',
            description: 'Combobox controls require aria-expanded, aria-controls (or aria-owns), and an accessible name.',
            badExample: '<input role="combobox">',
            goodExample: '<input role="combobox" aria-expanded="false" aria-controls="city-list" aria-label="Select City">'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_3_2_6_CONSISTENT_HELP',
            title: '3.2.6 Consistent Help',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG22/Understanding/consistent-help.html',
            description: 'Help mechanisms appear in the same relative order across pages where they are provided.',
            badExample: '<body><header><nav><a href="/">Home</a></nav></header><form><input type="text"></form></body>',
            goodExample: '<body><header><nav><a href="/">Home</a><a href="/help">Help</a></nav></header><form><input type="text"></form></body>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_1_4_6_CONTRAST_AAA',
            title: '1.4.6 Contrast (Enhanced)',
            level: 'AAA',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-enhanced.html',
            description: 'Visual presentation of text has a contrast ratio of at least 7:1 (4.5:1 for large text).',
            badExample: '<p style="color: #767676; background-color: #ffffff;">Gray text</p>',
            goodExample: '<p style="color: #333333; background-color: #ffffff;">High contrast text</p>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_5_2_POINTER_CANCELLATION',
            title: '2.5.2 Pointer Cancellation',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/pointer-cancellation.html',
            description: 'Functions operated using a single pointer should not execute on the down-event.',
            badExample: '<button onmousedown="deleteItem()">Delete</button>',
            goodExample: '<button onclick="deleteItem()">Delete</button>'
        ));

        self::add(new WcagCriterion(
            id: 'WCAG_2_1_4_CHAR_KEY_SHORTCUTS',
            title: '2.1.4 Character Key Shortcuts',
            level: 'A',
            url: 'https://www.w3.org/WAI/WCAG21/Understanding/character-key-shortcuts.html',
            description: 'Single printable character keyboard shortcuts must use a modifier or be remappable/disableable.',
            badExample: '<button aria-keyshortcuts="s">Save</button>',
            goodExample: '<button aria-keyshortcuts="Control+S">Save</button>'
        ));
    }

    public static function add(WcagCriterion $criterion): void
    {
        self::$criteria[$criterion->id] = $criterion;
    }

    public static function get(string $ruleId): ?WcagCriterion
    {
        self::load();
        if (isset(self::$criteria[$ruleId])) {
            return self::$criteria[$ruleId];
        }

        // Prefix fallback: match by WCAG success criterion prefix (e.g. WCAG_1_1_1_* -> any WCAG_1_1_1_*)
        if (preg_match('/^(WCAG_\d+_\d+_\d+)/', $ruleId, $m)) {
            $prefix = $m[1];
            foreach (self::$criteria as $id => $criterion) {
                if (str_starts_with($id, $prefix)) {
                    return $criterion;
                }
            }
        }

        return null;
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
