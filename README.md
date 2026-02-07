# CTA Manager

**Add beautiful, high-converting call-to-action buttons to your WordPress site with built-in analytics.**

CTA Manager is a lightweight WordPress plugin that lets you create, schedule, and track click-to-call, email, and link CTAs across your entire site. Embed them anywhere with shortcodes or the native Gutenberg block, monitor performance with built-in analytics, and manage your data with full import/export tools.

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0+ |
| PHP | 8.0+ |

---

## Installation

1. Download the plugin ZIP file.
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**.
3. Upload the ZIP file and click **Install Now**.
4. Activate the plugin.
5. Navigate to **Plugin CP > CTA Manager** to create your first CTA.

---

## Features

### CTA Types

CTA Manager ships with three action types that cover the most common conversion goals:

#### Phone Call CTA
Create click-to-call buttons that let mobile visitors call you with a single tap.

- One-tap mobile calling via `tel:` links
- International phone number format support
- All clicks tracked automatically in analytics

#### Link CTA
Direct visitors to any URL with a styled, trackable button.

- Internal and external link support
- Option to open links in a new tab
- Custom button text

#### Email CTA
Let visitors compose an email to you with one click.

- Opens the visitor's default mail app via `mailto:` link
- Single-recipient email support
- Click tracking in analytics

---

### Embedding

#### Shortcodes

Display CTAs anywhere WordPress processes shortcode content -- posts, pages, widgets, and more.

```
[cta-manager]                          <!-- Default CTA -->
[cta-manager id="123"]                 <!-- By ID -->
[cta-manager name="Your CTA Name"]     <!-- By name -->
```

Each CTA in the Manage CTAs list shows its unique shortcode that you can copy directly.

#### Gutenberg Block

Add CTAs directly in the WordPress block editor with zero shortcode syntax.

- Native block editor integration
- Visual CTA selector dropdown
- Live preview in the editor

To use: click the **+** button in the block editor, search for **CTA Manager**, select your CTA from the dropdown, and publish.

---

### Layout

#### Button Layout

A clean, minimal button-only layout optimized for headers, footers, sidebars, and inline content.

- Compact footprint that fits anywhere
- Multiple position options
- Fully mobile-optimized and responsive

---

### Scheduling

Control when your CTAs are visible with date-based scheduling.

- Set start and/or end dates using the date picker in the General tab
- Timezone-aware -- dates are checked server-side, so there is no front-end flicker
- Leave either date blank for open-ended windows (e.g., start now with no end date)
- Plan seasonal promotions, limited-time offers, and campaign-driven CTAs

---

### Analytics

Track impressions and clicks for up to 7 days with built-in analytics. No external tools required.

- **Impression tracking** -- see how often your CTAs are displayed
- **Click analytics** -- see how often visitors interact with each CTA
- **Rolling data window** -- automated daily cleanup keeps your database lean
- View performance in the **Analytics** tab inside the Plugin Control Panel

---

### Data Management

Full import and export tools to back up, migrate, and audit your CTA data.

#### Export CTA Data
Download all CTA configurations (names, types, targets, schedules, styling) as a JSON file. Use **Copy JSON to Clipboard** for quick inspection or version control.

#### Import CTA Data
Restore CTAs from a previously exported JSON backup. Includes file validation and safety checks before writing to the database.

#### Export Notifications
Export the in-plugin notification log to CSV or JSON. Filter by date range and include notification metadata for auditing.

#### Export Settings
Export all global plugin settings as JSON for easy migration between sites. Reproduces your defaults (button styling, toggles, thresholds) on another installation.

---

### Integration

#### Google Analytics 4

CTA Manager includes a built-in GA4 integration that automatically tracks CTA events in your Google Analytics property.

- **Automatic event tracking** -- impressions, clicks, and conversions are sent as structured GA4 events
- **Custom dimensions and metrics** -- CTA ID, title, page URL, device type, and more
- **Conversion attribution** -- mark CTA conversions as GA4 conversion events for full funnel analysis

**Setup:** Enter your GA4 Measurement ID (format: `G-XXXXXXXXXX`) in the CTA Manager Integrations settings and enable the toggle. Events will appear under **Reports > Engagement > Events** in your GA4 property.

---

### Developer Hooks

CTA Manager provides WordPress hooks for developers who want to extend or customize plugin behavior.

#### Database Hooks (Filters & Actions)

| Hook | Type | Description |
|---|---|---|
| `cta_db_before_insert` | Filter | Modify data before inserting into the database. Receives `$data` and `$table`. |
| `cta_db_after_insert` | Action | Fires after a successful insert. Receives `$result`, `$table`, `$data`. |
| `cta_db_before_update` | Filter | Modify data before updating records. Receives `$data`, `$table`, `$where`. |
| `cta_db_after_update` | Action | Fires after a successful update. Receives `$result`, `$table`, `$data`, `$where`. |

#### Permission Hook

| Hook | Type | Description |
|---|---|---|
| `cta_can_add_cta` | Filter | Control whether the current user can create new CTAs. Return `false` to block creation. |

**Example -- Limit non-admin users to 5 CTAs:**

```php
add_filter( 'cta_can_add_cta', function( $can_add ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        $data  = CTA_Data::get_instance();
        $count = count( $data->get_all_ctas() );
        return $count < 5;
    }
    return $can_add;
} );
```

---

## Upgrading to Pro

CTA Manager Pro unlocks advanced features including:

- **Popup and Slide-in CTA types** -- modal overlays and animated slide-in panels
- **Customizable card layouts** -- text above, below, left, or right of your button
- **Advanced styling** -- custom colors, fonts, gradients, padding, borders, shadows
- **Button and icon animations** -- pulse, bounce, shake, swing, and hover effects
- **Custom SVG icon library** -- upload and manage your own icons
- **Custom CSS editor** -- full CSS control with syntax highlighting
- **Device targeting** -- show CTAs to mobile, desktop, or tablet users only
- **URL targeting** -- blacklist/whitelist specific pages
- **A/B testing** -- multi-variant testing with traffic splitting and statistical analysis
- **Analytics export** -- download event data as CSV or JSON
- **Advanced embedding** -- custom HTML IDs, classes, and data-* attributes
- **JavaScript and PHP hooks** -- full lifecycle event system for deep integrations
- **Third-party integrations** -- Google Tag Manager, Slack, PostHog, and more

Learn more at [topdevamerica.com/plugins/cta-manager](https://topdevamerica.com/plugins/cta-manager).

---

## Frequently Asked Questions

### How do I display a CTA on my site?
Use the `[cta-manager]` shortcode in any post, page, or widget, or add the **CTA Manager** block in the Gutenberg editor.

### Can I show different CTAs on different pages?
Yes. Each CTA has its own shortcode with a unique ID. Place different shortcodes on different pages to show the CTA you want.

### How long is analytics data retained?
The free version retains impression and click data for up to 7 days. A daily cleanup job automatically removes older records to keep your database lean.

### Can I schedule a CTA to appear only during a promotion?
Yes. Set a start and end date in the General tab when editing a CTA. The CTA will only render between those dates.

### Does this plugin slow down my site?
No. CTA Manager loads assets only on pages where a CTA is present. The button layout is lightweight HTML/CSS with minimal JavaScript.

### Can I migrate my CTAs to another WordPress site?
Yes. Use the Export tools to download your CTAs and settings as JSON, then use the Import tool on the destination site to restore them.

---

## Support

For support, feature requests, or bug reports, visit [topdevamerica.com](https://topdevamerica.com).

---

## License

CTA Manager is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

**Author:** [TopDevAmerica](https://topdevamerica.com)
**Version:** 1.0.0
