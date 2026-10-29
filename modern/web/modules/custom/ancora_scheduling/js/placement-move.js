/**
 * @file
 * The move page of a placement (#130): waiting for the check, drag and drop.
 *
 * Progressive enhancement only. Without this script the page has a
 * "Refresh" link while the check runs, and every destination is a button.
 * With it, the page reloads itself while the check runs (at most 30 times),
 * and the activity can be dragged onto a valid cell: the drop presses that
 * cell's button, the same server step as a click. Nothing happens on hover.
 */
((Drupal, once) => {
  Drupal.behaviors.ancoraPlacementMove = {
    attach(context) {
      once('ancora-move-pending', '[data-ancora-check-pending]', context).forEach((pending) => {
        const key = `ancora-move-poll-${pending.dataset.ancoraCheckPending}`;
        let tries = 0;
        try {
          tries = Number(window.sessionStorage.getItem(key) || 0);
        }
        catch (e) {
          tries = 0;
        }
        if (tries >= 30) {
          return;
        }
        window.setTimeout(() => {
          try {
            window.sessionStorage.setItem(key, String(tries + 1));
          }
          catch (e) {
            // Without storage the link still works.
          }
          window.location.reload();
        }, 2000);
      });

      once('ancora-move-drag', '[data-ancora-move-drag]', context).forEach((handle) => {
        const form = handle.closest('form');
        handle.hidden = false;
        handle.addEventListener('dragstart', (event) => {
          event.dataTransfer.effectAllowed = 'move';
          event.dataTransfer.setData('text/plain', 'ancora-placement');
          form.classList.add('is-dragging');
        });
        handle.addEventListener('dragend', () => {
          form.classList.remove('is-dragging');
          form.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target'));
        });
        form.querySelectorAll('td.is-valid').forEach((cell) => {
          const button = cell.querySelector('[data-ancora-move-cell="1"]');
          if (!button) {
            return;
          }
          cell.addEventListener('dragover', (event) => {
            event.preventDefault();
            cell.classList.add('is-drop-target');
          });
          cell.addEventListener('dragleave', () => cell.classList.remove('is-drop-target'));
          cell.addEventListener('drop', (event) => {
            event.preventDefault();
            cell.classList.remove('is-drop-target');
            form.setAttribute('aria-busy', 'true');
            button.click();
          });
        });
      });

      // After a step, focus the feedback or the preview.
      once('ancora-move-focus', '.ancora-move', context).forEach(() => {
        const target = document.querySelector('#ancora-move-preview [autofocus]')
          || (document.querySelector('#ancora-move-feedback[role]') ? document.getElementById('ancora-move-feedback') : null);
        if (target) {
          target.focus();
        }
      });
    },
  };
})(Drupal, once);
