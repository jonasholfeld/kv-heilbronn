// Template-specific JS for the "shop" overview.
// init() runs on every page view; listeners are removed when `signal` aborts.

// Mobile: the page starts with a choice between Editionen and Kataloge; once
// one is chosen, both become tabs above it (see _mobile-shop.scss, the class
// does nothing on desktop). The chosen section is kept in the URL hash, so
// coming back from a shop item shows it again.
const SECTIONS = ['editionen', 'kataloge']

export function init(signal) {
    const overview = document.querySelector('.shop-overview')
    const tabs = [...document.querySelectorAll('[data-shop-tab]')]
    if (!overview || tabs.length === 0) return

    const show = (section) => {
        SECTIONS.forEach((name) =>
            overview.classList.toggle(`is-showing-${name}`, name === section)
        )
        overview.classList.toggle('has-section', SECTIONS.includes(section))
        tabs.forEach((tab) =>
            tab.setAttribute('aria-pressed', String(tab.dataset.shopTab === section))
        )
    }

    show(window.location.hash.slice(1))

    tabs.forEach((tab) => {
        tab.addEventListener(
            'click',
            () => {
                const section = tab.dataset.shopTab
                show(section)
                // Keeps swup's state of this history entry
                history.replaceState(history.state, '', `#${section}`)
                window.scrollTo(0, 0)
            },
            { signal }
        )
    })
}
