<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Base class of the records owned by their creator: plans and catalogues.
 *
 * The legacy column "creador" (docs/modernization/legacy-parity-inventory.md
 * PAR-001): every add handler sets it to the session user
 * (control/data/fdms/fdm_<Class>_creador.php). Here the owner field ("uid")
 * defaults to the current user when the record is created. A catalogue
 * record (activity type, specialty, level, cohort, subject, room, teacher)
 * has no plan: it serves every plan of its owner (PAR-003).
 *
 * An edit does not change the owner. Legacy set handlers rewrite "creador"
 * to the editing user (quirk Q-11), but in the legacy UI only the owner
 * reaches a record, so there the owner stays the same too.
 */
abstract class OwnedAcademicEntityBase extends AcademicEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type) + static::ownerBaseFieldDefinitions($entity_type);
    $fields['uid']
      ->setLabel(new TranslatableMarkup('Owner'))
      ->setDescription(new TranslatableMarkup('The user who created the record. Only that user sees it in the lists and edits it.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 50,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'settings' => ['link' => FALSE],
        'weight' => 50,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatorId(): ?int {
    $id = $this->getOwnerId();
    return $id === NULL ? NULL : (int) $id;
  }

}
