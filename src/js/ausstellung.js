// Template-specific JS for the "ausstellung" page.
// init() runs on every page view; listeners are removed when `signal` aborts.

export function init(signal) {
    const topInfoBox = document.querySelector(
        '.single-ausstellung-page__top-info-box'
    )
    const smallTopInfoBoxWrapper = document.querySelector(
        '.single-ausstellung-page__small-top-info-box-wrapper'
    )
    const smallTopInfoBox = document.querySelector(
        '.single-ausstellung-page__small-top-info-box'
    )

    if (topInfoBox && smallTopInfoBoxWrapper && smallTopInfoBox) {
        const EASE_OUT = 'cubic-bezier(0.22, 1, 0.36, 1)'
        let smallBoxVisible = false

        const showSmallBox = () => {
            if (smallBoxVisible) return
            smallBoxVisible = true
            smallTopInfoBoxWrapper.style.overflow = 'hidden'
            const targetHeight = smallTopInfoBoxWrapper.scrollHeight
            // start inner box squished so it stretches up into view
            smallTopInfoBox.style.transform = 'scaleY(0)'
            void smallTopInfoBox.offsetHeight // commit scaleY(0) as from-state
            smallTopInfoBoxWrapper.style.transition = `height 0.45s ${EASE_OUT}`
            smallTopInfoBox.style.transition = `transform 0.45s ${EASE_OUT}`
            requestAnimationFrame(() => {
                smallTopInfoBoxWrapper.style.height = targetHeight + 'px'
                smallTopInfoBox.style.transform = 'scaleY(1)'
            })
            const onEnd = (e) => {
                if (
                    e.target !== smallTopInfoBoxWrapper ||
                    e.propertyName !== 'height'
                )
                    return
                smallTopInfoBoxWrapper.style.height = 'auto'
                smallTopInfoBoxWrapper.style.overflow = ''
                smallTopInfoBoxWrapper.style.transition = ''
                smallTopInfoBox.style.transform = ''
                smallTopInfoBox.style.transition = ''
                smallTopInfoBoxWrapper.removeEventListener('transitionend', onEnd)
            }
            smallTopInfoBoxWrapper.addEventListener('transitionend', onEnd)
        }

        const hideSmallBox = () => {
            if (!smallBoxVisible) return
            smallBoxVisible = false
            smallTopInfoBoxWrapper.style.height =
                smallTopInfoBoxWrapper.scrollHeight + 'px'
            smallTopInfoBoxWrapper.style.overflow = 'hidden'
            void smallTopInfoBoxWrapper.offsetHeight // commit explicit height as from-state
            smallTopInfoBoxWrapper.style.transition = `height 0.45s ${EASE_OUT}`
            smallTopInfoBox.style.transition = `transform 0.45s ${EASE_OUT}`
            requestAnimationFrame(() => {
                smallTopInfoBoxWrapper.style.height = '0'
                smallTopInfoBox.style.transform = 'scaleY(0)'
            })
            const onEnd = (e) => {
                if (
                    e.target !== smallTopInfoBoxWrapper ||
                    e.propertyName !== 'height'
                )
                    return
                smallTopInfoBoxWrapper.style.height = ''
                smallTopInfoBoxWrapper.style.overflow = ''
                smallTopInfoBoxWrapper.style.transition = ''
                smallTopInfoBox.style.transform = ''
                smallTopInfoBox.style.transition = ''
                smallTopInfoBoxWrapper.removeEventListener('transitionend', onEnd)
            }
            smallTopInfoBoxWrapper.addEventListener('transitionend', onEnd)
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        hideSmallBox()
                    } else {
                        showSmallBox()
                    }
                })
            },
            { threshold: 0 }
        )

        observer.observe(topInfoBox)
        signal.addEventListener('abort', () => observer.disconnect())
    }

    const creditWrappers = document.querySelectorAll('.credits-wrapper')

    const toggleCredits = (wrapper) => {
        const collapse = wrapper.querySelector('.credits-collapse-wrapper')
        const content = wrapper.querySelector('.credits-content-wrapper')
        if (!collapse || !content) return

        const isOpen = wrapper.classList.contains('is-open')
        const targetHeight = content.scrollHeight
        const targetWidth = content.scrollWidth
        const duration = '0.35s'
        const easing = 'cubic-bezier(0.22, 1, 0.36, 1)'

        collapse.style.overflow = 'hidden'
        collapse.style.transition = `height ${duration} ${easing}, width ${duration} ${easing}`
        // Pull following content back up by the amount the bubble grows, so
        // the open bubble overlaps the white space instead of pushing the
        // next image down. Same timing as the collapse keeps layout steady.
        wrapper.style.transition = `border-radius 0.3s ease, margin-bottom ${duration} ${easing}`

        if (isOpen) {
            collapse.style.height = targetHeight + 'px'
            collapse.style.width = targetWidth + 'px'
            void collapse.offsetHeight
            requestAnimationFrame(() => {
                collapse.style.height = '0px'
                collapse.style.width = '0px'
                wrapper.style.marginBottom = '0px'
            })
            wrapper.classList.remove('is-open')
            return
        }

        collapse.style.height = '0px'
        collapse.style.width = '0px'
        void collapse.offsetHeight
        requestAnimationFrame(() => {
            collapse.style.height = targetHeight + 'px'
            collapse.style.width = targetWidth + 'px'
            wrapper.style.marginBottom = -targetHeight + 'px'
        })
        wrapper.classList.add('is-open')
    }

    creditWrappers.forEach((wrapper) => {
        wrapper.addEventListener('click', () => toggleCredits(wrapper))
    })

    window.addEventListener('resize', () => {
        creditWrappers.forEach((wrapper) => {
            if (!wrapper.classList.contains('is-open')) return
            const collapse = wrapper.querySelector('.credits-collapse-wrapper')
            const content = wrapper.querySelector('.credits-content-wrapper')
            if (!collapse || !content) return
            collapse.style.height = content.scrollHeight + 'px'
            collapse.style.width = content.scrollWidth + 'px'
            wrapper.style.marginBottom = -content.scrollHeight + 'px'
        })
    }, { signal })

    // Switching between image and text mode: the current view fades out while
    // drifting up, the layout is swapped while invisible, then the new view
    // fades in rising from slightly below.
    const page = document.querySelector('.single-ausstellung-page')
    let modeSwitching = false

    const setTextMode = async (on) => {
        const siteMenu = document.querySelector('.site-menu')
        const apply = () => {
            // The menu's closed/open transforms differ between the modes
            // (scaleY vs scaleX, see _menu.scss). Swap them without a
            // transition so a closed menu never flashes and an open one
            // stays put; only its colour is allowed to tween.
            if (siteMenu) {
                siteMenu.style.transition = 'background-color 0.6s ease'
            }
            // Lets the menu logo tween its colours too (see _ausstellung.scss)
            document.body.classList.add('is-mode-switching')
            // Snap the page layout too, so the sidebar and margins don't
            // visibly resize while the new view fades in
            document.body.classList.add('is-mode-swapping')
            document.body.classList.toggle('text-mode', on)
            window.scrollTo(0, 0)
            document.documentElement.scrollTop = 0
            document.body.scrollTop = 0
            void document.body.offsetHeight // commit new styles untransitioned
            requestAnimationFrame(() => {
                document.body.classList.remove('is-mode-swapping')
                if (siteMenu) siteMenu.style.transition = ''
            })
        }

        if (
            !page ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            apply()
            document.body.classList.remove('is-mode-switching')
            return
        }
        if (modeSwitching) return
        modeSwitching = true

        // Animate main's children rather than main itself: a transform on main
        // would re-anchor the position: fixed bottom buttons inside it.
        const animateViews = (keyframes, options) =>
            Array.from(page.children).map((el) => {
                const isFixed = getComputedStyle(el).position === 'fixed'
                const frames = isFixed
                    ? keyframes.map(({ opacity }) => ({ opacity }))
                    : keyframes
                return el.animate(frames, { fill: 'forwards', ...options })
            })

        const outAnimations = animateViews(
            [
                { opacity: 1, transform: 'translateY(0)' },
                { opacity: 0, transform: 'translateY(-1.5rem)' },
            ],
            { duration: 280, easing: 'cubic-bezier(0.55, 0, 1, 0.45)' }
        )
        await Promise.all(outAnimations.map((a) => a.finished)).catch(() => {})

        document.body.style.transition = 'background-color 0.6s ease'
        apply()

        const inAnimations = animateViews(
            [
                { opacity: 0, transform: 'translateY(2.5rem)' },
                { opacity: 1, transform: 'translateY(0)' },
            ],
            { duration: 600, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
        )
        outAnimations.forEach((a) => a.cancel())
        await Promise.all(inAnimations.map((a) => a.finished)).catch(() => {})
        // Drop the filled transforms so sticky/fixed positioning is untouched
        inAnimations.forEach((a) => a.cancel())
        document.body.style.transition = ''
        document.body.classList.remove('is-mode-switching')
        modeSwitching = false
    }

    const toggleTextModeJs = document.querySelector('.toggle-text-mode-js')

    if (toggleTextModeJs) {
        toggleTextModeJs.addEventListener('click', () => setTextMode(true))
    }

    const closeTextModeJs = document.querySelector('.close-text-mode-js')

    if (closeTextModeJs) {
        closeTextModeJs.addEventListener('click', () => setTextMode(false))
    }

    const ausstellungImageElements = document.querySelectorAll(
        '.single-ausstellung-page__images-wrapper .scroll-container > .image-coupler, .single-ausstellung-page__images-wrapper .scroll-container > .single-ausstellung-page__images-wrapper__image'
    )

    if (ausstellungImageElements.length > 0) {
        const firstAusstellungImageElement = ausstellungImageElements[0]

        const updateAusstellungImageVisibility = () => {
            ausstellungImageElements.forEach((el) => {
                if (
                    el === firstAusstellungImageElement &&
                    window.scrollY <= window.innerHeight * 0.25
                ) {
                    el.classList.add('is-visible')
                    return
                }

                const rect = el.getBoundingClientRect()
                const hiddenTranslateOffset = el.classList.contains('is-visible')
                    ? 0
                    : 20
                const adjustedTop = rect.top - hiddenTranslateOffset
                const adjustedBottom = rect.bottom - hiddenTranslateOffset
                const visiblePx =
                    Math.min(adjustedBottom, window.innerHeight) -
                    Math.max(adjustedTop, 0)
                const visibleRatio = rect.height > 0 ? visiblePx / rect.height : 0

                if (visibleRatio >= 0.1) {
                    el.classList.add('is-visible')
                } else if (adjustedTop >= window.innerHeight) {
                    el.classList.remove('is-visible')
                }
            })
        }

        window.addEventListener('scroll', updateAusstellungImageVisibility, {
            passive: true,
            signal,
        })
        window.addEventListener('resize', updateAusstellungImageVisibility, {
            passive: true,
            signal,
        })
        firstAusstellungImageElement.classList.add('is-visible')
        updateAusstellungImageVisibility()
    }
}
