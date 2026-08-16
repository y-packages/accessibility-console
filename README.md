# YakNet Accessibility Console 3.0 (AI-Powered Static Analysis & WCAG 2.2 Linter)

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-brightgreen.svg)](https://phpstan.org)
[![Tests](https://img.shields.io/badge/PHPUnit-191%20passed-success.svg)](https://phpunit.de)
[![Rules](https://img.shields.io/badge/WCAG%20Rules-127%20Active-purple.svg)](https://www.w3.org/WAI/standards-guidelines/wcag/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![AI Powered](https://img.shields.io/badge/AI%20Self--Healing-Google%20Gemini-orange.svg)](https://ai.google.dev)

**YakNet Accessibility Console**, web projelerinizdeki erişilebilirlik (WCAG 2.1, 2.2 ve WAI-ARIA) ve HTML söz dizimi hatalarını statik olarak analiz eden, **127 benzersiz kural setiyle** denetleyen ve **Yapay Zeka (Google Gemini AI)** desteğiyle kaynak kodunuzu otomatik tamir edebilen (Self-Healing) kurumsal düzeyde bir PHP statik analiz ve linter kütüphanesidir.

PHPStan ve PHP-CS-Fixer esintili modern mimarisiyle; Laravel Blade (`.blade.php`), Twig (`.twig`), PHP (`.php`) ve standart HTML şablonlarınızı doğrudan tarar, baseline desteğiyle mevcut teknik borcu yönetir ve CI/CD pipeline süreçlerinize sıfır yapılandırmayla entegre olur.

---

## 🌟 Öne Çıkan Özellikler

- **🛡️ 127 Benzersiz WCAG 2.1 / 2.2 & WAI-ARIA Kuralı:** Algılanabilir (Perceivable), Çalıştırılabilir (Operable), Anlaşılabilir (Understandable) ve Sağlam (Robust) prensiplerine göre yapılandırılmış 5 kademeli (Level 1-5) kural havuzu.
- **🔍 HTML & Şablon Söz Dizimi (Syntax/AST) Linter'ı:**
  - Aynı etiket üzerinde yinelenen nitelikler (`Duplicate Attributes` - HTML5 3.2.4 ihlali)
  - Kapanmamış veya çapraz kapanmış yapısal etiketler (`Mismatched / Unclosed Tags`)
  - Void olmayan etiketlerde geçersiz self-closing kullanımı (`<div />`, `<span />`, `<p />`)
  - Boşluksuz bitişik nitelik söz dizimi (`<a href="..."class="...">`)
  - Kapanmamış HTML yorum blokları (`<!--`)
- **⚡ Tek Geçişli (Single-Pass) Yüksek Hızlı Kural Motoru:** DOM ağacını tek bir geçişte tarayarak büyük şablonlarda 5-10 kat daha hızlı analiz gerçekleştirir.
- **📝 1:1 Satır Eşlemeli Şablon Ön İşleyici (Template Preprocessor):** Blade direktiflerini (`@if`, `@foreach`, `@component`), Twig bloklarını (`{% %}`, `{{ }}`) ve PHP etiketlerini temizlerken satır sayılarını birebir korur.
- **🎯 Yüksek Hassasiyetli Satır & Sütun Bulucu (`PreciseElementLocator`):** Çok satırlı ve dinamik şablon bileşenlerinde ihlalin kaynak koddaki tam satır ve sütun numarasını saptar.
- **🤖 Yapay Zeka ile Otomatik Onarım (AI Self-Healing):** Tespit edilen WCAG ve söz dizimi hataları için Google Gemini AI üzerinden akıllı çözüm ve tamir kodları üretir; kaynak dosyalarınızı (`--dry-run` desteğiyle) anında onarır.
- **📊 Erişilebilirlik Analitiği & Uyumluluk Matrisi:** POUR prensipleri, WCAG A/AA/AAA başarı oranları ve KLOC başına hata yoğunluğu (Defect Density) istatistikleri.
- **🛡️ Baseline Desteği (`a11y-baseline.json`):** Mevcut hataları dondurarak CI/CD süreçlerinin yalnızca **yeni eklenen** hatalarda kırılmasını sağlar.
- **🔌 Çoklu Çıktı Formatları:** Renkli konsol tablosu (`console`), makine tarafından okunabilir JSON (`json`), GitHub Actions PR bildirimleri (`github`) ve interaktif HTML Dashboard raporu (`--report`).
- **🏆 PHPStan Level 9 & %100 Tip Güvenliği:** Kod tabanı PHPStan en yüksek seviye (Level 9) ve 191+ birim testi ile tam doğrulanmıştır.

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

### 3. Kural Seviyesi Belirleme (`--level`)
1 (Temel) ile 5 (Çok Katı / Strict) arasında bir seviye belirleyin:
```bash
bin/a11y scan --level=5
```

### 4. Baseline (Teknik Borç Dondurma) Yönetimi
Mevcut tüm ihlalleri `a11y-baseline.json` dosyasına kaydederek sonraki taramalarda yok sayılmasını sağlar:
```bash
bin/a11y scan --generate-baseline
```

### 5. Format ve Raporlama Seçenekleri
- **GitHub Actions formatında çıktı:**
  ```bash
  bin/a11y scan --format=github
  ```
- **JSON formatında çıktı:**
  ```bash
  bin/a11y scan --format=json
  ```
- **İnteraktif HTML Dashboard Raporu:**
  ```bash
  bin/a11y scan --report=a11y-report.html
  ```

### 6. Yapay Zeka Destekli Otomatik Düzeltme (`fix`)
Tespit edilen erişilebilirlik sorunlarını yapay zeka ile kaynak dosyalarınızda otomatik olarak onarın:
```bash
bin/a11y fix
```
Dosyaları değiştirmeden yalnızca yapılacak düzeltme önerilerini görmek için:
```bash
bin/a11y fix --dry-run
```

### 7. Kuralları Listeleme (`rules`)
Kütüphanede tanımlı 127 kuralı seviyeleri, WCAG standartları ve açıklamalarıyla listeler:
```bash
bin/a11y rules
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