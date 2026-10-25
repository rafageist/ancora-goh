<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * An academic record of the Áncora planning dataset.
 */
interface AcademicEntityInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns the ID of the plan the record belongs to.
   *
   * @return int|null
   *   The plan ID; the plan's own ID for a plan; NULL while it is not set.
   */
  public function getPlanId(): ?int;

  /**
   * Returns the short code of the record, unique in its plan.
   *
   * @return string
   *   The code, or an empty string for records without a code.
   */
  public function getCode(): string;

  /**
   * Returns the fields shown as columns of the administrative list.
   *
   * @return list<string>
   *   Field names; "label" is rendered as a link to the record.
   */
  public static function listColumns(): array;

}
