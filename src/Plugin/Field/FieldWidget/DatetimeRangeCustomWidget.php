<?php

namespace Drupal\datetime_range_custom\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase;

/**
 * Plugin implementation of the MaterializeDateTimeWidget widget.
 *
 * @FieldWidget(
 *   id = "datetime_range_custom_widget",
 *   label = @Translation("DateTime Range Custom"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DatetimeRangeCustomWidget extends DateRangeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateStorage = $date_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    
    // Field type.
    $element['value'] = [
      '#title' => $this->t('Start date'),
      '#type' => 'date_time_range_start',
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => NULL,
      '#date_type' => NULL,
      '#required' => $element['#required'],
    ];
    // Field type.
    $element['end_value'] = [
      '#title' => $this->t('End date'),
      '#type' => 'date_time_range_end',
      '#date_timezone' => date_default_timezone_get(),
      '#default_value' => NULL,
      '#date_type' => NULL,
      '#required' => $element['#required'],
    ];
    $element['#element_validate'][] = [$this, 'validateStartEnd'];

    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateRangeItem::DATETIME_TYPE_DATE: // date

        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $format = 'm-d-Y';

        // Type of the field.
        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');

        // A date-only field should have no timezone conversion performed, so
        // use the same timezone as for storage.
        $element['end_value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // If field is date only, use default time format.
        $end_value_format = 'm-d-Y';

        // Type of the field.
        $element['end_value']['#date_type'] = $this->getFieldSetting('datetime_type');
        break;

      default: // datetime

        // Type of the field.
        $element['value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        // $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
        $format = 'm-d-Y h:i a';

        $element['value']['#date_type'] = $this->getFieldSetting('datetime_type');

        // Type of the field.
        $element['end_value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;

        // Assign the time format, because time will be saved in 24hrs format
        // in database.
        $end_value_format = 'm-d-Y h:i a';
        // $end_value_format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;

        // dpm($drupal_date_time);

        // Echo $end_value_format;exit;.
        $element['end_value']['#date_type'] = $this->getFieldSetting('datetime_type');
        break;
    }

    if ($items[$delta]->start_date) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
      $start_date = $items[$delta]->start_date;
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      if ($this->getFieldSetting('datetime_type') == DateRangeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        $start_date->setDefaultDateTime();
      }

      // Manual define form for input field.
      $element['value']['#default_value'] = $start_date->format($format);
    }
    if ($items[$delta]->end_date) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
      $end_date = $items[$delta]->end_date;
      if ($this->getFieldSetting('datetime_type') == DateRangeItem::DATETIME_TYPE_DATE) {
        // A date without time will pick up the current time, use the default
        // time.
        $end_date->setDefaultDateTime();
      }

      // Manual define form for input field.
      $element['end_value']['#default_value'] = $end_date->format($end_value_format);
    }

    // $element['value']['#date_format'] = $this->getSetting('date_format');

    // $element['end_value']['#date_format'] = $this->getSetting('date_format');


    return $element;
  }

  /**
   * Element_validate callback to ensure that the start date <= the end date.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateStartEnd(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $start_date = $element['value']['#value'];
    $end_date = $element['end_value']['#value'];

    // $get_start_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $start_date);
    // $get_end_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $end_date);

    // $change_start_value_format = $get_start_value->format('Y-m-d\TH:i:s');
    // $change_end_value_format = $get_end_value->format('Y-m-d\TH:i:s');

    // $timezone = new \DateTimeZone(date_default_timezone_get());
    // $start = new \DateTime($change_start_value_format, $timezone);
    // $end = new \DateTime($change_end_value_format, $timezone);

    // $result_start = DrupalDateTime::createFromDateTime($start);
    // $result_end = DrupalDateTime::createFromDateTime($end);

    $result_start = '';
    $result_end = '';

    // dpm($result_end);

    if(!empty($start_date) && empty($end_date)) {
      $form_state->setError($element['end_value'], $this->t('The @title end date value should not be null.', ['@title' => $element['#title']]));
    }

    if(empty($start_date) && !empty($end_date)) {
      $form_state->setError($element['value'], $this->t('The @title start date value should not be null.', ['@title' => $element['#title']]));
    }

    if(!empty($start_date)) {
      
      $get_start_value = '';

      switch ($this->getFieldSetting('datetime_type')) {
        case DateRangeItem::DATETIME_TYPE_DATE: // date
          $get_start_value = DrupalDateTime::createFromFormat('m-d-Y', $start_date);
          break;
        default: // datetime
          $get_start_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $start_date);  
          break;
      }

      $change_start_value_format = $get_start_value->format('Y-m-d\TH:i:s');
      $timezone = new \DateTimeZone(date_default_timezone_get());
      $start = new \DateTime($change_start_value_format, $timezone);

      $result_start = DrupalDateTime::createFromDateTime($start);
    }

    if(!empty($end_date)) {

      $get_end_value = '';

      switch ($this->getFieldSetting('datetime_type')) {
        case DateRangeItem::DATETIME_TYPE_DATE: // date
          $get_end_value = DrupalDateTime::createFromFormat('m-d-Y', $end_date);
          break;
        default: // datetime
          $get_end_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $end_date);
          break;
      }

      $change_end_value_format = $get_end_value->format('Y-m-d\TH:i:s');

      $timezone = new \DateTimeZone(date_default_timezone_get());
      $end = new \DateTime($change_end_value_format, $timezone);

      $result_end = DrupalDateTime::createFromDateTime($end);
    }

    if(!empty($start_date) && !empty($end_date)) {
      if ($result_start > $result_end) {
        $form_state->setError($element, $this->t('The @title end date cannot be before the start date', ['@title' => $element['#title']]));
      }

      if ($result_start == $result_end) {
        $form_state->setError($element, $this->t('The @title end date cannot be same with start date', ['@title' => $element['#title']]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // 'date_format' => 'm-d-Y h:i a',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = [];
    // $elements['date_format'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Custom date format'),
    //   '#description' => $this->t('Enter custom date format e.g YYYY-MM-DD is equivalent to 1997-02-06. <b>This format will be show after saving or refreshing the page.</b> </br> Reference: <a href="https://www.php.net/manual/en/datetime.formats.date.php" target="_blank">https://www.php.net/manual/en/datetime.formats.date.php</a>'),
    //   '#default_value' => $this->getSetting('date_format'),
    //   '#required' => FALSE,
    // ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    // $summary[] = $this->t('PHP custom date format: @php_date_format', ['@php_date_format' => !empty($this->getSetting('date_format')) ? $this->getSetting('date_format') : $this->t('None')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {
      if (!empty($item['value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        // $date = new DrupalDateTime($item['value']);

        $date = '';
        $timezone = new \DateTimeZone(date_default_timezone_get());

        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE: // date
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.
            $get_start_value = DrupalDateTime::createFromFormat('m-d-Y', $item['value']);
            $change_format_date = $get_start_value->format('Y-m-d\TH:i:s');
            
            $date = new \DateTime($change_format_date, $timezone);
            $date = DrupalDateTime::createFromDateTime($date);

            $date->setDefaultDateTime();
            $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
            break;

          default: // datetime
            $get_start_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $item['value']);
            $change_format_date = $get_start_value->format('Y-m-d\TH:i:s');

            $date = new \DateTime($change_format_date, $timezone);
            $date = DrupalDateTime::createFromDateTime($date);

            $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
            break;
        }

        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['value'] = $date->format($format);
      }
      if (!empty($item['end_value'])) {

        // Date value is now string not instance of DrupalDateTime (without T).
        // $date = new DrupalDateTime($item['end_value']);

        $date = '';
        $timezone = new \DateTimeZone(date_default_timezone_get());

        switch ($this->getFieldSetting('datetime_type')) {
          case DateRangeItem::DATETIME_TYPE_DATE: // date
            // If this is a date-only field, set it to the default time so the
            // timezone conversion can be reversed.

            $get_start_value = DrupalDateTime::createFromFormat('m-d-Y', $item['end_value']);
            $change_format_date = $get_start_value->format('Y-m-d\TH:i:s');
            
            $date = new \DateTime($change_format_date, $timezone);
            $date = DrupalDateTime::createFromDateTime($date);

            $date->setDefaultDateTime();
            $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
            break;

          default: // datetime
            $get_start_value = DrupalDateTime::createFromFormat('m-d-Y h:i a', $item['end_value']);
            $change_format_date = $get_start_value->format('Y-m-d\TH:i:s');

            $date = new \DateTime($change_format_date, $timezone);
            $date = DrupalDateTime::createFromDateTime($date);

            $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
            break;
        }

        // Adjust the date for storage.
        $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['end_value'] = $date->format($format);
      }

    }

    return $values;
  }

}
