<?php

declare(strict_types=1);

namespace Drupal\plausible_pageviews\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;


/**
 * Defines the 'plausible_pageviews' field type.
 *
 * @FieldType(
 *   id = "plausible_pageviews",
 *   label = @Translation("Plausible pageviews"),
 *   description = @Translation("Plausible pageviews field."),
 *   default_widget = "number",
 *   default_formatter = "number_integer",
 * )
 */
final class PlausiblePageviewsItem extends IntegerItem {
}
