(function ($) {
  'use strict';

  const refreshPreview = ($field, attachment) => {
    const $wrap = $field.find('.ssz-brand-admin__preview-wrap');
    $field.find('.ssz-brand-admin__image-id').val(attachment ? attachment.id : 0);
    $wrap.empty();
    if (attachment) {
      const url = attachment.sizes?.thumbnail?.url || attachment.url;
      $('<img>', { class: 'ssz-brand-admin__preview', src: url, alt: '' }).appendTo($wrap);
    }
  };

  $(document).on('click', '.ssz-brand-admin__select-image', function (event) {
    event.preventDefault();
    const $field = $(this).closest('.ssz-brand-admin-card');
    const frame = wp.media({
      title: 'Choose promotional image',
      button: { text: 'Use image' },
      library: { type: 'image' },
      multiple: false,
    });
    frame.on('select', function () {
      refreshPreview($field, frame.state().get('selection').first().toJSON());
    });
    frame.open();
  });

  $(document).on('click', '.ssz-brand-admin__remove-image', function (event) {
    event.preventDefault();
    refreshPreview($(this).closest('.ssz-brand-admin-card'), null);
  });
}(jQuery));
