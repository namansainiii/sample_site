/* Small progressive enhancements; every form still works without JavaScript. */
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function toast(message) {
        const el = document.getElementById('toast');
        if (!el) return;
        el.textContent = message;
        el.classList.add('show');
        clearTimeout(el._timer);
        el._timer = setTimeout(() => el.classList.remove('show'), 3200);
    }
    window.storeToast = toast;

    // Quantity steppers: <div class="qty"><button data-step="-1">…<input>…<button data-step="1"></div>
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.qty [data-step]');
        if (!button) return;
        const input = button.parentElement.querySelector('input');
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '100000', 10);
        const next = Math.min(max, Math.max(min, (parseInt(input.value, 10) || min) + parseInt(button.dataset.step, 10)));
        input.value = next;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Add-to-cart forms submit via fetch so the shopper stays on the page.
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-ajax-cart]');
        if (!form) return;
        event.preventDefault();
        const button = form.querySelector('[type=submit]');
        button && (button.disabled = true);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: new FormData(form),
            });
            const data = await response.json();
            toast(data.message || (response.ok ? 'Added to cart' : 'Could not add to cart'));
            const badge = document.getElementById('cartCount');
            if (badge && typeof data.count === 'number') {
                badge.textContent = data.count;
                badge.hidden = data.count === 0;
            }
        } catch (e) {
            form.removeAttribute('data-ajax-cart');
            form.submit();
        } finally {
            button && (button.disabled = false);
        }
    });

    // Cart quantity inputs auto-submit their row form.
    document.querySelectorAll('form[data-autosubmit] input').forEach((input) => {
        input.addEventListener('change', () => input.form.requestSubmit());
    });

    // Confirm destructive actions.
    document.addEventListener('submit', (event) => {
        const message = event.target.dataset.confirm;
        if (message && !window.confirm(message)) event.preventDefault();
    });
})();
