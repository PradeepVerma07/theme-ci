# CI360 Degrees – WordPress theme

A custom theme built from `CI360-V5-Preview.html`. Every page outputs the same HTML as the
prototype and uses its CSS unchanged, while all content comes from Custom Post Types and fields.

## Install

1. Install and activate **Secure Custom Fields** (free, WordPress.org) or **ACF Pro**.
2. Upload `ci360-theme.zip` in Appearance › Themes › Add New › Upload, then activate.
3. On first activation the theme imports everything automatically. If it didn't (for example,
   the fields plugin was activated after the theme), go to **Appearance › CI360 Setup** and click
   **Import / restore CI360 content**. WP-CLI: `wp ci360 import`.

The import creates the pages (Home, About, Founders, Services, Work, Insights, Contact, Privacy,
Terms), sets Home as the front page, sets permalinks to `/%postname%/`, builds the four menus,
and copies the 19 bundled images into the Media Library.

## Where to edit what

| On the site | In WordPress |
|---|---|
| Services (list, detail pages, home capabilities) | **Services** (order = numbering) |
| Projects (home stack, Work grid, project pages) | **Projects**; “Featured position” 1–4 puts it on the home page |
| Work filter pills, sector copy | **Projects › Project categories** |
| Insights | **Insights** |
| Testimonials slider | **Testimonials** |
| Team roster, founder profiles | **Team** (tick “Is a founder”) |
| FAQs | **FAQs** (group: Home / Contact) |
| Page headings, intros, images | Edit the page itself (Home, About, …) |
| Header, menu overlay, footer, offices, client strip, process steps, CTA, labels on service/project/insight pages, 404, form mode | **CI360 Settings** |
| Navigation | Appearance › Menus (Header, Full-screen menu, Footer, Legal) |
| Enquiries received | **Enquiries** (also emailed) |

Headings accept `<br>` for a line break and `<em>…</em>` for the italic accent words.

## Notes

- The contact form defaults to sending through WordPress (saved under Enquiries and emailed with
  `wp_mail`). Install an SMTP plugin for reliable delivery. To use the prototype's
  “open the visitor's email app” behaviour, change **CI360 Settings › Enquiries › Form mode**.
- The illustrated service compositions (strategy paper, billboard, podcast mic…) are design
  components: choose one per service and set its images; their decorative text lives in
  `inc/components.php`.
- Founder portraits are downloaded from the current ci360degrees.com URLs during import. If the
  download fails, the design falls back to initials; upload a portrait on the Team member.
- Use an SEO plugin (Yoast / Rank Math) for titles and meta; the theme adds a basic description
  only when none is active.

## Files

```
functions.php          loads inc/*
inc/cpt.php            post types + taxonomies
inc/fields.php         all field groups (registered in code, defaults = prototype copy)
inc/helpers.php        markup helpers mirroring the prototype (label, btn, img, star…)
inc/components.php     cards, project scenes, service visuals, FAQ, process, testimonials
inc/enquiry.php        /wp-json/ci360/v1/enquiry
inc/importer.php       setup screen + WP-CLI import
data/seed.json         all prototype content
front-page.php, page-templates/*.php, single-ci_*.php, 404.php
assets/css/main.css    prototype CSS, unchanged
assets/js/main.js      prototype interactions (router replaced by real page loads + wipe transition)
```
