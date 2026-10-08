// Page transitions: swup swaps only #swup. The site menu lives outside it and
// stays on the page; on every swap it takes over the next page's body classes
// and --colorPage without animating, and is cross-faded along with the page.
// An open menu leading to a page with a small sidebar collapses into that
// page's sidebar: frozen copies of the menu (and the homepage logo above it)
// keep the leaving page's look while they collapse, the real menu switches to
// the next page's state right away.
// Those visits skip the cross-fade: only the page content fades, the menu and
// the sidebar stay opaque and animate live. Towards pages without a small
// sidebar (e.g. the homepage) the open menu collapses to the top instead.

import Swup from 'swup'
import SwupPreloadPlugin from '@swup/preload-plugin'
import SwupScrollPlugin from '@swup/scroll-plugin'

// Kirby panel/API, uploaded media and file downloads load normally
const IGNORED_PATHS = /^\/(panel|api|media)(\/|$)|\.[a-z0-9]{2,4}$/i

const nextFrame = () =>
    new Promise((resolve) =>
        requestAnimationFrame(() => requestAnimationFrame(resolve))
    )

const isMenuOpen = () =>
    document.getElementById('site-menu')?.classList.contains('is-open') ??
    false

// Everything in the incoming content except the small sidebar
const HANDOFF_FADE_SELECTOR =
    '.transition-page :has(> .sidebar-small) > :not(.sidebar-small)'

// Copy of an element with all its current styles pinned inline, so it keeps
// the leaving page's look after the body classes have changed. Pinned as
// !important: some page rules are !important and would win otherwise.
function createFrozenCopy(element) {
    const clone = element.cloneNode(true)
    const sources = [element, ...element.querySelectorAll('*')]
    const targets = [clone, ...clone.querySelectorAll('*')]

    sources.forEach((source, index) => {
        const computed = getComputedStyle(source)
        let css = ''
        for (const property of computed) {
            // The copied transform matrix keeps Chrome from transitioning it
            if (index === 0 && property === 'transform') continue
            css += `${property}:${computed.getPropertyValue(property)} !important;`
        }
        targets[index].style.cssText = css
        targets[index].removeAttribute('id')
    })

    // Pseudo-elements (e.g. the active language dot) read the page colour
    clone.style.setProperty(
        '--colorPage',
        getComputedStyle(document.body).getPropertyValue('--colorPage'),
        'important'
    )
    clone.style.setProperty('transform', 'scaleX(1)', 'important')
    clone.style.setProperty('transition', 'none', 'important')
    clone.style.setProperty('pointer-events', 'none', 'important')
    clone.setAttribute('aria-hidden', 'true')

    return clone
}

// Frozen copies of the open menu and, on the homepage, of the logo above it,
// which sits in the page header and would fade out with the leaving content.
function freezeLeavingMenu() {
    const menu = document.getElementById('site-menu')
    const menuCopy = createFrozenCopy(menu)
    menu.after(menuCopy)
    menuCopy.scrollTop = menu.scrollTop
    const copies = [menuCopy]

    const logo = document.querySelector('#swup .site-header__title')
    if (logo && logo.getClientRects().length > 0) {
        const rect = logo.getBoundingClientRect()
        const logoCopy = createFrozenCopy(logo)
        const pin = {
            position: 'fixed',
            top: `${rect.top}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            height: `${rect.height}px`,
            margin: '0',
            'z-index': getComputedStyle(logo.closest('.site-header')).zIndex,
        }
        for (const [property, value] of Object.entries(pin)) {
            logoCopy.style.setProperty(property, value, 'important')
        }
        document.body.append(logoCopy)
        copies.push(logoCopy)
    }

    return copies
}

// How the frozen copies collapse: to the left like the small-sidebar pages'
// menus, or to the top like the homepage's menu (see _menu.scss)
const COLLAPSE = {
    left: {
        origin: 'left center',
        transform: 'scaleX(0)',
        transition: 'transform 0.3s ease',
    },
    up: {
        origin: 'top center',
        transform: 'scaleY(0)',
        // Fades out from 15% of the time on. The easing collapses most of
        // the height early, so a later fade would only hide a thin line.
        opacity: '0',
        transition:
            'transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3825s ease 0.0675s',
    },
}

// Collapse the frozen copies, then remove them
function collapseFrozenCopies(copies, direction) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        copies.forEach((copy) => copy.remove())
        return
    }

    const { origin, transform, opacity, transition } = COLLAPSE[direction]
    copies.forEach((copy) => {
        copy.style.setProperty('transform-origin', origin, 'important')
        copy.style.setProperty('transition', transition, 'important')
        copy.style.setProperty('transform', transform, 'important')
        if (opacity) copy.style.setProperty('opacity', opacity, 'important')

        // Removed once collapsed
        Promise.all(
            copy
                .getAnimations()
                .map((animation) => animation.finished.catch(() => {}))
        ).then(() => copy.remove())
    })
}

// Hand the persistent menu over to the incoming page. `handOff`: the open
// menu (as frozen copies) collapses into the page's small sidebar.
function syncPersistentChrome(incoming, handOff) {
    const html = document.documentElement
    const body = document.body
    const menu = document.getElementById('site-menu')

    // Switch the menu to the next page's (closed) state without transitions
    menu?.classList.add('is-swapping')

    body.className = incoming.body.className
    body.setAttribute('style', incoming.body.getAttribute('style') || '')

    if (handOff) {
        // The new content starts in its open-menu layout, without animating
        html.classList.add('is-menu-handoff')
        body.classList.add('menu-is-open')
    }

    if (menu) {
        menu.classList.remove('is-open', 'is-closing-fast', 'is-instant-hidden')
        menu.setAttribute('aria-hidden', 'true')
        // Set by the homepage's scroll colour logic
        menu.style.backgroundColor = ''

        // Language links point at the page they were rendered for
        const languages = menu.querySelector('.site-menu__languages')
        const incomingLanguages = incoming.querySelector(
            '#site-menu .site-menu__languages'
        )
        if (languages && incomingLanguages) {
            languages.innerHTML = incomingLanguages.innerHTML
        }
    }

    nextFrame().then(() => {
        body.classList.remove('pre-init')
        menu?.classList.remove('is-swapping', 'is-page-hidden')
    })
}

// The frozen copies collapse while the page's regular transitions push the
// small sidebar back out.
function closeHandedOffMenu(copies) {
    document.documentElement.classList.remove('is-menu-handoff')
    document.body.classList.remove('menu-is-open')
    collapseFrozenCopies(copies, 'left')
}

export function setupPageTransitions({ onBeforeSwap, onAfterSwap }) {
    if (!document.getElementById('swup')) return

    // With View Transitions the browser cross-fades a snapshot of the old
    // page into the new one, so both overlap. Otherwise swup fades out,
    // swaps and fades in via the CSS in _page-transitions.scss.
    const native = 'startViewTransition' in document
    // Frozen copies of the leaving page's menu during a hand-off visit
    let frozenCopies = []

    const swup = new Swup({
        containers: ['#swup'],
        native,
        animationSelector: native ? false : '[class*="transition-"]',
        ignoreVisit: (url, { el } = {}) => {
            if (el?.closest('[data-no-swup]')) return true
            // Menu labels are translated server-side, so switching
            // language needs a full page load
            if (el?.hasAttribute('hreflang')) return true
            const { pathname } = new URL(url, window.location.origin)
            return IGNORED_PATHS.test(pathname)
        },
        plugins: [
            new SwupPreloadPlugin(),
            new SwupScrollPlugin({
                animateScroll: {
                    betweenPages: false,
                    samePageWithHash: true,
                    samePage: true,
                },
            }),
        ],
    })

    swup.hooks.on('visit:start', (visit) => {
        // Left over if the previous visit was aborted
        document.documentElement.classList.remove(
            'is-handoff-visit',
            'is-collapse-visit'
        )
        frozenCopies.forEach((copy) => copy.remove())
        frozenCopies = []
        // Browsers abort View Transitions in hidden tabs (and log an error)
        if (document.visibilityState !== 'visible') {
            visit.animation.native = false
        }
        // An open menu may be handed over to the next page; load it before
        // animating out to know whether it has a small sidebar
        if (isMenuOpen()) visit.animation.wait = true
    })

    swup.hooks.before('animation:out:start', (visit) => {
        const menu = document.getElementById('site-menu')

        // An open menu collapses as a frozen copy with the leaving page's
        // look: into the next page's small sidebar, or to the top (into the
        // hidden state of pages without one, like the homepage). No
        // cross-fade, the content fades out and in.
        if (isMenuOpen() && visit.to.document) {
            const toSidebar =
                visit.to.document.querySelector('#swup .sidebar-small') !== null
            visit.meta.menuHandoff = toSidebar
            visit.meta.menuCollapse = !toSidebar
            frozenCopies = freezeLeavingMenu()
            visit.animation.native = false
            visit.animation.selector = '.transition-page'
            document.documentElement.classList.add('is-handoff-visit')
            if (!toSidebar) {
                document.documentElement.classList.add('is-collapse-visit')
            }
            return
        }

        // Fallback only: fade the menu out together with the leaving
        // content. In native mode the menu is part of the cross-faded
        // snapshot.
        if (!native) menu?.classList.add('is-page-hidden')
    })

    // Hand-off visits wait for the content fading in next to the sidebar
    swup.hooks.on('content:replace', (visit) => {
        if (visit.meta.menuHandoff) {
            visit.animation.selector = HANDOFF_FADE_SELECTOR
        }
    })

    swup.hooks.on('visit:end', (visit) => {
        if (!visit.meta.menuHandoff && !visit.meta.menuCollapse) return
        document.documentElement.classList.remove('is-collapse-visit')
        // Keep the colour transition until the page colour has tweened
        const colorTransitions = document.body
            .getAnimations()
            .map((animation) => animation.finished.catch(() => {}))
        Promise.all(colorTransitions).then(() => {
            if (swup.visit === visit) {
                document.documentElement.classList.remove('is-handoff-visit')
            }
        })
    })

    swup.hooks.before('content:replace', (visit) => {
        onBeforeSwap()
        if (visit.to.document) {
            syncPersistentChrome(visit.to.document, visit.meta.menuHandoff)
        }
    })

    // Runs after the new content is in place and scrolled to top
    swup.hooks.on('page:view', (visit) => {
        onAfterSwap()

        const copies = frozenCopies
        frozenCopies = []
        // Let the new page render once, then collapse the menu live
        if (visit.meta.menuHandoff) {
            nextFrame().then(() => closeHandedOffMenu(copies))
        } else if (visit.meta.menuCollapse) {
            nextFrame().then(() => collapseFrozenCopies(copies, 'up'))
        }
    })

    return swup
}
