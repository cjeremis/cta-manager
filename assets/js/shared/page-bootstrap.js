/**
 * CTA Manager - Page Bootstrap Utility Module
 *
 * Contains shared bootstrap behavior for component entry-point controllers.
 * Used to centralize document-ready and admin-ready initialization flows.
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

/**
 * Register and bootstrap a page controller.
 *
 * @param {Object} config Bootstrap configuration.
 * @param {string} config.globalName Window key for the controller.
 * @param {Function} [config.define] Function that mutates the controller with page methods.
 * @param {Function} [config.onReady] Function to run once when the page is ready.
 * @param {string} [config.readyEvent] jQuery event name to trigger after ready.
 * @param {boolean} [config.waitForAdminReady=true] Whether to wait for ctaAdminReady.
 * @returns {Object|null} Bootstrapped controller or null when jQuery is unavailable.
 */
export function bootstrapPageController(config) {
  var $ = window.jQuery;
  if (typeof $ !== 'function' || !$.fn) {
    return null;
  }

  var settings = config || {};
  var globalName = settings.globalName;
  if (!globalName) {
    return null;
  }

  var controller = window[globalName] || {};
  if (typeof settings.define === 'function') {
    settings.define(controller, $);
  }

  var hasRun = false;
  var runReady = function() {
    if (hasRun) {
      return;
    }
    hasRun = true;

    if (typeof settings.onReady === 'function') {
      settings.onReady(controller, $);
    }

    if (settings.readyEvent) {
      $(document).trigger(settings.readyEvent, [controller]);
    }
  };

  controller.init = function() {
    var waitForAdminReady = settings.waitForAdminReady !== false;
    if (waitForAdminReady) {
      $(document).on('ctaAdminReady', runReady);
      if (window.CTAAdminApp) {
        runReady();
      }
      return;
    }

    runReady();
  };

  window[globalName] = controller;

  $(function() {
    controller.init();
  });

  return controller;
}
