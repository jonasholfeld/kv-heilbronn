// Template-specific JS for the "reise" (single trip) page.

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
