# YakNet Accessibility Console 3.3 (AI-Powered Static Analysis & WCAG 2.2 Linter)

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-brightgreen.svg)](https://phpstan.org)
[![Tests](https://img.shields.io/badge/PHPUnit-230%20passed-success.svg)](https://phpunit.de)
[![Rules](https://img.shields.io/badge/WCAG%20Rules-130%20Active-purple.svg)](https://www.w3.org/WAI/standards-guidelines/wcag/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![AI Powered](https://img.shields.io/badge/AI%20Self--Healing-Google%20Gemini-orange.svg)](https://ai.google.dev)

**YakNet Accessibility Console**, web projelerinizdeki erişilebilirlik (WCAG 2.1, 2.2 ve WAI-ARIA) ve HTML söz dizimi hatalarını statik olarak analiz eden, **130 benzersiz kural setiyle** denetleyen, kurumsal CI/CD kalite kapısı (Quality Gates) sunan ve **Yapay Zeka (Google Gemini AI) ile Çevrimdışı Deterministik Motor** desteğiyle kaynak kodunuzu otomatik tamir edebilen (Self-Healing) kurumsal düzeyde bir PHP statik analiz ve linter kütüphanesidir.

PHPStan ve PHP-CS-Fixer esintili modern mimarisiyle; Laravel Blade (`.blade.php`), Twig (`.twig`), PHP (`.php`) ve standart HTML şablonlarınızı doğrudan tarar, baseline desteğiyle mevcut teknik borcu yönetir ve CI/CD pipeline süreçlerinize sıfır yapılandırmayla entegre olur.

---

## 🌟 Öne Çıkan Özellikler

- **🛡️ 130 Benzersiz WCAG 2.1 / 2.2 & WAI-ARIA Kuralı:** Algılanabilir (Perceivable), Çalıştırılabilir (Operable), Anlaşılabilir (Understandable) ve Sağlam (Robust) prensiplerine göre yapılandırılmış 5 kademeli kural havuzu. (Yeni: `LabelInName` WCAG 2.5.3, `RedundantEntry` WCAG 3.3.7, `TextSpacing` WCAG 1.4.12).
- **🏛️ Sektörel ve Mevzuat Ön Ayarları (Presets):** US Section 508, Avrupa Birliği EN 301 549 ve E-Ticaret (`--preset=section508|en301549|ecommerce`) profilleriyle hedeflenmiş standart denetimi.
- **🏷️ Dinamik SVG Rozet Üretimi (Badges):** Tarama tamamlandığında sağlık skoruna göre (yeşil/sarı/kırmızı) canlı SVG rozet (`--badge=public/a11y-badge.svg`) üretir.
- **📢 CI/CD Webhook Bildirimleri (Notifications):** Tarama sonuçlarını Slack ve Discord webhook kanallarına otomatik formatlanmış zengin mesajlarla iletir.
- **⚙️ Programatik Kural Denetleyicisi & İstatistikler:** `RuleController` ile DOM elementleri üzerinde doğrudan tekil kural yürütme ve `RuleExecutionStats` ile kural performans/yürütme süresi izleme.
- **⏱️ Gerçek Zamanlı İzleme Modu (`watch`):** Şablon dosyalarını (`.blade.php`, `.twig`, `.html`) arka planda izler, dosya kaydedildiğinde anında hızlı analiz yapıp konsolda canlı skor ve geri bildirim sunar.
- **🚦 CI/CD Kalite Kapısı Eşikleri (Quality Gates):** `--min-score=90`, `--max-warnings=5`, `--fail-on=error` seçenekleriyle pipeline süreçlerinde esnek kalite denetimi sağlar.
- **🔍 HTML & Şablon Söz Dizimi (Syntax/AST) Linter'ı:**
  - Aynı etiket üzerinde yinelenen nitelikler (`Duplicate Attributes` - HTML5 3.2.4 ihlali)
  - Kapanmamış veya çapraz kapanmış yapısal etiketler (`Mismatched / Unclosed Tags`)
  - Void olmayan etiketlerde geçersiz self-closing kullanımı (`<div />`, `<span />`, `<p />`)
  - Boşluksuz bitişik nitelik söz dizimi (`<a href="..."class="...">`)
  - Kapanmamış HTML yorum blokları (`<!--`)
- **⚡ Tek Geçişli (Single-Pass) Yüksek Hızlı Kural Motoru:** DOM ağacını tek bir geçişte tarayarak büyük şablonlarda 5-10 kat daha hızlı analiz gerçekleştirir.
- **📝 1:1 Satır Eşlemeli Şablon Ön İşleyici (Template Preprocessor):** Blade direktiflerini (`@if`, `@foreach`, `@component`), Twig bloklarını (`{% %}`, `{{ }}`) ve PHP etiketlerini temizlerken satır sayılarını birebir korur.
- **🎯 Yüksek Hassasiyetli Satır & Sütun Bulucu (`PreciseElementLocator`):** Çok satırlı ve dinamik şablon bileşenlerinde ihlalin kaynak koddaki tam satır ve sütun numarasını saptar.
- **🤖 Yapay Zeka & Çevrimdışı Otomatik Onarım (Self-Healing):**
  - **Çevrimdışı (Offline) Mod:** API anahtarı olmadan güvenli deterministik tamirler (`alt=""`, `aria-hidden="true"`, `rel="noopener noreferrer"`, `tabindex="0"`, `scope` temizliği vb.).
  - **AI Destekli Mod:** Karmaşık şablon mantığı ve etiketler için Google Gemini AI ile akıllı tamir.
- **📊 Erişilebilirlik Analitiği & Uyumluluk Matrisi:** POUR prensipleri, WCAG A/AA/AAA başarı oranları ve KLOC başına hata yoğunluğu (Defect Density) istatistikleri.
- **🛡️ Baseline Desteği (`a11y-baseline.json`):** Mevcut hataları dondurarak CI/CD süreçlerinin yalnızca **yeni eklenen** hatalarda kırılmasını sağlar.
- **🔌 Zengin Çıktı & Rapor Formatları:**
  - Konsol tablosu (`console`), JSON (`json`), GitHub Actions (`github`), GitLab SAST (`gitlab`)
  - **OASIS SARIF 2.1.0 (`sarif`):** GitHub Code Scanning / Security Alerts ve VS Code SARIF uyumlu.
  - **JUnit XML (`junit`):** Jenkins, GitLab CI, CircleCI, Bitbucket Pipelines uyumlu.
  - **Çoklu Dışa Aktarım (`--report`):** HTML (`.html`), JSON (`.json`), SARIF (`.sarif`), JUnit (`.xml`), PDF (`.pdf`) ve Excel (`.xlsx`).
- **🏆 PHPStan Level 9 & %100 Tip Güvenliği:** Kod tabanı PHPStan en yüksek seviye (Level 9) ve 230 birim testi ile tam doğrulanmıştır.

---

## 📦 Kurulum

Composer ile projenize geliştirme bağımlılığı olarak ekleyin:

```bash
composer require --dev yaknet/accessibility-console
```

---

## 🚀 Komut Satırı (CLI) Kullanımı

### 1. Yapılandırma Dosyası Oluşturma (`init`)
Projenizin kök dizininde varsayılan bir `a11y.yaml` konfigürasyon dosyası oluşturur:
```bash
bin/a11y init
```

### 2. Projeyi veya Dizinleri Tarama (`scan`)
Yapılandırma dosyasındaki yollara göre tüm projeyi tarar:
```bash
bin/a11y scan
```
Belirli bir dizini veya şablon klasörünü hedef göstererek taramak için:
```bash
bin/a11y scan resources/views
bin/a11y scan templates/
```
Canlı bir web sitesini crawler desteğiyle taramak için:
```bash
bin/a11y scan https://siteniz.com --crawl
```

### 3. CI/CD Kalite Kapısı Eşikleri (Quality Gates)
Pipeline süreçlerinde derlemenin kırılma şartlarını belirleyin:
```bash
# Sağlık skoru 90'ın altındaysa derlemeyi başarısız yap:
bin/a11y scan --min-score=90

# En fazla 3 uyarıya izin ver:
bin/a11y scan --max-warnings=3

# Yalnızca kritik ve hata seviyesindeki ihlallerde kırıl (uyarıları göz ardı et):
bin/a11y scan --fail-on=error
```

### 4. Gerçek Zamanlı İzleme Modu (`watch`)
Geliştirme yaparken şablon dosyalarınızı izleyin ve dosya kaydedildiğinde anında otomatik re-scan çalıştırın:
```bash
bin/a11y watch resources/views
```

### 5. Format ve Raporlama Seçenekleri
- **GitHub Code Scanning / SARIF 2.1.0 formatı:**
  ```bash
  bin/a11y scan --format=sarif
  ```
- **JUnit XML formatı (Jenkins, GitLab CI):**
  ```bash
  bin/a11y scan --format=junit
  ```
- **GitHub Actions bildirim formatı:**
  ```bash
  bin/a11y scan --format=github
  ```
- **JSON formatında çıktı:**
  ```bash
  bin/a11y scan --format=json
  ```
- **Çoklu Dışa Aktarım Formatları (`--report`):**
  ```bash
  bin/a11y scan --report=rapor.html     # İnteraktif HTML Dashboard
  bin/a11y scan --report=rapor.pdf      # PDF Sertifika ve Denetim Raporu
  bin/a11y scan --report=rapor.xlsx     # Microsoft Excel Çalışma Kitabı
  bin/a11y scan --report=rapor.sarif    # SARIF 2.1.0 Dosyası
  bin/a11y scan --report=rapor.xml      # JUnit XML Raporu
  ```

### 6. Mevzuat Ön Ayarları (`--preset`) ve Rozet Üretimi (`--badge`)
Mevzuat veya sektörel kurallara göre hedeflenmiş tarama yapın ve canlı SVG rozet oluşturun:
```bash
# US Section 508 federal uyumluluk profiliyle tara:
bin/a11y scan --preset=section508

# Avrupa Birliği EN 301 549 kamu web standardı profiliyle tara:
bin/a11y scan --preset=en301549

# E-Ticaret ödeme ve sepet akışları profiliyle tara:
bin/a11y scan --preset=ecommerce

# Tarama sonucunu SVG rozet olarak kaydet (README veya CI/CD artifact için):
bin/a11y scan --badge=public/badges/a11y.svg
```

### 7. Otomatik Düzeltme (`fix`)
Yapay zeka veya çevrimdışı yerel motor ile kaynak dosyalarınızı onarın:
```bash
# Gemini AI desteğiyle otomatik onarım:
bin/a11y fix

# Gemini API anahtarı olmadan tamamen çevrimdışı (offline) yerel motorla onarım:
bin/a11y fix --offline

# Dosyaları değiştirmeden yapılacak düzeltmeleri önizleme:
bin/a11y fix --offline --dry-run
```

### 8. Kuralları Listeleme ve Filtreleme (`rules`)
Kütüphanede tanımlı 130 kuralı listeleyin, arayın ve filtreleyin:
```bash
# Tüm kuralları listele:
bin/a11y rules

# Kural adına veya açıklamasına göre ara:
bin/a11y rules --search=contrast

# Seviye ve standarda göre filtrele:
bin/a11y rules --standard=AA --level=3
```

---

## ⚙️ Yapılandırma Dosyası (`a11y.yaml`)

Projenizin ihtiyaçlarına göre `a11y.yaml` dosyasını özelleştirebilirsiniz:

```yaml
# Tarama yapılacak dizin veya şablon yolları
paths:
  - resources/views
  - templates
  - public/partials

# Hariç tutulacak klasörler
exclude_paths:
  - vendor
  - node_modules
  - storage
  - tests/fixtures

# Kural Seviyesi (1: Kritik A -> 5: Kapsamlı AA/AAA)
level: 4

# Varsayılan çıktı formatı (console, json, github)
format: console

# Baseline dosyası yolu
baseline: a11y-baseline.json

# Kural istisnaları ve özel kurallar
rules:
  exclude:
    - WCAG_1_3_1_FIELDSET # Belirli bir kuralı devre dışı bırak
  include:
    - App\Rules\CustomBrandAccessibilityRule # Özel proje kuralı ekle
```

---

## 📐 Kural Seviyeleri (Rule Levels)

| Seviye | Adı | Kapsam | Örnek Kurallar |
| :--- | :--- | :--- | :--- |
| **Level 1** | Essential | Temel WCAG 2.1 A | `ImageAlt`, `ButtonName`, `HtmlHasLang`, `PageTitle` |
| **Level 2** | Standard | Standart WCAG 2.1/2.2 A & Söz Dizimi | `HtmlSyntaxValid`, `FormLabel`, `HeadingOrder`, `TrackValidation` |
| **Level 3** | Enhanced | Gelişmiş WAI-ARIA & Formlar | `DuplicateId`, `NestedInteractive`, `AriaCurrentValid`, `AccessibleAuth` |
| **Level 4** | Advanced | Kapsamlı WCAG 2.2 AA & UX | `TargetSizeMinimum`, `DraggingMovements`, `FocusNotObscured`, `ColorContrast` |
| **Level 5** | Strict | Çok Katı AAA & Best Practices | `FocusTrapping`, `ColorBlindnessContrast`, `NoFocusOutline` |

---

## 🔐 Yapay Zeka (AI) API Yapılandırması

AI Self-Healing ve otomatik kod onarım özelliklerini kullanabilmek için projenizin kök dizinindeki `.env` dosyasına **Google Gemini API** anahtarınızı ekleyin:

```env
GEMINI_API_KEY=AIzaSyA...your_api_key_here
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

1. Bu depoyu forklayın (`fork`).
2. Yeni bir özellik dalı açın (`git checkout -b feature/harika-kural`).
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Yeni WCAG kuralı eklendi'`).
4. Dalınıza push yapın (`git push origin feature/harika-kural`).
5. Bir **Pull Request** açın.

---

## 📜 Lisans

Bu proje **YakNet Bilişim** tarafından geliştirilmiştir ve **MIT Lisansı** altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına göz atabilirsiniz.