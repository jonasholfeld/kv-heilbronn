// Template-specific JS for the "ausstellung" page.

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

    if (isOpen) {
        collapse.style.height = targetHeight + 'px'
        collapse.style.width = targetWidth + 'px'
        void collapse.offsetHeight
        requestAnimationFrame(() => {
            collapse.style.height = '0px'
            collapse.style.width = '0px'
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
    })
})

const toggleTextModeJs = document.querySelector('.toggle-text-mode-js')

if (toggleTextModeJs) {
    toggleTextModeJs.addEventListener('click', () => {
        document.body.classList.add('text-mode')
        window.scrollTo(0, 0)
        document.documentElement.scrollTop = 0
        document.body.scrollTop = 0
    })
}

const closeTextModeJs = document.querySelector('.close-text-mode-js')

if (closeTextModeJs) {
    closeTextModeJs.addEventListener('click', () => {
        document.body.classList.remove('text-mode')
    })
}
