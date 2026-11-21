# YakNet Accessibility Console 4.0

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-brightgreen.svg)](https://phpstan.org)
[![Rules](https://img.shields.io/badge/WCAG%20%26%20ARIA%20Rules-175%20Active-purple.svg)](https://www.w3.org/WAI/standards-guidelines/wcag/)
[![Presets](https://img.shields.io/badge/Compliance%20Presets-11-teal.svg)](#-mevzuat-ve-sektörel-ön-ayarlar-presets)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Open Source](https://img.shields.io/badge/Open%20Source-YakNet-orange.svg)](https://forum.yak.net.tr)

**YakNet Accessibility Console**, web uygulamalarındaki erişilebilirlik (WCAG 2.1, WCAG 2.2, WAI-ARIA 1.2, ARIA APG) ve HTML söz dizimi sorunlarını statik ve DOM tabanlı olarak analiz eden, **175 benzersiz kural**, **11 mevzuat/sektör uyumluluk profili**, **VPAT 2.5 (ACR) rapor üreticisi**, **politika yönetim motoru (Policy Engine)** ve **otomatik onarım (Offline & AI Self-Healing)** yetenekleri sunan açık kaynaklı bir PHP erişilebilirlik analiz platformudur.

**YakNet** tarafından açık kaynak topluluğu ve büyük ölçekli organizasyonların, kamu portallarının, üniversitelerin ve ürün ekiplerinin ortak standartlarla erişilebilir web yazılımları üretebilmesi için geliştirilmektedir.

Laravel Blade (`.blade.php`), Twig (`.twig`), **Vue SFC (`.vue`)**, **React JSX/TSX (`.jsx`, `.tsx`)**, PHP (`.php`) ve standart HTML şablonlarını doğrudan tarar; satır içi gizleme yönergelerini (`<!-- a11y-disable-next-line -->`), baseline yönetimini ve CI/CD kalite kapılarını sıfır ek kurulumla destekler.

---

## 🌟 Öne Çıkan Özellikler

- **🛡️ 175 Benzersiz WCAG 2.1 / 2.2, WAI-ARIA 1.2 & APG Kuralı:**
  - **WCAG 2.2 Tam Kapsamı:** `FocusAppearance` (2.4.13), `FocusNotObscured` (2.4.11), `TargetSizeMinimum` & `TargetSpacingMinimum` (2.5.8), `DraggingMovements` (2.5.7), `ConsistentHelp` (3.2.6), `RedundantEntry` (3.3.7), `AccessibleAuth` (3.3.8).
  - **WAI-ARIA 1.2 & APG Bileşen Desenleri:** Tabs (`AriaTablistPattern`), Combobox (`AriaComboboxPattern`), Switch (`AriaSwitchState`), Slider (`AriaSliderAttributes`), Menu/Menubar (`AriaMenuPattern`), Tree (`AriaTreePattern`), Breadcrumb (`AriaBreadcrumbPattern`), Modal Dialog (`AriaModalBackdrop`), `aria-owns` & `aria-activedescendant` bütünlük doğrulaması, kullanımdan kaldırılmış/soyut rol denetimi (`AriaDeprecatedRoles`).
  - **Hareket, Nöbet Güvenliği ve Bilişsel Erişilebilirlik:** `FlashingContentBanned` (2.3.1 — >3Hz yanıp sönme koruması), `MotionAnimationReduced` (2.3.3 — `prefers-reduced-motion`), `OrientationLockBanned` (1.3.4), `CharacterKeyShortcuts` (2.1.4), `PointerCancellation` (2.5.2), `TimeoutWarning` (2.2.1), `HoverFocusContentDismissible` (1.4.13), `ColorContrastAAA` (1.4.6 — 7:1), `JustifiedTextBanned` (1.4.8), `AllCapsLongText` (3.1.5).
- **📋 VPAT 2.5 / ACR Uyumluluk Raporu Üreticisi (`vpat`):**
  - WCAG 2.1 / 2.2, Revised Section 508 ve EN 301 549 ile uyumlu **VPAT 2.5 Accessibility Conformance Report** belgelerini tek komutla **Markdown**, **HTML** ve **JSON** formatlarında üretir (`Supports`, `Partially Supports`, `Does Not Support`, `Not Evaluated`).
- **🏛️ 11 Mevzuat ve Sektörel Uyumluluk Ön Ayarı (`presets`):**
  - `wcag22-aa`, `wcag22-aaa`, `en301549`, `eaa2025` (European Accessibility Act), `section508`, `bitv2`, `government`, `healthcare`, `banking`, `education`, `ecommerce`.
- **⚖️ Politika Yönetim Motoru (`PolicyEngine`):**
  - `a11y.yaml` içinden organizasyon genelinde geçerli kalite kuralları tanımlayın: `min_score`, `max_critical`, `max_errors`, `max_warnings`, `required_standards` (örn. A & AA sıfır hata), `banned_rules` (sıfır toleranslı kurallar), `severity_overrides` (kural ciddiyetini yükseltme/düşürme) ve POUR ilkesi bazlı limitler (`principle_limits`).
- **🔇 Denetlenebilir Satır İçi Gizleme Yönergeleri (`InlineSuppressionParser`):**
  - `<!-- a11y-disable-next-line RULE_ID -- gerekçe -->`, `<!-- a11y-disable RULE_ID --> ... <!-- a11y-enable RULE_ID -->`, `<!-- a11y-disable-file -->` yönergelerini destekler ve her gizlemeyi gerekçesiyle birlikte denetim kaydına (`suppressedAuditLog`) işler.
- **📈 Geçmiş Takibi ve Regresyon Karşılaştırması (`compare` & `--save-history`):**
  - Tarama sonuçlarını zaman damgalı geçmiş dosyasında saklar, sağlık skoru trendini (`improving` / `stable` / `regressing`) hesaplar ve iki JSON rapor arasındaki yeni eklenen regresyonlar ile çözülen sorunları kıyaslar.
- **🧩 Çoklu Şablon ve Modern Frontend Desteği:**
  - Laravel Blade (`.blade.php`), Twig (`.twig`), **Vue SFC (`.vue`)**, **React JSX/TSX (`.jsx`, `.tsx`)**, PHP (`.php`), Latte (`.latte`) ve HTML (`.html`, `.htm`, `.xhtml`) dosyalarını 1:1 satır numarası koruyarak tarar.
- **🩺 Sistem Tanılama (`doctor`) & Kural Rehberi (`explain`):**
  - `a11y doctor` ile PHP ortamını, 175 kuralın kayıt tutarlılığını ve opsiyonel bağımlılıkları doğrulayın; `a11y explain <RULE_ID>` ile herhangi bir kuralın W3C bağlantısını, kötü/iyi kod örneklerini ve otomatik onarım durumunu görün.
- **⚡ Tek Geçişli Kural Motoru, Artımlı Önbellek (`--cache`) ve Profilleme (`--profile`):**
  - DOM ağacını tek geçişte tarar; içerik özeti tabanlı önbellek (`ScanCache`) ile değişmeyen dosyaları atlar ve `--profile` ile en yavaş çalışan kuralların milisaniye bazında istatistiklerini raporlar.
- **🧪 PHPUnit Erişilebilirlik Doğrulama Trait'i (`AccessibilityAssertions`):**
  - Laravel, Symfony ve PHP test sınıflarınıza `use AccessibilityAssertions;` ekleyerek `$this->assertHtmlAccessible($html)` ve `$this->assertMeetsAccessibilityPreset($html, 'wcag22-aa')` kontrollerini doğrudan yazın.
- **🛠️ Otomatik CI/CD Pipeline Üretici (`init --ci=...`):**
  - GitHub Actions, GitLab CI, Azure Pipelines ve Bitbucket Pipelines için hazır erişilebilirlik iş akışı dosyaları üretir.
- **🤖 Gelişmiş Çevrimdışı & Yapay Zeka Otomatik Onarım (`fix`):**
  - **32+ deterministik çevrimdışı (offline) kural onarımı** (API anahtarı gerekmeden) ve karmaşık şablonlar için **Google Gemini AI** desteği.
- **🔌 Zengin Çıktı, Rapor ve Bildirim Ekosistemi:**
  - Çıktı formatları: `console`, `json`, `github`, `gitlab`, `sarif` (OASIS SARIF 2.1.0), `junit`, `checkstyle`, `markdown`, `csv`, `xml`.
  - Dosya dışa aktarımları: HTML Dashboard (`.html`), PDF (`.pdf`), Excel (`.xlsx`), VPAT 2.5 (`.md`, `.html`, `.json`), SARIF (`.sarif`), JUnit/Checkstyle (`.xml`), SVG Rozet (`--badge`).
  - Bildirimler: **Slack**, **Discord**, **Microsoft Teams** ve **HMAC-SHA256 imzalı genel JSON Webhook** (`--webhook-url`).
- **🏆 PHPStan Level 9 & Tam Test Kapsamı:** Kod tabanı en üst seviye PHPStan Level 9 statik tip analizi (0 hata) ile doğrulanmıştır.

---

## 📦 Kurulum

Composer ile projenize geliştirme bağımlılığı olarak ekleyin:

```bash
composer require --dev yaknet/accessibility-console
```

---

## 🚀 Komut Satırı (CLI) Kullanımı

YakNet Accessibility Console **10 komut** sunar: `scan`, `fix`, `vpat`, `compare`, `rules`, `explain`, `presets`, `doctor`, `watch`, `init`.

### 1. Yapılandırma Oluşturma (`init`) ve Sistem Kontrolü (`doctor`)
```bash
# Kapsamlı varsayılan a11y.yaml dosyası oluşturur
bin/a11y init

# Ortam, 175 kural bütünlüğü ve entegrasyon durumunu doğrular
bin/a11y doctor
```

### 2. Proje, Şablon veya Canlı URL Tarama (`scan`)
```bash
# a11y.yaml yapılandırmasına göre projeyi tara
bin/a11y scan

# Belirli bir dizini (Blade, Twig, Vue, JSX/TSX, HTML) tara
bin/a11y scan resources/views
bin/a11y scan src/components

# Mevzuat / sektör ön ayarıyla tara ve VPAT 2.5 + SVG rozet üret
bin/a11y scan resources/views --preset=wcag22-aa --vpat=reports/vpat-2.5.md --badge=public/a11y.svg

# Performans profillemesi ve geçmiş kaydıyla tara
bin/a11y scan resources/views --profile --save-history=.a11y-history.json

# Canlı bir web sitesini crawler ve opsiyonel Headless Chrome ile tara
bin/a11y scan https://ornek.org --crawl --depth=3 --max-pages=25
```

### 3. VPAT 2.5 Uyumluluk Raporu Üretme (`vpat`)
```bash
# Markdown formatında VPAT 2.5 raporu üret
bin/a11y vpat resources/views --product="Açık Portal" --product-version="2.4.0" --output=VPAT-2.5.md

# Bağımsız HTML veya JSON VPAT 2.5 raporu üret
bin/a11y vpat resources/views --format=html --output=reports/vpat.html
bin/a11y vpat resources/views --format=json --output=reports/vpat.json
```

### 4. Regresyon Karşılaştırması (`compare`)
PR veya sürüm öncesi iki tarama raporunu kıyaslayıp yeni eklenen ihlallerde derlemeyi durdurun:
```bash
bin/a11y compare reports/before.json reports/after.json --fail-on-regression
```

### 5. Kuralları Keşfetme (`rules`), Açıklama (`explain`) ve Ön Ayarlar (`presets`)
```bash
# Tüm 175 kuralı listele
bin/a11y rules

# POUR ilkesine, seviyeye veya çevrimdışı onarım desteğine göre filtrele
bin/a11y rules --principle=Operable --standard=AA
bin/a11y rules --healable

# Belirli bir kuralın W3C rehberini ve kod örneklerini incele
bin/a11y explain WCAG_4_1_2_ARIA_TABLIST

# Tüm 11 mevzuat ve sektörel ön ayarı listele
bin/a11y presets
```

### 6. CI/CD Çıktı Formatları, Raporlar ve Bildirimler
```bash
bin/a11y scan --format=sarif --report=a11y.sarif
bin/a11y scan --format=checkstyle --report=a11y.checkstyle
bin/a11y scan --format=junit --report=a11y.xml
bin/a11y scan --report=rapor.html
bin/a11y scan --report=rapor.pdf
bin/a11y scan --report=rapor.xlsx

# Slack, Discord, Microsoft Teams veya genel webhook bildirimleri
bin/a11y scan --webhook-slack="$SLACK_WEBHOOK" --webhook-teams="$TEAMS_WEBHOOK"
```

### 7. Otomatik Onarım (`fix`) ve Canlı İzleme (`watch`)
```bash
# Çevrimdışı (API anahtarsız) deterministik onarım önizleme & uygulama
bin/a11y fix --offline --dry-run
bin/a11y fix --offline

# Gemini AI destekli onarım
bin/a11y fix

# Dosya değişikliklerini canlı izleme
bin/a11y watch resources/views
```

---

## 🏛️ Mevzuat ve Sektörel Ön Ayarlar (Presets)

| Ön Ayar (`--preset`) | Seviye | Kapsam |
| :--- | :---: | :--- |
| `wcag22-aa` | 4 | W3C WCAG 2.2 Level A & AA tam uyumluluk profili |
| `wcag22-aaa` | 5 | En katı W3C WCAG 2.2 Level AAA profili (tüm 175 kural) |
| `en301549` | 4 | Avrupa Birliği EN 301 549 kamu dijital erişilebilirlik standardı |
| `eaa2025` | 4 | European Accessibility Act (Direktif EU 2019/882) dijital hizmet profili |
| `section508` | 3 | ABD Revised Section 508 erişilebilirlik profili |
| `bitv2` | 5 | Almanya BITV 2.0 kamu erişilebilirlik standardı |
| `government` | 5 | Kamu portalları ve e-Devlet hizmetleri profili |
| `healthcare` | 5 | Sağlık ve hasta portalları profili (okunabilirlik ve hareket güvenliği odaklı) |
| `banking` | 4 | Dijital bankacılık ve finansal hizmetler profili |
| `education` | 4 | Üniversite, LMS ve akademik portallar (ADA Title II uyumlu) |
| `ecommerce` | 4 | E-Ticaret mağaza ve ödeme akışları profili |

---

## ⚙️ Yapılandırma (`a11y.yaml`) ve Politika Motoru

```yaml
paths:
  - resources/views
  - src/components

exclude_paths:
  - vendor
  - node_modules
  - storage
  - tests

level: 4
preset: wcag22-aa
format: console
baseline: a11y-baseline.json

extensions:
  - php
  - html
  - twig
  - blade.php
  - vue
  - jsx
  - tsx

# Organizasyon geneli yönetişim politikaları
policy:
  min_score: 90
  max_critical: 0
  max_errors: 0
  max_warnings: 10
  required_standards:
    - A
  banned_rules:
    - WCAG_1_1_1_ALT
    - WCAG_2_3_1_FLASHING_CONTENT
  severity_overrides:
    WCAG_2_5_8_TARGET_SIZE: error
  principle_limits:
    Perceivable: 0

notifications:
  slack_webhook: ""
  discord_webhook: ""
  teams_webhook: ""
  webhook_url: ""
  webhook_secret: ""

rules:
  exclude:
    - WCAG_1_3_1_FIELDSET
  include:
    - App\Rules\CustomAccessibilityRule
```

---

## 🔇 Satır İçi Gizleme Yönergeleri (Inline Suppressions)

İstisnai durumlarda belirli satır veya bloklarda kuralı gerekçesiyle birlikte gizleyebilirsiniz; tüm gizlemeler denetim kaydına alınır:

```html
<!-- a11y-disable-next-line WCAG_1_1_1_ALT -- Dekoratif arka plan görseli -->
<img src="pattern.svg">

<!-- a11y-disable WCAG_1_4_8_TEXT_JUSTIFY -- Matbu belge önizleme bloğu -->
<p style="text-align: justify;">Resmi belge metni...</p>
<!-- a11y-enable WCAG_1_4_8_TEXT_JUSTIFY -->
```

---

## 🧩 PHP Programatik API

```php
// 1. Hızlı tarama ve skor hesaplama
$violations = a11y_scan($html, level: 4);
$score = a11y_score($html, level: 4);

// 2. Mevzuat ön ayarıyla tarama
$govViolations = a11y_scan_preset($html, 'government');

// 3. VPAT 2.5 raporu ve POUR uyumluluk matrisi üretme
$vpatMarkdown = a11y_vpat($html, format: 'markdown', productName: 'YakNet Portal');
$matrix = a11y_compliance_matrix($html, level: 5);

// 4. Çevrimdışı deterministik hızlı onarım
$fixedHtml = a11y_quick_fix($html);
```

---

## 🧪 Geliştirme ve Test

```bash
# Tüm PHPUnit testlerini çalıştır
vendor/bin/phpunit

# PHPStan Level 9 statik tip analizini çalıştır
vendor/bin/phpstan analyse -l 9 src/
```

---

## 🤝 Katkıda Bulunma

Bu proje açık kaynaklıdır ve her türlü katkıya açıktır:

1. Depoyu forklayın (`fork`).
2. Yeni bir özellik dalı açın (`git checkout -b feature/yeni-kural`).
3. Değişikliklerinizi ve testlerinizi ekleyin (`vendor/bin/phpunit` ve `vendor/bin/phpstan analyse -l 9 src/` ile doğrulayın).
4. Bir **Pull Request** açın.

---

## 📜 Lisans

Bu proje **YakNet** tarafından geliştirilmiştir ve **MIT Lisansı** altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına göz atabilirsiniz.
