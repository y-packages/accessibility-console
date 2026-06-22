# YakNet Accessibility Console 2.9 (AI-Powered Static Analysis)

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![AI Powered](https://img.shields.io/badge/AI-Powered-purple.svg)](https://gemini.google.com)

**YakNet Accessibility Console**, web projelerinizdeki erişilebilirlik (WCAG 2.1) hatalarını sadece tespit etmekle kalmayan, aynı zamanda **Yapay Zeka (AI)** desteğiyle bu hataları otomatik olarak düzeltebilen (Self-Healing) profesyonel bir PHP static analysis kütüphanesidir.

PHPStan ve PHP-CS-Fixer esintili yapısıyla, yerel görünüm şablonlarınızı (`.blade.php`, `.twig`, `.html`, `.php`) doğrudan tarayabilir, baseline desteğiyle mevcut hataları yok sayabilir ve CI/CD süreçlerinize tam entegre çalışabilir.

---

## 🌟 Öne Çıkan Özellikler

- **🤖 AI Self-Healing:** Tespit edilen WCAG hataları için Google Gemini AI üzerinden akıllı çözüm önerileri alır ve kaynak kodunuzu (Blade, Twig, PHP, HTML) otomatik olarak tamir eder.
- **⚡ PHPStan Tarzı Analiz:** Tüm projeyi veya belirli dizinleri komut satırından tarayarak hataları tam dosya yolu ve satır numarası ile listeler.
- **🛡️ Baseline Desteği:** `a11y-baseline.json` oluşturarak mevcut erişilebilirlik hatalarını yok sayabilir, CI/CD pipeline'ınızın sadece yeni hatalarda kırılmasını sağlayabilirsiniz.
- **⚙️ YAML Konfigürasyonu:** `a11y.yaml` dosyası üzerinden tarama yollarını, hariç tutulacak klasörleri, kural seviyelerini ve özel kural setlerini yapılandırabilirsiniz.
- **🔌 Çoklu Çıktı Formatları:**
  - `console`: Renkli ve düzenli terminal tablosu.
  - `json`: Makine tarafından okunabilir JSON çıktısı.
  - `github`: GitHub Actions PR süreçlerinde doğrudan inline hata göstermek için workflow formatı.
- **📊 Görsel Raporlama:** Sonuçları modern bir HTML Dashboard raporu olarak dışarı aktarın.

---

## 📦 Kurulum

Composer ile projenize dahil edin:

```bash
composer require yaknet/accessibility-console
```

---

## 🚀 CLI Kullanımı

### 1. Yapılandırma Dosyası Oluşturma (`init`)
Projenizin kök dizininde varsayılan bir `a11y.yaml` konfigürasyon dosyası oluşturur:
```bash
bin/a11y init
```

### 2. Projeyi Tarama (`scan` / `analyse`)
`a11y.yaml` dosyasındaki kurallara ve dizinlere göre tüm projeyi tarar:
```bash
bin/a11y scan
```
Belirli bir dizini veya dosyayı hedef göstermek için:
```bash
bin/a11y scan resources/views
```
URL veya tek bir yerel HTML dosyasını taramak için (crawler desteğiyle):
```bash
bin/a11y scan https://test-siteniz.com --crawl
```

### 3. Baseline Yönetimi
Projeye ait mevcut tüm hataları `a11y-baseline.json` dosyasına kaydederek sonraki taramalarda yok sayılmasını sağlar:
```bash
bin/a11y scan --generate-baseline
```

### 4. Format ve Raporlama Seçenekleri
Hataları GitHub Actions formatında yazdırmak için:
```bash
bin/a11y scan --format=github
```
HTML Raporu kaydetmek için:
```bash
bin/a11y scan --report=report.html
```

### 5. AI Destekli Otomatik Düzeltme (`fix`)
Tespit edilen tüm hataları yapay zekaya analiz ettirip dosyalarınızı otomatik olarak tamir ettirin:
```bash
bin/a11y fix
```
Sadece neyin değişeceğini görmek için (`--dry-run`):
```bash
bin/a11y fix --dry-run
```

### 6. Kuralları Listeleme (`rules`)
Mevcut tüm WCAG kurallarını seviyeleri ve açıklamalarıyla birlikte listeler:
```bash
bin/a11y rules
```

---

## ⚙️ Yapılandırma (`a11y.yaml`)

Projenizi özelleştirmek için `a11y.yaml` dosyasını kullanabilirsiniz:

```yaml
# Tarama yapılacak dizin veya dosyalar
paths:
  - resources/views
  - public/templates

# Hariç tutulacak dizinler
exclude_paths:
  - vendor
  - node_modules
  - storage

# WCAG seviyesi (1-9, varsayılan 4)
level: 4

# Varsayılan çıktı formatı (console, json, github)
format: console

# Baseline dosyası yolu
baseline: a11y-baseline.json

# Kural özelleştirmeleri
rules:
  exclude:
    - WCAG_1_3_1_FIELDSET # Bu kuralı çalıştırma
  include:
    - App\Rules\CustomAccessibilityRule # Özel kural ekle
```

---

## 🔐 Yapay Zeka API Anahtarı

AI düzeltme ve öneri özelliklerini kullanabilmek için projenizin kök dizininde bir `.env` dosyası oluşturup **Google Gemini API** anahtarınızı eklemeniz gerekir:

```env
GEMINI_API_KEY=AIzaSyA...your_key_here
```

---

## 🤝 Katkıda Bulunma

Bu proje açık kaynaklıdır ve katkılarınıza açıktır. Lütfen hata bildirimleri için `Issue` açmaktan veya yeni özellikler için `Pull Request` göndermekten çekinmeyin.

## 📜 Lisans

Bu yazılım **YakNet Bilişim** tarafından geliştirilmiştir ve **MIT Lisansı** altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına göz atabilirsiniz.