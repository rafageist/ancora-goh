/**
 * @file
 * The status page of a timetable run (FC-31): waiting for the run to end.
 *
 * Progressive enhancement only. Without this script the page has a
 * "Refresh" link while the run is queued or running. With it, the page
 * reloads itself every 3 seconds, at most 100 times per run. The server
 * renders the marker (and attaches this script) only while the run is queued
 * or running, so the reloads stop once it has ended.
 */
((Drupal, once) => {
  const DELAY = 3000;
  const MAX = 100;

  Drupal.behaviors.ancoraJobRefresh = {
    attach(context) {
      once('ancora-job-pending', '[data-ancora-job-pending]', context).forEach((pending) => {
        const key = `ancora-job-poll-${pending.dataset.ancoraJobPending}`;
        let tries = 0;
        try {
          tries = Number(window.sessionStorage.getItem(key) || 0);
        }
        catch (e) {
          tries = 0;
        }
        if (tries >= MAX) {
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
        }, DELAY);
      });
    },
  };
})(Drupal, once);
