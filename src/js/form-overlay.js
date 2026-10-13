// Form overlays (shop order, membership, trip registration), see
// site/snippets/form-overlay.php and site/plugins/kv-forms. The selected
// states of the editions and pills are pure CSS (:has(:checked)); this
// opens/closes the overlay, validates while typing and prepares the
// submission.
// init() runs on every page view; listeners are removed when `signal` aborts.

// Same rules as in site/plugins/kv-forms
const TEL_DISALLOWED = /[^0-9+()/ -]/g
const TEL_MIN_DIGITS = 5
const NUMBER_DISALLOWED = /\D/g
const IBAN_DISALLOWED = /[^A-Za-z0-9 ]/g
const BIC = /^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const UPLOAD_TYPES = ['application/pdf', 'image/jpeg', 'image/png']
// Format errors show up once typing pauses, not with every keystroke
const TYPING_DELAY = 700

function isIban(value) {
    const iban = value.replace(/\s/g, '').toUpperCase()
    if (!/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/.test(iban)) return false
    const rearranged = iban.slice(4) + iban.slice(0, 4)
    let remainder = 0
    for (const char of rearranged) {
        const digits = /[A-Z]/.test(char) ? String(char.charCodeAt(0) - 55) : char
        for (const digit of digits) {
            remainder = (remainder * 10 + Number(digit)) % 97
        }
    }
    return remainder === 1
}

// Y-m-d (date input) or d.m.Y (typed by hand)
function parseDate(value) {
    let year, month, day
    const iso = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)
    const german = value.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/)
    if (iso) {
        ;[, year, month, day] = iso.map(Number)
    } else if (german) {
        ;[, day, month, year] = german.map(Number)
    } else {
        return null
    }
    const date = new Date(year, month - 1, day)
    return date.getMonth() === month - 1 && date.getDate() === day ? date : null
}

function isOldEnough(date, years) {
    const limit = new Date()
    limit.setFullYear(limit.getFullYear() - years)
    return date <= limit
}

// Keeps only the allowed characters and the caret where it was
function filterInput(input, disallowed, transform = (value) => value) {
    const cleaned = transform(input.value.replace(disallowed, ''))
    if (cleaned === input.value) return
    const before = input.value.slice(0, input.selectionStart)
    const position = transform(before.replace(disallowed, '')).length
    input.value = cleaned
    input.setSelectionRange(position, position)
}

// Inline validation: every field gets an error line below it. Required
// fields complain when left (blur) or on submit, format errors while typing;
// a shown error is re-checked on every change and disappears once fixed.
// Returns the check for the submission.
function setupValidation(form, signal) {
    const messages = JSON.parse(form.dataset.messages || '{}')
    const msg = (key, data = {}) =>
        Object.entries(data).reduce(
            (text, [name, value]) => text.replaceAll(`{${name}}`, value),
            messages[key] || ''
        )
    const uploadMax = Number(form.dataset.uploadMax)

    const controls = []

    const addControl = (wrapper, inputs, check) => {
        const error = document.createElement('p')
        error.className = 'kv-form__error'
        error.id = 'form-error-' + controls.length
        error.setAttribute('aria-live', 'polite')
        error.hidden = true
        ;(wrapper.closest('.kv-form__item') ?? wrapper).append(error)
        inputs.forEach((input) => input.setAttribute('aria-describedby', error.id))

        const control = {
            inputs,
            show() {
                const message = check()
                wrapper.classList.toggle('is-invalid', message !== '')
                inputs.forEach((input) =>
                    input.setAttribute('aria-invalid', message !== '')
                )
                error.textContent = message
                error.hidden = message === ''
                return message === ''
            },
            isShown: () => !error.hidden,
        }
        controls.push(control)
        return control
    }

    // Text fields
    form.querySelectorAll('.kv-form__field').forEach((wrapper) => {
        const input = wrapper.querySelector('input, textarea')
        const label = input.dataset.label
        const type = input.dataset.type || 'text'

        const control = addControl(wrapper, [input], () => {
            const value = input.value.trim()
            if (value === '' || (type === 'number' && Number(value) === 0)) {
                return input.required ? msg('required', { label }) : ''
            }
            if (type === 'email' && !EMAIL.test(value)) return msg('email', { label })
            if (type === 'tel' && (value.match(/\d/g) || []).length < TEL_MIN_DIGITS) {
                return msg('tel', { label })
            }
            if (type === 'iban' && !isIban(value)) return msg('iban', { label })
            if (type === 'bic' && !BIC.test(value.replace(/\s/g, ''))) {
                return msg('bic', { label })
            }
            if (type === 'date') {
                const date = parseDate(value)
                if (!date) return msg('date', { label })
                const minAge = Number(input.dataset.minAge)
                if (minAge && !isOldEnough(date, minAge)) {
                    return msg('age', { label, age: minAge })
                }
            }
            return ''
        })

        // Dates: the placeholder is only visible in a text input, the date
        // picker appears once the field is used
        if (type === 'date') {
            input.addEventListener(
                'focus',
                () => {
                    input.type = 'date'
                },
                { signal }
            )
            input.addEventListener(
                'blur',
                () => {
                    if (input.value === '') input.type = 'text'
                },
                { signal }
            )
        }

        let timer = null
        input.addEventListener(
            'input',
            () => {
                if (type === 'tel') filterInput(input, TEL_DISALLOWED)
                if (type === 'number') filterInput(input, NUMBER_DISALLOWED)
                if (type === 'iban' || type === 'bic') {
                    filterInput(input, IBAN_DISALLOWED, (value) => value.toUpperCase())
                }
                clearTimeout(timer)
                if (control.isShown()) {
                    control.show()
                } else if (input.value.trim() !== '') {
                    timer = setTimeout(() => control.show(), TYPING_DELAY)
                }
            },
            { signal }
        )
        input.addEventListener(
            'blur',
            () => {
                clearTimeout(timer)
                // Leaving an untouched optional field says nothing
                if (input.value !== '' || input.required) control.show()
            },
            { signal }
        )
    })

    // Single choice (prices, salutation, …) and edition images
    form.querySelectorAll(
        '.kv-form__choice[data-required], .kv-form__editions[data-required]'
    ).forEach((fieldset) => {
        const inputs = [...fieldset.querySelectorAll('input')]
        const control = addControl(fieldset, inputs, () =>
            inputs.some((input) => input.checked)
                ? ''
                : msg('choose', { label: fieldset.dataset.label })
        )
        fieldset.addEventListener('change', () => control.show(), { signal })
    })

    // Consents (privacy, SEPA mandate, …)
    form.querySelectorAll('.kv-form__consent').forEach((wrapper) => {
        const input = wrapper.querySelector('input')
        if (!input.required) return
        const label = input.dataset.label
        const control = addControl(wrapper, [input], () => {
            if (input.checked) return ''
            return label ? msg('consentLabel', { label }) : msg('consent')
        })
        input.addEventListener('change', () => control.show(), { signal })
    })

    // Uploads: the pill shows the chosen file
    form.querySelectorAll('.kv-form__upload').forEach((wrapper) => {
        const input = wrapper.querySelector('input')
        const name = wrapper.querySelector('[data-upload-name]')
        const label = input.dataset.label
        const control = addControl(wrapper, [input], () => {
            const file = input.files[0]
            if (!file) return input.required ? msg('required', { label }) : ''
            if (!UPLOAD_TYPES.includes(file.type)) return msg('uploadType', { label })
            if (file.size > uploadMax) return msg('uploadSize', { label })
            return ''
        })
        input.addEventListener(
            'change',
            () => {
                const file = input.files[0]
                name.textContent = file
                    ? `${name.dataset.empty}: ${file.name}`
                    : name.dataset.empty
                wrapper.classList.toggle('is-filled', Boolean(file))
                control.show()
            },
            { signal }
        )
    })

    // Groups like "Personenanzahl": at least one of their fields
    form.querySelectorAll('.kv-form__group[data-required]').forEach((group) => {
        const inputs = [...group.querySelectorAll('input, textarea')]
        const isFilled = (input) =>
            input.type === 'checkbox' || input.type === 'radio'
                ? input.checked
                : input.type === 'file'
                  ? input.files.length > 0
                  : input.value.trim() !== '' &&
                    !(input.dataset.type === 'number' && Number(input.value) === 0)
        const control = addControl(group, [], () =>
            inputs.some(isFilled) ? '' : msg('group', { label: group.dataset.label })
        )
        group.addEventListener(
            'input',
            () => {
                if (control.isShown()) control.show()
            },
            { signal }
        )
        group.addEventListener(
            'change',
            () => {
                if (control.isShown()) control.show()
            },
            { signal }
        )
    })

    // On submit: show every error and jump to the first one
    return () => {
        const invalid = controls.filter((control) => !control.show())
        if (invalid.length === 0) return true
        const first = invalid[0]
        const target =
            first.inputs.find((input) => input.type !== 'radio' || input.checked) ??
            first.inputs[0]
        target?.focus({ preventScroll: true })
        ;(target ?? form)
            .closest('.kv-form__item')
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' })
        return false
    }
}

// Fields as wide as their placeholder (width "Wie die Bezeichnung"): the
// placeholder is uppercase, which `size` can't account for
function sizeAutoFields(form) {
    const context = document.createElement('canvas').getContext('2d')
    form.querySelectorAll('.kv-form__item--auto .kv-form__field input').forEach((input) => {
        const style = getComputedStyle(input)
        context.font = `${style.fontSize} ${style.fontFamily}`
        const text = input.placeholder.toUpperCase()
        // Date inputs also need room for the date picker
        const extra = input.dataset.type === 'date' ? 2 : 0.5
        input.style.width = `calc(${Math.ceil(context.measureText(text).width)}px + ${extra}em)`
    })
}

// Which and how many editions are selected (shop)
function setupEditionSelection(form, signal) {
    form.querySelectorAll('.kv-form__editions').forEach((fieldset) => {
        const info = fieldset.querySelector('[data-form-selection]')
        if (!info) return
        const update = () => {
            const numbers = [...fieldset.querySelectorAll('input:checked')].map(
                (input) => '#' + input.value
            )
            const text =
                numbers.length === 0
                    ? info.dataset.none
                    : numbers.length === 1
                      ? info.dataset.one
                      : info.dataset.many
            info.textContent = text
                .replaceAll('{count}', numbers.length)
                .replaceAll('{numbers}', numbers.join(', '))
        }
        update()
        fieldset.addEventListener('change', update, { signal })
    })
}

export function init(signal) {
    const host = document.querySelector('[data-form-host]')
    const overlay = host?.querySelector('[data-form-overlay]')
    if (!overlay) return

    const form = overlay.querySelector('[data-form]')
    const openButtons = document.querySelectorAll('[data-form-open]')
    const closeButton = overlay.querySelector('[data-form-close]')
    let lastOpener = null

    // The pages are cached, so the CSRF token is fetched once the form is
    // opened. Submitting waits for it.
    let tokenRequest = null
    const loadToken = () => {
        if (!form || tokenRequest) return
        const submit = form.querySelector('[data-form-submit]')
        tokenRequest = fetch(form.dataset.tokenUrl, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            signal,
        })
            .then((response) => response.json())
            .then(({ token }) => {
                form.querySelector('[data-form-csrf]').value = token
                submit.disabled = false
            })
            .catch(() => {
                // Retry on the next opening
                tokenRequest = null
            })
    }

    let scrollYBeforeOpen = 0

    const open = (event) => {
        lastOpener = event.currentTarget
        // On mobile the page itself scrolls (not the overlay): start the form
        // at the top and come back to the same spot when it closes
        scrollYBeforeOpen = window.scrollY
        host.classList.add('is-form-open')
        // Measured once the overlay (and so the font) is rendered
        if (form) sizeAutoFields(form)
        overlay.scrollTop = 0
        window.scrollTo(0, 0)
        loadToken()
        overlay.querySelector('h2')?.focus({ preventScroll: true })
    }

    const close = () => {
        host.classList.remove('is-form-open')
        window.scrollTo(0, scrollYBeforeOpen)
        lastOpener?.focus({ preventScroll: true })
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', open, { signal })
    })
    closeButton?.addEventListener('click', close, { signal })

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape' &&
                closeButton &&
                host.classList.contains('is-form-open')
            ) {
                close()
            }
        },
        { signal }
    )

    if (!form) return

    // Small textareas grow with their text
    form.querySelectorAll('.kv-form__field--textarea:not(.kv-form__field--tall) textarea').forEach(
        (textarea) => {
            textarea.addEventListener(
                'input',
                () => {
                    textarea.style.height = 'auto'
                    textarea.style.height = textarea.scrollHeight + 'px'
                },
                { signal }
            )
        }
    )

    setupEditionSelection(form, signal)
    const validateAll = setupValidation(form, signal)

    form.addEventListener(
        'submit',
        (event) => {
            const submit = form.querySelector('[data-form-submit]')
            if (submit.disabled || !validateAll()) {
                event.preventDefault()
                return
            }
            // Against double submissions from double clicks
            submit.disabled = true
        },
        { signal }
    )

    // Going back to the form restores the page from the bfcache
    window.addEventListener(
        'pageshow',
        (event) => {
            if (event.persisted && tokenRequest) {
                form.querySelector('[data-form-submit]').disabled = false
            }
        },
        { signal }
    )
}
