/**
 * @file
 * The planning workspace (#141): regions in place, history, focus, keys.
 *
 * Progressive enhancement only. Without this script every link of the
 * workspace is a plain GET link that loads the whole page, and the URL
 * holds the whole state (period, perspective, record, selection).
 *
 * With it, a workspace link ([data-ancora-nav]) pushes its URL into the
 * history, loads only the regions (Drupal.ancoraWorkspace.fetchRegions and
 * applyRegions, the region loader), replaces them and moves the focus to a
 * defined target; a newer navigation aborts an older one. Back and forward
 * load the regions of the URL they arrive at. The move form of the
 * inspector (S1b) is submitted in place too: the same POST, its regions
 * applied; a pending destination check is polled every second, with the
 * time waited. The canvas gets a roving focus (arrow keys, Home and End); Escape closes
 * the inspector drawer. Nothing here decides anything: the server renders
 * every region, and validates every value.
 */
((Drupal, once) => {
  const ws = (Drupal.ancoraWorkspace = Drupal.ancoraWorkspace || {});

  /**
   * The element to focus after a load of the given kind.
   */
  ws.focusTarget = (root, kind, previous) => {
    if (kind === 'select') {
      return root.querySelector('#ancora-inspector-title');
    }
    if (kind === 'close' && previous) {
      const block = root.querySelector(`[data-ancora-placement="${previous}"]`);
      if (block) {
        return block;
      }
    }
    return root.querySelector('#ancora-canvas-title');
  };

  /**
   * The latest load (navigation, poll or form step): its number, the
   * controller that aborts a navigation, and whether a form step is in
   * flight.
   */
  let latest = 0;
  let controller = null;
  let writing = false;
  let pollTimer = null;
  let polls = 0;

  /**
   * The most polls of one destination check (one a second).
   */
  const MAX_POLLS = 180;

  /**
   * Whether the move form shows a check that is still queued or running.
   */
  const pending = (root) => root.querySelector('[data-ancora-check-pending]') !== null;

  /**
   * After fresh regions: status, focus, and the poll of a pending check.
   */
  ws.settle = (root, fresh, kind, previous, wasPending) => {
    ws.applyRegions(root, fresh);
    root.removeAttribute('aria-busy');
    const feedback = root.querySelector('#ancora-move-feedback');
    const message = feedback ? feedback.textContent.trim() : '';
    const canvas = root.querySelector('[data-ancora-status-text]');
    const status = root.querySelector('[data-ancora-status]');
    const pendingNow = pending(root);
    let text = canvas ? canvas.dataset.ancoraStatusText : '';
    let target = null;
    if (kind === 'write' || (kind === 'poll' && wasPending && !pendingNow)) {
      const grid = root.querySelector('#ancora-move-grid');
      const preview = root.querySelector('#ancora-move-preview .button--primary');
      const failed = root.querySelector('.ancora-move__error');
      if (message !== '') {
        text = message;
        target = feedback;
      }
      else if (preview) {
        text = root.querySelector('#ancora-move-preview').textContent.replace(/\s+/g, ' ').trim();
        target = preview;
      }
      else if (pendingNow) {
        text = root.querySelector('[data-ancora-check-pending]').textContent.replace(/\s+/g, ' ').trim();
        target = root.querySelector('#ancora-inspector-move-title');
      }
      else if (failed) {
        text = failed.textContent.trim();
        target = failed;
      }
      else if (grid) {
        const moving = root.querySelector('#ancora-canvas-moving');
        text = moving ? moving.textContent.replace(/\s+/g, ' ').trim() : grid.textContent.trim();
        target = grid;
      }
    }
    else if (kind !== 'poll') {
      target = ws.focusTarget(root, kind, previous);
    }
    if (status && (kind !== 'poll' || target)) {
      status.textContent = text;
    }
    if (target) {
      if (!target.hasAttribute('tabindex') && !target.matches('a, button, input')) {
        target.setAttribute('tabindex', '-1');
      }
      target.focus();
    }
    // A pending check: look again in a second (bounded), in place.
    window.clearTimeout(pollTimer);
    if (pendingNow && polls < MAX_POLLS) {
      polls += 1;
      pollTimer = window.setTimeout(() => ws.navigate(root, window.location.href, 'poll', false), 1000);
    }
    else if (!pendingNow) {
      polls = 0;
    }
  };

  /**
   * Loads the regions of a URL in place.
   *
   * Navigations can overlap (a slow response, a fast second click, quick
   * Back and Forward, the poll of a check), so each one gets a number and
   * aborts the one before it. Only the latest load's response is ever
   * applied, and only it moves the focus or clears the busy state. A link's
   * URL is pushed into the history when it is followed, so the history
   * keeps the order of the user's actions, whatever order the responses
   * arrive in.
   */
  ws.navigate = async (root, url, kind, push) => {
    const id = ++latest;
    if (controller) {
      controller.abort();
    }
    window.clearTimeout(pollTimer);
    controller = new AbortController();
    const { signal } = controller;
    const selected = root.querySelector('.ancora-block.is-selected');
    const previous = selected ? selected.dataset.ancoraPlacement : null;
    const wasPending = pending(root);
    if (push) {
      window.history.pushState({ ancoraWorkspace: true, kind }, '', url);
    }
    if (kind !== 'poll') {
      root.setAttribute('aria-busy', 'true');
    }
    let fresh;
    try {
      fresh = await ws.fetchRegions(root, url, signal);
    }
    catch (e) {
      if (id !== latest || signal.aborted) {
        // Replaced by a newer load: nothing to do.
        return;
      }
      // Anything unexpected (a 404, a lost session): the full page says it.
      window.location.assign(url);
      return;
    }
    if (id !== latest) {
      return;
    }
    ws.settle(root, fresh, kind, previous, wasPending);
  };

  /**
   * Submits a step of the move form in place (S1b).
   *
   * The same POST the browser would send without this script (the form's
   * fields, its build id and CSRF token, and the pressed button), to the
   * same workspace URL, asking for the regions only. The server validates
   * and runs the step (PlacementMoveForm, PlacementModification) and answers
   * with the regions of the workspace URL it redirects to, or of the rebuilt
   * preview; they replace the current ones. One step at a time: while a step is in
   * flight, other steps and workspace links wait; it is never aborted, so
   * what the server did is always shown.
   */
  ws.submit = async (root, form, submitter) => {
    if (writing) {
      return;
    }
    writing = true;
    const id = ++latest;
    if (controller) {
      controller.abort();
      controller = null;
    }
    window.clearTimeout(pollTimer);
    root.setAttribute('aria-busy', 'true');
    const body = submitter ? new FormData(form, submitter) : new FormData(form);
    let response;
    let html;
    try {
      // The regions only: the same step, a smaller answer.
      response = await fetch(form.action, { method: 'POST', body, credentials: 'same-origin', headers: { Accept: 'text/html', 'X-Ancora-Regions': '1' } });
      html = await response.text();
    }
    catch (e) {
      writing = false;
      // The step's outcome is unknown here: the full page shows it.
      window.location.assign(window.location.href);
      return;
    }
    writing = false;
    if (id !== latest) {
      return;
    }
    const fresh = new DOMParser().parseFromString(html, 'text/html');
    if (!response.ok || !fresh.querySelector('[data-ancora-region="inspector"]')) {
      // A refusal of access, a lost session: the full page says it.
      window.location.assign(response.ok ? response.url : window.location.href);
      return;
    }
    if (response.redirected) {
      window.history.pushState({ ancoraWorkspace: true, kind: 'write' }, '', response.url);
    }
    ws.settle(root, fresh, 'write', null, false);
  };

  /**
   * The roving focus of the canvas: one tab stop, arrows move.
   */
  const roving = (grid) => {
    const blocks = () => Array.from(grid.querySelectorAll('.ancora-block'));
    const all = blocks();
    if (all.length === 0) {
      return;
    }
    const current = grid.querySelector('.ancora-block.is-selected') || all[0];
    all.forEach((block) => {
      block.tabIndex = block === current ? 0 : -1;
    });
    const move = (from, to) => {
      if (!to) {
        return;
      }
      from.tabIndex = -1;
      to.tabIndex = 0;
      to.focus();
    };
    grid.addEventListener('keydown', (event) => {
      const block = event.target.closest('.ancora-block');
      if (!block) {
        return;
      }
      const day = block.closest('.ancora-canvas__day');
      const inDay = Array.from(day.querySelectorAll('.ancora-block'));
      const index = inDay.indexOf(block);
      const days = Array.from(grid.querySelectorAll('.ancora-canvas__day'));
      const nearest = (step) => {
        for (let d = days.indexOf(day) + step; d >= 0 && d < days.length; d += step) {
          const candidates = Array.from(days[d].querySelectorAll('.ancora-block'));
          if (candidates.length) {
            const row = Number(block.dataset.ancoraRow);
            return candidates.reduce((best, c) => (Math.abs(Number(c.dataset.ancoraRow) - row) < Math.abs(Number(best.dataset.ancoraRow) - row) ? c : best));
          }
        }
        return null;
      };
      const targets = {
        ArrowDown: () => inDay[index + 1],
        ArrowUp: () => inDay[index - 1],
        ArrowRight: () => nearest(1),
        ArrowLeft: () => nearest(-1),
        Home: () => inDay[0],
        End: () => inDay[inDay.length - 1],
      };
      if (targets[event.key]) {
        event.preventDefault();
        move(block, targets[event.key]());
      }
    });
  };

  /**
   * The text filter of the record list: display only.
   */
  const filter = (label) => {
    const input = label.querySelector('input');
    const list = label.parentElement.querySelector('.ancora-wsnav__list');
    if (!input || !list) {
      return;
    }
    label.hidden = false;
    input.addEventListener('input', () => {
      const text = input.value.trim().toLowerCase();
      list.querySelectorAll('li').forEach((item) => {
        item.hidden = text !== '' && !item.textContent.toLowerCase().includes(text);
      });
    });
  };

  Drupal.behaviors.ancoraPlanWorkspace = {
    attach(context) {
      once('ancora-workspace', '[data-ancora-workspace]', context).forEach((root) => {
        window.history.replaceState({ ancoraWorkspace: true, kind: 'load' }, '', window.location.href);
        // A check that is still pending when the page opens: poll it.
        if (pending(root)) {
          pollTimer = window.setTimeout(() => ws.navigate(root, window.location.href, 'poll', false), 1000);
        }
        root.addEventListener('click', (event) => {
          const link = event.target.closest('a[data-ancora-nav]');
          if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) {
            return;
          }
          event.preventDefault();
          if (!writing) {
            ws.navigate(root, link.href, link.dataset.ancoraNav, true);
          }
        });
        // The steps of the move form (and the destination buttons on the
        // canvas, which belong to it by their form attribute).
        root.addEventListener('submit', (event) => {
          const form = event.target;
          if (!form.matches('[data-ancora-move-form]') || event.defaultPrevented) {
            return;
          }
          event.preventDefault();
          ws.submit(root, form, event.submitter);
        });
        root.addEventListener('keydown', (event) => {
          if (event.key !== 'Escape') {
            return;
          }
          const close = root.querySelector('.ancora-inspector.has-selection [data-ancora-nav="close"]');
          if (close && !writing && event.target.closest('.ancora-inspector')) {
            event.preventDefault();
            ws.navigate(root, close.href, 'close', true);
          }
        });
        window.addEventListener('popstate', (event) => {
          if (event.state && event.state.ancoraWorkspace) {
            ws.navigate(root, window.location.href, event.state.kind, false);
          }
          else {
            window.location.reload();
          }
        });
      });
      once('ancora-workspace-canvas', '[data-ancora-canvas]', context).forEach(roving);
      once('ancora-workspace-filter', '[data-ancora-filter]', context).forEach(filter);
      // The time a destination check has waited, counted on from the
      // server's value.
      once('ancora-workspace-elapsed', '[data-ancora-elapsed]', context).forEach((span) => {
        const start = Number(span.dataset.ancoraElapsed) || 0;
        const since = Date.now();
        const timer = window.setInterval(() => {
          if (!span.isConnected) {
            window.clearInterval(timer);
            return;
          }
          span.textContent = Drupal.t('@s s', { '@s': start + Math.floor((Date.now() - since) / 1000) });
        }, 1000);
      });
    },
  };
})(Drupal, once);
