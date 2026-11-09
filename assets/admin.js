(function ($) {
  'use strict';

  $(document).on('click', '[data-asoprint-select-pdf]', function (event) {
    event.preventDefault();

    const field = $(this).closest('[data-asoprint-pdf-field]');
    const frame = wp.media({
      title: 'PDF belgesi seç',
      button: { text: 'Bu PDF’yi kullan' },
      library: { type: 'application/pdf' },
      multiple: false
    });

    frame.on('select', function () {
      const attachment = frame.state().get('selection').first().toJSON();
      field.find('[data-asoprint-pdf-id]').val(attachment.id || 0);
      field.find('[data-asoprint-pdf-name]').text(attachment.filename || attachment.title || 'PDF');
      field.find('[data-asoprint-remove-pdf]').prop('hidden', false);
      field.find('[data-asoprint-open-pdf]').attr('href', attachment.url || '#').prop('hidden', false);
    });

    frame.open();
  });

  $(document).on('click', '[data-asoprint-remove-pdf]', function (event) {
    event.preventDefault();
    const field = $(this).closest('[data-asoprint-pdf-field]');
    field.find('[data-asoprint-pdf-id]').val('0');
    field.find('[data-asoprint-pdf-name]').text('Henüz PDF seçilmedi.');
    field.find('[data-asoprint-open-pdf]').attr('href', '#').prop('hidden', true);
    $(this).prop('hidden', true);
  });
})(jQuery);

