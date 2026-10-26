// Animated underline: on hover the line shrinks from the right to the left
// and stays away; it is drawn again from the left once the mouse leaves. Used for external links (other hosts, e-mail), the
// "more" labels inside cards (hovering the whole card plays it) and links in
// form consents. The text is wrapped in a span that carries the line as a
// background, so it also works across line breaks and leaves the element's
// own ::before (e.g. the square in front of e-mail addresses) alone. See
// .link-animated in main.scss.

// Labels inside a card link, animated when the card is hovered
const CARD_LABELS = [
    '.infobox__link',
    '.shop-item__link',
    '.exhibition__link',
    '.reise-card__mehr',
    '.shop-item-card__link',
].join(', ')

// Internal links in running text that get the animation as well
const TEXT_LINKS = '.kv-form__consent a[href]'

function isExternal(link) {
    if (link.protocol === 'mailto:') return true
    if (link.protocol !== 'http:' && link.protocol !== 'https:') return false
    return link.hostname !== window.location.hostname
}

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
        if (!isExternal(link) || !isUnderlined(link)) return
        if (link.querySelector('img, svg, video')) return
        animate(link)
    })

    root.querySelectorAll(
        `:is(${CARD_LABELS}, ${TEXT_LINKS}):not(.link-animated)`
    ).forEach(animate)
}
