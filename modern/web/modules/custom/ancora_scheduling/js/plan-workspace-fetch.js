/**
 * @file
 * The region loader of the planning workspace (#141): a GET fetch.
 *
 * Asks the regions endpoint for the regions of a workspace URL (same query)
 * and replaces each [data-ancora-region] element with the server's, with
 * Drupal's behaviors detached and attached around it. A failed request
 * rejects, and the caller falls back to a full page load.
 */
((Drupal, drupalSettings) => {
  const ws = (Drupal.ancoraWorkspace = Drupal.ancoraWorkspace || {});

  ws.transport = async (root, url) => {
    const target = new URL(root.dataset.ancoraRegionsUrl, window.location.href);
    target.search = new URL(url, window.location.href).search;
    const response = await fetch(target, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
    if (!response.ok) {
      throw new Error(`regions: ${response.status}`);
    }
    const fresh = new DOMParser().parseFromString(await response.text(), 'text/html');
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
