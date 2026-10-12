# Asosyoloji Basılı Yayın Arşivi

Basılı dergi sayılarını WordPress içinde ayrı kayıtlar halinde saklar ve PDF arşivi olarak yayınlar.

## Özellikler

- Basılı Yayınlar yönetim menüsü
- Başlık, sayı, yayın tarihi, kapak görseli ve PDF belgesi
- PDF için WordPress Medya Kütüphanesi seçicisi ve dosya türü doğrulaması
- Otomatik `/basili-yayinlar/` arşiv sayfası
- Grid veya liste görünümüne sahip klasik widget
- Sayfa ve yazılarda kullanılabilen kısa kod
- Asosyoloji temasının açık/koyu görünümü ve `#540048` marka rengiyle uyumlu responsive tasarım
- Tema içindeki eski manuel basılı sayılar widgetını, eklenti etkin olduğu sürece gizleme

## Kullanım

1. Eklentiyi kurup etkinleştirin.
2. Yönetim menüsünden **Basılı Yayınlar → Yeni yayın ekle** seçeneğine gidin.
3. Başlık, sayı ve tarihi girin; PDF belgesini seçin. İsterseniz kapak görseli belirleyin.
4. **Görünüm → Bileşenler** bölümünden **Asosyoloji: Basılı Yayın Arşivi** widgetını ekleyin.

Bir sayfanın içeriğine eklemek için:

```text
[asosyoloji_basili_yayinlar]
```

Örnek seçenekler:

```text
[asosyoloji_basili_yayinlar title="Dergi Arşivi" count="12" layout="grid" order="DESC"]
```

`layout` değeri `grid` veya `list`; `order` değeri `DESC` veya `ASC` olabilir. Açıklamayı göstermek için `description="1"` kullanın.

## Gereksinimler

- WordPress 6.0+
- PHP 8.0+

