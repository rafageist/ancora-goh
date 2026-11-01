/**
 * @file
 * The region loader of the planning workspace (#141): a GET fetch.
 *
 * Asks the regions endpoint for the regions of a workspace URL (same query,
 * abortable), then replaces each [data-ancora-region] element with the
 * server's, with Drupal's behaviors detached and attached around it. A
 * failed request rejects; the caller falls back to a full page load unless a
 * newer navigation replaced it.
 */
((Drupal, drupalSettings) => {
  const ws = (Drupal.ancoraWorkspace = Drupal.ancoraWorkspace || {});

  /**
   * The regions of a workspace URL, as a parsed document (abortable).
   */
  ws.fetchRegions = async (root, url, signal) => {
    const target = new URL(root.dataset.ancoraRegionsUrl, window.location.href);
    target.search = new URL(url, window.location.href).search;
    const response = await fetch(target, { credentials: 'same-origin', headers: { Accept: 'text/html' }, signal });
    if (!response.ok) {
      throw new Error(`regions: ${response.status}`);
    }
    return new DOMParser().parseFromString(await response.text(), 'text/html');
  };

  /**
   * Replaces each region with the server's, behaviors around it.
   */
  ws.applyRegions = (root, fresh) => {
    fresh.querySelectorAll('[data-ancora-region]').forEach((region) => {
      const old = root.querySelector(`[data-ancora-region="${region.dataset.ancoraRegion}"]`);
      if (!old) {
        return;
      }
      Drupal.detachBehaviors(old, drupalSettings, 'unload');
      const node = document.importNode(region, true);
      old.replaceWith(node);
      Drupal.attachBehaviors(node, drupalSettings);
    });
  };
})(Drupal, drupalSettings);
