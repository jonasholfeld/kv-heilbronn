import '../scss/main.scss'
import { setupPageTransitions } from './page-transitions.js'

// Old function
requestAnimationFrame(() => {
    document.body.classList.remove('pre-init')
})

const AUSSTELLUNGEN_ROW_IMAGE_HEIGHT_REM = 18
const REM_IN_PX = () =>
    parseFloat(getComputedStyle(document.documentElement).fontSize) || 16

function waitForImageDimensions(image) {
    if (image.complete && image.naturalWidth > 0 && image.naturalHeight > 0) {
        return Promise.resolve()
    }

    if (image.complete) {
        return Promise.resolve()
    }

    return new Promise((resolve) => {
        const finalize = () => {
            image.removeEventListener('load', finalize)
            image.removeEventListener('error', finalize)
            resolve()
        }

        image.addEventListener('load', finalize, { once: true })
        image.addEventListener('error', finalize, { once: true })
    })
}

// Width and height from the attributes (set by the image snippet), so the
// layout doesn't have to wait for the image to load
function getImageSize(image) {
    const width = Number(image.getAttribute('width')) || image.naturalWidth
    const height = Number(image.getAttribute('height')) || image.naturalHeight
    return { width, height }
}

function hasImageSize(image) {
    const { width, height } = getImageSize(image)
    return width > 0 && height > 0
}

// Images rendered with `defer` have no src yet; they load once needed
function loadDeferredImages(container) {
    container.querySelectorAll('img[data-srcset]').forEach((image) => {
        image.srcset = image.dataset.srcset
        image.src = image.dataset.src
        image.removeAttribute('data-srcset')
        image.removeAttribute('data-src')
    })
}

function isElementVisibleForMeasurement(element) {
    return element.getClientRects().length > 0 && element.clientWidth > 0
}

function getValidAusstellungenImages(images) {
    return images.filter(
        (image) => !image.classList.contains('is-invalid') && hasImageSize(image)
    )
}

async function updateAusstellungenImageRow(row) {
    const images = [...row.querySelectorAll('img')]
    row.classList.remove('ausstellungen-row-images--limit-1')
    row.classList.remove('ausstellungen-row-images--limit-2')

    if (images.length === 0) {
        row.classList.remove('is-pending')
        return
    }

    if (!isElementVisibleForMeasurement(row)) {
        row.classList.remove('is-pending')
        return
    }

    const unsizedImages = images.filter((image) => !hasImageSize(image))

    if (unsizedImages.length > 0) {
        row.classList.add('is-pending')
        await Promise.all(unsizedImages.map(waitForImageDimensions))
        unsizedImages.forEach((image) => {
            image.classList.toggle('is-invalid', !hasImageSize(image))
        })
    }

    const validImages = getValidAusstellungenImages(images)

    if (validImages.length === 0) {
        row.classList.remove('is-pending')
        return
    }

    const gap = parseFloat(
        getComputedStyle(row).columnGap || getComputedStyle(row).gap || '0'
    )
    const imageHeight = AUSSTELLUNGEN_ROW_IMAGE_HEIGHT_REM * REM_IN_PX()
    const availableWidth = row.clientWidth

    const imageWidths = validImages.map((image) => {
        const { width, height } = getImageSize(image)
        return imageHeight * (width / height)
    })

    const getRequiredWidth = (count) =>
        imageWidths.slice(0, count).reduce((total, width) => total + width, 0) +
        gap * Math.max(0, count - 1)

    if (validImages.length >= 2 && getRequiredWidth(2) > availableWidth) {
        row.classList.add('ausstellungen-row-images--limit-1')
    } else if (
        validImages.length >= 3 &&
        getRequiredWidth(3) > availableWidth
    ) {
        row.classList.add('ausstellungen-row-images--limit-2')
    }

    row.classList.remove('is-pending')
}

function setupAusstellungenImageRows(signal) {
    const rows = [...document.querySelectorAll('.ausstellungen-row-images')]

    if (rows.length === 0) return

    const updateAllRows = () => {
        rows.forEach((row) => {
            updateAusstellungenImageRow(row)
        })
    }

    updateAllRows()
    window.addEventListener('resize', updateAllRows, { passive: true, signal })

    // Broken images drop out of the row
    rows.forEach((row) => {
        row.querySelectorAll('img').forEach((image) => {
            image.addEventListener(
                'error',
                () => {
                    image.classList.add('is-invalid')
                    updateAusstellungenImageRow(row)
                },
                { once: true, signal }
            )
        })
    })

    if ('ResizeObserver' in window) {
        const resizeObserver = new ResizeObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.contentRect.width > 0) {
                    updateAusstellungenImageRow(entry.target)
                }
            })
        })

        rows.forEach((row) => {
            resizeObserver.observe(row)
        })
        signal.addEventListener('abort', () => resizeObserver.disconnect())
    }
}

// The small sidebars slide out of the layout when the menu opens, which needs
// their natural width as a fixed value (CSS can't transition from `auto`).
// The computed width ignores the scale transform (and isn't rounded like
// offsetWidth), so it is safe to measure any time.
function setupSidebarWidths(signal) {
    const sidebars = [...document.querySelectorAll('.sidebar-small')]

    if (sidebars.length === 0) return

    const measure = () => {
        sidebars.forEach((sidebar) => {
            sidebar.style.setProperty(
                '--sidebar-width',
                getComputedStyle(sidebar).width
            )
        })
    }

    measure()
    document.fonts?.ready.then(() => {
        if (!signal.aborted) measure()
    })
    window.addEventListener('resize', measure, { passive: true, signal })
}

// Everything tied to the current page's content. Runs on the initial load and
// again after every page transition; listeners are removed when `signal` aborts.
function initPage(signal) {
    setupAusstellungenImageRows(signal)
    setupSidebarWidths(signal)

    const siteHeader = document.querySelector('.site-header')
    const siteHeaderTitle = document.querySelector(
        '.site-header .site-header__title'
    )
    const headerSvg = document.querySelector('.site-header #header-svg')
    const headerSvgSt0 = document.querySelector('.site-header svg .st0')
    const siteMenu = document.getElementById('site-menu')
    const sideNavigation = document.querySelector('.side-navigation')
    const footer = document.querySelector('footer')
    const topInfoRowCategoryButton = document.querySelector(
        '.top-info-row .category-button-js'
    )
    const homeColorSections = [
        ...document.querySelectorAll(
            '.exhibition-wrapper, .termine-section, .shop-item-wrapper, .infobox-wrapper'
        ),
    ]
    const homeSections = document.querySelectorAll('main.home > div')
    const lastHomeSection = homeSections.length
        ? homeSections[homeSections.length - 1]
        : null
    const isHomePage =
        document.body.classList.contains('home') ||
        document.querySelector('main.home') !== null

    const categoryLabels = document.querySelectorAll('.category-label')
    const categoryPageByLabel = {
        Ausstellungen: '/ausstellungen',
        Kunstverein: '/kunstverein',
        Termine: '/termine',
        Reisen: '/reisen',
        Shop: '/shop',
        shop: '/shop',
    }

    const HEADER_MAX_WIDTH = 99 // rem
    const HEADER_MIN_WIDTH = 23.4 // rem
    const SVG_ASPECT_RATIO = 429.05 / 112 // viewBox width / height
    // The last home section gets a 15vw scroll-top-element buffer (see main.js
    // scroll-top-element height calc) plus 1vw of inner bottom padding, so its
    // real bounding box reaches 16vw further down than its visible content.
    const BOTTOM_SECTION_WHITESPACE_RATIO = 0.02
    const BOTTOM_MENU_CLOSE_EARLY_RATIO = 0.2
    let isMenuOpening = false
    let closeMenuOnHomeScroll = null

    const relativeRoute = window.location.pathname
    const isAusstellungPage = relativeRoute.includes('/ausstellungen/')

    function getBottomExpandProgress() {
        if (!lastHomeSection) return 0
        const rect = lastHomeSection.getBoundingClientRect()
        const whitespace = window.innerWidth * BOTTOM_SECTION_WHITESPACE_RATIO
        const contentBottom = rect.bottom - whitespace
        // Section's visible content is still (at least partly) on screen, no expansion yet.
        if (contentBottom > 0) return 0

        const maxScrollY =
            document.documentElement.scrollHeight - window.innerHeight
        const sectionAbsoluteBottom = contentBottom + window.scrollY
        const expandDistance = Math.max(1, maxScrollY - sectionAbsoluteBottom)

        return Math.min(1, Math.max(0, -contentBottom / expandDistance))
    }

    function shouldCloseMenuBeforeBottomExpand() {
        if (!lastHomeSection) return false
        const rect = lastHomeSection.getBoundingClientRect()
        const whitespace = window.innerWidth * BOTTOM_SECTION_WHITESPACE_RATIO
        const contentBottom = rect.bottom - whitespace
        const maxScrollY =
            document.documentElement.scrollHeight - window.innerHeight
        const sectionAbsoluteBottom = contentBottom + window.scrollY
        const expandDistance = Math.max(1, maxScrollY - sectionAbsoluteBottom)

        return contentBottom <= expandDistance * BOTTOM_MENU_CLOSE_EARLY_RATIO
    }

    function updateHeaderWidth() {
        if (!siteHeader || isAusstellungPage) return
        const threshold = window.innerWidth * 0.182
        const topProgress = Math.min(1, Math.max(0, window.scrollY / threshold))
        const bottomExpandProgress = getBottomExpandProgress()

        if (
            shouldCloseMenuBeforeBottomExpand() &&
            siteMenu?.classList.contains('is-open')
        ) {
            closeMenuOnHomeScroll?.({ quickFade: true })
        }

        // Once the last section has scrolled out of view, the header smoothly
        // expands back towards full width as the user keeps scrolling, instead
        // of the top-of-page shrink progress.
        const progress =
            bottomExpandProgress > 0 ? 1 - bottomExpandProgress : topProgress
        const width =
            HEADER_MAX_WIDTH + (HEADER_MIN_WIDTH - HEADER_MAX_WIDTH) * progress
        siteHeader.style.width = `${width}rem`

        if (headerSvg) {
            if (progress > 0) {
                headerSvg.style.boxShadow = 'inset 0 0 0 1px var(--svgColor)'
            }
            headerSvg.style.borderWidth = `${0.7 * progress}rem`
            headerSvg.style.borderStyle = 'solid'
        }

        // ── Logo height constraint to prevent footer overlap ──────────────────────
        if (headerSvg && footer && bottomExpandProgress > 0) {
            const remPx = REM_IN_PX()
            const headerWidthPx = width * remPx
            const svgNaturalHeight = headerWidthPx / SVG_ASPECT_RATIO

            const footerTop = footer.getBoundingClientRect().top
            const headerTop = siteHeader.getBoundingClientRect().top
            const availableHeight = footerTop - headerTop

            if (availableHeight > 0 && svgNaturalHeight > availableHeight) {
                const constrainedWidth = availableHeight * SVG_ASPECT_RATIO

                // bottomExpandProgress at which the SVG height first equals availableHeight
                const widthAtThresholdRem =
                    (availableHeight * SVG_ASPECT_RATIO) / remPx
                const thresholdBEP = Math.min(
                    1,
                    Math.max(
                        0,
                        1 -
                            (widthAtThresholdRem - HEADER_MAX_WIDTH) /
                                (HEADER_MIN_WIDTH - HEADER_MAX_WIDTH)
                    )
                )
                const translateProgress = Math.min(
                    1,
                    Math.max(
                        0,
                        (bottomExpandProgress - thresholdBEP) /
                            Math.max(0.001, 1 - thresholdBEP)
                    )
                )

                const translateX =
                    ((headerWidthPx - constrainedWidth) / 2) * translateProgress

                // headerSvg.style.width = constrainedWidth + 'px'
                headerSvg.style.height = availableHeight + 'px'
                // headerSvg.style.transform = `translateX(${translateX}px)`
                headerSvg.style.background = 'white'
                // headerSvg.style.boxShadow = 'inset 0 0 0 8px rgb(220, 224, 227)'
                if (siteHeaderTitle) {
                    // siteHeaderTitle.style.background = 'rgb(220, 224, 227)'
                }
            } else {
                headerSvg.style.width = ''
                headerSvg.style.height = ''
                headerSvg.style.transform = ''
                headerSvg.style.background = ''
                headerSvg.style.boxShadow =
                    progress > 0 ? 'inset 0 0 0 1px var(--svgColor)' : ''
                if (siteHeaderTitle) {
                    siteHeaderTitle.style.background = ''
                }
            }
        } else if (headerSvg) {
            headerSvg.style.width = ''
            headerSvg.style.height = ''
            headerSvg.style.transform = ''
            headerSvg.style.background = ''
            headerSvg.style.boxShadow =
                progress > 0 ? 'inset 0 0 0 1px var(--svgColor)' : ''
            if (siteHeaderTitle) {
                siteHeaderTitle.style.background = ''
            }
        }

        if (sideNavigation) {
            if (bottomExpandProgress > 0) {
                sideNavigation.classList.add('hidden')
            } else if (
                (!siteMenu || !siteMenu.classList.contains('is-open')) &&
                !isMenuOpening
            ) {
                sideNavigation.classList.remove('hidden')
            }
        }
    }

    if (siteHeader) {
        const DEFAULT_LOGO_FILL = '#DCE0E3'
        let hasInitializedHeaderColor = false

        function updateHeaderLogoColor() {
            if (!headerSvgSt0) return

            const topMargin = window.innerWidth * 0.01 // 1vw
            let activeSection = null

            for (const section of homeColorSections) {
                const rect = section.getBoundingClientRect()
                if (rect.top <= topMargin && rect.bottom > topMargin) {
                    activeSection = section
                    break
                }
            }

            if (!activeSection && homeColorSections.length > 0) {
                const firstBelow = homeColorSections.find(
                    (section) => section.getBoundingClientRect().top > topMargin
                )
                if (firstBelow) {
                    activeSection = firstBelow
                }
            }

            const COLOR_VARS = ['--color']
            const getInlineCssVar = (element, varName) => {
                const inlineStyle = element.getAttribute('style') || ''
                const match = inlineStyle.match(
                    new RegExp(`${varName}\\s*:\\s*([^;]+)`, 'g')
                )
                if (!match || match.length === 0) return ''
                const lastMatch = match[match.length - 1]
                return lastMatch.split(':').slice(1).join(':').trim()
            }

            const sectionColor = activeSection
                ? (() => {
                      for (const varName of COLOR_VARS) {
                          const inlineVar = getInlineCssVar(activeSection, varName)
                          if (inlineVar) return inlineVar
                          const computedVar = getComputedStyle(activeSection)
                              .getPropertyValue(varName)
                              .trim()
                          if (computedVar) return computedVar
                      }
                      return ''
                  })()
                : ''

            const color = sectionColor || DEFAULT_LOGO_FILL
            if (!hasInitializedHeaderColor) {
                if (headerSvgSt0) headerSvgSt0.style.transition = 'none'
                if (headerSvg) headerSvg.style.transition = 'none'
            }
            headerSvgSt0.style.fill = color
            if (siteMenu) {
                siteMenu.style.backgroundColor = color
            }
            if (topInfoRowCategoryButton) {
                const category = activeSection?.getAttribute('data-category') || ''
                topInfoRowCategoryButton.textContent = category
                if (topInfoRowCategoryButton instanceof HTMLAnchorElement) {
                    topInfoRowCategoryButton.setAttribute(
                        'href',
                        categoryPageByLabel[category] || '#'
                    )
                    console.log(category)
                }
            }
            if (footer) {
                footer.style.setProperty('--backgroundColorFooter', color)
            }

            if (headerSvg) {
                headerSvg.style.setProperty('--svgColor', color)
                headerSvg.style.borderColor = color
            }

            if (!hasInitializedHeaderColor) {
                requestAnimationFrame(() => {
                    if (headerSvgSt0) headerSvgSt0.style.transition = ''
                    if (headerSvg) headerSvg.style.transition = ''
                })
                hasInitializedHeaderColor = true
            }
        }

        window.addEventListener('scroll', updateHeaderWidth, { passive: true, signal })
        window.addEventListener('scroll', updateHeaderLogoColor, {
            passive: true,
            signal,
        })
        window.addEventListener('resize', updateHeaderWidth, { passive: true, signal })
        window.addEventListener('resize', updateHeaderLogoColor, {
            passive: true,
            signal,
        })
        updateHeaderWidth()
        updateHeaderLogoColor()
    }

    const alignKunstvereinHashInBlocks = () => {
        if (!document.querySelector('.kunstverein')) return
        if (!window.location.hash) return
        console.log('Aligning Kunstverein hash in blocks...')

        const blocksContainer = document.querySelector('.kunstverein-blocks')
        if (!blocksContainer) return
        console.log('Found blocks container:', blocksContainer)

        const target = document.querySelector(window.location.hash)
        console.log('Found target for hash:', target)
        if (!target || !blocksContainer.contains(target)) return

        const containerRect = blocksContainer.getBoundingClientRect()
        const targetRect = target.getBoundingClientRect()
        const nextScrollTop =
            blocksContainer.scrollTop + (targetRect.top - containerRect.top)

        console.log('Scrolling blocks container to:', nextScrollTop)
        blocksContainer.scrollTo({
            top: nextScrollTop,
            behavior: 'smooth',
        })
    }

    if (document.readyState === 'complete') {
        alignKunstvereinHashInBlocks()
    } else {
        window.addEventListener('load', alignKunstvereinHashInBlocks, { signal })
    }

    // ── Menu toggle ───────────────────────────────────────────────────────────────

    const menuButtons = [...document.querySelectorAll('.menu-button-js')]
    const body = document.body
    const singleAusstellungPage = document.querySelector('.single-ausstellung-page')
    const singleReisePage = document.querySelector('.single-reise-page')
    const menuButtonJs = document.querySelector('.menu-button-js')

    if (menuButtons.length > 0) {
        const setMenuButtonsActive = (active) => {
            menuButtons.forEach((button) =>
                button.classList.toggle('is-active', active)
            )
        }
        const remToPx = () => window.innerWidth / 100 // 1rem = 1vw
        const HEADER_HEIGHT_REM = 26.6
        const PROGRAMMATIC_SCROLL_GUARD_MS = 700
        const SCROLL_WAIT_FALLBACK_TIMEOUT_MS = 1200
        const SCROLL_TARGET_EPSILON_PX = 2
        const MENU_OPEN_SCROLL_PROGRESS = 0.5
        let ignoreMenuCloseOnScrollUntil = 0

        const ignoreMenuCloseOnProgrammaticScroll = () => {
            ignoreMenuCloseOnScrollUntil =
                performance.now() + PROGRAMMATIC_SCROLL_GUARD_MS
        }

        const shouldIgnoreMenuCloseOnScroll = () =>
            performance.now() < ignoreMenuCloseOnScrollUntil

        const waitForScrollToFinish = (targetY) =>
            new Promise((resolve) => {
                const startY = window.scrollY
                const totalDistance = Math.max(0, targetY - startY)
                const openAtY = startY + totalDistance * MENU_OPEN_SCROLL_PROGRESS
                const cleanup = []
                const done = () => {
                    cleanup.forEach((fn) => fn())
                    resolve()
                }

                const timeoutId = window.setTimeout(
                    done,
                    SCROLL_WAIT_FALLBACK_TIMEOUT_MS
                )
                cleanup.push(() => window.clearTimeout(timeoutId))

                let stableFrames = 0
                let lastY = window.scrollY

                const tick = () => {
                    const currentY = window.scrollY
                    if (currentY >= openAtY - SCROLL_TARGET_EPSILON_PX) {
                        done()
                        return
                    }
                    const isNearTarget =
                        Math.abs(currentY - targetY) <= SCROLL_TARGET_EPSILON_PX
                    const isStable = Math.abs(currentY - lastY) <= 0.5

                    if (isNearTarget && isStable) {
                        stableFrames += 1
                        if (stableFrames >= 2) {
                            done()
                            return
                        }
                    } else {
                        stableFrames = 0
                    }

                    lastY = currentY
                    requestAnimationFrame(tick)
                }

                requestAnimationFrame(tick)
            })

        const closeMenu = ({ quickFade = false } = {}) => {
            categoryLabels.forEach((label) => label.classList.remove('show'))
            if (sideNavigation) {
                sideNavigation.classList.remove('hidden')
            }
            if (siteHeader) {
                siteHeader.classList.remove('open')
            }
            if (menuButtonJs) {
                menuButtonJs.classList.remove('close-button-js')
            }
            if (singleAusstellungPage) {
                setTimeout(() => {
                    singleAusstellungPage.classList.remove('menu-is-open')
                }, 0)
            }
            if (singleReisePage) {
                setTimeout(() => {
                    singleReisePage.classList.remove('menu-is-open')
                }, 0)
            }
            if (siteMenu) {
                if (quickFade) {
                    siteMenu.classList.add('is-closing-fast')
                    siteMenu.classList.add('is-instant-hidden')
                } else {
                    siteMenu.classList.remove('is-closing-fast')
                    siteMenu.classList.remove('is-instant-hidden')
                }
                siteMenu.classList.remove('is-open')
                siteMenu.setAttribute('aria-hidden', 'true')
                siteMenu.classList.remove('is-closing-fast')
            }
            setMenuButtonsActive(false)
            body.classList.remove('menu-is-open')
        }

        closeMenuOnHomeScroll = closeMenu

        const handleMenuButtonClick = async (button) => {
            console.log('Menu button clicked!!')
            const isOpen = siteMenu
                ? siteMenu.classList.contains('is-open')
                : button.classList.contains('is-active')

            if (!isOpen) {
                isMenuOpening = true
                if (siteMenu) {
                    siteMenu.classList.remove('is-instant-hidden')
                }
                if (siteHeader) {
                    siteHeader.classList.add('open')
                }
                if (menuButtonJs) {
                    menuButtonJs.classList.add('close-button-js')
                }
                if (singleAusstellungPage) {
                    singleAusstellungPage.classList.add('menu-is-open')
                }
                if (singleReisePage) {
                    singleReisePage.classList.add('menu-is-open')
                }
                const targetY = HEADER_HEIGHT_REM * remToPx()
                const distanceToTarget = targetY - window.scrollY
                if (sideNavigation) {
                    sideNavigation.classList.add('hidden')
                }
                if (distanceToTarget > SCROLL_TARGET_EPSILON_PX && isHomePage) {
                    ignoreMenuCloseOnProgrammaticScroll()
                    window.scrollTo({ top: targetY, behavior: 'smooth' })
                    await waitForScrollToFinish(targetY)
                }
                // Force header to minimized state immediately so it doesn't
                // overlap the menu while the smooth scroll is still animating
                if (siteHeader) {
                    siteHeader.style.width = `${HEADER_MIN_WIDTH}rem`
                    if (headerSvg) {
                        headerSvg.style.borderWidth = '0.7rem'
                        headerSvg.style.borderStyle = 'solid'
                    }
                }
                if (siteMenu) {
                    siteMenu.classList.add('is-open')
                    siteMenu.setAttribute('aria-hidden', 'false')
                }
                setMenuButtonsActive(true)
                isMenuOpening = false
                // Add class to minimize all exhibition galleries so they don't overlap the menu area
                body.classList.add('menu-is-open')
                categoryLabels.forEach((label) => label.classList.add('show'))
            } else {
                isMenuOpening = false
                closeMenu()
                // Let scroll events restore header width naturally
            }
        }

        menuButtons.forEach((button) => {
            button.addEventListener('click', () => {
                void handleMenuButtonClick(button)
            })
        })

        document.addEventListener('click', (event) => {
            if (
                !siteMenu?.classList.contains('is-open') &&
                !menuButtons.some((button) =>
                    button.classList.contains('is-active')
                )
            ) {
                return
            }

            const target = event.target
            if (!(target instanceof Element)) return
            if (target.closest('.menu-button-js')) return
            if (target.closest('.site-menu__toggle--close')) {
                closeMenu()
                return
            }
            if (
                target.closest('.site-menu__inner') ||
                target.closest('.site-menu__toggle-container')
            ) {
                return
            }
            // Only close on clicks into empty space, not on interactive elements
            if (
                target.closest(
                    'a, button, input, select, textarea, label, summary, [role="button"], .credits-wrapper'
                )
            ) {
                return
            }

            closeMenu()
        }, { signal })

        window.addEventListener(
            'scroll',
            () => {
                if (!isHomePage) return
                if (
                    !siteMenu?.classList.contains('is-open') &&
                    !menuButtons.some((button) =>
                        button.classList.contains('is-active')
                    )
                ) {
                    return
                }
                if (shouldIgnoreMenuCloseOnScroll()) return

                const topCloseThreshold = HEADER_HEIGHT_REM * remToPx()
                if (window.scrollY < topCloseThreshold) {
                    closeMenu({ quickFade: true })
                    return
                }

                const scrollBottom = window.innerHeight + window.scrollY
                const pageHeight = document.documentElement.scrollHeight
                if (scrollBottom >= pageHeight - SCROLL_TARGET_EPSILON_PX) {
                    closeMenu({ quickFade: true })
                }
            },
            { passive: true, signal }
        )
    }

    const button = document.querySelector('[data-demo-toggle]')

    if (button) {
        button.addEventListener('click', () => {
            button.classList.toggle('is-active')
            button.textContent = button.classList.contains('is-active')
                ? 'Vite funktioniert'
                : 'Interaktion testen'
        })
    }

    if (homeSections.length > 0) {
        const updateHomeSectionVisibility = () => {
            homeSections.forEach((section) => {
                const rect = section.getBoundingClientRect()
                const hiddenTranslateOffset = section.classList.contains(
                    'is-visible'
                )
                    ? 0
                    : 20
                const adjustedTop = rect.top - hiddenTranslateOffset
                const adjustedBottom = rect.bottom - hiddenTranslateOffset
                const visiblePx =
                    Math.min(adjustedBottom, window.innerHeight) -
                    Math.max(adjustedTop, 0)
                const visibleRatio = rect.height > 0 ? visiblePx / rect.height : 0

                if (visibleRatio >= 0.1) {
                    section.classList.add('is-visible')
                } else if (adjustedTop >= window.innerHeight) {
                    section.classList.remove('is-visible')
                }
            })
        }

        window.addEventListener('scroll', updateHomeSectionVisibility, {
            passive: true,
            signal,
        })
        window.addEventListener('resize', updateHomeSectionVisibility, {
            passive: true,
            signal,
        })
        updateHomeSectionVisibility()
    }

    // ── Ausstellungen row expand/collapse ─────────────────────────────────────────

    const EASE_OUT = 'cubic-bezier(0.22, 1, 0.36, 1)'

    const expandRowBody = (body) => {
        loadDeferredImages(body)
        body.style.display = 'block'
        body.style.height = '0'
        body.style.overflow = 'hidden'
        body.style.opacity = '0'
        const targetHeight = body.scrollHeight
        body.style.transition = `height 0.45s ${EASE_OUT}, opacity 0.25s ease`
        requestAnimationFrame(() => {
            body.style.height = targetHeight + 'px'
            body.style.opacity = '1'
        })
        const onEnd = (e) => {
            if (e.propertyName !== 'height') return
            body.style.height = 'auto'
            body.style.overflow = ''
            body.style.transition = ''
            body.removeEventListener('transitionend', onEnd)
        }
        body.addEventListener('transitionend', onEnd)
    }

    const collapseRowBody = (body) => {
        body.style.height = body.scrollHeight + 'px'
        body.style.overflow = 'hidden'
        body.style.transition = `height 0.45s ${EASE_OUT}, opacity 0.25s ease`
        requestAnimationFrame(() => {
            body.style.height = '0'
            body.style.opacity = '0'
        })
        const onEnd = (e) => {
            if (e.propertyName !== 'height') return
            body.style.display = 'none'
            body.style.height = ''
            body.style.overflow = ''
            body.style.opacity = ''
            body.style.transition = ''
            body.removeEventListener('transitionend', onEnd)
        }
        body.addEventListener('transitionend', onEnd)
    }

    const rowHeaders = [...document.querySelectorAll('.ausstellungen-row-header')]
    rowHeaders.forEach((header) => {
        // Start loading a row's images on hover, so they are there on click
        const preloadRowImages = () => {
            const body = header
                .closest('.ausstellungen-row')
                ?.querySelector('.ausstellungen-row-body')
            if (body) loadDeferredImages(body)
        }
        header.addEventListener('pointerenter', preloadRowImages, {
            once: true,
            signal,
        })
        header.addEventListener('focus', preloadRowImages, { once: true, signal })

        header.addEventListener('click', () => {
            const row = header.closest('.ausstellungen-row')
            if (!row) return
            const body = row.querySelector('.ausstellungen-row-body')
            if (!body) return

            const wasOpen = row.classList.contains('is-open')

            rowHeaders.forEach((otherHeader) => {
                const otherRow = otherHeader.closest('.ausstellungen-row')
                if (!otherRow || otherRow === row) return

                const otherWasOpen = otherRow.classList.contains('is-open')
                const otherBody = otherRow.querySelector('.ausstellungen-row-body')
                if (!otherWasOpen) {
                    otherHeader.setAttribute('aria-expanded', 'false')
                    return
                }

                otherRow.classList.remove('is-open')
                otherHeader.setAttribute('aria-expanded', 'false')

                if (otherBody) {
                    collapseRowBody(otherBody)
                }
            })

            const isOpen = !wasOpen
            row.classList.toggle('is-open', isOpen)
            header.setAttribute('aria-expanded', String(isOpen))
            if (isOpen) {
                expandRowBody(body)
            } else {
                collapseRowBody(body)
            }
        })
    })

    const ausstellungenFilterButton = document.querySelector(
        '.ausstellungen-filter'
    )

    if (ausstellungenFilterButton) {
        const filterPanels = document.querySelector('.ausstellungen-filter-panels')
        const filterToggleButtons = [
            ...document.querySelectorAll('[data-filter-toggle]'),
        ]
        const rows = [...document.querySelectorAll('[data-ausstellung-row]')]
        const content = document.querySelector('.ausstellungen-content')
        const selected = {
            year: new Set(),
            artist: new Set(),
        }

        if (filterPanels) {
            filterPanels.classList.remove('is-open')
        }

        filterToggleButtons.forEach((button) => {
            const kind = button.getAttribute('data-filter-toggle')
            if (!kind) return
            const panel = document.querySelector(`[data-filter-options="${kind}"]`)
            if (!panel) return
            panel.classList.remove('is-open')
            button.setAttribute('aria-expanded', 'false')
        })

        const normalizeValue = (value) =>
            (value || '').toString().trim().toLocaleLowerCase().replace(/\s+/g, ' ')

        const applyFilter = () => {
            rows.forEach((row) => {
                const rowYear = normalizeValue(row.dataset.year)
                const rowArtist = normalizeValue(row.dataset.artist)
                const yearMatch =
                    selected.year.size === 0 || selected.year.has(rowYear)
                const artistMatch =
                    selected.artist.size === 0 || selected.artist.has(rowArtist)

                row.classList.toggle(
                    'is-filter-hidden',
                    !(yearMatch && artistMatch)
                )
            })

            if (content) {
                const headings = [
                    ...content.querySelectorAll('.ausstellungen-section-title'),
                ]
                headings.forEach((heading) => {
                    let hasVisibleRow = false
                    let next = heading.nextElementSibling
                    while (next && !next.matches('.ausstellungen-section-title')) {
                        if (
                            next.matches('[data-ausstellung-row]') &&
                            !next.classList.contains('is-filter-hidden')
                        ) {
                            hasVisibleRow = true
                            break
                        }
                        next = next.nextElementSibling
                    }
                    heading.hidden = !hasVisibleRow
                })
            }
        }

        const updateAllButtonsState = (kind) => {
            const allButton = document.querySelector(`[data-filter-all="${kind}"]`)
            if (!allButton) return
            const isSelected = selected[kind].size === 0
            allButton.classList.toggle('is-selected', isSelected)
            allButton.setAttribute('aria-pressed', String(isSelected))
        }

        const handleOptionClick = (button) => {
            const kind = button.getAttribute('data-filter-value')
            const value = normalizeValue(button.getAttribute('data-value'))
            if (!kind || !value) return

            if (selected[kind].has(value)) {
                selected[kind].delete(value)
                button.classList.remove('is-selected')
                button.setAttribute('aria-pressed', 'false')
            } else {
                selected[kind].add(value)
                button.classList.add('is-selected')
                button.setAttribute('aria-pressed', 'true')
            }

            updateAllButtonsState(kind)
            applyFilter()
        }

        const handleAllClick = (button) => {
            const kind = button.getAttribute('data-filter-all')
            if (!kind) return
            selected[kind].clear()

            document
                .querySelectorAll(`[data-filter-value="${kind}"]`)
                .forEach((option) => {
                    option.classList.remove('is-selected')
                    option.setAttribute('aria-pressed', 'false')
                })

            button.classList.add('is-selected')
            button.setAttribute('aria-pressed', 'true')
            applyFilter()
        }

        const resetAllFilters = () => {
            selected.year.clear()
            selected.artist.clear()

            document.querySelectorAll('[data-filter-value]').forEach((option) => {
                option.classList.remove('is-selected')
                option.setAttribute('aria-pressed', 'false')
            })

            document.querySelectorAll('[data-filter-all]').forEach((allButton) => {
                allButton.classList.add('is-selected')
                allButton.setAttribute('aria-pressed', 'true')
            })

            applyFilter()
        }

        ausstellungenFilterButton.addEventListener('click', () => {
            if (!filterPanels) return
            const open = !filterPanels.classList.contains('is-open')
            filterPanels.classList.toggle('is-open', open)
            ausstellungenFilterButton.setAttribute('aria-expanded', String(open))
        })

        filterToggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const kind = button.getAttribute('data-filter-toggle')
                if (!kind) return
                const panel = document.querySelector(
                    `[data-filter-options="${kind}"]`
                )
                if (!panel) return
                const open = !panel.classList.contains('is-open')
                if (!open) {
                    panel.classList.remove('is-open')
                    button.setAttribute('aria-expanded', 'false')
                    return
                }

                filterToggleButtons.forEach((otherButton) => {
                    const otherKind = otherButton.getAttribute('data-filter-toggle')
                    if (!otherKind) return
                    const otherPanel = document.querySelector(
                        `[data-filter-options="${otherKind}"]`
                    )
                    if (!otherPanel) return
                    const isCurrent = otherButton === button
                    otherPanel.classList.toggle('is-open', isCurrent)
                    otherButton.setAttribute('aria-expanded', String(isCurrent))
                })

                resetAllFilters()
            })
        })

        document.querySelectorAll('[data-filter-value]').forEach((button) => {
            button.setAttribute(
                'data-filter-key',
                normalizeValue(button.getAttribute('data-value'))
            )
            button.setAttribute('aria-pressed', 'false')
            button.addEventListener('click', () => handleOptionClick(button))
        })

        document.querySelectorAll('[data-filter-all]').forEach((button) => {
            button.setAttribute('aria-pressed', 'true')
            button.addEventListener('click', () => handleAllClick(button))
        })

        applyFilter()
    }

    // ── Homepage overlay ──────────────────────────────────────────────────────────

    const homeOverlay = document.getElementById('home-overlay')

    if (homeOverlay) {
        const HOME_OVERLAY_COOKIE_NAME = 'home-overlay-last-seen'

        const getTodayKey = () => {
            const now = new Date()
            const year = now.getFullYear()
            const month = String(now.getMonth() + 1).padStart(2, '0')
            const day = String(now.getDate()).padStart(2, '0')

            return `${year}-${month}-${day}`
        }

        const getCookieValue = (name) => {
            const cookies = document.cookie ? document.cookie.split('; ') : []
            const cookie = cookies.find((entry) => entry.startsWith(`${name}=`))

            if (!cookie) return null

            return decodeURIComponent(cookie.split('=').slice(1).join('='))
        }

        const setCookieValue = (name, value, expiresAt) => {
            document.cookie = [
                `${name}=${encodeURIComponent(value)}`,
                `expires=${expiresAt.toUTCString()}`,
                'path=/',
                'SameSite=Lax',
            ].join('; ')
        }

        const markHomeOverlaySeenForToday = () => {
            const now = new Date()
            const nextMidnight = new Date(now)
            nextMidnight.setHours(24, 0, 0, 0)

            setCookieValue(HOME_OVERLAY_COOKIE_NAME, getTodayKey(), nextMidnight)
        }

        const dismissOverlay = () => {
            homeOverlay.classList.add('is-dismissed')
            homeOverlay.addEventListener(
                'transitionend',
                () => {
                    homeOverlay.style.display = 'none'
                },
                { once: true }
            )
        }

        const closeButtonJs = homeOverlay.querySelector('.close-button-js')

        if (getCookieValue(HOME_OVERLAY_COOKIE_NAME) === getTodayKey()) {
            homeOverlay.style.display = 'none'
        } else {
            markHomeOverlaySeenForToday()

            // const autoTimer = setTimeout(dismissOverlay, 3000)

            if (closeButtonJs) {
                closeButtonJs.addEventListener('click', () => {
                    dismissOverlay()
                })
            }
        }
    }

    // ── Shop item image zoom ────────────────────────────────────────────────────

    const shopItemPage = document.querySelector('.shop-item-page')

    if (shopItemPage) {
        const zoomToggles = [
            ...shopItemPage.querySelectorAll('[data-zoom-toggle]'),
        ]
        const zoomCloseButton = shopItemPage.querySelector('[data-zoom-close]')

        const closeZoom = () => {
            shopItemPage.classList.remove('is-zoomed')
            zoomToggles.forEach((toggle) =>
                toggle.classList.remove('is-zoomed-image')
            )
        }

        const openZoom = (toggle) => {
            zoomToggles.forEach((el) =>
                el.classList.toggle('is-zoomed-image', el === toggle)
            )
            // Zoomed images span the page, so pick a larger srcset candidate
            toggle.querySelector('img[sizes]')?.setAttribute('sizes', '100vw')
            shopItemPage.classList.add('is-zoomed')
        }

        zoomToggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                if (toggle.classList.contains('is-zoomed-image')) {
                    closeZoom()
                } else {
                    openZoom(toggle)
                }
            })
        })

        if (zoomCloseButton) {
            zoomCloseButton.addEventListener('click', (event) => {
                event.stopPropagation()
                closeZoom()
            })
        }
    }

    // const scrollTopElements = document.querySelectorAll('.scroll-top-element')
    // scrollTopElements.forEach((el) => {
    //     const contentHeight = el.querySelector('.content-wrapper').scrollHeight
    //     const tenViewWidth = window.innerWidth * 0.15
    //     el.style.setProperty('height', contentHeight + tenViewWidth + 'px')
    // })

    // on resize, recalculate heights for all scroll top elements
    window.addEventListener('resize', () => {
        scrollTopElements.forEach((el) => {
            const contentHeight = el.querySelector('.content-wrapper').scrollHeight
            const tenViewWidth = window.innerWidth * 0.15
            el.style.setProperty('height', contentHeight + tenViewWidth + 'px')
        })
    }, { signal })
}

// Template-specific scripts, keyed by the template's body class
const pageScripts = {
    ausstellung: () => import('./ausstellung.js'),
    reise: () => import('./reise.js'),
}

let pageController = null

function startPage() {
    pageController = new AbortController()
    const { signal } = pageController
    initPage(signal)

    const template = Object.keys(pageScripts).find((name) =>
        document.body.classList.contains(name)
    )
    if (template) {
        pageScripts[template]().then((module) => {
            if (!signal.aborted) module.init(signal)
        })
    }
}

function stopPage() {
    pageController?.abort()
}

startPage()
setupPageTransitions({ onBeforeSwap: stopPage, onAfterSwap: startPage })
