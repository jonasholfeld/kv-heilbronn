// Template-specific JS for the "kunstverein" page.
// init() runs on every page view; listeners are removed when `signal` aborts.

// Mobile: the text blocks are an accordion. Each block collapses to its first
// paragraph (the heading); tapping it opens the rest (see
// _mobile-kunstverein.scss, the classes do nothing on desktop).
export function init(signal) {
    const blocks = [
        ...document.querySelectorAll(
            '.kunstverein-blocks .kunstverein-block:not(.satzung):not(.kunstverein-block--form)'
        ),
    ]

    const setOpen = (block, open) => {
        block.classList.toggle('is-open', open)
        block
            .querySelector('.kunstverein-block__heading')
            ?.setAttribute('aria-expanded', String(open))
    }

    blocks.forEach((block) => {
        const heading = block.querySelector(':scope > div > :first-child')
        if (!heading) return
        heading.classList.add('kunstverein-block__heading')
        heading.setAttribute('role', 'button')
        heading.setAttribute('tabindex', '0')
        heading.setAttribute('aria-expanded', 'false')

        const toggle = () => setOpen(block, !block.classList.contains('is-open'))
        heading.addEventListener('click', toggle, { signal })
        heading.addEventListener(
            'keydown',
            (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return
                event.preventDefault()
                toggle()
            },
            { signal }
        )
    })

    // Links to a section (/kunstverein#team) open it
    const openFromHash = () => {
        if (!window.location.hash) return
        const target = document.getElementById(
            decodeURIComponent(window.location.hash.slice(1))
        )
        const block = target?.closest('.kunstverein-block')
        if (!block || !blocks.includes(block)) return
        setOpen(block, true)
        block.scrollIntoView({ block: 'start' })
    }

    openFromHash()
    window.addEventListener('hashchange', openFromHash, { signal })
}
