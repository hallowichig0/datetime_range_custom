<?php

namespace Drupal\datetime_range_custom\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Provides a MaterializeDateTime form element.
 *
 * @FormElement("date_time_range_end")
 */
class DatetimeRangeCustomEnd extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {

    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#maxlength' => 512,
      '#size' => 25,
      '#process' => [[$class, 'processDatetimeRangeCustom']],
      '#pre_render' => [[$class, 'preRenderDatetimeRangeCustom']],
      '#theme_wrappers' => ['form_element'],
      '#theme' => 'input__textfield',
    ];

  }

  /**
   * Render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderDatetimeRangeCustom(array $element) {
    Element::setAttributes($element, ['id', 'name', 'value', 'size']);
    static::setAttributes($element, ['form-date']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processDatetimeRangeCustom(&$element, FormStateInterface $form_state, &$complete_form) {
    // Get system regional settings.
    $first_day = \Drupal::config('system.date')->get('first_day');

    // Get date format value.
    // $date_format = $element['#date_format'];

    // Default settings.
    $settings = [
      'data-first-day' => $first_day,
      // 'data-date-format' => $date_format,
    ];

    // Push field type to JS for changing between date only and time fields.
    // Difference between date and date range fields.
    if (isset($element['#date_type'])) {
      $settings['data-date-time-range-end'] = $element['#date_type'];
    }

    else {
      // Combine date range formats.
      $range_date_type = $element['#date_date_element'] . $element['#date_time_element'];
      $settings['data-date-time-range-end'] = $range_date_type;
    }

    // Append our attributes to element.
    $element['#attributes'] += $settings;
    $element['#attributes']['class'] = ['form-control'];

    // Prefix and Suffix.
    $element['#prefix'] = "<div class='container'>
    <div class='row'>
        <div class='col-sm-6'>";
    $element['#suffix'] = "</div></div></div>";

    // Attach library.
    $complete_form['#attached']['library'][] = 'datetime_range_custom/datetime_range_custom';

    return $element;
  }

  /**
   * Return default settings. Pass in values to override defaults.
   *
   * @param array $values
   *   Some Desc.
   *
   * @return array
   *   Some Desc.
   */
  public static function settings(array $values = []) {
    $settings = [
      'lang' => 'en',
    ];

    return array_merge($settings, $values);
  }

}
