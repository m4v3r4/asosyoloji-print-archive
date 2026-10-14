# Asosyoloji Basılı Yayın Arşivi

Basılı dergi sayılarını WordPress içinde ayrı kayıtlar halinde saklar ve PDF arşivi olarak yayınlar.

## Özellikler

- Basılı Yayınlar yönetim menüsü
- Başlık, sayı, yayın tarihi, kapak görseli ve PDF belgesi
- PDF için WordPress Medya Kütüphanesi seçicisi ve dosya türü doğrulaması
- Sayfaları sürükleyerek çevrilebilen, ekran dönüşünde kendini yeniden ölçen ve mobil kontrollere sahip 3D PDF okuyucu
- Mobilde açılır sayfa önizlemeleri, başparmak erişimine uygun gezinme ve taşmasız tek sayfa görünümü
- Son kalınan sayfayı tarayıcıda hatırlama, %100–200 yakınlaştırma ve küçük sayfa önizlemeleri
- Eski yayın içeriklerindeki PDF bloklarını otomatik tanıma
- Standart WordPress/Apache sunucularında ek MIME yapılandırması gerektirmeyen yerel PDF motoru
- Otomatik `/basili-yayinlar/` arşiv sayfası
- Grid veya liste görünümüne sahip klasik widget
- Sayfa ve yazılara görsel önizlemeyle eklenebilen Gutenberg bloğu
- Sayfa ve yazılarda kullanılabilen kısa kod
- Asosyoloji temasının açık/koyu görünümü ve `#540048` marka rengiyle uyumlu responsive tasarım
- Tema içindeki eski manuel basılı sayılar widgetını, eklenti etkin olduğu sürece gizleme
- GitHub Release üzerinden WordPress yönetim panelinde otomatik güncelleme
- Manuel “Güncellemeleri tekrar kontrol et” işleminde sürüm önbelleğini anında yenileme

## Kullanım

1. Eklentiyi kurup etkinleştirin.
2. Yönetim menüsünden **Basılı Yayınlar → Yeni yayın ekle** seçeneğine gidin.
3. Başlık, sayı ve tarihi girin; PDF belgesini seçin. İsterseniz kapak görseli belirleyin.
4. **Görünüm → Bileşenler** bölümünden **Asosyoloji: Basılı Yayın Arşivi** widgetını ekleyin.

Blok editöründe **Blok ekle → Asosyoloji Basılı Yayınlar** seçeneğini kullanabilirsiniz. Sağ panelden başlığı, gösterilecek yayın sayısını, grid/liste görünümünü, sıralamayı ve açıklamaları düzenleyebilirsiniz.

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

- WordPress 6.3+
- PHP 8.0+

## Dahil edilen açık kaynak kütüphaneler

- PDF.js 5.4.394 — Apache License 2.0
- StPageFlip 2.0.7 — MIT License

Lisans metinleri `assets/vendor` klasöründeki ilgili kütüphane dizinlerinde yer alır.

