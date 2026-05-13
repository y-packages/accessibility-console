# YakNet Accessibility Console 2.0 (AI-Powered)

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![AI Powered](https://img.shields.io/badge/AI-Powered-purple.svg)](https://gemini.google.com)

**YakNet Accessibility Console**, web projelerinizdeki erişilebilirlik (WCAG 2.1) hatalarını sadece tespit etmekle kalmayan, aynı zamanda **Yapay Zeka (AI)** desteğiyle bu hataları otomatik olarak düzeltebilen (Self-Healing) profesyonel bir PHP kütüphanesidir.

## 🌟 Öne Çıkan Özellikler

- **🤖 AI Self-Healing:** Tespit edilen WCAG hataları için Google Gemini AI üzerinden akıllı çözüm önerileri alır ve isterseniz kaynak kodunuzu (Blade, Twig, PHP) otomatik olarak tamir eder.
- **⚡ Modern CLI:** Rust/Vite tarzı şık bir komut satırı arayüzü ile saniyeler içinde tarama ve düzeltme yapın.
- **📊 Görsel Dashboard:** Tarama sonuçlarını modern ve şık bir HTML raporu olarak dışarı aktarın.
- **🔍 Akıllı Kaynak Tespiti:** Hataları projenizdeki tam dosya yolu ve satır numarası ile eşleştirir.
- **🛠️ Geliştirici Dostu:** PSR-4 uyumlu, modern PHP 8.2+ mimarisi ve genişletilebilir kural motoru.

## 📦 Kurulum

Composer ile projenize hemen dahil edin:

```bash
composer require yaknet/accessibility-console
```

## 🚀 CLI Kullanımı

Kütüphane, `vendor/bin/a11y` (veya kütüphane dizininde `bin/a11y`) üzerinden kullanılabilir.

### 1. Standart Tarama
Bir URL'yi veya yerel bir HTML dosyasını tarayın:
```bash
bin/a11y scan https://test-siteniz.com
```

### 2. AI Destekli Tarama ve Raporlama
Yapay zeka önerileriyle birlikte tarama yapın ve sonucu HTML olarak kaydedin:
```bash
bin/a11y scan https://test-siteniz.com --ai --report=sonuc.html
```

### 3. Otomatik Düzeltme (Self-Healing) - **DEVRİMSEL**
Tespit edilen hataları yapay zekaya düzelttirin ve dosyalarınızı güncelleyin:
```bash
bin/a11y fix https://test-siteniz.com --project-path=/var/www/projeniz
```

## 💻 Kütüphane Olarak Kullanım

Kendi PHP uygulamalarınızın içine entegre edin:

```php
use YakNet\AccessibilityConsole\Core\Scanner;
use YakNet\AccessibilityConsole\Rules\StandardRuleSet;

// 1. Scanner'ı ve Kuralları Hazırla
$scanner = new Scanner();
foreach (StandardRuleSet::all() as $rule) {
    $scanner->addRule($rule);
}

// 2. HTML İçeriğini Tara
$html = file_get_contents('view.html');
$violations = $scanner->scan($html);

// 3. Sonuçları İşle
foreach ($violations as $v) {
    echo "Hata: {$v->message} (Kural: {$v->ruleId})\n";
}
```

## ⚙️ Yapılandırma

AI özelliklerini kullanabilmek için projenizin kök dizininde bir `.env` dosyası oluşturun ve **Google Gemini API** anahtarınızı ekleyin:

```env
GEMINI_API_KEY=AIzaSyA...your_key_here
```

## 🛠️ Mevcut Kurallar (WCAG 2.1)

- **A11Y_VIEWPORT:** Zoom engelleme kontrolü.
- **WCAG_1_1_1_ALT:** Görsel alt metin kontrolü.
- **WCAG_1_3_1_LABEL:** Form etiketi kontrolü.
- **WCAG_1_3_1_FIELDSET:** Fieldset/Legend kontrolü.
- **WCAG_1_3_1_HEADING:** Başlık hiyerarşisi kontrolü.
- **WCAG_2_4_4_LINK_TEXT:** Boş link kontrolü.
- **WCAG_3_1_1_LANG:** HTML dil etiketi kontrolü.
- **WCAG_4_1_2_BUTTON:** Boş buton kontrolü.

## 🤝 Katkıda Bulunma

Bu proje açık kaynaklıdır ve katkılarınıza açıktır. Lütfen hata bildirimleri için `Issue` açmaktan veya yeni özellikler için `Pull Request` göndermekten çekinmeyin.

## 📜 Lisans

Bu yazılım **YakNet Bilişim** tarafından geliştirilmiştir ve **MIT Lisansı** altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına göz atabilirsiniz.