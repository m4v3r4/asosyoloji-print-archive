(function (blocks, blockEditor, components, element, i18n, serverSideRender) {
  'use strict';

  const el = element.createElement;
  const InspectorControls = blockEditor.InspectorControls;
  const PanelBody = components.PanelBody;
  const TextControl = components.TextControl;
  const RangeControl = components.RangeControl;
  const SelectControl = components.SelectControl;
  const ToggleControl = components.ToggleControl;
  const ServerSideRender = serverSideRender;
  const __ = i18n.__;

  blocks.registerBlockType('asosyoloji/print-archive', {
    apiVersion: 3,
    title: __('Asosyoloji Basılı Yayınlar', 'asosyoloji-print-archive'),
    description: __('Basılı dergi sayılarını kapakları ve PDF bağlantılarıyla listeler.', 'asosyoloji-print-archive'),
    icon: 'media-document',
    category: 'widgets',
    attributes: {
      title: { type: 'string', default: 'Basılı Yayın Arşivi' },
      count: { type: 'number', default: 12 },
      layout: { type: 'string', default: 'grid' },
      order: { type: 'string', default: 'DESC' },
      showDescription: { type: 'boolean', default: false }
    },
    keywords: [
      __('dergi', 'asosyoloji-print-archive'),
      __('PDF', 'asosyoloji-print-archive'),
      __('arşiv', 'asosyoloji-print-archive')
    ],
    supports: {
      html: false,
      align: ['wide', 'full']
    },
    edit: function (props) {
      const attributes = props.attributes;
      const setAttributes = props.setAttributes;
      const blockProps = blockEditor.useBlockProps({
        className: 'asoprint-block-editor'
      });

      return el(
        'div',
        blockProps,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Basılı yayın ayarları', 'asosyoloji-print-archive'), initialOpen: true },
            el(TextControl, {
              label: __('Başlık', 'asosyoloji-print-archive'),
              value: attributes.title,
              onChange: function (value) {
                setAttributes({ title: value });
              }
            }),
            el(RangeControl, {
              label: __('Gösterilecek yayın sayısı', 'asosyoloji-print-archive'),
              value: attributes.count,
              min: 1,
              max: 50,
              onChange: function (value) {
                setAttributes({ count: value });
              }
            }),
            el(SelectControl, {
              label: __('Görünüm', 'asosyoloji-print-archive'),
              value: attributes.layout,
              options: [
                { label: __('Kapak grid', 'asosyoloji-print-archive'), value: 'grid' },
                { label: __('Liste', 'asosyoloji-print-archive'), value: 'list' }
              ],
              onChange: function (value) {
                setAttributes({ layout: value });
              }
            }),
            el(SelectControl, {
              label: __('Sıralama', 'asosyoloji-print-archive'),
              value: attributes.order,
              options: [
                { label: __('Yeniden eskiye', 'asosyoloji-print-archive'), value: 'DESC' },
                { label: __('Eskiden yeniye', 'asosyoloji-print-archive'), value: 'ASC' }
              ],
              onChange: function (value) {
                setAttributes({ order: value });
              }
            }),
            el(ToggleControl, {
              label: __('Kısa açıklamaları göster', 'asosyoloji-print-archive'),
              checked: attributes.showDescription,
              onChange: function (value) {
                setAttributes({ showDescription: value });
              }
            })
          )
        ),
        el(ServerSideRender, {
          block: 'asosyoloji/print-archive',
          attributes: attributes,
          EmptyResponsePlaceholder: function () {
            return el(
              'div',
              { className: 'components-placeholder' },
              el('div', { className: 'components-placeholder__label' }, __('Basılı Yayın Arşivi', 'asosyoloji-print-archive')),
              el('div', { className: 'components-placeholder__instructions' }, __('Önizleme için önce en az bir basılı yayın ekleyin.', 'asosyoloji-print-archive'))
            );
          }
        })
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n, window.wp.serverSideRender);
