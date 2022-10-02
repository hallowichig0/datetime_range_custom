/**
 * @file
 */

 (function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.datetime_range_custom = {
    attach: function (context, settings) {

      // Setting the current language for the calendar.
      var language = drupalSettings.path.currentLanguage;

      $(context).find('input[data-date-time-range-start]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget Type.
        var widgetType = input.data('dateTimeRangeStart');

        var DateTimeFormat = 'MM-DD-YYYY hh:mm a';

        var DateFormat = 'MM-DD-YYYY';

        // If field widget is Date Time.
        if (widgetType === 'datetime') {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: DateTimeFormat,
            lang: language,
            shortTime: true,
            clearButton: true,
            nowButton: true,
          });
        }

        // If field widget is Date only.
        else {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: DateFormat,
            lang: language,
            time: false,
            clearButton: true,
            nowButton: true,
          });
        }
      });

      $(context).find('input[data-date-time-range-end]').once('datePicker').each(function () {
        var input = $(this);

        // Get widget Type.
        var widgetType = input.data('dateTimeRangeEnd');
        
        var DateTimeFormat = 'MM-DD-YYYY hh:mm a';

        var DateFormat = 'MM-DD-YYYY';

        // If field widget is Date Time.
        if (widgetType === 'datetime') {
          // console.log(new Date($('#' + input.attr('id')).val()));
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: DateTimeFormat,
            shortTime: true,
            clearButton: true,
            nowButton: true,
          });
        }

        // If field widget is Date only.
        else {
          $('#' + input.attr('id')).bootstrapMaterialDatePicker({
            format: DateFormat,
            lang: language,
            time: false,
            clearButton: true,
            nowButton: true,
          });
        }
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
