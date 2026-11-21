<?php

declare(strict_types=1);

namespace YakNet\AccessibilityConsole\Tests\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use YakNet\AccessibilityConsole\Core\RuleRegistry;
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\AbbreviationExpansion;
use YakNet\AccessibilityConsole\Rules\AllCapsLongText;
use YakNet\AccessibilityConsole\Rules\AriaActivedescendantValid;
use YakNet\AccessibilityConsole\Rules\AriaBreadcrumbPattern;
use YakNet\AccessibilityConsole\Rules\AriaComboboxPattern;
use YakNet\AccessibilityConsole\Rules\AriaDeprecatedRoles;
use YakNet\AccessibilityConsole\Rules\AriaLivePoliteMisuse;
use YakNet\AccessibilityConsole\Rules\AriaMenuPattern;
use YakNet\AccessibilityConsole\Rules\AriaModalBackdrop;
use YakNet\AccessibilityConsole\Rules\AriaOwnsValidTarget;
use YakNet\AccessibilityConsole\Rules\AriaSliderAttributes;
use YakNet\AccessibilityConsole\Rules\AriaSwitchState;
use YakNet\AccessibilityConsole\Rules\AriaTablistPattern;
use YakNet\AccessibilityConsole\Rules\AriaTreePattern;
use YakNet\AccessibilityConsole\Rules\ArticleAccessibleHeading;
use YakNet\AccessibilityConsole\Rules\BlockquoteCiteValid;
use YakNet\AccessibilityConsole\Rules\ButtonTypeExplicit;
use YakNet\AccessibilityConsole\Rules\CharacterKeyShortcuts;
use YakNet\AccessibilityConsole\Rules\CheckboxGroupFieldset;
use YakNet\AccessibilityConsole\Rules\ColorContrastAAA;
use YakNet\AccessibilityConsole\Rules\ConsistentHelp;
use YakNet\AccessibilityConsole\Rules\DocumentHasH1;
use YakNet\AccessibilityConsole\Rules\FlashingContentBanned;
use YakNet\AccessibilityConsole\Rules\FocusAppearance;
use YakNet\AccessibilityConsole\Rules\FontSizeMinimum;
use YakNet\AccessibilityConsole\Rules\FormErrorSummary;
use YakNet\AccessibilityConsole\Rules\HeaderMainNestedInvalid;
use YakNet\AccessibilityConsole\Rules\HeadingLengthConcise;
use YakNet\AccessibilityConsole\Rules\HoverFocusContentDismissible;
use YakNet\AccessibilityConsole\Rules\IframeSandboxAccessibility;
use YakNet\AccessibilityConsole\Rules\InputTypeValidation;
use YakNet\AccessibilityConsole\Rules\JustifiedTextBanned;
use YakNet\AccessibilityConsole\Rules\LinkDownloadContext;
use YakNet\AccessibilityConsole\Rules\MotionAnimationReduced;
use YakNet\AccessibilityConsole\Rules\NavAccessibleName;
use YakNet\AccessibilityConsole\Rules\OrientationLockBanned;
use YakNet\AccessibilityConsole\Rules\OutputForValid;
use YakNet\AccessibilityConsole\Rules\PointerCancellation;
use YakNet\AccessibilityConsole\Rules\RuleLevels;
use YakNet\AccessibilityConsole\Rules\SectionAccessibleName;
use YakNet\AccessibilityConsole\Rules\SelectOptionNotEmpty;
use YakNet\AccessibilityConsole\Rules\SvgFocusableIeEdge;
use YakNet\AccessibilityConsole\Rules\TargetSpacingMinimum;
use YakNet\AccessibilityConsole\Rules\TextareaAccessibleName;
use YakNet\AccessibilityConsole\Rules\TimeDatetimeValid;
use YakNet\AccessibilityConsole\Rules\TimeoutWarning;

class V4NewRulesTest extends TestCase
{
    public function testAll175RulesPassRegistryValidationWithUniqueIds(): void
    {
        $rules = RuleLevels::getRulesForLevel(9);
        $validation = RuleRegistry::validate($rules);

        $this->assertTrue($validation['valid'], implode("\n", $validation['errors']));
        $this->assertSame(175, $validation['count']);
    }

    /**
     * @return array<string, array{0: class-string, 1: string, 2: string}>
     */
    public static function newRulesProvider(): array
    {
        return [
            'FocusAppearance' => [
                FocusAppearance::class,
                '<style>a:focus { outline: 1px solid #999; }</style>',
                '<style>a:focus { outline: 2px solid #005fcc; }</style>',
            ],
            'ConsistentHelp' => [
                ConsistentHelp::class,
                '<html><body><header><nav><a href="/">Home</a></nav></header><form><input type="text"></form></body></html>',
                '<html><body><header><nav><a href="/">Home</a><a href="/help">Help</a></nav></header><form><input type="text"></form></body></html>',
            ],
            'MotionAnimationReduced' => [
                MotionAnimationReduced::class,
                '<style>@keyframes bounce { 0% { top: 0; } 100% { top: 10px; } }</style>',
                '<style>@keyframes bounce { 0% { top: 0; } 100% { top: 10px; } } @media (prefers-reduced-motion: reduce) { * { animation: none; } }</style>',
            ],
            'OrientationLockBanned' => [
                OrientationLockBanned::class,
                '<style>@media (orientation: landscape) { body { display: none; } }</style>',
                '<style>@media (min-width: 768px) { .grid { display: grid; } }</style>',
            ],
            'HoverFocusContentDismissible' => [
                HoverFocusContentDismissible::class,
                '<span data-tooltip="Help text">?</span>',
                '<button type="button" aria-describedby="tip1">?</button><div id="tip1" role="tooltip">Help text</div>',
            ],
            'CharacterKeyShortcuts' => [
                CharacterKeyShortcuts::class,
                '<button aria-keyshortcuts="s">Save</button>',
                '<button aria-keyshortcuts="Control+S">Save</button>',
            ],
            'PointerCancellation' => [
                PointerCancellation::class,
                '<button onmousedown="submitOrder()">Pay</button>',
                '<button onclick="submitOrder()">Pay</button>',
            ],
            'TimeoutWarning' => [
                TimeoutWarning::class,
                '<div class="session-timeout" data-timeout="300">Expiring soon</div>',
                '<div role="timer" aria-live="polite" class="session-timeout" data-timeout="300">Expiring soon <button type="button">Extend</button></div>',
            ],
            'FlashingContentBanned' => [
                FlashingContentBanned::class,
                '<div style="animation: flash 0.1s infinite;">Warning</div>',
                '<div style="animation: fade 1s ease-in;">Warning</div>',
            ],
            'TargetSpacingMinimum' => [
                TargetSpacingMinimum::class,
                '<a href="/a" style="margin-left: -6px;">Link</a>',
                '<a href="/a" style="margin-left: 8px; padding: 8px;">Link</a>',
            ],
            'AriaTablistPattern' => [
                AriaTablistPattern::class,
                '<div role="tablist"><button role="tab">Overview</button></div>',
                '<div role="tablist"><button id="t1" role="tab" aria-selected="true" aria-controls="p1">Overview</button></div><div id="p1" role="tabpanel" aria-labelledby="t1">Content</div>',
            ],
            'AriaComboboxPattern' => [
                AriaComboboxPattern::class,
                '<input role="combobox">',
                '<input role="combobox" aria-expanded="false" aria-controls="list1" aria-label="Search country">',
            ],
            'AriaSwitchState' => [
                AriaSwitchState::class,
                '<div role="switch">Dark mode</div>',
                '<button role="switch" aria-checked="false">Dark mode</button>',
            ],
            'AriaSliderAttributes' => [
                AriaSliderAttributes::class,
                '<div role="slider"></div>',
                '<div role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-label="Volume" tabindex="0"></div>',
            ],
            'AriaMenuPattern' => [
                AriaMenuPattern::class,
                '<ul role="menu"><li><a href="/">Home</a></li></ul>',
                '<ul role="menu"><li role="none"><a role="menuitem" href="/">Home</a></li></ul>',
            ],
            'AriaTreePattern' => [
                AriaTreePattern::class,
                '<ul role="tree"><li role="treeitem">Folder<ul role="group"><li role="treeitem">File</li></ul></li></ul>',
                '<ul role="tree" aria-label="Files"><li role="treeitem" aria-expanded="true">Folder<ul role="group"><li role="treeitem">File</li></ul></li></ul>',
            ],
            'AriaBreadcrumbPattern' => [
                AriaBreadcrumbPattern::class,
                '<nav class="breadcrumb"><ol><li><a href="/">Home</a></li><li>Docs</li></ol></nav>',
                '<nav aria-label="Breadcrumb" class="breadcrumb"><ol><li><a href="/">Home</a></li><li aria-current="page">Docs</li></ol></nav>',
            ],
            'AriaModalBackdrop' => [
                AriaModalBackdrop::class,
                '<div role="dialog" aria-label="Confirm">Are you sure?</div>',
                '<div role="dialog" aria-modal="true" aria-label="Confirm">Are you sure?</div>',
            ],
            'AriaLivePoliteMisuse' => [
                AriaLivePoliteMisuse::class,
                '<main aria-live="assertive"><p>Page body</p></main>',
                '<main><div role="status" aria-live="polite">Saved</div></main>',
            ],
            'AriaOwnsValidTarget' => [
                AriaOwnsValidTarget::class,
                '<div aria-owns="nonexistent-node">Menu</div>',
                '<div aria-owns="child-node">Menu</div><ul id="child-node"><li>Item</li></ul>',
            ],
            'AriaActivedescendantValid' => [
                AriaActivedescendantValid::class,
                '<div role="listbox" aria-activedescendant="missing-opt"></div>',
                '<div role="listbox" tabindex="0" aria-activedescendant="opt1"><div id="opt1" role="option">One</div></div>',
            ],
            'AriaDeprecatedRoles' => [
                AriaDeprecatedRoles::class,
                '<ul role="directory"><li>Item</li></ul>',
                '<ul role="list"><li>Item</li></ul>',
            ],
            'ArticleAccessibleHeading' => [
                ArticleAccessibleHeading::class,
                '<article><p>Body text without heading</p></article>',
                '<article><h2>News Title</h2><p>Body text</p></article>',
            ],
            'SectionAccessibleName' => [
                SectionAccessibleName::class,
                '<section role="region"><p>Content</p></section>',
                '<section role="region" aria-label="Statistics"><p>Content</p></section>',
            ],
            'NavAccessibleName' => [
                NavAccessibleName::class,
                '<nav><a href="/">Home</a></nav><nav><a href="/about">About</a></nav>',
                '<nav aria-label="Primary"><a href="/">Home</a></nav><nav aria-label="Footer"><a href="/about">About</a></nav>',
            ],
            'HeaderMainNestedInvalid' => [
                HeaderMainNestedInvalid::class,
                '<article><main><p>Invalid nesting</p></main></article>',
                '<main><article><h2>Valid nesting</h2></article></main>',
            ],
            'HeadingLengthConcise' => [
                HeadingLengthConcise::class,
                '<h2>' . str_repeat('Very long heading content ', 10) . '</h2>',
                '<h2>Concise Section Heading</h2>',
            ],
            'AbbreviationExpansion' => [
                AbbreviationExpansion::class,
                '<p><abbr>WCAG</abbr> standard</p>',
                '<p><abbr title="Web Content Accessibility Guidelines">WCAG</abbr> standard</p>',
            ],
            'BlockquoteCiteValid' => [
                BlockquoteCiteValid::class,
                '<blockquote cite="">Quoted sentence</blockquote>',
                '<blockquote cite="https://www.w3.org">Quoted sentence</blockquote>',
            ],
            'TimeDatetimeValid' => [
                TimeDatetimeValid::class,
                '<time datetime="yesterday">Yesterday</time>',
                '<time datetime="2026-11-21">21 Nov 2026</time>',
            ],
            'IframeSandboxAccessibility' => [
                IframeSandboxAccessibility::class,
                '<iframe src="/embed" title="Widget" tabindex="-1"></iframe>',
                '<iframe src="/embed" title="Widget"></iframe>',
            ],
            'DocumentHasH1' => [
                DocumentHasH1::class,
                '<html><body><main><h2>Subtitle only</h2></main></body></html>',
                '<html><body><main><h1>Main Title</h1><h2>Subtitle</h2></main></body></html>',
            ],
            'InputTypeValidation' => [
                InputTypeValidation::class,
                '<input type="text" name="email" id="email">',
                '<input type="email" name="email" id="email">',
            ],
            'FormErrorSummary' => [
                FormErrorSummary::class,
                '<input type="email" aria-invalid="true">',
                '<input type="email" aria-invalid="true" aria-describedby="email-err"><span id="email-err">Invalid email</span>',
            ],
            'CheckboxGroupFieldset' => [
                CheckboxGroupFieldset::class,
                '<form><input type="checkbox" name="topics[]" value="a"><input type="checkbox" name="topics[]" value="b"></form>',
                '<form><fieldset><legend>Topics</legend><input type="checkbox" name="topics[]" value="a"><input type="checkbox" name="topics[]" value="b"></fieldset></form>',
            ],
            'SelectOptionNotEmpty' => [
                SelectOptionNotEmpty::class,
                '<select aria-label="Country"><option value="tr"></option></select>',
                '<select aria-label="Country"><option value="tr">Türkiye</option></select>',
            ],
            'TextareaAccessibleName' => [
                TextareaAccessibleName::class,
                '<textarea name="comments"></textarea>',
                '<label for="comments">Comments</label><textarea id="comments" name="comments"></textarea>',
            ],
            'OutputForValid' => [
                OutputForValid::class,
                '<output for="missing_input">0</output>',
                '<input id="a" type="number"><output for="a">0</output>',
            ],
            'ButtonTypeExplicit' => [
                ButtonTypeExplicit::class,
                '<form><button>Toggle</button></form>',
                '<form><button type="button">Toggle</button></form>',
            ],
            'LinkDownloadContext' => [
                LinkDownloadContext::class,
                '<a href="/files/report.pdf">Annual Report</a>',
                '<a href="/files/report.pdf">Annual Report (PDF)</a>',
            ],
            'ColorContrastAAA' => [
                ColorContrastAAA::class,
                '<p style="color: #767676; background-color: #ffffff;">Medium contrast text</p>',
                '<p style="color: #111111; background-color: #ffffff;">High contrast text</p>',
            ],
            'FontSizeMinimum' => [
                FontSizeMinimum::class,
                '<p style="font-size: 8px;">Tiny legal text</p>',
                '<p style="font-size: 16px;">Readable text</p>',
            ],
            'AllCapsLongText' => [
                AllCapsLongText::class,
                '<p style="text-transform: uppercase;">' . str_repeat('This is a long uppercase paragraph. ', 5) . '</p>',
                '<p style="text-transform: uppercase;">SHORT BADGE</p>',
            ],
            'JustifiedTextBanned' => [
                JustifiedTextBanned::class,
                '<p style="text-align: justify;">Justified text block</p>',
                '<p style="text-align: start;">Aligned text block</p>',
            ],
            'SvgFocusableIeEdge' => [
                SvgFocusableIeEdge::class,
                '<svg aria-hidden="true"><circle cx="5" cy="5" r="5"></circle></svg>',
                '<svg aria-hidden="true" focusable="false"><circle cx="5" cy="5" r="5"></circle></svg>',
            ],
        ];
    }

    /**
     * @param class-string $ruleClass
     */
    #[DataProvider('newRulesProvider')]
    public function testRuleDetectsViolationAndPassesCompliantMarkup(string $ruleClass, string $badHtml, string $goodHtml): void
    {
        $scanner = new Scanner();
        $scanner->addRule(new $ruleClass());

        $badViolations = $scanner->scan($badHtml);
        $this->assertNotEmpty($badViolations, "Expected {$ruleClass} to report violation for bad HTML.");

        $goodViolations = $scanner->scan($goodHtml);
        $this->assertEmpty($goodViolations, "Expected {$ruleClass} to report 0 violations for compliant HTML.");
    }
}
