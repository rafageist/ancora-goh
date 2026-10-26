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
   *   The plan ID; the plan's own ID for a plan; NULL for a catalogue record
   *   (it serves every plan of its creator) or while it is not set.
   */
  public function getPlanId(): ?int;

  /**
   * Returns the user ID of the record's creator (legacy "creador").
   *
   * For a plan and the catalogue records, the user who created it (the
   * owner field). For a record of a plan, the plan's creator: legacy
   * dame_creador_de_planificacion() and dame_creador_de_plantilla().
   *
   * @return int|null
   *   The user ID, or NULL while it is not known.
   */
  public function getCreatorId(): ?int;

  /**
   * Returns the short code of the record, unique in its plan or catalogue.
   *
   * Codes of catalogue records and plans are unique among the creator's
   * records of the type; codes of plan records, in the plan.
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
