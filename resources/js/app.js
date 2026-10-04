import './bootstrap';
import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;

/**
 * Everything an Alpine component might call is registered on `window` BEFORE
 * Alpine.start(), which is at the bottom of this file.
 *
 * Alpine.start() walks the DOM synchronously and runs every x-init immediately.
 * Anything assigned to window after that call does not exist yet as far as those
 * components are concerned — which is how the sign-in button came to fail with
 * "window.loadGoogleSignIn is not a function" despite the function being right
 * there in this file, a few lines too late.
 */

/**
 * Date pickers.
 *
 * flatpickr used to be pulled in three times on some pages — bundled here, plus
 * two separate CDN <script> tags — and each page re-initialised it with its own
 * inline configuration. It is set up once, from markup, via data attributes.
 *
 *   data-datepicker="start"  paired arrival field
 *   data-datepicker="end"    paired departure field
 *   data-datepicker="range"  single input holding both
 */
function initDatePickers(root = document) {
    const start = root.querySelector('[data-datepicker="start"]');
    const end = root.querySelector('[data-datepicker="end"]');

    if (start && end) {
        const endPicker = flatpickr(end, {
            dateFormat: 'Y-m-d',
            minDate: new Date().fp_incr(1),
            disable: readDisabledDates(end),
        });

        flatpickr(start, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: readDisabledDates(start),
            onChange: ([date]) => {
                if (!date) return;
                // Departure must be at least the night after arrival.
                const next = new Date(date.getTime() + 86400000);
                endPicker.set('minDate', next);
                if (!endPicker.selectedDates[0] || endPicker.selectedDates[0] <= date) {
                    endPicker.setDate(next, true);
                }
            },
        });
    }

    root.querySelectorAll('[data-datepicker="range"]').forEach((el) => {
        const form = el.closest('form');
        const checkIn = form?.querySelector('input[name="check_in"]');
        const checkOut = form?.querySelector('input[name="check_out"]');

        flatpickr(el, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: readDisabledDates(el),
            onChange: (dates, _str, instance) => {
                if (dates.length !== 2) {
                    if (checkIn) checkIn.value = '';
                    if (checkOut) checkOut.value = '';
                    el.dispatchEvent(new CustomEvent('stay:cleared', { bubbles: true }));
                    return;
                }

                const [from, to] = dates;
                if (checkIn) checkIn.value = instance.formatDate(from, 'Y-m-d');
                if (checkOut) checkOut.value = instance.formatDate(to, 'Y-m-d');

                el.dispatchEvent(new CustomEvent('stay:selected', {
                    bubbles: true,
                    detail: { checkIn: checkIn?.value, checkOut: checkOut?.value },
                }));
            },
        });
    });
}

/**
 * Nights the property cannot be booked, rendered into the markup by the server
 * so the calendar and the availability service always agree.
 */
function readDisabledDates(el) {
    try {
        return JSON.parse(el.dataset.disabledDates || '[]');
    } catch {
        return [];
    }
}

document.addEventListener('DOMContentLoaded', () => initDatePickers());

window.initDatePickers = initDatePickers;

/**
 * Google sign-in, loaded on demand.
 *
 * The import lives here, inside a bundled module, because Vite can only
 * code-split an import it can see at build time — a dynamic import written in an
 * inline <script> in a Blade file is just a runtime path it cannot resolve.
 * Writing it here gets the Firebase SDK its own chunk, fetched the moment someone
 * actually clicks the button and never before.
 */
window.loadGoogleSignIn = () => import('./google-signin.js');

// Last, now that every helper the markup can reach is registered.
Alpine.start();
