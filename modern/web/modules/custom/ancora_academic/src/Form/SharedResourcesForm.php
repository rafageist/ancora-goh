<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Form;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ResourceGroup;
use Drupal\ancora_academic\SharedResources;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The other resources of an activity template (D9d), ?template=<id>.
 *
 * The legacy resource window of a template (otros_recursos_asignados.tpl):
 * its groups in id order, each with its priority, its resources, how many
 * are needed and whether they are essential; add a group, add resources to
 * a group, remove a resource, delete a group. SharedResources holds the
 * legacy values and what the engine does with them.
 */
final class SharedResourcesForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly SharedResources $resources,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'), $container->get(SharedResources::class));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ancora_academic_template_resources';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#cache']['contexts'] = ['url.query_args', 'user'];
    $id = (string) $this->getRequest()->query->get('template', '');
    $template = ctype_digit($id) ? $this->entityTypeManager->getStorage('ancora_activity_template')->load($id) : NULL;
    if (!$template instanceof ActivityTemplate || !$template->access('update')) {
      $form['empty'] = ['#markup' => '<p>' . $this->t('Open this page from the page of one of your activity templates.') . '</p>'];
      return $form;
    }
    $form_state->set('template', (int) $template->id());
    $form['intro'] = [
      '#markup' => '<p>' . $this->t('The other resources %template needs, in groups. When it places an activity, the engine takes from every group except the last one as many resources as the first group needs, by resource priority and then the least used; it does not check whether they are free, so a resource booked at the same time makes the run fail. An essential group (other than the last) that needs more resources than it has keeps the activity from being placed.', ['%template' => $template->label()]) . '</p>',
    ];
    $form['back'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to the template'),
      '#url' => $template->toUrl(),
    ];
    $priorities = $this->resources->priorityOptions($template->id());
    $candidates = $this->resources->candidates($template);
    $form['groups'] = ['#tree' => TRUE];
    $n = 0;
    foreach ($this->resources->groups($template->id()) as $group_id => $group) {
      $n++;
      $members = $this->resources->members($group_id);
      $element = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Group @n', ['@n' => $n]),
        '#attributes' => ['class' => ['ancora-resource-group']],
      ];
      $priority = (int) $group->get('priority')->value;
      $element['priority'] = [
        '#type' => 'select',
        '#title' => $this->t('Priority'),
        '#options' => array_combine(array_unique([...$priorities, $priority]), array_unique([...$priorities, $priority])),
        '#default_value' => $priority,
      ];
      $needed = (int) $group->get('needed')->value;
      $counts = array_unique([...range(1, max(1, count($members))), $needed]);
      sort($counts);
      $element['needed'] = [
        '#type' => 'select',
        '#title' => $this->t('Needed'),
        '#options' => array_combine($counts, $counts),
        '#default_value' => $needed,
      ];
      $element['essential'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Essential'),
        '#default_value' => (bool) $group->get('essential')->value,
      ];
      $current = [];
      foreach ($members as $member_id => $member) {
        $resource = $member->get('other_resource')->entity;
        $current[(int) $member_id] = $this->t('@resource (priority @p)', [
          '@resource' => $resource ? $resource->label() : '?',
          '@p' => (int) $member->get('priority')->value,
        ]);
      }
      $element['remove'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Remove resources'),
        '#options' => $current,
        '#access' => $current !== [],
      ];
      $element['add'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Add resources'),
        '#options' => $candidates,
        '#access' => $candidates !== [],
      ];
      $element['save'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save group @n', ['@n' => $n]),
        '#name' => 'save_' . $group_id,
        '#group_id' => (int) $group_id,
        '#op' => 'save',
      ];
      $element['delete'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete group @n', ['@n' => $n]),
        '#name' => 'delete_' . $group_id,
        '#group_id' => (int) $group_id,
        '#op' => 'delete',
      ];
      $form['groups'][$group_id] = $element;
    }
    if ($n === 0) {
      $form['none'] = ['#markup' => '<p>' . $this->t('The template needs no other resource.') . '</p>'];
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['add_group'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a group'),
      '#name' => 'add_group',
      '#op' => 'add_group',
    ];
    $catalogue = Url::fromRoute('entity.ancora_other_resource.collection');
    if ($catalogue->access()) {
      $form['actions']['catalogue'] = [
        '#type' => 'link',
        '#title' => $this->t('Your other resources'),
        '#url' => $catalogue,
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $template = $this->entityTypeManager->getStorage('ancora_activity_template')->load((int) $form_state->get('template'));
    if (!$template instanceof ActivityTemplate || !$template->access('update')) {
      return;
    }
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'] ?? '';
    $messages = [];
    if ($op === 'add_group') {
      $messages = $this->resources->addGroup($template);
    }
    else {
      $group = $this->entityTypeManager->getStorage('ancora_resource_group')->load((int) ($trigger['#group_id'] ?? 0));
      if (!$group instanceof ResourceGroup || (int) $group->get('template')->target_id !== (int) $template->id()) {
        return;
      }
      if ($op === 'delete') {
        $group->delete();
      }
      else {
        $values = (array) $form_state->getValue(['groups', $group->id()]);
        $remove = array_map('intval', array_keys(array_filter((array) ($values['remove'] ?? []))));
        $members = $this->resources->members($group->id());
        $gone = array_intersect_key($members, array_flip($remove));
        if ($gone !== []) {
          $this->entityTypeManager->getStorage('ancora_resource_member')->delete($gone);
        }
        $add = array_map('intval', array_keys(array_filter((array) ($values['add'] ?? []))));
        $add = array_values(array_intersect(array_keys($this->resources->candidates($template)), $add));
        if ($add !== []) {
          $messages = $this->resources->addMembers($group, $add);
        }
        if ($messages === []) {
          $messages = $this->resources->setGroup($group, [
            'priority' => (int) ($values['priority'] ?? 1),
            'needed' => (int) ($values['needed'] ?? 1),
            'essential' => !empty($values['essential']),
          ]);
        }
      }
    }
    foreach ($messages as $message) {
      $this->messenger()->addError($message);
    }
    if ($messages === []) {
      $this->messenger()->addStatus($this->t('Saved.'));
    }
  }

}
