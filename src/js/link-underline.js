// Animated underline: on hover the line shrinks from the right to the left
// and stays away; it is drawn again from the left once the mouse leaves. Used
// for all underlined links, the "more" labels inside cards (hovering the
// whole card plays it) and links in form consents. The text is wrapped in a
// span that carries the line as a background, so it also works across line
// breaks and leaves the element's own ::before (e.g. the square in front of
// e-mail addresses) alone. See .link-animated in main.scss.

// Labels inside a card link, animated when the card is hovered
const CARD_LABELS = [
    '.infobox__link',
    '.shop-item__link',
    '.exhibition__link',
    '.reise-card__mehr',
    '.reise-card__anmeldung',
    '.shop-item-card__link',
].join(', ')

// Links in form consents, animated in any case
const TEXT_LINKS = '.kv-form__consent a[href]'

// Without a line until hovered
const REVEAL_LINKS = [
    'footer a',
    '.site-menu__toggle--close',
    '.site-menu__imprint-btn',
    '.site-menu__data-btn',
].join(', ')

// Only links that are underlined anyway: buttons, bubbles and image links
// keep their look
function isUnderlined(link) {
    return getComputedStyle(link).textDecorationLine.includes('underline')
}

function animate(element) {
    const label = document.createElement('span')
    label.className = 'link-animated__label'
    label.append(...element.childNodes)
    element.append(label)
    element.classList.add('link-animated')
}

export function setupLinkUnderlines(root = document) {
    root.querySelectorAll('a[href]:not(.link-animated)').forEach((link) => {
        if (!isUnderlined(link)) return
        if (link.querySelector('img, svg, video')) return
        animate(link)
    })

    root.querySelectorAll(
        `:is(${CARD_LABELS}, ${TEXT_LINKS}):not(.link-animated)`
    ).forEach(animate)

    // Footer links and some buttons of the menu have no line; it is drawn
    // while hovered
    root.querySelectorAll(`:is(${REVEAL_LINKS}):not(.link-animated)`).forEach(
        (link) => {
            animate(link)
            link.classList.add('link-animated--reveal')
        }
    )
}

// Links with a marker right in front of them (a bullet of their list item or
// the square of an e-mail address): while the link is hovered the circle
// turns into a square and the square into a circle (see .link-marker in
// main.scss). Only if nothing but the link follows the marker.
const MARKER_LEVELS = 3

// A marker is a small coloured circle or square, about as big as the text.
// Other ::before boxes don't count, e.g. the placeholder that pushes a small
// sidebar aside while the menu is open (_sidebar.scss).
function hasMarker(element) {
    const marker = getComputedStyle(element, '::before')
    const width = parseFloat(marker.width)
    const height = parseFloat(marker.height)
    const fontSize = parseFloat(getComputedStyle(element).fontSize)
    return (
        marker.content !== 'none' &&
        marker.display !== 'none' &&
        !isTransparent(marker.backgroundColor) &&
        width > 0 &&
        Math.abs(width - height) <= 1 &&
        width <= 2 * fontSize
    )
}

// The first node of `element` that is not just white space or a line break
function firstContent(element) {
    return [...element.childNodes].find((node) =>
        node.nodeType === Node.TEXT_NODE
            ? node.textContent.trim() !== ''
            : node.nodeName !== 'BR'
    )
}

// The link itself or an ancestor it starts with, if that carries a marker
function findMarker(link) {
    let element = link
    for (let level = 0; level <= MARKER_LEVELS && element; level++) {
        if (hasMarker(element)) return element
        const parent = element.parentElement
        if (!parent || firstContent(parent) !== element) return null
        element = parent
    }
    return null
}

// Every other text link gets a round dot in front of it (.link-dot in
// main.scss): on white in the page colour (grey without one), on a coloured
// box in white. It turns square on hover like the markers above. Not for
// buttons, image links, whole boxes that are links, the menu and the page
// titles in the top bars.
const NO_DOT = '#site-menu, .bubble, .page-info, .site-header'
// Phone numbers, also the ones iOS turns into links by itself
const NO_DOT_LINKS = 'a[href^="tel:"], a[x-apple-data-detectors]'
const BLOCK_CONTENT = 'p, div, ul, ol, h1, h2, h3, h4, header, section'

function isTransparent(color) {
    return color === 'transparent' || /rgba\(.*,\s*0\)$/.test(color)
}

// The element whose background shows behind `element`
function backdropOf(element) {
    for (let node = element; node; node = node.parentElement) {
        if (!isTransparent(getComputedStyle(node).backgroundColor)) return node
    }
    return null
}

// Whether `element` starts a line: nothing but white space before it, up to
// the next block or line break. Links within a sentence get no dot.
function startsLine(element) {
    for (let node = element; node.parentElement; node = node.parentElement) {
        let previous = node.previousSibling
        while (previous?.nodeType === Node.TEXT_NODE && !previous.textContent.trim()) {
            previous = previous.previousSibling
        }
        if (previous) {
            return (
                previous.nodeName === 'BR' ||
                (previous.nodeType === Node.ELEMENT_NODE &&
                    getComputedStyle(previous).display !== 'inline') ||
                getComputedStyle(node).display !== 'inline'
            )
        }
        if (getComputedStyle(node.parentElement).display !== 'inline') return true
    }
    return true
}

// A link that looks like a button or tag (on a rounded background) is not a
// text link
function isTextLink(element) {
    if (element.closest(NO_DOT) || element.matches(NO_DOT_LINKS)) return false
    if (!startsLine(element)) return false
    if (element.querySelector('img, svg, video, ' + BLOCK_CONTENT)) return false
    if (element.textContent.trim() === '') return false
    const backdrop = backdropOf(element)
    return (
        !backdrop ||
        parseFloat(getComputedStyle(backdrop).borderTopLeftRadius) === 0
    )
}

function dotColor(element) {
    const backdrop = backdropOf(element)
    if (
        backdrop &&
        getComputedStyle(backdrop).backgroundColor !== 'rgb(255, 255, 255)'
    ) {
        return 'white'
    }
    return (
        getComputedStyle(document.body).getPropertyValue('--colorPage').trim() ||
        '#dce0e3'
    )
}

// Which candidates get a dot, and in which colour. Decided for all of them
// first, then applied, so adding one dot doesn't change the next decision.
function updateDots(candidates) {
    candidates
        .map((element) => [
            element,
            isTextLink(element) ? dotColor(element) : null,
        ])
        .forEach(([element, color]) => {
            element.classList.toggle('link-dot', !!color)
            element.classList.toggle('link-marker', !!color)
            element.classList.toggle('link-marker--round', !!color)
            if (color) element.style.setProperty('--link-dot-color', color)
        })
}

// Backgrounds and shapes are still fading in when a page is shown (0.5s),
// so the dots are checked again once they are settled
const DOT_RECHECK_MS = 700

function addMarker(marker, link, signal) {
    const isSquare =
        parseFloat(getComputedStyle(marker, '::before').borderTopLeftRadius) === 0
    marker.classList.add(
        'link-marker',
        isSquare ? 'link-marker--square' : 'link-marker--round'
    )
    addHover(marker, link, signal)
}

function addHover(marker, link, signal) {
    link.addEventListener(
        'pointerenter',
        () => marker.classList.add('is-link-hovered'),
        { signal }
    )
    link.addEventListener(
        'pointerleave',
        () => marker.classList.remove('is-link-hovered'),
        { signal }
    )
}

export function setupLinkMarkers(signal, root = document) {
    const dotCandidates = []

    root.querySelectorAll('a[href]').forEach((link) => {
        if (link.closest(NO_DOT)) return
        if (link.classList.contains('link-dot')) {
            dotCandidates.push(link)
            addHover(link, link, signal)
            return
        }
        const marker = findMarker(link)
        if (marker) {
            addMarker(marker, link, signal)
        } else {
            dotCandidates.push(link)
            addHover(link, link, signal)
        }
    })

    // The "more" labels inside box links; some have a dot of their own
    root.querySelectorAll(CARD_LABELS).forEach((label) => {
        const link = label.closest('a[href]')
        if (!link || label.closest(NO_DOT)) return
        if (hasMarker(label) && !label.classList.contains('link-dot')) {
            addMarker(label, link, signal)
            return
        }
        dotCandidates.push(label)
        addHover(label, link, signal)
    })

    updateDots(dotCandidates)
    const recheck = setTimeout(() => updateDots(dotCandidates), DOT_RECHECK_MS)
    signal.addEventListener('abort', () => clearTimeout(recheck))
}
