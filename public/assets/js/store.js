(() => {
    const note = document.querySelector('#note');
    const count = document.querySelector('#note-count');
    if (note && count) note.addEventListener('input', () => { count.textContent = `${note.value.length}/150`; });

    const fulfillmentRadios = document.querySelectorAll('input[name=fulfillment_type]');
    const deliveryFields = document.querySelector('#delivery-fields');
    if (fulfillmentRadios.length && deliveryFields) {
        const syncDelivery = () => { const delivery = document.querySelector('input[name=fulfillment_type]:checked')?.value === 'delivery'; deliveryFields.hidden = !delivery; deliveryFields.querySelectorAll('select,textarea').forEach((field) => { field.required = delivery; }); };
        fulfillmentRadios.forEach((radio) => radio.addEventListener('change', syncDelivery));
        syncDelivery();
    }

    const variant = document.querySelector('#variant');
    const quantity = document.querySelector('#quantity');
    const availability = document.querySelector('#availability');
    if (variant && quantity && availability) {
        const sync = () => {
            const selected = variant.selectedOptions[0];
            const stock = Number(selected?.dataset.stock || 0);
            quantity.max = String(Math.max(stock, 1));
            quantity.value = String(Math.min(Number(quantity.value || 1), Math.max(stock, 1)));
            availability.textContent = stock > 0 ? `Tersedia ${stock} bolu untuk ukuran ini` : 'Stok sedang habis';
        };
        variant.addEventListener('change', sync);
        sync();
    }
})();