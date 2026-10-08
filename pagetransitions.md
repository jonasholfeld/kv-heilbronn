## Plan: Expressive Transitions With Shared Menu

### Revised Menu Requirement (supersedes conflicting baseline details below)

User decision: smoothly animate the outgoing menu appearance into the destination page's normal state. Do NOT preserve open state across fresh document navigations. Do NOT close the outgoing menu before its snapshot. Menu includes collapsed/minimized/expanded geometry and page/scroll-dependent color scheme. Keep unsupported-browser ordinary navigation and reduced-motion opt-out.

Corrected verified DOM facts:

- site/snippets/navi.php:6-20 conditionally renders .site-header and .side-navigation only when includeSiteMenu is true (home).
- site/snippets/navi.php:22 onward ALWAYS renders #site-menu, including .inner-page-logo, toggle controls and .site-menu\_\_inner on ALL pages. This corrects an erroneous discovery-agent report that the menu only existed on home.
- Inner templates have their own .menu-button-js triggers, sometimes more than one (exhibition text/gallery alternatives).
- Two SVG nodes can use id=header-svg on home. Current global query selects the first. When establishing transition identities or initial-state calculations, use scoped classes/selectors and unique instance IDs for these two menu logos; update only affected selectors as needed.
- main.js updateHeaderWidth:210 onward derives homepage header geometry from scroll and can close menu near bottom expansion. updateHeaderLogoColor:329 onward derives color from active homepage section and updates logo, panel and footer.
- main.js closeMenu:522 and handleMenuButtonClick:561 update .is-open/.hidden, body.menu-is-open, gallery states and ARIA. Keep these as authoritative ordinary interaction behavior.
- \_menu.scss:3 base collapsed panel uses scaleY(0)/opacity 0; :64 .is-open restores scaleY(1). :225 onward shop/edition/katalog/reise/ausstellung.text-mode use scaleX(0), different logo/panel/background composition. :322 white-font overrides colors. Nested overrides and media queries determine actual state; do not infer state only from body classes.

### Revised Execution Steps

Phase A: Native baseline (before adding menu complexity)

1. Add shared transition partial imported by main.scss; opt into native cross-document transitions only for no-preference motion. Build and test one same-origin link in production CSS. No router or click interception.
2. Implement ~400ms upward reveal for root content with existing easing, ordinary fallback and reduced-motion rules. Rebuild and verify before menu edits. Depends on step 1.

Phase B: Shared menu identity and first working morph 3. Add stable transition identities for corresponding logical menu components: panel/shell, visible logo, and visible compact trigger. Use .site-menu as panel anchor; match homepage logo to inner-page logo when visible; match homepage compact trigger to the appropriate visible template trigger. Do not name the entire .site-header if separately naming its logo/controls. Assign each identity to at most one rendered element per document. Use CSS for unambiguous identities, narrowly scoped lifecycle naming when multiple template controls compete. Keep menu snapshots above root wipe. Depends on step 2. 4. Implement a first behavior check on open-home-menu -> inner-page-default-collapsed navigation. Retain full outgoing snapshot; destination keeps its own normal state. Morph panel bounds/position when both sides have meaningful visible geometry; fade/retract links and logo when destination lacks visible counterparts. Hidden scale-zero/display-none targets cannot provide a useful visible snapshot: do not claim the browser automatically morphs these into a compact button. If needed, add a small dedicated untransformed shell/visual anchor to navi.php with nonzero collapsed bounds and snapshot only the appropriate visible trigger. Preserve existing page layout. Avoid scaling a bitmap full of text from expanded panel into a button. Rebuild and capture intermediate frames to verify this exact pair before broadening. 5. Extend styles to minimized/collapsed/open source states and cross-template color changes. Let geometry interpolate where appropriate; crossfade old/new color treatments smoothly without claiming the snapshot crossfade interpolates each live CSS color property. Keep logo aspect ratio and text legibility via separate snapshots/object fitting rather than arbitrary whole-header stretching. Shared menu completes in the same ~400ms window as content. Depends on step 4.

Phase C: Incoming-state readiness 6. Make destination layout/colors correct BEFORE destination snapshot. Existing main.js module mutates homepage geometry/color and hidden sections after parsing; verify early capture behavior in Chromium/Safari. Extract only the minimal menu initial-state computation from existing logic if needed, ensuring ordinary initialization and transition initialization reuse the same calculation. Emit known default page colors/states in navi.php using existing initialHeaderColor/--colorPage data. For JS-only/restored-scroll states, add an early small lifecycle bootstrap loaded in head.php (blocking/render readiness only for critical initialization if demonstrated necessary), using feature-detected pageswap/pagereveal. Register before pagereveal; do not rely on late main.js listener or await ready to set incoming identity/state. Establish snapshot state without running overlapping live CSS animations; restore ordinary animation behavior after transition. Do not await all gallery assets. Depends on steps 3-5; refine alongside them when snapshot timing blocks first morph. 7. Ensure BFCache and history: dynamically assigned names and transition-only classes cleared after capture/completion, never serialize or mutate outgoing open state just to force a transition. Back/forward should respect native restored document state and scroll, unlike fresh navigation defaults. Ensure nontransition events, canceled/skipped transitions, disabled JS/bootstrap and unsupported APIs never leave invisible/locked menus. Reuse actual ARIA/class synchronization; no persistent overlay, blanket body hidden state or click delay.

Phase D: Verification 8. npm run build after each slice; PHP lint any touched snippets. Test actual Kirby production build with VITE_DEV_SERVER=false and PHP server; dev separately. Do not run make serve (commits/deploys). 9. Browser matrix Chromium/Safari 18.2+ and unsupported Firefox; desktop/mobile/short-height layouts; reduced motion. Capture start/mid/end frames for expanded -> collapsed, collapsed -> expanded-home-logo, minimized home -> inner, different page color/white-font modes, and open menu language navigation. Assert menu is not part of root wipe, no duplicate names, no text stretching/logo jump, color jump or blank homepage. Confirm fresh destination default state vs native BFCache restored state, keyboard/link/new-tab/download/external/hash behavior and slow-network skip fallback.

### Revised Critical Files (absolute paths)

- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/main.scss: transition partial import.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/\_page-transitions.scss: new partial for root reveal, independent shared menu groups and reduced motion.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/site/snippets/navi.php: stable logical visual anchors, unique/scoped logo selectors and server-known incoming defaults; minimal wrapper only if collapsed-target test needs it.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/\_menu.scss: minimal anchoring/state snapshot adjustments, preserve current visual variants and live toggles.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/js/main.js: reuse initial menu/header state calculations, visible trigger selection, integration with existing closeMenu/open/color/width functions; no second competing menu controller.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/site/snippets/head.php: early critical lifecycle/bootstrap integration if needed for consistent snapshot timing.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/js/page-transitions.js: planned isolated lifecycle helper if JavaScript coordination is needed; early head loading must be deliberately configured rather than assuming late import is timely.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/\_home.scss: conditional initial reveal adjustment only if entry compounding reproduced.
- Existing template-local .menu-button-js nodes: prefer identifying the visible instance through selectors instead of editing all templates.
- vite.config.js: keep unchanged unless isolated early bootstrap requires an explicit entry; this is asset-loading plumbing, not transition logic.

Scope remains native full-document Kirby navigation, no SPA conversion, persistent menu DOM or Swup/Barba/GSAP dependency. Shared menu animation is now REQUIRED, not optional logo dissolve. A small lifecycle coordinator is justified for identity/readiness, not navigation interception. State persistence across fresh loads is explicitly excluded. The earlier baseline below is retained as reference; its steps 4 and 6 and conditional-only menu integration file descriptions are superseded by this revision.

---

## Original Baseline Reference

TL;DR: Add native cross-document View Transitions to this server-rendered Kirby site. Vite bundles SCSS but does not implement navigation transitions. Use a CSS-first upward wipe/reveal (~400ms) with a separately fading site header, normal navigation in unsupported browsers, and reduced-motion opt-out. No router conversion or navigation library.

User decisions:

- Unsupported browsers may navigate normally without animation.
- Prefer a more expressive slide or wipe over a subtle fade.
- This is a planning request; no implementation has been performed.

Verified architecture:

- Workspace root: /Users/jonasholfeld/workspace/26/KV Heilbronn/website
- site/snippets/head.php:7 loads src/js/main.js on every page via snippet('vite').
- src/js/main.js:1 imports src/scss/main.scss. Existing header sizing/color, menu state, image row measurements, home visibility, hash behavior are initialized per full document load.
- site/snippets/vite.php emits module scripts in development (CSS imported/injected by Vite); production emits manifest CSS as link rel=stylesheet plus module script. Production CSS is render-blocking; development timing can differ.
- Normal Kirby links, no client router. Template main wrappers have different classes; header uses .site-header and menu is separate .site-menu.
- src/js/main.js closeMenu (around line 522) mutates header/gallery/menu classes; click handler deliberately leaves menu-inner links alone. Preserve outgoing menu state as part of outgoing root snapshot initially, do not insert artificial close/delay before navigation.
- src/scss/\_home.scss:31-45 initially hides main.home > div, then main.js updateHomeSectionVisibility (around 690) reveals visible sections with existing 1s animation. Need check for compounded/blank entry.
- Header widths depend on scroll (99rem vs 23.4rem) and colors vary. Do not accept default shared-element geometry scaling for the logo.
- package.json has npm run dev, build, preview; no automated test script/dependency identified.
- Makefile start runs Kirby :8000 and Vite. Makefile serve builds, stages, commits, pushes, deploys: DO NOT use serve for validation.

### Steps

Phase 1: Native baseline

1. Add a dedicated transition SCSS partial and import it from main.scss after the existing imports. Opt both source/destination documents into cross-document navigation with @view-transition navigation:auto. Keep existing Vite configuration and all template links unchanged. Initial falsifiable check: ordinary same-origin page link triggers a root transition in a supported browser with built CSS; no JS interception is necessary.
2. Build immediately with npm run build and smoke-test one real Kirby navigation using production assets (Vite stopped or VITE_DEV_SERVER=false). Fix parser/build or opt-in issues before visual expansion. Dependent on step 1.

Phase 2: Visual polish 3. Style root old/new snapshots: old page stays in place and fades gently; destination reveals bottom-to-top using clip-path inset, ~400ms with existing cubic-bezier(0.22,1,0.36,1) easing. Avoid translating/scaling entire document and broad blend ghosting. Explicitly coordinate group/old/new duration and layering. Dependent on successful step 2. 4. Give only .site-header a unique stable view-transition-name via CSS. Use a short dissolve; suppress default group geometry morph to avoid stretching SVG when source and destination header sizes differ. Ensure header group is above root in transition overlay. Leave footer/menu/content in root snapshot initially. Named header can be omitted if its initial JS-dependent geometry cannot be captured reliably without broad refactoring. Dependent on step 3; test scrolled-to-top/scrolled-to-scrolled navigation and language switch. 5. Scope @view-transition opt-in to prefers-reduced-motion:no-preference and disable all transition snapshot animations under reduce. Unsupported browsers ignore feature and retain ordinary links. Do not hide body or add blocking overlays/delays. Can be implemented alongside steps 3-4, validate jointly.

Phase 3: Compatibility and delivery 6. Validate incoming homepage visibility, initial header/color state, pre-init removal, image/font shifts and menu snapshots under cold network/cache. Existing home animation must not compound into a blank/delayed reveal. Only if reproduced, add a small isolated initialization adjustment in main.js and/or \_home.scss; preserve scroll-driven section reveals after entry. Do not await all gallery images or render-block the entire large script. If a critical early snapshot needs a lifecycle handler, use supported cross-document pageswap/pagereveal APIs and an early isolated head listener, not startViewTransition around window.location. No generic click interception. Dependent on steps 3-5. 7. Rebuild, browser-test matrix below, inspect diff for narrowly scoped changes, record fallback behavior. If PHP changed, php -l each touched snippet. No commits, deployment or unrelated cleanup. Dependent on step 6.

### Relevant Files (absolute)

- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/main.scss: required edit, import transition styles, .site-header styling reference.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/\_page-transitions.scss: planned new partial, opt-in, keyframes, snapshot selectors, header naming, reduced-motion handling.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/js/main.js: reference and conditional minimal edit only for reproduced initial-view readiness, existing closeMenu and updateHomeSectionVisibility logic.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/src/scss/\_home.scss: reference and conditional targeted adjustment for compounded initial reveals.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/site/snippets/head.php: reference; conditional early lifecycle integration only if needed by evidence.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/site/snippets/navi.php: reference .site-header, .site-menu, normal Kirby/language links. No routine markup edits required.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/site/snippets/vite.php: reference dev/production CSS difference, no planned edit.
- /Users/jonasholfeld/workspace/26/KV Heilbronn/website/vite.config.js: unchanged.

### Verification

1. npm run build after first edit and final edits; existing Sass warnings distinguish from new failures.
2. Serve production bundle with php -S 127.0.0.1:8000 -t . kirby/router.php; use a free port if needed. Set VITE_DEV_SERVER=false if development server is running. Test dev via make start separately, not npm run preview alone (Kirby must render PHP).
3. Supported current Chromium and Safari 18.2+; Firefox/unsupported fallback. MDN compatibility at research time: Chromium cross-document support from 126, Safari 18.2, Firefox unsupported. document.startViewTransition availability alone is not sufficient to prove cross-document support.
4. Desktop and mobile: home -> exhibition index -> exhibition detail -> travel -> shop -> home; switch German/English; back/forward restores native scroll; hash-only navigation, external links, downloads, modifier-click/new tabs and direct page loads remain native.
5. Menu open/closed navigation, rapidly repeated navigation, scrolled header positions, page-specific colors. No overlay left behind, logo stretching, duplicate transition names, invisible destination, horizontal overflow, errors or layout shifts.
6. Emulate prefers-reduced-motion:reduce and unsupported browser; navigation must stay usable with no added animation. Compare homepage initial reveal and existing scroll animations; changes are scoped to page transitions rather than wholesale existing animation cleanup.
7. Cold cache and throttled network; supported browser may skip a slow transition, and page must still render normally. Verify production CSS is loaded on both documents and transition completes without a body visibility dependency.

### Decisions / Scope

- Includes sitewide eligible same-origin document navigations; language links only if same origin.
- Excludes SPA/fetch navigation, Swup/Barba/GSAP dependency, shared thumbnail-to-artwork morphs, gallery transitions, prefetch infrastructure and global redesign.
- Native full loads still fetch/render Kirby pages; animation does not by itself speed navigation.
- Cross-origin navigation and redirects are not animated; normal fallback is intentional.
- document.startViewTransition is for same-document DOM updates, not wrapping a full page location change.
- Future shared artwork morphing can be a separate phase with per-item stable identity, unique names and lifecycle handling; not required now.

Sources: MDN https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/@view-transition and https://developer.mozilla.org/en-US/docs/Web/API/View_Transition_API/Using .
