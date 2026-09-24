# CI360 Degrees – Hello Elementor child theme

The CI360 demo theme converted to a **Hello Elementor child theme**. Every section of the site is an
Elementor widget, every page is available as an Elementor template, and one click rebuilds the full
demo. The front end is identical to the CI360 demo theme (same HTML, same CSS).

## Requirements

- **Hello Elementor** (parent theme, free) – install it, but activate this child theme.
- **Elementor** (free). Elementor Pro is optional (only for Theme Builder headers/footers/singles).
- **Secure Custom Fields** (free) or **ACF Pro** – for Services, Projects, Team, Settings…

## Install

1. Appearance › Themes › Add New: install **Hello Elementor** (don't need to activate it).
2. Upload `ci360-hello-child.zip` and **activate** it.
3. The demo imports automatically on activation. If it doesn't (plugins activated later), go to
   **Appearance › CI360 Setup › Import / restore CI360 content** (or `wp ci360 import`).

The import creates all services, projects, insights, testimonials, team (with photos), FAQs,
91 images, menus, and these pages built with Elementor: Home, About, Founders, Services, Work,
Insights, Blog (/blogs/), Contact, Privacy policy, Terms & conditions.


## v2.5 editable design controls

- **Header:** upload a logo in **CI360 Settings → Header & menu** or use **Appearance → Customize → Site Identity**. Add, remove, rename and reorder header links in **Appearance → Menus** using the Header and Full-screen menu locations.
- **Footer:** upload a separate footer logo, edit footer copy/CTA/copyright in **CI360 Settings**, and manage Explore + Legal links through the footer menu locations.
- **Insights hero:** edit the Insights widget in Elementor. It now includes **Cinematic**, **Split** and **Minimal** hero styles, optional featured article, optional background image, copy and CTA controls.
- **Services hero:** edit the Services Hero widget in Elementor. Choose **Editorial split**, **Blue panel** or **Minimal**; change copy, CTA, background image, visual service and badge visibility.
- **Services cards:** the Services Grid widget now offers **Editorial**, **Classic** and **Compact** layouts, 2/3 columns, optional headings, summaries and tags. Each Service has an optional dedicated card image under **Service details → Visual**.
- A quick access page is available at **Appearance → CI360 Editing Guide**.

## Section-wise: 38 widgets (Elementor panel › “CI360 Sections”)

| Page | Widgets |
|---|---|
| Home | Home – Hero · Client Logo Strip · Home – Selected Work · Home – Capabilities · Home – About Teaser · Word Marquee · Process Steps · Testimonials Slider · Insights – Featured + Stack · FAQ Accordion (Insights – Latest is also available) |
| About | Page Hero – Banner · About – Vision, Mission & Values · About – The Way We Think · About – Timeline · Team – Meet the Minds · About – Industries · Call to Action |
| Founders | Page Hero – Banner · Founders – Profiles · Founders – Shared Belief · Founders – Studio · Call to Action |
| Services | Page Hero – Banner · Services – Card Grid · Process Steps · FAQ Accordion · Call to Action |
| Work | Page Hero – Banner · Work – Portfolio Grid & Filters · Call to Action |
| Insights | Page Hero – Banner · Insights – All Articles · Insights – Topics · Call to Action |
| Blog | Page Hero – Banner · Blog – Archive (filters + search) · Call to Action (Blog – Posts Grid is also available) |
| Contact | Page Hero – Banner · Contact – Details & Form · Contact – Office Locations · Process Steps · FAQ Accordion |
| Legal | Legal Page Content · Call to Action |
| Theme Builder (Pro) | Site Header · Site Footer · Service – Full Detail · Project – Full Detail · Insight – Full Article |

Every widget opens with the demo content already filled in. Text, headings, images and lists
(logos, steps, timeline, values, industries, topics, contact details, legal sections, hero collage)
are edited in the widget. Headings accept `<br>` and `<em>…</em>` (accent words).

Lists that come from post types stay central, so they update everywhere at once:
Services, Projects, Insights, Testimonials, Team (photo = “Member photo”), FAQs (by group).
Offices, email, phone, header/footer text and labels on service/project/insight pages are in
**CI360 Settings**. Menus are in Appearance › Menus.

### Insights – Featured + Stack (home)

Dark (default, deep navy with cyan glow) or light style. One large featured article, three stacked cards beside it (a row of three below it on tablets), a swipe carousel with dots on mobile, and a
“Stay informed. Stay ahead.” bar with a button. Under **Articles** choose the source:
- **Chosen below** (default): the four articles in the widget (title, category, excerpt, image, link).
- **Latest blog posts**: newest WordPress posts (featured image, categories, excerpt).
- **CI360 Insights**: the Insights post type.

### Page Hero – Banner

The hero on About, Founders, Services, Work, Insights, Blog and Contact: dark blue banner with breadcrumb, label,
page title, intro, two buttons, a tilted main image with a small second image, a number badge, a
spinning star sticker and a row of stats. In the badge and stats, `{services}`, `{projects}`,
`{insights}`, `{team}` and `{posts}` are replaced with live counts. Links starting with `#` scroll to a
section on the same page (Services Card Grid = `#capabilities`, Portfolio = `#portfolio`,
Insights list = `#articles`, Blog grid = `#posts`, contact form = `#enquiry-form`).
The previous text-only heroes (Page Hero, Services – Hero, Work – Hero) are still available.

### Blog – Archive (filters + search)

Every blog post as a card (image with category badge, date • read time, title, excerpt, author,
“Read more”), with category filter pills and instant search in a bar that stays below the header
while scrolling. Posts in categories whose name or slug contains “case study”/“case studies” are left
out of the cards, the filters and the search (change the list in the widget). With no posts yet it
shows the CI360 Insights.

## Template-wise: Elementor library

**Templates › Saved Templates** gets:
- 9 full-page templates: “CI360 · Home (full page)”, “CI360 · About CI360 (full page)”…
- one template per section, e.g. “CI360 · Home – Hero”, “CI360 · FAQ Accordion (Our Services)”.

Insert them from the folder icon in the Elementor editor, or export them from Saved Templates to
reuse on another site.

## Good to know

- Pages built with CI360 sections still render without Elementor (the same sections are drawn by
  the theme), so deactivating Elementor never leaves a page empty.
- To rebuild only the Elementor pages/templates: `wp ci360 elementor`.
- Adding a CI360 widget to your own container: set the container to Full width with 0 padding and
  0 gap (or copy a CI360 section template, which is already set up).
- Hello Elementor's own CSS/header/footer are switched off (the CI360 design is complete). An
  Elementor Pro header/footer template, when published, replaces the CI360 one automatically.
- Services, projects and insights use the theme's single templates; with Elementor Pro you can build
  single templates using the “Full Detail” widgets.
- The contact form saves each enquiry under **Enquiries** and emails it (use an SMTP plugin).

## Files

```
style.css                  child theme header (Template: hello-elementor)
functions.php              loads inc/*
inc/hello.php              Hello Elementor integration
inc/sections-config.php    all 34 sections: settings + demo defaults
inc/sections.php           section renderers (same HTML as the demo theme)
inc/elementor.php          widget category + registration
inc/elementor-widgets.php  widget classes
inc/demo-pages.php         which sections each demo page uses
inc/demo.php               builds Elementor pages + library templates
inc/singles.php            service/project/insight pages, header, footer
inc/importer.php           content import (CPTs, images, menus)
inc/cpt.php, fields.php, components.php, helpers.php, enquiry.php, setup.php
assets/css/main.css        CI360 design
assets/css/elementor.css   Elementor layout + image-rule compatibility
assets/js/main.js          interactions (also re-binds inside the Elementor editor)
data/seed.json             demo content
```
