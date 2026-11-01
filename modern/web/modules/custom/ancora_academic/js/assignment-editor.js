/**
 * @file
 * Drag and drop, live search and feedback for the assignment editor (#122).
 *
 * Progressive enhancement only: every change is also a button of the form,
 * which works without this script and from the keyboard. A drop fills the
 * form's "drop" fields with the stable ids of the dragged item and of the
 * target and presses the same hidden submit button; the server decides what
 * the drop means and validates it. Nothing happens on hover, and a removal
 * by drop asks first.
 */
((Drupal, once) => {
  /**
   * The drop zone of an element for a dragged kind, or null.
   */
  function zoneFor(element, kind) {
    let node = element;
    while (node && node !== document) {
      if (node.dataset && node.dataset.dropKind && node.dataset.dropKind.split(' ').includes(kind)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  }

  /**
   * Fills the drop fields and submits the form.
   */
  function submitDrop(form, values) {
    Object.entries(values).forEach(([name, value]) => {
      const field = form.querySelector(`[data-ancora-drop="${name}"]`);
      if (field) {
        field.value = value;
      }
    });
    form.setAttribute('aria-busy', 'true');
    form.classList.add('is-busy');
    Drupal.announce(Drupal.t('Saving the change…'));
    form.querySelector('[data-ancora-drop="apply"]').click();
  }

  /**
   * Asks before a removal by drop, in a small dialog.
   */
  function confirmRemoval(label, onConfirm) {
    const element = document.createElement('div');
    element.textContent = Drupal.t('Remove @label from this template? You can undo it afterwards.', { '@label': label });
    const dialog = Drupal.dialog(element, {
      title: Drupal.t('Remove an assignment'),
      width: 460,
      buttons: [
        {
          text: Drupal.t('Remove'),
          class: 'button button--danger',
          click: () => {
            dialog.close();
            onConfirm();
          },
        },
        {
          text: Drupal.t('Cancel'),
          class: 'button',
          click: () => dialog.close(),
        },
      ],
    });
    dialog.showModal();
  }

  /**
   * What a drop of a kind on a zone means; null when it means nothing.
   */
  function dropValues(zone, kind, id) {
    const op = zone.dataset.dropOp;
    if (op === 'assign') {
      return { op, target: kind, item: id };
    }
    if (op === 'priority') {
      return { op, target: kind.replace('-assignment', ''), item: id, value: zone.dataset.priority };
    }
    if (op === 'add_group') {
      return { op, target: zone.dataset.id, item: id };
    }
    if (op === 'remove') {
      return { op, target: kind.replace('-assignment', ''), item: id, confirmed: '1' };
    }
    return null;
  }

  Drupal.behaviors.ancoraAssignmentEditor = {
    attach(context) {
      once('ancora-assign', 'form.ancora-assign', context).forEach((form) => {
        let dragged = null;
        form.classList.add('is-enhanced');

        form.querySelectorAll('[data-drag-kind]').forEach((item) => {
          item.addEventListener('dragstart', (event) => {
            dragged = { kind: item.dataset.dragKind, id: item.dataset.id, label: item.dataset.label };
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.dataset.label || '');
            item.classList.add('is-dragging');
            form.classList.add(`is-dragging-${dragged.kind}`);
          });
          item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            form.className = form.className.replace(/\bis-dragging-\S+/g, '').trim();
            form.querySelectorAll('.is-drop-target').forEach((zone) => zone.classList.remove('is-drop-target'));
            dragged = null;
          });
        });

        form.addEventListener('dragover', (event) => {
          const zone = dragged && zoneFor(event.target, dragged.kind);
          if (zone) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            form.querySelectorAll('.is-drop-target').forEach((z) => z !== zone && z.classList.remove('is-drop-target'));
            zone.classList.add('is-drop-target');
          }
        });

        form.addEventListener('drop', (event) => {
          const zone = dragged && zoneFor(event.target, dragged.kind);
          if (!zone) {
            return;
          }
          event.preventDefault();
          zone.classList.remove('is-drop-target');
          const { kind, id, label } = dragged;
          const values = dropValues(zone, kind, id);
          if (!values) {
            return;
          }
          if (values.op === 'remove') {
            confirmRemoval(label, () => submitDrop(form, values));
          }
          else {
            submitDrop(form, values);
          }
        });

        // The buttons show the same busy state.
        form.addEventListener('submit', () => {
          form.setAttribute('aria-busy', 'true');
          form.classList.add('is-busy');
        });
      });

      // Live search: the server filters on submit; this filters as you type.
      once('ancora-assign-filter', 'input[data-ancora-filter]', context).forEach((input) => {
        input.addEventListener('input', () => {
          const term = input.value.trim().toLowerCase();
          const scope = input.dataset.ancoraFilter === 'templates' ? '.ancora-assign__templates li' : '.ancora-assign [data-search]';
          document.querySelectorAll(scope).forEach((item) => {
            item.hidden = term !== '' && !(item.dataset.search || '').includes(term);
          });
        });
      });

      // After a change, the focus goes to the changed element; an error is
      // also shown in a dialog.
      once('ancora-assign-feedback', '.ancora-assign', context).forEach(() => {
        const target = window.location.hash ? document.getElementById(window.location.hash.slice(1)) : null;
        const feedback = document.getElementById('ancora-assign-feedback');
        if (feedback && feedback.dataset.ancoraFeedback === 'error') {
          const element = document.createElement('div');
          element.innerHTML = feedback.querySelector('ul') ? feedback.querySelector('ul').outerHTML : '';
          const dialog = Drupal.dialog(element, {
            title: Drupal.t('The change was not saved'),
            width: 420,
            buttons: [{ text: Drupal.t('OK'), class: 'button button--primary', click: () => dialog.close() }],
            close: () => (target || feedback).focus(),
          });
          dialog.showModal();
        }
        else if (target) {
          target.focus();
        }
      });
    },
  };
})(Drupal, once);
