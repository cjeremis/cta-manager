/**
 * CTA Manager - Admin Bar Responsive Handler
 *
 * Prevents WordPress from hiding the CTA Manager admin bar item on small screens
 * by monitoring visibility and restoring it if needed.
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

(function() {
  'use strict';

  const CTA_ADMIN_BAR = {
    /**
     * Initialize the handler
     */
    init: function() {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', this.setup.bind(this));
      } else {
        this.setup();
      }
    },

    /**
     * Set up the visibility monitoring
     */
    setup: function() {
      const ctaMenuItem = document.getElementById('wp-admin-bar-cta-manager');
      if (!ctaMenuItem) {
        setTimeout(this.setup.bind(this), 200);
        return;
      }

      // Watch for visibility changes
      const observer = new MutationObserver(() => {
        this.checkAndRestore();
      });

      const adminBar = document.getElementById('wp-admin-bar');
      if (adminBar) {
        observer.observe(adminBar, {
          childList: true,
          subtree: true,
          attributes: true,
        });
      }

      // Check on resize
      window.addEventListener('resize', () => {
        this.checkAndRestore();
      });

      // Initial check
      this.checkAndRestore();
    },

    /**
     * Check if menu item is hidden and restore it
     */
    checkAndRestore: function() {
      const ctaMenuItem = document.getElementById('wp-admin-bar-cta-manager');
      if (!ctaMenuItem) return;

      const computedStyle = window.getComputedStyle(ctaMenuItem);

      // If it's hidden, remove any hiding attributes/styles that WordPress added
      if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
        ctaMenuItem.style.removeProperty('display');
        ctaMenuItem.style.removeProperty('visibility');
        ctaMenuItem.style.removeProperty('opacity');
        ctaMenuItem.removeAttribute('hidden');
        ctaMenuItem.removeAttribute('aria-hidden');
      }
    },
  };

  CTA_ADMIN_BAR.init();
})();
