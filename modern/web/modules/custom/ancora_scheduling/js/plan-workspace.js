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
 * defined target; a newer navigation aborts an older one. Back and forward load the regions of the URL they arrive at.
 * The canvas gets a roving focus (arrow keys, Home and End); Escape closes
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
   * The latest navigation: its number, and the controller that aborts it.
   */
  let latest = 0;
  let controller = null;

  /**
   * Loads the regions of a URL in place.
   *
   * Navigations can overlap (a slow response, a fast second click, quick
   * Back and Forward), so each one gets a number and aborts the one before
   * it. Only the latest navigation's response is ever applied, and only it
   * moves the focus or clears the busy state. A link's URL is pushed into
   * the history when it is followed, so the history keeps the order of the
   * user's actions, whatever order the responses arrive in.
   */
  ws.navigate = async (root, url, kind, push) => {
    const id = ++latest;
    if (controller) {
      controller.abort();
    }
    controller = new AbortController();
    const { signal } = controller;
    const selected = root.querySelector('.ancora-block.is-selected');
    const previous = selected ? selected.dataset.ancoraPlacement : null;
    if (push) {
      window.history.pushState({ ancoraWorkspace: true, kind }, '', url);
    }
    root.setAttribute('aria-busy', 'true');
    let fresh;
    try {
      fresh = await ws.fetchRegions(root, url, signal);
    }
    catch (e) {
      if (id !== latest || signal.aborted) {
        // Replaced by a newer navigation: nothing to do.
        return;
      }
      // Anything unexpected (a 404, a lost session): the full page says it.
      window.location.assign(url);
      return;
    }
    if (id !== latest) {
      return;
    }
    ws.applyRegions(root, fresh);
    root.removeAttribute('aria-busy');
    const canvas = root.querySelector('[data-ancora-status-text]');
    const status = root.querySelector('[data-ancora-status]');
    if (canvas && status) {
      status.textContent = canvas.dataset.ancoraStatusText;
    }
    const target = ws.focusTarget(root, kind, previous);
    if (target) {
      target.focus();
    }
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
        root.addEventListener('click', (event) => {
          const link = event.target.closest('a[data-ancora-nav]');
          if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) {
            return;
          }
          event.preventDefault();
          ws.navigate(root, link.href, link.dataset.ancoraNav, true);
        });
        root.addEventListener('keydown', (event) => {
          if (event.key !== 'Escape') {
            return;
          }
          const close = root.querySelector('.ancora-inspector.has-selection [data-ancora-nav="close"]');
          if (close && event.target.closest('.ancora-inspector')) {
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
    },
  };
})(Drupal, once);
