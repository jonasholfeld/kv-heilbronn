// Order form overlay of the shop item pages (edition, katalog), see
// site/snippets/order-form.php. The selected states of the editions and the
// pills are pure CSS (:has(:checked)); this opens/closes the overlay,
// validates while typing and prepares the submission.
// init() runs on every page view; listeners are removed when `signal` aborts.

// Same rules as in site/plugins/kv-shop-order
const TEL_DISALLOWED = /[^0-9+()/ -]/g
const TEL_MIN_DIGITS = 5
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
// Format errors show up once typing pauses, not with every keystroke
const TYPING_DELAY = 700

// Inline validation: every field gets an error line below it. Required
// fields complain when left (blur) or on submit, format errors while typing;
// a shown error is re-checked on every change and disappears once fixed.
function setupValidation(form, signal) {
    const msg = (key, label) =>
        (form.dataset[key] || '').replaceAll('{label}', label || '')

    const controls = []

    const addControl = (wrapper, inputs, check) => {
        const error = document.createElement('p')
        error.className = 'order-form__error'
        error.id = 'order-error-' + controls.length
        error.setAttribute('aria-live', 'polite')
        error.hidden = true
        wrapper.after(error)
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
    form.querySelectorAll('.order-form__field').forEach((wrapper) => {
        const input = wrapper.querySelector('input, textarea')
        const label = input.dataset.label
        const control = addControl(wrapper, [input], () => {
            const value = input.value.trim()
            if (value === '') return input.required ? msg('msgRequired', label) : ''
            if (input.type === 'email' && !EMAIL.test(value)) {
                return msg('msgEmail', label)
            }
            if (
                input.type === 'tel' &&
                (value.match(/\d/g) || []).length < TEL_MIN_DIGITS
            ) {
                return msg('msgTel', label)
            }
            return ''
        })

        let timer = null
        input.addEventListener(
            'input',
            () => {
                // Phone numbers: digits, spaces and + ( ) / - only
                if (input.type === 'tel') {
                    const cleaned = input.value.replace(TEL_DISALLOWED, '')
                    if (cleaned !== input.value) {
                        const before = input.value.slice(0, input.selectionStart)
                        const position = before.replace(TEL_DISALLOWED, '').length
                        input.value = cleaned
                        input.setSelectionRange(position, position)
                    }
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

    // Which and how many editions are selected
    form.querySelectorAll('.order-form__editions').forEach((fieldset) => {
        const info = fieldset.querySelector('[data-order-selection]')
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

    // Single choice (prices, salutation) and edition images
    form.querySelectorAll('.order-form__choice[data-required], .order-form__editions[data-required]').forEach(
        (fieldset) => {
            const inputs = [...fieldset.querySelectorAll('input')]
            const control = addControl(fieldset, inputs, () =>
                inputs.some((input) => input.checked)
                    ? ''
                    : msg('msgChoose', fieldset.dataset.label)
            )
            fieldset.addEventListener('change', () => control.show(), { signal })
        }
    )

    // Privacy consent
    form.querySelectorAll('.order-form__consent').forEach((wrapper) => {
        const input = wrapper.querySelector('input')
        const control = addControl(wrapper, [input], () =>
            input.checked ? '' : msg('msgConsent')
        )
        input.addEventListener('change', () => control.show(), { signal })
    })

    // On submit: show every error and jump to the first one
    return () => {
        const invalid = controls.filter((control) => !control.show())
        if (invalid.length === 0) return true
        const first = invalid[0].inputs[0]
        first.focus({ preventScroll: true })
        first
            .closest('.order-form__field, fieldset, .order-form__consent')
            .scrollIntoView({ behavior: 'smooth', block: 'center' })
        return false
    }
}

export function init(signal) {
    const main = document.querySelector('main.shop-item-page')
    const overlay = main?.querySelector('[data-order-overlay]')
    if (!overlay) return

    const form = overlay.querySelector('[data-order-form]')
    const openButtons = main.querySelectorAll('.bestellen-btn')
    const closeButton = overlay.querySelector('[data-order-close]')

    // The pages are cached, so the CSRF token is fetched once the form is
    // opened. Submitting waits for it.
    let tokenRequest = null
    const loadToken = () => {
        if (!form || tokenRequest) return
        const submit = form.querySelector('[data-order-submit]')
        tokenRequest = fetch(form.dataset.tokenUrl, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            signal,
        })
            .then((response) => response.json())
            .then(({ token }) => {
                form.querySelector('[data-order-csrf]').value = token
                submit.disabled = false
            })
            .catch(() => {
                // Retry on the next opening
                tokenRequest = null
            })
    }

    const open = () => {
        main.classList.add('is-ordering')
        overlay.scrollTop = 0
        loadToken()
        overlay.querySelector('h2')?.focus({ preventScroll: true })
    }

    const close = () => {
        main.classList.remove('is-ordering')
        openButtons[0]?.focus({ preventScroll: true })
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
                main.classList.contains('is-ordering')
            ) {
                close()
            }
        },
        { signal }
    )

    if (!form) return

    // The textarea grows with its text
    form.querySelectorAll('textarea').forEach((textarea) => {
        textarea.addEventListener(
            'input',
            () => {
                textarea.style.height = 'auto'
                textarea.style.height = textarea.scrollHeight + 'px'
            },
            { signal }
        )
    })

    const validateAll = setupValidation(form, signal)

    form.addEventListener(
        'submit',
        (event) => {
            const submit = form.querySelector('[data-order-submit]')
            if (submit.disabled || !validateAll()) {
                event.preventDefault()
                return
            }
            // Against double orders from double clicks
            submit.disabled = true
        },
        { signal }
    )

    // Going back to the form restores the page from the bfcache
    window.addEventListener(
        'pageshow',
        (event) => {
            if (event.persisted && tokenRequest) {
                form.querySelector('[data-order-submit]').disabled = false
            }
        },
        { signal }
    )
}
